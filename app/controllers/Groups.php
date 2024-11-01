<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Groups extends CI_Controller
{
    public $page_lengths = [ 10, 20, 50, 100 ];
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

        if ( ! empty($_POST['group'])) {
            $group = $_POST['group'];
            if (empty($group['id']) or ! preg_match('/^\s*\-?\d+\s*$/', $group['id'])) {
                $data['message'] = lang('incorrect_input');
            } else {
                $group['id'] = trim($group['id']);
                $check = $this->db->where('uid', $group['id'])
                                  // ->where('user_id', $data['userinfo']->id)
                                  ->count_all_results('group');
                if ($check) {
                    $data['message'] = lang('group_exists');
                } else {
                    $info = $this->bot_model->api_request('getChat', [
                        'chat_id' => $group['id']
                    ]);
                    if (empty($info['type']) or ! in_array($info['type'], ['group', 'supergroup'])) {
                        data_log('Wrong chat type', $info);
                        $data['message'] = lang('incorrect_input');
                    } elseif (empty($info['title'])) {
                        data_log('Group title not found', $info);
                        $data['message'] = lang('incorrect_input');
                    } else {
                        $bot = $this->bot_model->api_request('getChatMember', [
                            'chat_id' => $group['id'],
                            'user_id' => $this->bot_model->id
                        ]);
                        if (@$bot['status'] !== 'administrator') {
                            $data['message'] = lang('bot_not_admin');
                        } else {
                            if (empty(trim($group['name'])))
                                $group['name'] = $info['title'];
                            $members_count = (int) $this->bot_model->api_request('getChatMembersCount', [
                                'chat_id' => $group['id']
                            ]);
                            $id = $this->group_model->create([
                                'uid'     => $group['id'],
                                'name'    => $group['name'],
                                'config'  => json($this->group_model->default_config),
                                'active'  => false,
                                'user_id' => $data['userinfo']->id,
                                'stats'   => json_encode([
                                    'members' => $members_count,
                                    'joined'  => 0,
                                    'left'    => 0,
                                    'banned'  => 0,
                                    'muted'   => 0,
                                ]),
                            ]);
                            if ( ! $id)
                                $data['message'] = lang('something_goes_wrong');
                            else
                                die(header('Location: /groups?id=' . $id));
                        }
                    }
                }
            }
        } elseif ( ! empty($_POST['config']) and ! empty($_POST['id'])) {
            $config = $_POST['config'];
            $id = $_POST['id'];
            $check = $this->db->where('user_id', $data['userinfo']->id)
                              ->where('id', $id)
                              ->count_all_results('group');
            if ( ! $check) {
                $data['message'] = lang('group_not_found');
            } else {
                $stop_words = @json_decode(@urldecode(@base64_decode(@$config['stop_words']['list'])));
                if (empty($stop_words))
                    $stop_words = [];
                $new_config = [
                    'filter_admins' => ! empty(@$config['filter_admins']),
                    'messages' => [
                        'bot_commands' => ! empty(@$config['messages']['bot_commands']),
                        'images' => ! empty(@$config['messages']['images']),
                        'voices' => ! empty(@$config['messages']['voices']),
                        'files' => ! empty(@$config['messages']['files']),
                        'stickers' => ! empty(@$config['messages']['stickers']),
                        'dices' => ! empty(@$config['messages']['dices']),
                        'links' => ! empty(@$config['messages']['links']),
                    ],
                    'forward' => [
                        'all' => ! empty(@$config['forward']['all']),
                        'media' => ! empty(@$config['forward']['media']),
                        'links' => ! empty(@$config['forward']['links']),
                    ],
                    'restrict' => [
                        'time' => (int) @$config['restrict']['time'],
                        'mul' => (int) @$config['restrict']['mul'],
                    ],
                    'joined_restrict' => [
                        'time' => (int) @$config['joined_restrict']['time'],
                        'mul' => (int) @$config['joined_restrict']['mul'],
                    ],
                    'stop_words' => [
                        'active' => ! empty(@$config['stop_words']['active']),
                        'list' => $stop_words,
                    ],
                    'user_joined' => ! empty(@$config['user_joined']),
                    'user_left' => ! empty(@$config['user_left']),
                ];
                $this->db->where('id', $id)->update('group', [
                    'config' => json_encode($new_config),
                    'active' => ! empty(@$_POST['active'])
                ]);
                $data['message'] = lang('settings_saved');
                $data['good_message'] = true;
            }
        }

        if ( ! empty($_GET['id']))
            $data['group'] = $this->group_model->get((int) $_GET['id']);

        $data['groups'] = $this->group_model->page(1, 0, [
            'user_id'  => $data['userinfo']->id,
        ]);
        $data['page_lengths'] = $this->page_lengths;
        $this->load->view('groups', $data);
        $this->security_model->debugger($data);
    }

    public function delete()
    {
        $group_id = (int) $_POST['id'] ?? 0;
        $group = $this->group_model->get($group_id);
        $user = $this->user_model->get();
        if (empty($group) or $group->user_id != $user->id)
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $result = $this->group_model->delete($group_id);
        if ($result === false)
            echo json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]);
        else
            echo json_encode([ 'result' => 'ok' ]);
    }

    public function users($group_id=false, $page = 1)
    {
        $user = $this->user_model->get();
        $group = $this->db->where('user_id', $user->id)
                          ->where('id', $group_id)
                          ->get('group')
                          ->row();
        if ( ! $group)
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $page_length = (isset($_GET['per_page']) and in_array($_GET['per_page'], [10]))
                     ? $_GET['per_page']
                     : $this->page_lengths[0];
        $page = intval($page) <= 0
              ? 1
              : (int) $page;
        $offset = ($page - 1) * $page_length;
        $result = [];

        $records_total = $this->db->count_all_results('tg_user');

        $db = $this->db;
        $db->where('chat_id', $group->uid);
        if (isset($_GET['filter']))
            $filter = $_GET['filter'];
        if ( ! empty($filter['contact'])) {
            $contact = $this->db->escape_like_str(trim(urldecode($filter['contact'])));
            $db->group_start();
            $db->where("LOWER(first_name) LIKE LOWER('%{$contact}%') ESCAPE '!'");
            $db->or_where("LOWER(last_name) LIKE LOWER('%{$contact}%') ESCAPE '!'");
            $db->or_where("LOWER(username) LIKE LOWER('%{$contact}%') ESCAPE '!'");
            $db->group_end();
            $records_total = $db->count_all_results('tg_user');
            $db->where('chat_id', $group->uid);
            $db->group_start();
            $db->where("LOWER(first_name) LIKE LOWER('%{$contact}%') ESCAPE '!'");
            $db->or_where("LOWER(last_name) LIKE LOWER('%{$contact}%') ESCAPE '!'");
            $db->or_where("LOWER(username) LIKE LOWER('%{$contact}%') ESCAPE '!'");
            $db->group_end();
        }

        $users = $db->order_by('id', 'desc')
                    ->limit($page_length, $offset)
                    ->get('tg_user')
                    ->result();

        foreach ($users as $user) {
            $item = [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'username' => $user->username,
                'last_seen' => $user->last_seen,
                'actions' => [
                    'muted' => ! empty($user->muted_for),
                    'banned' => (bool) $user->banned
                ],
            ];
            $result []= $item;
        }

        $this->load->library('pagination');
        $this->pagination->initialize([
          'base_url'    => '',
          'uri_segment' => 4,
          'suffix'      => '',
          'total_rows'  => $records_total,
          'per_page'    => $page_length,
          'cur_page'    => $page,
        ]);
        $pagination_block = $this->pagination->create_links();

        echo json_encode([
            'result' => $result,
            'pagination' => $pagination_block
        ]);
    }

    public function send($group_id=false)
    {
        $text = @$_POST['text'];
        if (empty(trim(strip_tags($text))))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $user = $this->user_model->get();
        $group = $this->db->where('user_id', $user->id)
                          ->where('id', $group_id)
                          ->get('group')
                          ->row();
        if ( ! $group)
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));

        $result = $this->bot_model->send_html($group->uid, $text);
        if (empty($result['message_id'])) {
            if (isset($result['description']))
                die(json_encode([
                    'result' => 'error',
                    'error' => $result['description']
                ]));
            die(json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]));
        }

        echo json_encode([ 'result' => 'ok' ]);
    }

    public function mute()
    {
        if ( ! isset($_POST['id']) or ! isset($_POST['state']))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $user = $this->user_model->get();
        $tg_user = $this->db->where('id', $_POST['id'])
                            ->get('tg_user')
                            ->row();
        if (empty($tg_user))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $group = $this->db->where('uid', $tg_user->chat_id)
                          ->where('user_id', $user->id)
                          ->get('group')
                          ->row();
        if (empty($group))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        if ($_POST['state'] === 'false')
            $result = $this->bot_model->unmute_user($group->uid, $tg_user->uid);
        else 
            $result = $this->bot_model->mute_user($group->uid, $tg_user->uid);
        if (empty($result))
            die(json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]));
        if (isset($result['ok']) and ! $result['ok'] and isset($result['description']))
            die(json_encode([
                'result' => 'error',
                'error' => $result['description']
            ]));
        echo json_encode([ 'result' => 'ok' ]);
    }

    public function ban()
    {
        if ( ! isset($_POST['id']) or ! isset($_POST['state']))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $user = $this->user_model->get();
        $tg_user = $this->db->where('id', $_POST['id'])
                            ->get('tg_user')
                            ->row();
        if (empty($tg_user))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $group = $this->db->where('uid', $tg_user->chat_id)
                          ->where('user_id', $user->id)
                          ->get('group')
                          ->row();
        if (empty($group))
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        if ($_POST['state'] === 'false')
            $result = $this->bot_model->unban_user($group->uid, $tg_user->uid);
        else 
            $result = $this->bot_model->ban_user($group->uid, $tg_user->uid);
        if (empty($result))
            die(json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]));
        if (isset($result['ok']) and ! $result['ok'] and isset($result['description']))
            die(json_encode([
                'result' => 'error',
                'error' => $result['description']
            ]));
        echo json_encode([ 'result' => 'ok' ]);
    }

}
