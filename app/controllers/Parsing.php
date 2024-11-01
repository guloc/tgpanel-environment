<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Parsing extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ( ! RS_STATUS)
            $this->load->view('technical_works');
        $this->security_model->check_visit();
        $this->security_model->check_auth();
    }
    
    public function index($data=array())
    {
        $data = $this->info_model->get_basic_info($data);

        if ( ! empty($_POST['channel'])) {
            $channel = $_POST['channel'];
            if ($channel['platform'] == 'telegram') {
                $links_input = str_replace('https://t.me/', '@', @$channel['links']);
                $links_input = str_replace([',', ';'], ' ', $links_input);
                preg_match_all('/@(\w+)/', $links_input, $match);
                $links = @$match[1];
            } else if ($channel['platform'] == 'vk') {
                $links_input = str_replace('https://', '', @$channel['links']);
                $links_input = str_replace([',', ';'], ' ', $links_input);
                preg_match_all($this->vk_model->link_regex, $links_input, $match);
                $links = @$match[3];
            }
            $active = ! empty($channel['active']);
            $config = $this->channel_model->default_config;
            if ( ! empty($channel['autopost'])) {
                $target_channel = $this->db->where('user_id', $data['userinfo']->id)
                                           ->where('type', 'target')
                                           ->where('id', $channel['autopost'])
                                           ->get('channel')
                                           ->row();
                if (empty($target_channel)) {
                    $data['message'] = lang('incorrect_input');
                } else {
                    $config['autopost'] = $target_channel->id;
                }
            }
            if (empty($links) or ! empty($data['message'])) {
                $data['message'] = lang('incorrect_input');
            } else {
                $data['extra_messages'] = [];
                foreach ($links as $channel_link) {
                    $check = $this->db->where('link', $channel_link)
                                      ->where('type', 'source')
                                      ->where('platform', $channel['platform'])
                                      ->count_all_results('channel');
                    if ($check) {
                        $data['extra_messages'] []= [
                            'text' => lang('channel_exists')
                        ];
                    } else {
                        if ($channel['platform'] == 'telegram') {
                            $info = $this->madeline_model->get_info('@' . $channel_link);
                            if (empty($info['type']) or $info['type'] !== 'channel'
                              or empty(@$info['Chat']['title'])
                            ) {
                                data_log('Unknown Info type', $info);
                                $data['extra_messages'] []= [
                                    'text' => lang('cant_get_channel_info')
                                ];
                            } else {
                                $channel_name = $info['Chat']['title'];
                                $id = $this->channel_model->create([
                                    'user_id'  => $data['userinfo']->id,
                                    'uid'      => $info['channel_id'],
                                    'name'     => $channel_name,
                                    'link'     => $channel_link,
                                    'type'     => 'source',
                                    'platform' => $channel['platform'],
                                    'active'   => $active,
                                    'config'   => json_encode($config)
                                ]);
                                $channel = $this->channel_model->get($id);
                                $this->madeline_model->join_channel($channel_link);
                                if ( ! $id)
                                    $data['message'] = lang('something_goes_wrong');
                                else
                                    $redirect = '/parsing?id=' . $id;
                            }
                        } elseif ($channel['platform'] == 'vk') {
                            $resolve = $this->vk_model->resolve($channel_link);
                            if (@$resolve['type'] !== 'group' or empty(@$resolve['object_id'])) {
                                $data['message'] = lang('incorrect_input');
                            } else {
                                $info = $this->vk_model->group_info($resolve['object_id']);
                                if (empty($info['name'])) {
                                    data_log('Can\'t get info', $info);
                                    $data['extra_messages'] []= [
                                        'text' => lang('cant_get_channel_info')
                                    ];
                                } else {
                                    $id = $this->channel_model->create([
                                        'user_id'  => $data['userinfo']->id,
                                        'uid'      => '-' . $resolve['object_id'],
                                        'name'     => $info['name'],
                                        'link'     => $channel_link,
                                        'type'     => 'source',
                                        'platform' => $channel['platform'],
                                        'active'   => $active,
                                        'config'   => json_encode($config),
                                    ]);
                                    $channel = $this->channel_model->get($id);
                                    try {
                                        $this->vk_model->join_group($resolve['object_id']);
                                    } catch (Exception $e) {
                                        $error_msg = $e->getMessage();
                                        if (strpos($error_msg, 'you are already in this community') !== false)
                                            $error_msg = null;
                                    }
                                    if ( ! $id)
                                        $data['message'] = lang('something_goes_wrong');
                                    elseif ( ! empty($error_msg))
                                        $data['message'] = $error_msg;
                                    else
                                        $redirect = '/parsing?id=' . $id;
                                }
                            }
                        }
                    }
                }
                if (empty($data['message']) and empty($data['extra_messages'])) {
                    if (count($links) == 1)
                        die(header('Location: ' . $redirect));
                    else if ( ! empty($target_channel))
                        die(header('Location: /channels?id=' . $target_channel->id));
                }
            }
        } elseif ( ! empty($_POST['config']) and ! empty($_POST['id'])) {
            $config = $_POST['config'];
            $id = $_POST['id'];
            $check = $this->db->where('user_id', $data['userinfo']->id)
                              ->where('id', $id)
                              ->where('type', 'source')
                              ->count_all_results('channel');
            if ( ! $check) {
                $data['message'] = lang('channel_not_found');
            } else {
                $stop_words = @json_decode(urldecode(@base64_decode(@$config['stop_words'])));
                $start_words = @json_decode(urldecode(@base64_decode(@$config['start_words'])));
                $replaces = @json_decode(urldecode(@base64_decode(@$config['replaces'])));
                if (empty($stop_words))
                    $stop_words = [];
                if (empty($start_words))
                    $start_words = [];
                if (empty($replaces))
                    $replaces = [];
                $new_config = [
                    'data' => [
                        'text' => ! empty(@$config['data']['text']),
                        'image' => ! empty(@$config['data']['image']),
                        'video' => ! empty(@$config['data']['video']),
                        'audio' => ! empty(@$config['data']['audio']),
                        'files' => ! empty(@$config['data']['files']),
                    ],
                    'stop_words' => $stop_words,
                    'start_words' => $start_words,
                    'subscript' => empty($config['subscript'])
                                 ? ''
                                 : $config['subscript'],
                    'replaces' => $replaces,
                    'remove_links' => ! empty(@$config['remove_links']),
                    'remove_tags' => ! empty(@$config['remove_tags']),
                    'paraphrase' => ! empty(@$config['paraphrase_active'])
                                  ? @$config['paraphrase_prompt']
                                  : false,
                    'autopost' => ! empty(@$config['autopost_on'])
                                ? @$config['autopost']
                                : false,
                    'moderation' => ! empty(@$config['moderation'])
                                ? @$config['moderation']
                                : false,
                ];
                $this->db->where('id', $id)->update('channel', [
                    'config' => json_encode($new_config),
                    'active' => ! empty(@$_POST['active'])
                ]);
                $data['message'] = lang('settings_saved');
                $data['good_message'] = true;
            }
        }

        if ( ! empty($_GET['id']))
            $data['channel'] = $this->channel_model->get((int) $_GET['id']);

        $data['channels'] = $this->channel_model->page(1, 0, [
            'user_id'  => $data['userinfo']->id,
            'type'     => 'source',
            'order_by' => 'id DESC'
        ]);
        $data['target_channels'] = $this->channel_model->page(1, 0, [
            'user_id'  => $data['userinfo']->id,
            'type'     => 'target',
            'order_by' => 'id DESC'
        ]);
        $data['posts'] = $this->post_model->page(1, 0, [
            'user_id'  => $data['userinfo']->id,
            'order_by' => 'updated_at DESC'
        ]);
        $data['default_paraphrase_prompt'] = $this->channel_model->default_paraphrase_prompt;
        $this->load->view('parsing', $data);
        $this->security_model->debugger($data);
    }

    public function delete()
    {
        $id = (int) $_POST['id'] ?? 0;
        $user = $this->user_model->get();
        $channel = $this->db->where('id', $id)
                            ->where('user_id', $user->id)
                            ->where('type', 'source')
                            ->get('channel')
                            ->row();
        if (empty($channel))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $result = $this->channel_model->delete($id);
        if ($result === false)
            echo json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]);
        else
            echo json_encode([ 'result' => 'ok' ]);
    }

    public function toggle()
    {
        if ( ! isset($_POST['id']) or ! isset($_POST['active']))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $id = (int) $_POST['id'];
        $user = $this->user_model->get();
        $channel = $this->db->where('id', $id)
                            ->where('user_id', $user->id)
                            ->where('type', 'source')
                            ->get('channel')
                            ->row();
        if (empty($channel))
            die(json_encode([
                'result' => 'error',
                'error' => lang('channel_not_found')
            ]));
        $result = $this->channel_model->update($id, [
            'active' => $_POST['active'] == 'true'
        ]);
        if ($result === false)
            echo json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]);
        else
            echo json_encode([ 'result' => 'ok' ]);
    }

}
