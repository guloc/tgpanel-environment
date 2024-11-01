<?php
class Bot_model extends CI_Model
{
    public $log = false;

    public $id;
    public $token;
    private $mode = 'common';
    private $api_url = 'https://api.telegram.org/bot';
    private $commands = [
        [ 'command' => 'single', 'description' => 'Простой режим ответов на запросы' ],
        [ 'command' => 'paraphrase', 'description' => 'Перефразирование большого текста' ],
        [ 'command' => 'context_on', 'description' => 'Режим диалога вкл.' ],
        [ 'command' => 'context_off', 'description' => 'Режим диалога выкл.' ],
        [ 'command' => 'image', 'description' => 'Режим генерации изображений' ],
    ];
    public $media_extensions = [
        'photo' => [ 'jpg', 'jpeg', 'png' ],
        'video' => [ 'mp4', 'mpg', 'mpeg', 'avi', 'mov' ],
        'audio' => [ 'mp3', 'm4u' ],
    ];
    private $filesize_limit = 50000000;
    private $post_attempts = 3;

    function __construct() {
        parent::__construct();
        $this->token = $this->config_model->get('telegram_bot_token');
        $this->id = explode(':', $this->token)[0];
    }

    function setMode($mode) {
        $this->mode = $mode;
    }

    public function exec_curl_request($handle)
    {
        $requests = cached_data('tg_requests');
        if (empty($requests)) {
            $requests = [
                'second' => [
                    'dt' => date('Y-m-d H:i:s'),
                    'count' => 0,
                ],
                'minute' => [
                    'dt' => date('Y-m-d H:i'),
                    'count' => 0,
                ],
            ];
        }
        if ($requests['second']['dt'] != date('Y-m-d H:i:s')) {
            $requests['second'] = [
                'dt' => date('Y-m-d H:i:s'),
                'count' => 0,
            ];
        }
        if ($requests['minute']['dt'] != date('Y-m-d H:i')) {
            $requests['minute'] = [
                'dt' => date('Y-m-d H:i'),
                'count' => 0,
            ];
        }
        $requests['second']['count']++;
        $requests['minute']['count']++;
        cache_data('tg_requests', $requests);

        $response = curl_exec($handle);

        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            if ($this->mode != 'silent') {
                logg("Curl returned error $errno: $error");
            }
            curl_close($handle);
            return false;
        }
        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);
        if ($http_code >= 500) {
            sleep(10);
            return false;
        } else if ($http_code != 200) {
            $response = json_decode($response, true);
            if ($this->mode != 'silent')  {
                logg("Request has failed with error {$response['error_code']}: {$response['description']}");
            }
            if ($http_code == 401) {
                logg('Invalid access token provided');
            }
            return $response;
        } else {
            $response = json_decode($response, true);
            $response = $response['result'];
        }
        return $response;
    }
    
    public function api_request($method, $parameters=[])
    {
        if (RS_MODE == 'development')
            return false;
        $url = $this->api_url . $this->token . '/';
        if ( ! is_string($method)) {
            if ($this->mode != 'silent') {
                logg("Method name must be a string");
            }
            return false;
        }
        if ( ! $parameters) {
            $parameters = array();
        } else if ( ! is_array($parameters)) {
            if ($this->mode != 'silent') {
                logg("Parameters must be an array");
            }
            return false;
        }
        $parameters["method"] = $method;
        
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        $result = $this->exec_curl_request($handle);

        if (empty($result) or ! empty($result['error_code'])) {
            if ( ! empty($result['description'])
              and preg_match(
                    '/Too Many Requests\: retry after (\d+)/',
                    $result['description'],
                    $match
                  )
            ) {
                data_log("Waiting {$match[1]} seconds before retry...", [
                    'request' => $parameters,
                    'requests_count' => cached_data('tg_requests')
                ]);
                sleep($match[1]);
                return $this->api_request($method, $parameters);
            }
        }

        return $result;
    }

    public function set_webhook($webhook_url)
    {
        $token = $this->config_model->get('telegram_bot_token');
        $api_url = $this->api_url . $token . '/setWebhook';
        $post_fields = [ 'url' => $webhook_url ];
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $api_url); 
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_fields);
        $this->exec_curl_request($handle);
        $this->token = $token;
        $this->api_request('setMyCommands', [ 'commands' => $this->commands ]);
    }

    public function get_self()
    {
        $me = cached_data('bot_info');
        if ( ! $me) {
            $me = $this->api_request('getMe');
            cache_data('bot_info', $me, 3600);
        }
        return $me;
    }

    public function get_user($chat_id, $user)
    {
        $userinfo = $this->db->where('uid', $user['id'])
                             ->where('chat_id', $chat_id)
                             ->get('tg_user')
                             ->row_array();
        if ($userinfo) {
            $this->db->where('uid', $user['id'])
                     ->where('chat_id', $chat_id)
                     ->update('tg_user', [
                        'last_seen' => now()
                     ]);
        } else {
            $this->db->insert('tg_user', [
                'uid'        => $user['id'],
                'chat_id'    => $chat_id,
                'first_name' => @$user['first_name'],
                'last_name'  => @$user['last_name'],
                'username'   => @$user['username'],
            ]);
            $userinfo = $this->db->where('uid', $user['id'])
                                 ->where('chat_id', $chat_id)
                                 ->get('tg_user')
                                 ->row_array();
        }
        return $userinfo;
    }

    public function set_state($chat_id, $state, $params=false)
    {
        $data = [ 'state' => $state ];
        if ($params !== false) {
            $data['parameters'] = $params === null
                                ? null
                                : base64_encode(json($params, false));
        }
        $this->db->where('chat_id', $chat_id)
                 ->update('tg_user', $data);
    }

    public function add_context($chat_id, $text, $role='user')
    {
        if ($role == 'system')
            $this->clear_context($chat_id);
        $this->db->insert('bot_context', [
            'chat_id' => $chat_id,
            'role'    => $role,
            'text'    => $text
        ]);
    }

    public function get_context($chat_id, $limit=true)
    {
        if ($limit) {
            $data = $this->db->where('chat_id', $chat_id)
                             ->order_by('id', 'asc')
                             ->get('bot_context')
                             ->result_array();
            $text = '';
            foreach ($data as $item)
                $text .= "\n" . $item['text'];
            if (mb_strlen($text) > 2000 or count($data) > 20) {
                $this->db->where('chat_id', $chat_id)
                         ->where('role !=', 'system')
                         ->order_by('id', 'asc')
                         ->limit(2)
                         ->delete('bot_context');
            }
        }
        $context = [];
        $result = $this->db->where('chat_id', $chat_id)
                           ->order_by('id', 'asc')
                           ->get('bot_context')
                           ->result_array();
        foreach ($result as $row) {
            $context []= [
                'role' => $row['role'],
                'content' => $row['text'],
            ];
        }
        return $context;
    }

    public function clear_context($chat_id)
    {
        $this->db->where('chat_id', $chat_id)
                 ->delete('bot_context');
    }

    public function send_message($chat_id, $text, $extra_params=[])
    {
        $params = [
            'chat_id' => $chat_id,
            'text' => $text
        ] + $extra_params;
        return $this->api_request('sendMessage', $params);
    }

    public function send_html($chat_id, $text, $extra_params=[])
    {
        $extra_params['parse_mode'] = 'HTML';
        if ( ! isset($extra_params['disable_web_page_preview']))
            $extra_params['disable_web_page_preview'] = true;
        return $this->send_message($chat_id, tg_html($text), $extra_params);
    }

    public function send_media($chat_id, $text, $links, $extra_params=[], $moderation=false)
    {
        $url = $this->api_url . $this->token . '/sendMediaGroup';
        $post_fields = $extra_params;
        $post_fields['chat_id'] = $chat_id;
        $post_fields['media'] = '';
        $media_group = [];
        $other_files = [];

        $media_total_size = 0;
        $files_total_size = 0;
        foreach ($links as $link) {
            if (media_type($link) == 'unknown')
                $files_total_size += filesize($link);
            else
                $media_total_size += filesize($link);
        }

        $i = 1;
        foreach ($links as $link) {
            preg_match('/\.(\w+)$/', $link, $match);
            $media = [
                'media' => "attach://file{$i}.{$match[1]}"
            ];
            if (empty($media_group) and ! empty(trim(strip_tags($text)))) {
                if (mb_strlen(trim(strip_tags($text))) <= 1010) {
                    $media['caption'] = tg_html($text);
                    $media['parse_mode'] = 'HTML';
                } elseif ($moderation) {
                    $media['caption'] = '<b>[' . lang('too_long_text') . ']</b>';
                    $media['parse_mode'] = 'HTML';
                }
            }
            foreach ($this->media_extensions as $type => $extensions)
                if (in_array(strtolower($match[1]), $extensions))
                    $media['type'] = $type;
            if (empty($media['type'])) {
                if ($files_total_size > $this->filesize_limit) {
                    $file_id = $this->madeline_model->send_file_to_bot($link);
                    if ( ! empty($file_id)) {
                        $other_files []= [
                            'type' => 'document',
                            'media' => $file_id,
                        ];
                    }
                } else {
                    $other_files []= [
                        'type' => 'document',
                        'media' => "attach://file{$i}.{$match[1]}",
                        'file' =>  new CURLFile(realpath($link)),
                    ];
                }
                continue;
            }
            if ($media_total_size > $this->filesize_limit) {
                $media['media'] = $this->madeline_model->send_file_to_bot($link);
            } else {
                $post_fields["file{$i}.{$match[1]}"] = new CURLFile(realpath($link));
            }
            $media_group []= $media;
            $i++;
        }
        if ($this->log)
            data_log([
                'media_group' =>$media_group,
                'other_files' =>$other_files,
            ]);
        if ( ! empty($media_group)) {
            $chunks = array_chunk($media_group, 10);
            foreach ($chunks as $chunk) {
                $post_fields['media'] = json_encode($chunk);
                $handle = curl_init(); 
                curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
                curl_setopt($handle, CURLOPT_URL, $url); 
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1); 
                curl_setopt($handle, CURLOPT_POSTFIELDS, $post_fields);

                $result = $this->exec_curl_request($handle);

                if ($this->log)
                    data_log('media', [
                        'request' => $post_fields,
                        'result' => $result
                    ]);

            }
        }

        if (empty($other_files))
            return $result ?? null;

        if (empty($media_group) and ! empty(trim(tg_html($text)))) {
            $other_files[0]['caption'] = tg_html($text);
            $other_files[0]['parse_mode'] = 'HTML';
        }

        $post_fields = [
            'chat_id' => $chat_id,
            'media'   => '',
        ];
        if ($files_total_size <= $this->filesize_limit) {
            foreach ($other_files as &$file) {
                $file_name = str_replace('attach://', '', $file['media']);
                $post_fields[$file_name] = $file['file'];
                unset($file['file']);
            }
        }

        $chunks = array_chunk($other_files, 10);
        foreach ($chunks as $chunk) {
            $post_fields['media'] = json_encode($other_files);
            $handle = curl_init(); 
            curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
            curl_setopt($handle, CURLOPT_URL, $url); 
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post_fields);
            
            $result = $this->exec_curl_request($handle);

            if ($this->log)
                data_log('documents', [
                    'request' => $post_fields,
                    'result' => $result
                ]);
        }
        return $result;
    }

    public function send_post($chat_id, $post, $extra_params=[])
    {
        $result = false;
        $post_files = unjson($post->files);
        // Try to send
        if ( ! empty($post_files) and is_array($post_files)) {
            $links = [];
            foreach ($post_files as $file_name) {
                $links []= "assets/upload/{$post->user_id}/{$file_name}";
            }
            try {
                $result = $this->send_media(
                    $chat_id,
                    $post->content,
                    $links,
                    $extra_params,
                    $post->status == 'moderation'
                );
            } catch (\Throwable $e) {
                logg("#{$post->id} ERROR: " . $e->getMessage());
            }
        } else {
            $result = $this->send_html($chat_id, $post->content, $extra_params);
        }
        // Save result
        $new_params = [
            'status' => 'posted'
        ];
        if (empty($result) or ! empty($result['error_code'])) {
            if ( ! empty($result['description'])
              and preg_match(
                    '/Too Many Requests\: retry after (\d+)/',
                    $result['description'],
                    $match
                  )
            ) {
                data_log("Waiting {$match[1]} seconds before retry...", [
                    'post' => $post->id,
                    'requests_count' => cached_data('tg_requests')
                ]);
                sleep($match[1]);
                return $this->send_post($chat_id, $post, $extra_params);
            }
            $log_msg = empty($result)
                     ? "WARNING: Posting #{$post->id} has empty result. "
                     : "WARNING: Posting #{$post->id} returned error. ";
            $new_params['attempt'] = ($post->attempt ?? 0) + 1;
            if ($new_params['attempt'] >= $this->post_attempts) {
                $new_params['status'] = 'draft';
                $log_msg .= 'Post moved to drafts';
            } else {
                $new_params['status'] = 'queued';
                $log_msg .= 'Post returned to queue';
            }
            logg($log_msg);
        }
        if ($post->status == 'moderation') {
            unset($new_params['status']);
            $new_params['updated_at'] = now();
        }
        $this->post_model->update($post->id, $new_params);
        if ($post->status != 'moderation')
            sleep(1);
    }

    public function send_for_moderation($post)
    {
        $post = (object) $post;
        $moderator_id = $this->config_model->get('posts_moderator');
        if (empty($moderator_id)) {
            logg('ERROR: Moderator id is not set. Post #{$post->id} was stopped');
            return;
        }
        $link = '<a href="' . RS_SITE_URL . '/posting?id=' . $post->id .'">'
              . '#' . $post->id
              . '</a>';
        $this->send_message($moderator_id, lang('post') . ' ' . $link . ':', [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ]);
        $this->send_post($moderator_id, $post);
        $this->send_message($moderator_id, sprintf(lang('approve_or_decline'), $link), [
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => "✅ " . lang('approve'),
                            'callback_data' => "approve{$post->id}"
                        ],
                        [
                            'text' => "❌ " . lang('decline'),
                            'callback_data' => "decline{$post->id}"
                        ],
                    ]
                ]
            ],
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ]);
        $this->post_model->update($post->id, [
            'updated_at' => now()
        ]);
        sleep(1);
    }

    public function del_message($chat_id, $message_id)
    {
        return $this->api_request('deleteMessage', [
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ]);
    }

    public function get_admins($chat_id)
    {
        $admins = cached_data($chat_id . 'admins');
        if (empty($admins)) {
            $admins = [];
            $data = $this->api_request('getChatAdministrators', [
                'chat_id' => $chat_id
            ]);
            if (empty($data['ok'])) {
                data_log('ERROR: ' . $chat_id, $data);
                $admins = [];
            } else {
                foreach ($data as $item) {
                    if ( ! empty(@$item['user']['id']))
                        $admins []= $item['user']['id'];
                }
            }
            cache_data($chat_id . 'admins', $admins, 1800);
        }
        return $admins;
    }

    public function ban_user($chat_id, $user_id)
    {
        $result = $this->api_request('banChatMember', [
            'chat_id' => $chat_id,
            'user_id' => $user_id
        ]);
        if ( ! empty($result['ok'])) {
            $group = $this->db->where('uid', $chat_id)->get('group')->row();
            if ($group) {
                $stats = unjson($group->stats);
                $stats['banned']++;
                $this->db->where('uid', $chat_id)->update('group', [
                    'stats' => json_encode($stats)
                ]);
            }
            $this->db->where('uid', $user_id)->update('tg_user', [
                'banned' => 1
            ]);
        }
        return $result;
    }

    public function unban_user($chat_id, $user_id)
    {
        $result = $this->api_request('unbanChatMember', [
            'chat_id' => $chat_id,
            'user_id' => $user_id
        ]);
        if ( ! empty($result['ok'])) {
            $group = $this->db->where('uid', $chat_id)->get('group')->row();
            if ($group) {
                $stats = unjson($group->stats);
                $stats['banned']--;
                $this->db->where('uid', $chat_id)->update('group', [
                    'stats' => json_encode($stats)
                ]);
            }
            $this->db->where('uid', $user_id)->update('tg_user', [
                'banned' => 0
            ]);
        }
        return $result;
    }

    public function mute_user($chat_id, $user_id, $time=false)
    {
        $params = [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'permissions' => [
                'can_send_messages' => false,
                'can_send_audios' => false,
                'can_send_documents' => false,
                'can_send_photos' => false,
                'can_send_videos' => false,
                'can_send_video_notes' => false,
                'can_send_polls' => false,
                'can_send_other_messages' => false,
                'can_add_web_page_previews' => false,
                'can_change_info' => false,
                'can_invite_users' => false,
                'can_pin_messages' => false,
                'can_manage_topics' => false,
            ],
        ];
        if ($time)
            $params['until_date'] = time() + $time;
        $result = $this->api_request('restrictChatMember', $params);
        if ( ! empty($result['ok'])) {
            $group = $this->db->where('uid', $chat_id)->get('group')->row();
            if ($group) {
                $stats = unjson($group->stats);
                $stats['muted']++;
                $this->db->where('uid', $chat_id)->update('group', [
                    'stats' => json_encode($stats)
                ]);
            }
            $this->db->where('uid', $user_id)->update('tg_user', [
                'muted_for' => $time
                             ? date('Y-m-d H:i:s', $params['until_date'])
                             : '2100-01-01 00:00:00'
            ]);
        }
        return $result;
    }

    public function unmute_user($chat_id, $user_id, $time=false)
    {
        $params = [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'permissions' => [
                'can_send_messages' => true,
                'can_send_audios' => true,
                'can_send_documents' => true,
                'can_send_photos' => true,
                'can_send_videos' => true,
                'can_send_video_notes' => true,
                'can_send_polls' => true,
                'can_send_other_messages' => true,
                'can_add_web_page_previews' => true,
                'can_change_info' => true,
                'can_invite_users' => true,
                'can_pin_messages' => true,
                'can_manage_topics' => true,
            ],
        ];
        if ($time)
            $params['until_date'] = time() + $time;
        $result = $this->api_request('restrictChatMember', $params);
        if ( ! empty($result['ok'])) {
            $group = $this->db->where('uid', $chat_id)->get('group')->row();
            if ($group) {
                $stats = unjson($group->stats);
                $stats['muted']--;
                $this->db->where('uid', $chat_id)->update('group', [
                    'stats' => json_encode($stats)
                ]);
            }
            $this->db->where('uid', $user_id)->update('tg_user', [
                'muted_for' => null
            ]);
        }
        return $result;
    }

}