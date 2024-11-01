<?php
require_once(APPPATH.'libraries/MadelineProto/vendor/autoload.php');
require_once(APPPATH.'libraries/TelegramEntityParser.php');

class Madeline_model extends CI_Model
{
    function __construct() {
        parent::__construct();
    }

    public $client;
    private $log = false;

    private $entity_types = [
        'messageEntityTextUrl' => 'text_link',
        'messageEntityBold' => 'bold',
        'messageEntityItalic' => 'italic',
        'messageEntityUnderline' => 'underline',
        'messageEntityStrike' => 'strikethrough',
        'messageEntityCode' => 'code',
        'messageEntityPre' => 'pre',
        'messageEntityBlockquote' => 'blockquote',
        'messageEntitySpoiler' => 'spoiler',
        // skip
        'messageMediaWebPage' => false,
        'messageEntityCustomEmoji' => false,
        'messageEntityHashtag' => false,
        'messageEntityMention' => false,
        'messageEntityUrl' => false,
        'messageEntityBotCommand' => false,
    ];
    private $needless_media = [
        'messageMediaWebPage',
        'messageMediaPoll',
    ];

    public function check_auth()
    {
        if (empty($this->client))
            $client = new \danog\MadelineProto\API('session.madeline');
        else
            $client = $this->client;
        return $client->getSelf();
    }

    public function start($login=false)
    {
        if ( ! empty($this->client))
            return;
        if (cached_var('tg_auth_problem'))
            $this->die_error('Auth stopped');

        $settings = new \danog\MadelineProto\Settings;
        $logger_settings = (new \danog\MadelineProto\Settings\Logger)
            ->setLevel(0);
        $tpl_settings = (new \danog\MadelineProto\Settings\Templates)
            ->setHtmlTemplate(@file_get_contents('tpl/base/static/tg_auth.html'));
        $settings->merge($logger_settings);
        $settings->merge($tpl_settings);
        $this->client = new \danog\MadelineProto\API('session.madeline', $settings);
        try {
            $this->client->start();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            logg($msg);
            cache_var('tg_auth_problem', 1, 3600);
        }
    }

    public function logout()
    {
        $this->client()->logout();
    }

    public function client()
    {
        $this->start();
        return $this->client;
    }

    public function get_self()
    {
        return $this->client()->getSelf();
    }

    public function join_channel($channel_link)
    {
        $this->client()->channels->joinChannel(channel: '@' . $channel_link);
        $this->client()->account->updateNotifySettings(
            peer: [
                '_' => 'inputNotifyPeer',
                'peer' => $channel_link,
            ],
            settings: [
                '_' => 'inputPeerNotifySettings',
                'silent' => false,
                'mute_until' => 0
            ]
        );
    }
    public function leave_channel($channel_link)
    {
        $this->client()->channels->leaveChannel(channel: '@' . $channel_link);
    }

    public function get_posts($channel_link, $extra_params=[])
    {
        if (cached_var('STOP PARSING')) {
            logg('Parsing stopped. Please check logs');
            die();
        }
        $params = [
            'peer'        => '@' . $channel_link,
            'offset_id'   => 0,
            'offset_date' => 0,
            'add_offset'  => 0,
            // 'limit'       => 10,
            'max_id'      => 0,
            'min_id'      => 0,
            'hash'        => 0
        ];
        foreach ($params as $key => $value)
            if (isset($extra_params[$key]))
                $params[$key] = $extra_params[$key];
        try {
            $result = $this->client()->messages->getHistory($params);
        } catch (Exception $e) {
            logg('ERROR: ' . $e->getMessage());
            cache_var('STOP PARSING', 1, 86400);
            $this->bot_model->send_message('190626814', [
                'text' => 'Madeline ERROR: ' . $e->getMessage()
            ]);
            $result = $e->getMessage();
        }
        return $result;
    }

    public function entities_to_html($text, $entities, $remove_links=false, $remove_tags=false)
    {
        if ($remove_tags)
            $text = preg_replace('/#\w+/', '', $text);
        $entity_types = array_keys($this->entity_types);
        $new_entities = [];
        foreach ($entities as $entity) {
            if (empty($this->entity_types[$entity['_']])
              or ($remove_links and $entity['_'] == 'messageEntityTextUrl')
            )
                continue;
            $new_entity = [
                'offset' => $entity['offset'],
                'length' => $entity['length'],
                'type' => $this->entity_types[$entity['_']],
            ];
            if ($entity['_'] == 'messageEntityTextUrl')
                $new_entity['url'] = $entity['url'];
            $new_entities []= $new_entity;
        }
        return htmlspecialchars_decode(entitiesToHtml($text, $new_entities));
    }

    public function handle_messages($channel, $messages)
    {
        $config = unjson($channel->config);
        $posts = [];
        $groups = [];
        $last_post_id = $channel->last_post_id;
        foreach ($messages as $msg) {
            if ($msg['_'] !== 'message')
                continue;
            $post = [ 'uid' => $msg['id']];
            // Message
            if ( ! empty($msg['message']) and @$config['data']['text']) {
                // Start words
                $stop = false;
                if ( ! empty(@$config['start_words'])) {
                    foreach ($config['start_words'] as $word) {
                        if (mb_strpos($msg['message'], $word) === false)
                            $stop = true;
                    }
                }
                if ($stop)
                    continue;
                // Stop words
                $stop = false;
                if ( ! empty(@$config['stop_words'])) {
                    foreach ($config['stop_words'] as $word) {
                        if (mb_strpos($msg['message'], $word) !== false)
                            $stop = true;
                    }
                }
                if ($stop)
                    continue;
                $post['content'] = $msg['message'];
                // Entities
                if (isset($msg['entities'])) {
                    $post['content'] = $this->entities_to_html(
                        $post['content'],
                        $msg['entities'],
                        @$config['remove_links'],
                        @$config['remove_tags']
                    );
                    $post['entities'] = [];
                    foreach ($msg['entities'] as $entity) {
                        if ( ! in_array($entity['_'], array_keys($this->entity_types)))
                            if ($this->log)
                                logg('New entity: ' . $entity['_']);
                        if ( ! @$this->entity_types[$entity['_']])
                            continue;
                    }
                }
                // Replaces
                if ( ! empty(@$config['replaces'])) {
                    foreach ($config['replaces'] as $replace) {
                        $post['content'] = str_ireplace(
                            $replace['from'],
                            $replace['to'],
                            $post['content']
                        );
                    }
                }
                $post['content'] = tg_html($post['content'], true);
                // Paraphrase
                if ($config['paraphrase'] and ! empty(trim(strip_tags($post['content'])))) {
                    $text = ai_get_text(str_ireplace('{usertext}', $post['content'], $config['paraphrase']));
                    if ($text == $this->llm_model->error_text) {
                        logg('ERROR: Could not paraphrase');
                    } else {
                        $post['content'] = $text;
                    }
                }
                // Subscript
                if ( ! empty(trim(strip_tags($config['subscript']))))
                    $post['content'] .= $config['subscript'];
            }
            // Media
            if (isset($msg['media'])) {
                if ( (isset($msg['media']['ext']) and isset($msg['media']['id']))
                  or ! in_array($msg['media']['_'], $this->needless_media)
                ) {
                    $file_name = '';
                    if (isset($msg['media']['name']) and isset($msg['media']['id'])) {
                        $file_name = $msg['media']['name'];
                        $msg['media'] = $msg['media']['id'];
                    }
                    $file_path = $this->madeline_model->client()->downloadToDir($msg['media'], 'assets/upload/'.$channel->user_id.'/');
                    if ( ! empty($file_name)) {
                        preg_match('/([^\/]+\.\w+)$/i', $file_path, $match);
                        if ($match[1] !== $file_name) {
                            preg_match('/(assets\/upload\/\d+\/.+)$/', $file_path, $match);
                            $file_path_old = $match[1];
                            $file_path_new = preg_replace('/[^\/]+\\.\w+$/', $file_name, $file_path_old);
                            try {
                                $rename_result = rename($file_path_old, $file_path_new);
                            } catch (\Throwable $e) {
                                logg('ERROR: ' . $e->getMessage);
                                $rename_result = false;
                            }
                            if ( ! $rename_result) {
                                data_log([
                                    'file_path' => $file_path,
                                    'file_path_old' => $file_path_old,
                                    'file_path_new' => $file_path_new,
                                ]);
                            }
                            $file_path = '/' . $file_path_new;
                        }
                    }
                    if ( (media_type($file_path) == 'photo' and @$config['data']['image'])
                      or (media_type($file_path) == 'video' and @$config['data']['video'])
                      or (media_type($file_path) == 'audio' and @$config['data']['audio'])
                      or (media_type($file_path) == 'unknown' and @$config['data']['files'])
                    ) {
                        preg_match('/assets\/upload\/\d+\/(.+)$/', $file_path, $match);
                        $post['media'] = $match[1];
                    } else {
                        preg_match('/(assets\/upload\/\d+\/.+)$/', $file_path, $match);
                        unlink($match[1]);
                    }
                }
            }
            // Media group
            if (isset($msg['grouped_id'])) {
                $post['group'] = $msg['grouped_id'];
                if (empty($groups[$msg['grouped_id']]))
                    $groups[$msg['grouped_id']] = [ 'uid' => $msg['id'] ];
                elseif ($groups[$msg['grouped_id']]['uid'] < $msg['id'])
                    $groups[$msg['grouped_id']]['uid'] = $msg['id'];
                if (isset($post['content']))
                    $groups[$msg['grouped_id']]['content'] = $post['content'];
                if (isset($post['entities']))
                    $groups[$msg['grouped_id']]['entities'] = $post['entities'];
                if (isset($post['media'])) {
                    if (empty($groups[$msg['grouped_id']]['media']))
                        $groups[$msg['grouped_id']]['media'] = [];
                    $groups[$msg['grouped_id']]['media'] []= $post['media'];
                }
                if ( ! in_array($msg['grouped_id'], $posts))
                    $posts []= $msg['grouped_id'];
            } else {
                $posts []= $post;
            }
        }
        foreach ($groups as $id => $group) {
            if ( ! empty($group['media']))
                $group['media'] = array_reverse($group['media']);
            foreach ($posts as &$post) {
                if ($post === $id) {
                    $post = $group;
                    $post['grouped_id'] = $id;
                }
            }
        }
        $posts = array_reverse($posts);
        foreach ($posts as $item) {
            if (empty($item['content']) and empty($item['media']))
                continue;

            if ($last_post_id < $item['uid'])
                $last_post_id = $item['uid'];
            if ( ! empty($item['grouped_id']))
                $item['uid'] = $item['grouped_id'];
            usleep(mt_rand(1, 1000000));
            $check = $this->db->where('uid', $item['uid'])
                              ->where('source_id', $channel->id)
                              ->count_all_results('post');
            if ($check == 0) {
                if ( ! empty($item['media'])  and is_array($item['media']))
                    $item['media'] = array_values(array_unique($item['media']));
                $post_data = [
                    'uid' => $item['uid'],
                    'user_id' => $channel->user_id,
                    'source_id' => $channel->id,
                    'content' => $item['content'] ?? null,
                    'files' => empty($item['media'])
                             ? null
                             : (is_array($item['media'])
                                 ? json_encode($item['media'])
                                 : json_encode([$item['media']])
                               ),
                    'name' => empty($item['content'])
                            ? 'Media ' . $msg['id']
                            : ( mb_strlen(strip_tags($item['content'])) > 30
                                ? mb_substr(strip_tags($item['content']), 0, 30) . '...'
                                : strip_tags($item['content'])
                              ),
                    'status' => 'draft',
                ];
                if ( ! empty($config['autopost']) and is_numeric($config['autopost'])) {
                    $post_data['channel_id'] = $config['autopost'];
                    $post_data['status'] = @$config['moderation']
                                         ? 'moderation'
                                         : 'queued';
                    $post_data['pub_date'] = now();
                }

                if ($this->log)
                    data_log('Post', $post_data);

                $post_id = $this->post_model->create($post_data);
            }
        }
        $this->db->where('id', $channel->id)->update('channel', [
            'last_post_id' => $last_post_id
        ]);
    }

    public function parse_posts($channel, $extra_params=[])
    {
        $result = $this->madeline_model->get_posts($channel->link, [
            'min_id' => $channel->last_post_id
        ] + $extra_params);
        if ( ! isset($result['messages'])) {
            data_log('ERROR', $result);
        } else {
            if ($this->log)
                data_log('Response', $result);
            $this->handle_messages($channel, $result['messages']);
        }
    }

    public function get_info($id)
    {
        $info = $this->client()->getInfo($id);
        if ($this->log)
            data_log('ID: ' . $id, $info);
        return $info;
    }

    public function get_full_info($id)
    {
        $info = $this->client()->getFullInfo($id);
        if ($this->log)
            data_log('ID: ' . $id, $info);
        return $info;
    }

    public function get_raw_channel_stat($id)
    {
        try {
            return $this->client()->stats->getBroadcastStats(channel:$id);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_channel_graph($id)
    {
        $raw_stats = $this->get_raw_channel_stat($id);
        if (is_string($raw_stats))
            return $raw_stats;
        $stats = [
            'time' => 'x',
            'join' => '',
            'left' => '',
        ];
        foreach ($raw_stats['followers_graph']['json']['names'] as $item => $name) {
            if ($name == 'Joined')
                $stats['join'] = $item;
            if ($name == 'Left')
                $stats['left'] = $item;
        }
        foreach ($raw_stats['followers_graph']['json']['columns'] as $column) {
            $c = array_shift($column);
            foreach ($stats as $name => &$item) {
                if ($c == $item) {
                    $item = $column;
                }
            }
        }
        return array_slice(array_combine(
            array_map(
                fn($time): string => date('d/m', $time / 1000),
                $stats['time']
            ),
            array_map(
                function($join, $left) {
                    return [
                        'join' => $join,
                        'left' => $left
                    ];
                },
                $stats['join'],
                $stats['left']
             )
        ), -7);
    }

    public function start_bot()
    {
        $bot_name = $this->bot_model->get_self()['username'];
        $this->client()->sendMessage(peer: $bot_name, message: '/start');
    }

    public function send_file_to_bot($filepath)
    {
        $bot_name = $this->bot_model->get_self()['username'];
        $file = new danog\MadelineProto\LocalFile($filepath);
        $type = media_type($filepath);
        try {
            if ($type == 'photo') {
                $result = $this->client()->sendPhoto(peer: $bot_name, file: $file);
            } elseif ($type == 'video') {
                $result = $this->client()->sendVideo(peer: $bot_name, file: $file);
            } elseif ($type == 'audio') {
                $result = $this->client()->sendAudio(peer: $bot_name, file: $file);
            } else {
                $result = $this->client()->sendDocument(peer: $bot_name, file: $file);
            }
        } catch (\Throwable $e) {
            logg('ERROR: ' . $e->getMessage());
            return false;
        }
        if (empty($result) or empty($result->media) or empty($result->media->botApiFileId)) {
            data_log('ERROR: Can\'t send file', $result);
            return false;
        }
        if ($this->log)
            data_log([
                'filepath' => $filepath,
                'result' => $result->media
            ]);
        $file_id = $result->media->botApiFileId;
        $file_uid = $result->media->botApiFileUniqueId;
        if ($type == 'unknown') {
            for ($i = 0; $i < 10; $i++) {
                sleep(1);
                $bot_files = cached_data('bot_files');
                if ( ! empty($bot_files) and ! empty($bot_files[$file_uid])) {
                    $file_id = $bot_files[$file_uid];
                    break;
                }
            }
        }
        return $file_id;
    }

    public function send_media($chat_id, $text, $links, $extra_params=[])
    {
        $media_group = [];
        $other_files = [];

        $media_total_size = 0;
        $files_total_size = 0;
        $i = 1;
        foreach ($links as $link) {
            preg_match('/\.(\w+)$/', $link, $match);
            $type = 'inputMediaDocument';
            if (media_type($link) == 'photo')
                $type = 'inputMediaPhoto';
            if (media_type($link) == 'video')
                $type = 'inputMediaVideo';
            if (media_type($link) == 'audio')
                $type = 'inputMediaAudio';
            $file = new danog\MadelineProto\LocalFile($link);
            $media = [
                '_' => 'inputSingleMedia',
                'media' =>  [
                    '_' => $type,
                    'file' => $file
                ]
            ];
            if (empty($media_group) and ! empty($text)) {
                $media['media']['message'] = tg_html($text);;
                $media['media']['parse_mode'] = 'HTML';
            }
            if (media_type($link) == 'unknown') {
                $other_files []= [
                    '_' => 'inputSingleMedia',
                    'media' =>  [
                        '_' => $type,
                        'file' => $link
                    ]
                ];
                continue;
            }
            $media_group []= $media;
        }
        data_echo($media_group);
        $result = $this->client()->messages->sendMultiMedia(
            peer: $chat_id,
            multi_media: $media_group,
        );
    }

    private function die_error($message='')
    {
        logg('Error: ' . $message);
        die(header('Location: /parsing/error'));
    }

}