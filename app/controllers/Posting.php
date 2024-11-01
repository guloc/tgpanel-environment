<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Posting extends CI_Controller
{
    private $allowed_types = [];
    private $max_filesize = 20000000;
    private $per_page = 20;

    public function __construct()
    {
        parent::__construct();
        if ( ! RS_STATUS)
            $this->load->view('technical_works');
        $this->security_model->check_visit();
        $this->security_model->check_auth();
        $ext_arrays = array_values($this->bot_model->media_extensions);
        foreach ($ext_arrays as $arr) {
            $this->allowed_types = array_merge($this->allowed_types, $arr);
        }
    }
    
    public function index($data=array())
    {
        $data = $this->info_model->get_basic_info($data);

        if ( ! empty($_POST['create'])) {
            $post = $_POST['create'];
            if (empty(trim(@$post['name']))) {
                $data['message'] = lang('incorrect_input');
            } else {
                if ( ! empty($post['channel'])) {
                    $channel = $this->db->where('id', $post['channel'])
                                        ->where('user_id', $data['userinfo']->id)
                                        ->where('type', 'target')
                                        ->get('channel')
                                        ->row();
                }
                if ( ! $channel)
                    $post['channel'] = null;
                $post_id = $this->post_model->create([
                    'name' => $post['name'],
                    'user_id' => $data['userinfo']->id,
                    'channel_id' => $post['channel'],
                    'status' => 'draft',
                ]);
                if (empty($post_id)) {
                    $data['message'] = lang('something_goes_wrong');
                } else {
                    die(header('Location: /posting?id=' . $post_id));
                }
            }
        }

        if ( ! empty($_GET['id'])) {
            $data['post'] = $this->db->where('id', $_GET['id'])
                                     ->where('user_id', $data['userinfo']->id)
                                     ->get('post')
                                     ->row();
            if (empty($data['post']))
                $data['message'] = lang('post_not_found');
        }

        $data['channels'] = $this->channel_model->page(1, 0, [
            'user_id'  => $data['userinfo']->id,
            'type'     => 'target',
            'order_by' => 'subs DESC'
        ]);
        $data['allowed_types'] = $this->allowed_types;
        $data['media_extensions'] = $this->bot_model->media_extensions;
        $data['max_filesize'] = $this->max_filesize;
        $this->load->view('posting', $data);
        $this->security_model->debugger($data);
    }

    public function content()
    {
        $user = $this->user_model->get();
        if ( ! empty($_GET['id']) and is_numeric($_GET['id'])) {
            $post = $this->db->where('id', $_GET['id'])
                             ->where('user_id', $user->id)
                             ->get('post')
                             ->row();
        }
        if (empty($post))
            not_found();
        $content = safe($post->content);
        echo "<pre style='white-space: pre-wrap;'>{$content}</pre>";
    }

    public function get()
    {
        $user = $this->user_model->get();
        $post_id = @$_GET['id'];
        $post = $this->db->where('id', $post_id)
                                 ->where('user_id', $user->id)
                                 ->get('post')
                                 ->row();
        if (empty($post))
            die(json_encode([
                'result' => 'error',
                'error' => lang('post_not_found')
            ]));
        echo json_encode([
            'result' => $post
        ]);
    }

    public function delete()
    {
        $post_id = (int) $_POST['id'] ?? 0;
        $post = $this->post_model->get($post_id);
        $user = $this->user_model->get();
        if (empty($post) or $post->user_id != $user->id)
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $result = $this->post_model->delete($post_id);
        if ($result === false)
            echo json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]);
        else
            echo json_encode([ 'result' => 'ok' ]);
    }

    public function clear_all()
    {
        $status = $_POST['status'] ?? '';
        $user = $this->user_model->get();
        if (empty($status))
            $posts = $this->db->where('user_id', $user->id)
                              ->get('post')
                              ->result();
        else
            $posts = $this->db->where('status', $status)
                              ->where('user_id', $user->id)
                              ->get('post')
                              ->result();
        $result = false;
        foreach ($posts as $post)
            $result = $this->post_model->delete($post->id) or $result;
        if ($result === false) {
            echo json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]);
        } else {
            if (empty($status))
                logg("Deleted all posts");
            else
                logg("Deleted all posts with {$status} status");
            echo json_encode([ 'result' => 'ok' ]);
        }
    }

    public function edit()
    {
        $post = @$_POST['post'];
        $user = $this->user_model->get();

        if ( ! empty($post['pub_now'])) {
            $post['status'] = 'queued';
            $post['pub_date'] = time_jump(now(), '+5 seconds');
        }

        if (empty($post['name']))
            $post['name'] = mb_substr(trim(strip_tags($post['content'])), 0, 50);
        if (empty($post['name']))
            die(json_encode([
                'result' => 'error',
                'error' => lang('post_name_empty')
            ]));
        if ( ! empty($post['id'])) {
            $post_old = $this->post_model->get(@$post['id']);
            if (empty($post_old) or $post_old->user_id != $user->id)
                die(json_encode([
                    'result' => 'error',
                    'error' => lang('post_not_found')
                ]));
        }
        if (empty($post['status']) or ! in_array($post['status'], ['draft', 'queued', 'posted', 'moderation'])
          or empty($post['pub_date']) or ! is_valid('datetime', $post['pub_date'])
        )
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        if ( ! empty($post['channel_id'])) {
            $channel = $this->db->where('id', @$post['channel_id'])
                                ->where('user_id', $user->id)
                                ->where('type', 'target')
                                ->get('channel')
                                ->row();
            if (empty($channel))
                die(json_encode([
                    'result' => 'error',
                    'error' => lang('channel_not_found')
                ]));
        }
        if ($post['status'] == 'queued') {
            if ($post['pub_date'] <= now())
                die(json_encode([
                    'result' => 'error',
                    'error' => lang('set_future_date')
                ]));
            if (empty($channel))
                die(json_encode([
                    'result' => 'error',
                    'error' => lang('set_channel')
                ]));
            if (empty(trim(strip_tags($post['content']))) and empty(unjson($post['files'])))
                die(json_encode([
                    'result' => 'error',
                    'error' => lang('post_is_empty')
                ]));
        }
        if (empty($post['id'])) {
            $post['id'] = $this->post_model->create([
                'user_id' => $user->id,
                'name' => $post['name'],
                'content' => @$post['content'],
                'pub_date' => $post['pub_date'] == '0000-00-00 00:00:00'
                            ? null
                            : $post['pub_date'],
                'status' => $post['status'],
                'channel_id' => $channel->id ?? null,
                'files' => $post['files'] ? $post['files'] : null,
            ]);
            if (empty($post['id']))
                die(json_encode([
                    'result' => 'error',
                    'error' => lang('something_goes_wrong')
                ]));
        } else {
            $this->post_model->update($post['id'], [
                'name' => @$post['name'] ?? '',
                'content' => @$post['content'],
                'pub_date' => $post['pub_date'] == '0000-00-00 00:00:00'
                            ? null
                            : $post['pub_date'],
                'status' => $post['status'],
                'channel_id' => $channel->id ?? null,
                'files' => $post['files'] ? $post['files'] : null,
            ]);
        }
        $result = [
            'result' => 'ok',
            'id' => $post['id'],
            'name' => $post['name'],
            'channel_name' => @$channel->name ?? '',
        ];
        if ( ! empty($post['pub_now']))
            $result['pub_date'] = $post['pub_date'];
        echo json_encode($result);
    }

    public function posts()
    {
        $user = $this->user_model->get();
        $per_page = (isset($_GET['per_page']) and is_valid('uint', $_GET['per_page']))
                     ? $_GET['per_page']
                     : $this->per_page;
        $page = (empty($_GET['page']) or ! is_valid('uint', $_GET['page']))
              ? 1
              : $_GET['page'];

        $filter = [
            'user_id' => $user->id,
            'order_by' => 'pub_date DESC, updated_at DESC'
        ];
        if ( ! empty($_GET['status'])
          and in_array($_GET['status'], $this->post_model->status_list)
        ) {
            $filter['status'] = $_GET['status'];
        }

        $posts_page = $this->post_model->page($page, $per_page, $filter);

        $channel_ids = array_values(array_unique(
            array_filter(
                array_column($posts_page, 'channel_id'),
                function($item) {
                    return ! empty($item);
                }
            )
        ));
        $channels = empty($channel_ids)
                  ? []
                  : $this->db->where_in('id', $channel_ids)
                             ->get('channel')
                             ->result();
        $channel_names = array_combine(
            array_column($channels, 'id'),
            array_column($channels, 'name')
        );

        $posts = [];
        foreach ($posts_page as $item) {
            $posts []= [
                'id' => $item->id,
                'name' => $item->name,
                'content' => $item->content,
                'channel_id' => $item->channel_id,
                'channel_name' => empty($item->channel_id)
                                ? ''
                                : ( isset($channel_names[$item->channel_id])
                                    ? $channel_names[$item->channel_id]
                                    : lang('channel_not_found')
                                  ),
                'status' => $item->status,
                'pub_date' => $item->pub_date,
                'files' => unjson($item->files),
            ];
        }
        echo json_encode([
            'result' => $posts
        ]);
    }

    public function upload()
    {
        $user = $this->user_model->get();
        if ( ! isset($_FILES['file']))
            die(json_encode([
                'error' => lang('incorrect_input')
            ]));
        $file_names = [];
        $tmp = $_FILES['file'];
        $files = array_map(
            function($name, $tmp_name, $size) {
                return [
                    'name' => $name,
                    'tmp_name' => $tmp_name,
                    'size' => $size,
                ];
            },
            $tmp['name'],
            $tmp['tmp_name'],
            $tmp['size']
        );
        foreach ($files as $file) {
            // Check size
            if ($file['size'] > $this->max_filesize)
                die(json_encode([
                    'error' => lang('file_too_big')
                ]));
            // Check extension
            preg_match('/\.([A-Za-z0-9-_]+)$/', $file['name'], $filext);
            $filext = $filext[1];
            if ( ! in_array(strtolower($filext), $this->allowed_types))
                die(json_encode([
                    'error' => lang('file_type_incorrect')
                ]));
            // Load file
            $file_name = preg_replace('/[^\d]/', '', now())
                       . '_' . mt_rand(1000, 9999)
                       . '.' . $filext;
            $file_path = 'assets/upload/' . $user->id . '/';
            if ( ! file_exists($file_path))
                mkdir($file_path, '0755');
            if (is_writable($file_path))
                copy($file['tmp_name'], $file_path . $file_name);
            $file_names []= $file_name;
        }

        echo json_encode([
            'result' => $file_names
        ]);
    }

    public function send_all()
    {
        $this->security_model->only_admin();

        $this->post_model->send_queued();
        
        die(json_encode([
            'result' => 'ok'
        ]));
    }

}
