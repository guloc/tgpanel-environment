<?php

class Wp_model extends CI_Model
{
    private $log = false;
    public $link_regex = '/^\s*(https\:\/\/)?([^\/]+)/';
    public $title_prompt = 'Create a SEO title based on this text no more than 60 characters. The title text should be in the same language as the text. The response should contain only the title itself';
    public $default_title = 'Новый пост';

    function __construct() {
        parent::__construct();
    }

    public function request($url, $login, $key, $headers=[], $post_fields=null, $post_custom=false)
    {
        $headers [] = 'Authorization: Basic '
                    . base64_encode("{$login}:{$key}");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($post_fields) {
            if ($post_custom)
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            else
                curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = [
            'response' => curl_exec($ch)
        ];
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($this->log) {
            $response = unjson($result['response'])
                      ? unjson($result['response'])
                      : $result['response'];
            data_log([
                'request' => [
                    'url' => $url,
                    'headers' => $headers,
                    'post' => $post_fields,
                ],
                'result' => [
                    'http_code' => $result['http_code'],
                    'response' => $response
                ]
            ]);
        }

        return (object) $result;
    }

    public function check_auth($domain, $login, $key)
    {
        $url = "https://{$domain}/wp-json/wp/v2/users/me";
        $result = $this->request($url, $login, $key);

        if ($result->http_code == 200) {
            $user_data = unjson($result->response);
            if (isset($user_data['id'])) {
                return true;
            } else {
                logg('User data not found. Response: ' . $result->response);
            }
        } else {
            logg('Authentication error');
        }
        return false;
    }

    public function create_post($site, $post_data)
    {
        $url = "https://{$site->link}/wp-json/wp/v2/posts";
        $result = $this->request(
            $url,
            $site->access_name,
            $site->access_token,
            [ 'Content-Type: application/json' ],
            json_encode($post_data)
        );
        if ($result->http_code === 201) {
            $post = unjson($result->response);
            return $post['id'];
        } else {
            throw new Exception("Media upload error: {$result->http_code} - {$result->response}");
        }
    }

    public function upload_media($site, $file_path)
    {
        $url = "https://{$site->link}/wp-json/wp/v2/media";
        $file_name = basename($file_path);
        $mime_type = mime_content_type($file_path);
        $result = $this->request(
            $url,
            $site->access_name,
            $site->access_token,
            [ "Content-Disposition: form-data; filename=\"{$file_name}\"" ],
            [ 'file' => new CURLFile($file_path, $mime_type, $file_name) ]
        );
        if ($result->http_code != 201)
            throw new Exception("Media upload error: {$result->http_code} - {$result->response}");
        return unjson($result->response);
    }

    public function set_featured_media($site, $post_id, $media_id)
    {
        $url = "https://{$site->link}/wp-json/wp/v2/posts/{$post_id}";
        $data = [ 'featured_media' => $media_id ];
        $result = $this->request(
            $url,
            $site->access_name,
            $site->access_token,
            [ 'Content-Type: application/json' ],
            json_encode($data),
            true
        );
        if ($result->http_code != 200)
            throw new Exception("Featured media error: {$result->http_code} - {$result->response}");
    }

    public function update_post($site, $post_id, $content)
    {
        $url = "https://{$site->link}/wp-json/wp/v2/posts/{$post_id}";
        $data = [ 'content' => $content ];
        $result = $this->request(
            $url,
            $site->access_name,
            $site->access_token,
            [ 'Content-Type: application/json' ],
            json_encode($data),
            true
        );
        if ($result->http_code != 200)
            throw new Exception("Post update error: {$result->http_code} - {$result->response}");
    }

    public function post($site, $post)
    {
        if (empty(trim(strip_tags($post->content))))
            throw new Exception("Post content is empty");

        // Create title
        $context = [
            [
                'role' => 'system',
                'content' => $this->title_prompt
            ],
        ];
        $title = ai_get_text(strip_tags($post->content), $context, 50);
        if ($title == $this->llm_model->error_text) {
            logg("Could not create title for post #{$post->id}. Using default title");
            $title = $this->default_title . ' ' . date('Y-m-d H:i:s');
        }
        $title = preg_replace('/^"(.+)"$/', '$1', $title);

        // Create post
        $post_data = [
            'title'     => $title,
            'content'   => $post->content,
            'status'    => 'publish',
            'post_type' => 'post',
        ];
        $post_id = $this->create_post($site, $post_data);
        $this->post_model->update($post->id, [
            'status' => 'posted'
        ]);

        // Upload files and update post
        $gallery_html = "\n\n<!-- wp:html -->\n<div class='custom-gallery'>\n";
        $post_files = unjson($post->files);
        $files_count = 0;
        if ( ! empty($post_files) and is_array($post_files)) {
            $first = true;
            $files_count = count($post_files);
            foreach ($post_files as $file_name) {
                $type = media_type($file_name);
                if ($type != 'photo')
                    continue;
                $file_path = "assets/upload/{$post->user_id}/{$file_name}";
                $media_data = $this->upload_media($site, $file_path);
                if ($first) {
                    $this->set_featured_media($site, $post_id, $media_data['id']);
                    $first = false;
                }
                $gallery_html .= "<figure class='wp-block-image size-large'>
                                    <img src='{$media_data['source_url']}'
                                       class='wp-image-{$media_data['id']}'
                                       data-full-url='{$media_data['source_url']}'
                                       onclick='openFullImage(this)'
                                       style='cursor:pointer;'
                                    />
                                  </figure>";
            }
        }
        $gallery_html .= "</div>";
        $gallery_html .= "<script>
                            function openFullImage(img) {
                                var fullUrl = img.getAttribute('data-full-url');
                                window.open(fullUrl, '_blank');
                            }
                          </script>\n";
        $gallery_html .= "<!-- /wp:html -->";
        $gallery_html .= '<style>
                            .custom-gallery {
                                display:flex;
                                flex-wrap: wrap;
                                justify-content: space-between;
                            }
                            .wp-block-image {
                                width: calc(25% - 10px);
                                margin-bottom: 10px;
                            }
                          </style>';
        if ($files_count > 1) {
            $updated_content = $post->content . $gallery_html;
            $this->update_post($site, $post_id, $updated_content);
        }

        sleep(1);
    }

}