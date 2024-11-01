<?php
require_once(APPPATH.'libraries/VkSDK/vendor/autoload.php');

class Vk_model extends CI_Model
{
    private $log = false;

    public $client;
    public $config;
    public $redirect_uri = RS_SITE_URL . '/settings/vk_auth';
    public $link_regex = '/\s*(https\:\/\/)?(m\.)?vk\.com\/(\w+)/';
    public $parsing_limits = [
        840  => 0,
        2700  => 10,
        5400  => 100,
        10200 => 200,
        19800 => 300,
        41400 => 400,
        84600 => 500,
    ];

    function __construct() {
        parent::__construct();
        $this->config = $this->config_model->get('vk_config', true);
    }

    public function connect()
    {
        $client_id = $this->config_model->get('vk_config', true)['app_id'];
        $state = RS_KEY;
        $oauth = new \VK\OAuth\VKOAuth();
        $display = \VK\OAuth\VKOAuthDisplay::PAGE;
        $scope = [
            \VK\OAuth\Scopes\VKOAuthUserScope::OFFLINE,
            \VK\OAuth\Scopes\VKOAuthUserScope::WALL,
            \VK\OAuth\Scopes\VKOAuthUserScope::GROUPS,
            \VK\OAuth\Scopes\VKOAuthUserScope::STATS,
            \VK\OAuth\Scopes\VKOAuthUserScope::PHOTOS,
            \VK\OAuth\Scopes\VKOAuthUserScope::VIDEO,
            \VK\OAuth\Scopes\VKOAuthUserScope::AUDIO,
            \VK\OAuth\Scopes\VKOAuthUserScope::DOCS,
        ];
        $browser_url = $oauth->getAuthorizeUrl(
            \VK\OAuth\VKOAuthResponseType::CODE,
            $client_id,
            $this->redirect_uri,
            $display,
            $scope,
            $state
        );
        die(header('Location: ' . $browser_url));
    }

    public function access_token()
    {
        if ( ! empty($this->config['access_token'])
          and ! empty($this->config['access_token_expires'])
          and $this->config['access_token_expires'] > now()
        ) {
            return $this->config['access_token'];
        }

        $oauth = new \VK\OAuth\VKOAuth();
        $client_id = $this->config['app_id'];
        $client_secret = $this->config['private_key'];
        $code = $this->config['code'];

        $response = $oauth->getAccessToken($client_id, $client_secret, $this->redirect_uri, $code);
        if ($this->log)
            data_log($response);
        $expires = $response['expires_in'] == 0
                 ? time_jump(now(), "+10 years")
                 : time_jump(now(), "+{$response['expires_in']} seconds");
        $this->config['user_id'] = $response['user_id'];
        $this->config['access_token'] = $response['access_token'];
        $this->config['access_token_expires'] = $expires;
        $this->config_model->update('vk_config', json($this->config));
        
        return $response['access_token'];
    }

    public function client()
    {
        if (empty($this->client))
            $this->client = new \VK\Client\VKApiClient();
        return $this->client;
    }

    public function resolve($screen_name)
    {
        $access_token = $this->access_token();
        $client = $this->client();
        $response = $client->utils()->resolveScreenName($access_token, [
            'screen_name' => $screen_name
        ]);
        if ($this->log)
            data_log($response);
        return $response;
    }

    public function check_permissions($access_token)
    {
        $response = $this->client()->groups()->getTokenPermissions($access_token);
        if ($this->log)
            data_log('Response', $response);
        return array_column($response['permissions'], 'name');
    }

    public function group_info($group_id)
    {
        $access_token = $this->access_token();
        $client = $this->client();
        $response = $client->groups()->getById($access_token, [
            'group_id' => str_replace('-', '', $group_id),
            'fields' => [ 'members_count' ]
        ]);
        if ($this->log)
            data_log($response);
        return $response[0];
    }

    public function join_group($group_id)
    {
        $access_token = $this->access_token();
        $client = $this->client();
        $response = $client->groups()->join($access_token, [
            'group_id' => str_replace('-', '', $group_id)
        ]);
    }

    public function parse_posts($group, $count=10, $create_posts=true)
    {
        $access_token = $this->access_token();
        $client = $this->client();
        $config = unjson($group->config);

        // Get
        try {
            $response = $client->wall()->get($access_token, [
                'owner_id' => $group->uid,
                'count'    => $count
            ]);
            if ($this->log)
                data_log('wall.get', $response);
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
            logg('ERROR: ' . $error_msg);
            if (strpos($error_msg, 'limit') !== false)
                cache_var('vk_parsing_stopped', 1, 86400);
            return;
        }

        // Handle
        $last_post_id = 0;
        $posts = [];
        foreach ($response['items'] as $item) {
            if ($item['marked_as_ads'])
                continue;
            $post = [
                'user_id' => $group->user_id,
                'source_id' => $group->id,
                'uid' => $item['id'] . $item['owner_id'],
                'name' => empty($item['text'])
                        ? 'Media ' . $item['id']
                        : ( mb_strlen($item['text']) > 30
                            ? mb_substr($item['text'], 0, 30) . '...'
                            : $item['text']
                          ),
                'status' => 'draft',
            ];
            // Check duplicate
            $check = $this->db->where('uid', $post['uid'])
                              ->count_all_results('post');
            if ($check > 0)
                continue;
            // Text
            if (@$config['data']['text']) {
                $post['content'] = $item['text'];
                // Start words
                $stop = false;
                if ( ! empty(@$config['start_words'])) {
                    foreach ($config['start_words'] as $word) {
                        if (mb_strpos($post['content'], $word) === false)
                            $stop = true;
                    }
                }
                if ($stop)
                    continue;
                // Stop words
                $stop = false;
                if ( ! empty(@$config['stop_words'])) {
                    foreach ($config['stop_words'] as $word) {
                        if (mb_strpos($post['content'], $word) !== false)
                            $stop = true;
                    }
                }
                if ($stop)
                    continue;
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
                // Paraphrase
                if (@$config['paraphrase'] and ! empty(trim(strip_tags($post['content'])))) {
                    $text = ai_get_text(str_ireplace('{usertext}', $post['content'], $config['paraphrase']));
                    if ($text == $this->llm_model->error_text) {
                        logg('ERROR: Could not paraphrase');
                    } else {
                        $post['content'] = $text;
                    }
                }
                // Remove tags
                if (@$config['remove_tags']) {
                    $regex = '/#\w+ */';
                    $post['content'] = preg_replace($regex, '', $post['content']);
                }
                // Remove links
                if (@$config['remove_links']) {
                    $regex = $this->info_model->url_regex;
                    $post['content'] = preg_replace($regex, '', $post['content']);
                }
                // Text format
                $post['content'] = tg_html(trim($post['content']), true);
                // Subscript
                if ( ! empty(trim(strip_tags($config['subscript']))))
                    $post['content'] .= $config['subscript'];
            }
            // Media
            $post['files'] = [];
            foreach ($item['attachments'] as $attach) {
                if ($attach['type'] == 'poll')
                    continue 2;
                // Photo
                if (@$config['data']['image'] and $attach['type'] == 'photo') {
                    $name = $attach['photo']['id'] . $attach['photo']['owner_id'] . '.jpg';
                    $post['files'] []= $name;
                    $file = @file_get_contents($attach['photo']['orig_photo']['url']);
                    @file_put_contents("assets/upload/{$group->user_id}/{$name}", $file);
                }
                // Video
                elseif (@$config['data']['video'] and $attach['type'] == 'video') {
                    if (empty($post['video']))
                        $post['video'] = [];
                    $video_id = $attach['video']['owner_id'] . '_' . $attach['video']['id'];
                    if ( ! empty($attach['video']['access_key']))
                        $video_id .= '_' . $attach['video']['access_key'];
                    $post['video'] []= $video_id;
                }
                // Audio
                elseif (@$config['data']['audio'] and $attach['type'] == 'audio') {
                    $name = '';
                    if ( ! empty($attach['audio']['artist']))
                        $name = $attach['audio']['artist'];
                    if ( ! empty($attach['audio']['title'])) {
                        if ( ! empty($name))
                            $name .= ' - ';
                        $name .= $attach['audio']['title'];
                    }
                    if (empty($name))
                        $name = $attach['audio']['id'] . $attach['audio']['owner_id'];
                    $name .= '.mp3';
                    $post['files'] []= $name;
                    $file = @file_get_contents($attach['audio']['url']);
                    @file_put_contents("assets/upload/{$group->user_id}/{$name}", $file);
                }
                // Documents
                elseif (@$config['data']['files'] and $attach['type'] == 'doc') {
                    $name = $attach['doc']['title'];
                    $file = @file_get_contents($attach['doc']['url']);
                    @file_put_contents("assets/upload/{$group->user_id}/{$name}", $file);
                    $post['files'] []= $name;
                }
            }
            // Download videos
            if ( ! empty($post['video'])) {
                $videos = $client->video()->get($access_token, [
                    'videos' => implode(',', $post['video'])
                ]);
                if (empty($videos) or empty($videos['items'])) {
                    data_log('ERROR: Can\'t get videos', $videos);
                } else {
                    if ($this->log)
                        data_log('video.get', $videos);
                    foreach ($videos['items'] as $item) {
                        $name = $item['id'] . $item['owner_id'] . '.mp4';
                        $path = realpath('assets/upload/' . $group->user_id) . '/';
                        $player_html = @file_get_contents($item['player']);
                        preg_match('/"hls"\:"([^"]+)"/', $player_html, $match);
                        if (empty(@$match[1])) {
                            data_log("ERROR: Playlist not found", $item);
                        } else {
                            $hls_url = str_replace('\\/', '/', $match[1]);
                            $output = shell_exec("ffmpeg -i \"{$hls_url}\" -c copy {$path}{$name}");
                            if (stripos($output, 'error') !== false
                              or stripos($output, 'failed') !== false
                            ) {
                                logg("ERROR: Video decoding failed. Output:\n{$output}");
                            } else {
                                $post['files'] []= $name;
                            }
                        }
                    }
                }
                unset($post['video']);
            }
            // Autopost
            if ( ! empty($config['autopost']) and is_numeric($config['autopost'])) {
                $post['channel_id'] = @$config['autopost'];
                $post['status'] = @$config['moderation']
                                ? 'moderation'
                                : 'queued';
                $post['pub_date'] = now();
            }
            if (empty(trim(strip_tags($post['content']))) and empty($post['files']))
                continue;
            $posts []= $post;
            if ($item['id'] > $last_post_id)
                $last_post_id = $item['id'];
        }
        array_reverse($posts);
        if ($create_posts) {
            foreach ($posts as $post) {
                $post['files'] = json_encode($post['files']);
                $post_id = $this->post_model->create($post);
            }
            if ($last_post_id > 0) {
                $this->db->where('id', $group->id)->update('channel', [
                    'last_post_id' => $last_post_id
                ]);
            }
        } else {
            return $posts;
        }
    }

    public function post($group, $post)
    {
        $client = $this->client();

        $post_data = [
            'owner_id'   => $group->uid,
            'from_group' => 1,
            'signed'     => 0,
        ];
        $text = vk_format($post->content);
        if ( ! empty($text))
            $post_data['message'] = $text;
        $post_files = unjson($post->files);
        if ( ! empty($post_files) and is_array($post_files))
        {
            $post_data['attachments'] = '';
            foreach ($post_files as $filename)
            {
                $type = media_type($filename);
                if ($type == 'unknown')
                    $type = 'doc';
                
                if ($type == 'photo') {
                    $media = $this->upload_photo($group, $post->user_id . '/' . $filename);
                    sleep(1);
                }
                else if ($type == 'video') {
                    $media = $this->upload_video($group, $post->user_id . '/' . $filename);
                    sleep(1);
                }
                else if ($type == 'doc') {
                    $media = $this->upload_doc($group, $post->user_id . '/' . $filename);
                    sleep(1);
                }
                else {
                    continue;
                }

                if (empty($media))
                    continue;

                $attach = $type . $media['owner_id'] . '_' . $media['id'];
                $post_data['attachments'] .= (empty($post_data['attachments']) ? '' : ',')
                                           . $attach;
            }
        }
        $post_data['guid'] = md5(json_encode($post_data));

        if ($this->log)
            data_log('Post #' . $post->id, $post_data);

        if ( (empty($post_data['message']) or empty(trim($post_data['message'])))
          and empty($post_data['attachments'])
        ) {
            logg("Post #{$post->id} skipped because it is empty");
            $this->post_model->update($post->id, [
                'status' => 'posted'
            ]);
        } else {
            $response = $client->wall()->post($group->access_token, $post_data);
            if ($this->log)
                data_log('Response', $response);

            if ( ! empty(@$response['post_id']))
                $this->post_model->update($post->id, [
                    'status' => 'posted'
                ]);
        }

        sleep(1);
    }

    public function upload_photo($channel, $filepath)
    {
        $access_token = $this->access_token();
        $client = $this->client();

        // Get upload server address
        $upload_server = $client->photos()->getWallUploadServer($access_token, [
            'group_id' => substr($channel->uid, 1)
        ]);
        if (empty($upload_server['upload_url'])) {
            data_log('Can\'t get upload server', $upload_server);
            return false;
        }
        if ($this->log)
            data_log('getWallUploadServer', $upload_server);

        // Upload photo
        $photo = $client->getRequest()->upload(
            $upload_server['upload_url'],
            'photo',
            'assets/upload/' . $filepath
        );
        if (empty($photo['photo']) or empty($photo['server']) or empty($photo['hash'])) {
            data_log('Can\'t upload photo', $photo);
            return false;
        }
        if ($this->log)
            data_log('upload', $photo);

        // Save uploaded photo
        $result = $client->photos()->saveWallPhoto($access_token, [
            'group_id' => substr($channel->uid, 1),
            'photo' => $photo['photo'],
            'server' => $photo['server'],
            'hash' => $photo['hash'],
        ]);
        if (empty($result[0]) or empty($result[0]['id']) or empty($result[0]['owner_id'])) {
            data_log('Can\'t save photo', $result);
            return false;
        }
        if ($this->log)
            data_log('saveWallPhoto', $result);

        return $result[0];
    }

    public function upload_video($channel, $filepath, $name='')
    {
        $access_token = $this->access_token();
        $client = $this->client();
        
        // Get upload server address
        $video_data = [
            'group_id' => substr($channel->uid, 1),
            'wallpost' => 0,
        ];
        if ( ! empty($name))
            $video_data['name'] = $name;
        $upload_server = $client->video()->save($access_token, $video_data);
        if (empty($upload_server['upload_url'])) {
            data_log('Can\'t get upload server', $upload_server);
            return false;
        }
        if ($this->log)
            data_log('save', $upload_server);

        // Upload video

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $upload_server['upload_url']);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'video_file' => new CURLFile('assets/upload/' . $filepath)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, TRUE);
        $result = unjson(curl_exec($ch));
        curl_close($ch);

        if (empty($result['video_id']) or empty($result['video_hash']) or empty($result['owner_id'])) {
            data_log('Can\'t upload video', $result);
            return false;
        }
        if ($this->log)
            data_log('upload', $result);

        $result['id'] = $result['video_id'];

        return $result;
    }

    public function upload_doc($channel, $filepath)
    {
        $access_token = $this->access_token();
        $client = $this->client();

        // Get upload server address
        $upload_server = $client->docs()->getWallUploadServer($access_token, [
            'group_id' => substr($channel->uid, 1)
        ]);
        if (empty($upload_server['upload_url'])) {
            data_log('Can\'t get upload server', $upload_server);
            return false;
        }
        if ($this->log)
            data_log('getWallUploadServer', $upload_server);

        // Upload document
        $doc = $client->getRequest()->upload(
            $upload_server['upload_url'],
            'file',
            'assets/upload/' . $filepath
        );
        if (empty($doc['file'])) {
            data_log('Can\'t upload document', $doc);
            return false;
        }
        if ($this->log)
            data_log('upload', $doc);

        // Save uploaded document
        $result = $client->docs()->save($access_token, [
            'file' => $doc['file']
        ]);
        if (empty($result['doc']) or empty($result['doc']['id']) or empty($result['doc']['owner_id'])) {
            data_log('Can\'t save document', $result);
            return false;
        }
        if ($this->log)
            data_log('save', $result);

        return $result['doc'];
    }

}