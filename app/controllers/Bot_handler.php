<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Bot_handler extends CI_Controller
{
    private $log_updates = false;

    public function __construct()
    {
        parent::__construct();
        $this->security_model->check_visit();
    }
    
    public function index()
    {
        if (empty($_GET['key']) or $_GET['key'] != RS_KEY)
            not_found();

        $content = file_get_contents("php://input");
        $update = json_decode($content, true);
        if ($this->log_updates)
            data_log($update);

        if (@$update['my_chat_member']['new_chat_member']['user']['id'] == $this->bot_model->id
          and @$update['my_chat_member']['new_chat_member']['status'] == 'administrator'
          and in_array(@$update['my_chat_member']['chat']['type'], ['group', 'supergroup'])
        ) {
            $chat = $update['my_chat_member']['chat'];
            $group = $this->db->where('uid', $chat['id'])
                              ->get('group')
                              ->row();
            if ( ! $group) {
                $this->bot_model->send_message($chat['id'], "Chat ID: <pre>{$chat['id']}</pre>", [
                    'parse_mode' => 'HTML'
                ]);
            }
        } elseif (isset($update['message']) or isset($update['edited_message'])) {
            $message = $update['edited_message'] ?? $update['message'];
            $chat = $message['chat'];
            if ( ! in_array($chat['type'], ['group', 'supergroup']))
                return $this->private_chat($update);
            $group = $this->db->where('uid', $chat['id'])
                              ->get('group')
                              ->row();
            if ( ! $group or ! $group->active)
                return;
            $cfg = unjson($group->config);
            if ($message['from']['id'] === $this->bot_model->id)
                return;
            $user = $this->bot_model->get_user($chat['id'], $message['from']);

            if (isset($message['new_chat_member'])) {
                $stats = unjson($group->stats);
                $stats['joined']++;
                $stats['members']++;
                $this->group_model->update($group->id, [
                    'stats' => json_encode($stats)
                ]);
                $restrict_time = @$cfg['joined_restrict']['time']
                               * @$cfg['joined_restrict']['mul'];
                if ($restrict_time > 0) {
                    $this->bot_model->mute_user($chat['id'], $user['uid'], $restrict_time);
                }
            }
            if (isset($message['left_chat_member'])) {
                $stats = unjson($group->stats);
                $stats['left']++;
                $stats['members']--;
                $this->group_model->update($group->id, [
                    'stats' => json_encode($stats)
                ]);
            }

            if (@$cfg['filter_admins'] === false) {
                $admins = $this->bot_model->get_admins($chat['id']);
                if (in_array($user['uid'], $admins))
                    return;
            }

            $entities = isset($message['entities'])
                      ? array_column($message['entities'], 'type')
                      : [];

            $text = (string) (@$message['text'] . @$message['caption']);
            $banword_detected = false;
            if ( ! empty($text)
              and ! empty(@$cfg['stop_words']['list'])
              and @$cfg['stop_words']['active']
            ) {
                foreach ($cfg['stop_words']['list'] as $word) {
                    if (mb_stripos($text, $word) !== false) {
                        $banword_detected = true;
                        break;
                    }
                }
            }

            if ( (@$cfg['messages']['images'] and isset($message['photo']))
              or (@$cfg['messages']['voices'] and isset($message['voice']))
              or (@$cfg['messages']['files'] and isset($message['document']))
              or (@$cfg['messages']['stickers'] and (isset($message['sticker']) or isset($message['animation'])))
              or (@$cfg['messages']['dices'] and isset($message['dice']))
              or (@$cfg['messages']['links'] and in_array('url', $entities))
              or (isset($message['forward_origin']) and (
                  @$cfg['forward']['all']
                  or (@$cfg['forward']['links'] and in_array('url', $entities))
                  or (@$cfg['forward']['media']
                    and ( isset($message['audio']) or isset($message['document'])
                       or isset($message['story']) or isset($message['video'])
                       or isset($message['voice']) or isset($message['photo'])
                     )
                   )
                )
              )
              or $banword_detected
            ) {
                $this->bot_model->del_message($chat['id'], $message['message_id']);
                $restrict_time = @$cfg['restrict']['time']
                               * @$cfg['restrict']['mul'];
                if ($restrict_time > 0) {
                    $this->bot_model->mute_user($chat['id'], $user['uid'], $restrict_time);
                }
            }
            if ( (@$cfg['messages']['bot_commands'] and in_array('bot_command', $entities))
              or (@$cfg['user_joined'] and isset($message['new_chat_member']))
              or (@$cfg['user_left'] and isset($message['left_chat_member']))
            ) {
                $this->bot_model->del_message($chat['id'], $message['message_id']);
            }
        } elseif (isset($update['callback_query'])) {
            $callback = $update['callback_query'];
            $this->bot_model->api_request('answerCallbackQuery', [
                'callback_query_id' => $callback['id']
            ]);
            $moderator_id = $this->config_model->get('posts_moderator');
            if (empty($moderator_id) or $moderator_id != $callback['message']['chat']['id']) {
                logg('ERROR: Moderator id mismatch');
                return;
            }
            if (preg_match('/^approve(\d+)$/', $callback['data'], $match)) {
                $this->db->where('id', $match[1])->update('post', [
                    'status' => 'queued',
                    'attempt' => 0
                ]);
                $this->bot_model->api_request('editMessageText', [
                    'chat_id' => $callback['message']['chat']['id'],
                    'message_id' => $callback['message']['message_id'],
                    'text' => sprintf(lang('post_approved'), $match[1]),
                    'parse_mode' => 'HTML',
                ]);
            } elseif (preg_match('/^decline(\d+)$/', $callback['data'], $match)) {
                $this->db->where('id', $match[1])->update('post', [
                    'status' => 'draft'
                ]);
                $this->bot_model->api_request('editMessageText', [
                    'chat_id' => $callback['message']['chat']['id'],
                    'message_id' => $callback['message']['message_id'],
                    'text' => sprintf(lang('post_declined'), $match[1]),
                    'parse_mode' => 'HTML',
                ]);
            } else {
                logg('ERROR: Wrong callback');
                return;
            }
        }
    }

    private function private_chat($update)
    {
        if (empty($update['message']))
            return false;

        $message = $update['message'];
        $chat_id = $message['chat']['id'];
        $user_id = $message['from']['id'];

        if ($message['chat']['type'] != 'private')
            return;
        
        // Document ids
        if (isset($message['document'])) {
            $uid = $message['document']['file_unique_id'];
            $file_id = $message['document']['file_id'];
            $bot_files = cached_data('bot_files');
            if (empty($bot_files))
                $bot_files = [];
            $bot_files[$uid] = $file_id;
            cache_data('bot_files', $bot_files);
            return;
        }

        // Private chat

        $allowed_users = $this->config_model->get('bot_users', true);
        if ( ! in_array($chat_id, $allowed_users)) {
            $msg = "Chat ID: <pre>{$chat_id}</pre>";
            $this->bot_model->send_message($chat_id, $msg, [
                'parse_mode' => 'HTML'
            ]);
            return;
        }

        $user = $this->bot_model->get_user($user_id, $message['from']);
        $state = $user["state"];

        // TODO:
        // if ( ! checkMessage($message))
        //     return;

        // Клавиатуры
        $remove_kb = ['reply_markup' => ['remove_keyboard' => true]];
        $kb_ok_cancel = ['keyboard' => [['✅ Готово'], [ '🔙 Назад' ] ], 'resize_keyboard' => true];
        
        if (isset($message['text'])) {
            $text = mb_strtolower($message['text']);

            // Старт
            if (strpos($text, "/start") === 0) {
                $msg = "Привет, я ChatGPT бот.\n"
                     . "Напишите свой запрос или воспользуйтесь переключением режимов в меню.\n\n"
                     . "/single - Простой режим ответов без контекста\n"
                     . "/paraphrase - Режим перефразирования большого текста\n"
                     . "/context_on - Включение режима диалога\n"
                     . "/context_off - Отключение режима диалога\n"
                     . "/image - Режим генерации изображений\n\n"
                     . "Эти команды всегда доступны в меню слева от поля ввода сообщения";
                $this->bot_model->send_message($chat_id, $msg);
            }
            // Простой режим
            else if ($text == '/single' or $text == '🔙 назад') {
                $this->bot_model->set_state($chat_id, '');
                $this->bot_model->clear_context($chat_id);
                $msg = 'Включен простой режим';
                $this->bot_model->send_message($chat_id, $msg, $remove_kb);
            }
            // Вкл контекст
            else if ($text == '/context_on') {
                $this->bot_model->set_state($chat_id, 'context');
                $this->bot_model->clear_context($chat_id);
                $msg = 'Режим диалога включен';
                $this->bot_model->send_message($chat_id, $msg, $remove_kb);
            }
            // Выкл контекст
            else if ($text == '/context_off') {
                $this->bot_model->set_state($chat_id, '');
                $this->bot_model->clear_context($chat_id);
                $msg = 'Режим диалога отключен';
                $this->bot_model->send_message($chat_id, $msg, $remove_kb);
            }
            // Режим генерации изображений
            else if ($text == '/image') {
                $this->bot_model->set_state($chat_id, 'image');
                $this->bot_model->clear_context($chat_id);
                $msg = 'Режим генерации изображений включен';
                $this->bot_model->send_message($chat_id, $msg, $remove_kb);
            }
            // Режим перефразирования
            else if ($text == '/paraphrase') {
                $this->bot_model->set_state($chat_id, 'paraphrase', null);
                $this->bot_model->clear_context($chat_id);
                $msg = 'Введите текст который нужно перефразировать и нажмите кнопку Готово';
                $this->bot_model->send_message($chat_id, $msg, ['reply_markup' => $kb_ok_cancel]);
            }
            // Текстовый запрос
            else {
                if ($state != 'paraphrase')
                   $this->bot_model->api_request('sendChatAction', [
                    'chat_id' => $chat_id,
                    'action' => 'typing'
                ]);
                // Генерация изображения
                if ($state == 'image') {
                    $image_url = $this->openai_model->get_image($message['text']);
                    $this->bot_model->api_request('sendPhoto', [
                        'chat_id' => $chat_id,
                        'photo' => $image_url
                    ]);
                // Работа с текстом для перефразирования
                } else if ($state == 'paraphrase') {
                    // Добавление текста
                    if ($text != '✅ готово') {
                        $this->bot_model->add_context($chat_id, $message['text']);
                        $msg = 'Сообщение записано. Если ввод текста закончен, нажмите кнопку Готово для перефразирования';
                        $this->bot_model->send_message($chat_id, $msg, ['reply_markup' => $kb_ok_cancel]);
                        return;
                    }
                    // Обработка запроса
                    $context = $this->bot_model->get_context($chat_id, false);
                    if (empty($context)) {
                        $msg = 'Введите текст который нужно перефразировать и нажмите кнопку Готово';
                        $this->bot_model->send_message($chat_id, $msg, ['reply_markup' => $kb_ok_cancel]);
                        return;
                    }
                    $this->bot_model->clear_context($chat_id);
                    $this->bot_model->set_state($chat_id, 'paraphrase_processing');
                    $this->bot_model->send_message($chat_id, 'Начинаю обработку...', $remove_kb);
                    $tpl = $this->config_model->get('bot_paraphrase_prompt');
                    $full_text = '';
                    foreach ($context as $item) {
                        $full_text .= ' ' . $item['content'];
                    }
                    $sentences = explode('.', $full_text);
                    $chunk = '';
                    foreach ($sentences as $sentence) {
                        $chunk .= trim($sentence) . '. ';
                        if (mb_strlen($chunk) > 3000) {
                            $this->bot_model->api_request('sendChatAction', [
                                'chat_id' => $chat_id,
                                'action' => 'typing'
                            ]);
                            $prompt = str_replace('{UserText1}', "\n" . $chunk, $tpl);
                            $msg = ai_get_text($prompt, '', 2500);
                            $this->bot_model->send_message($chat_id, $msg, ['reply_markup' => $kb_ok_cancel]);
                            $chunk = '';
                        }
                    }
                    if (mb_strlen($chunk) > 0) {
                        $this->bot_model->api_request('sendChatAction', [
                            'chat_id' => $chat_id,
                            'action' => 'typing'
                        ]);
                        $prompt = str_replace('{UserText1}', "\n" . $chunk, $tpl);
                        $msg = ai_get_text($prompt, '', 2500);
                        $this->bot_model->send_message($chat_id, $msg, ['reply_markup' => $kb_ok_cancel]);
                    }
                    $this->bot_model->set_state($chat_id, 'paraphrase');
                    $msg = "Если хотите перефразировать еще один текст, введите его и нажмите кнопку Готово.\n"
                         . 'Либо нажмите кнопку Назад для выхода в обычный режим.';
                    $this->bot_model->send_message($chat_id, $msg, ['reply_markup' => $kb_ok_cancel]);
                } else if ($state == 'paraphrase_processing') {
                    return;
                } else {
                    // Запрос с контекстом
                    if ($state == 'context') {
                        $context = $this->bot_model->get_context($chat_id);
                        $msg = ai_get_text($message['text'], $context);
                        $this->bot_model->add_context($chat_id, $message['text'], 'user');
                        $this->bot_model->add_context($chat_id, $msg, 'assistant');
                    }
                    // Простой запрос
                    else {
                        $msg = ai_get_text($message['text']);
                    }
                    $extra_params = [];
                    if (strpos($msg, '```') !== false) {
                        $extra_params['parse_mode'] = 'Markdown';
                    }
                    $this->bot_model->send_message($chat_id, $msg, $extra_params);
                }
            }

        }
        else {
            $msg = "Я понимаю только текст";
            $this->bot_model->send_message($chat_id, $msg);
        }

    }

}
