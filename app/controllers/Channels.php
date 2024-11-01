<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Channels extends CI_Controller
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
        $data['update_available'] = $this->config_model->get('update_available', true);

        if ( ! empty($_POST['create'])) {
            $create = $_POST['create'];

            // Regex
            $regex = $this->channel_model->link_regex;
            if ($create['platform'] == 'vk')
                $regex = $this->vk_model->link_regex;
            if ($create['platform'] == 'wordpress')
                $regex = $this->wp_model->link_regex;

            if (empty($create['link'])
              or ! preg_match($regex, $create['link'], $match)
            ) {
                $data['message'] = lang('incorrect_input');
            }
            else {
                // Link
                if ($create['platform'] == 'vk')
                    $create['link'] = $match[3];
                else if ($create['platform'] == 'wordpress')
                    $create['link'] = $match[2];
                else
                    $create['link'] = $match[6] ?? $match[3];

                $check = $this->db->where('user_id', $data['userinfo']->id)
                                  ->where('link', $create['link'])
                                  ->where('type', 'target')
                                  ->count_all_results('channel');
                if ($check) {
                    $data['message'] = lang('channel_exists');
                } else {
                    // Telegram
                    if ($create['platform'] == 'telegram') {
                        $info = $this->madeline_model->get_full_info('@' . $create['link']);
                        if (@$info['type'] != 'channel' or empty(@$info['channel_id'])) {
                            $data['message'] = lang('incorrect_input');
                        } elseif (empty($info['Chat'])
                          or empty($info['Chat']['admin_rights'])
                          or empty($info['Chat']['admin_rights']['post_messages'])
                        ) {
                            $data['message'] = lang('bot_not_in_channel');
                        } else {
                            $graph = $this->madeline_model->get_channel_graph('@' . $create['link']);
                            if (is_string($graph)) {
                                $data['extra_messages'] = [
                                    [ 'text' => lang('stats_unavailable') ]
                                ];
                            }
                            $create['name'] = trim(@$create['name']);
                            if (empty($create['name']))
                                $create['name'] = $info['Chat']['title'];
                            $id = $this->channel_model->create([
                                'uid'      => $info['channel_id'],
                                'user_id'  => $data['userinfo']->id,
                                'name'     => $create['name'],
                                'link'     => $create['link'],
                                'type'     => 'target',
                                'platform' => 'telegram',
                                'subs'     => $info['full']['participants_count'] ?? 0,
                                'stats'    => json_encode([
                                    'graph' => is_string($graph)
                                             ? []
                                             : $graph
                                ]),
                            ]);
                            $data['message'] = lang('channel_created');
                            $data['good_message'] = true;
                        }
                    }
                    // VK
                    else if ($create['platform'] == 'vk') {
                        $resolve = $this->vk_model->resolve($create['link']);
                        if (@$resolve['type'] !== 'group' or empty($create['access_token'])) {
                            $data['message'] = lang('incorrect_input');
                        } elseif (empty(@$resolve['object_id'])) {
                            $data['message'] = lang('something_goes_wrong');
                        } else {
                            $permissions = $this->vk_model->check_permissions($create['access_token']);
                            $info = $this->vk_model->group_info($create['link']);
                            if ( ! in_array('wall', $permissions)) {
                                $data['message'] = lang('vk_permissions_error');
                            } else {
                                $create['name'] = empty($create['name'])
                                                ? $info['name']
                                                : $create['name'];
                                $id = $this->channel_model->create([
                                    'uid'      => '-' . $resolve['object_id'],
                                    'user_id'  => $data['userinfo']->id,
                                    'name'     => $create['name'],
                                    'link'     => $create['link'],
                                    'type'     => 'target',
                                    'platform' => 'vk',
                                    'subs'     => $info['members_count'] ?? 0,
                                    'stats'    => json_encode([
                                        'graph' => []
                                    ]),
                                    'access_token' => $create['access_token'],
                                ]);
                                $data['message'] = lang('channel_created');
                                $data['good_message'] = true;
                            }
                        }
                    }
                    // Wordpress
                    else {
                        if (empty($create['access_token']) or empty($create['access_name'])) {
                            $data['message'] = lang('incorrect_input');
                        } else {
                            $check = $this->wp_model->check_auth(
                                $create['link'],
                                $create['access_name'],
                                $create['access_token']
                            );
                            if ( ! $check) {
                                $data['message'] = lang('auth_error');
                            } else {
                                $create['name'] = empty($create['name'])
                                                ? $create['link']
                                                : $create['name'];
                                $id = $this->channel_model->create([
                                    'user_id'  => $data['userinfo']->id,
                                    'name'     => $create['name'],
                                    'link'     => $create['link'],
                                    'type'     => 'target',
                                    'platform' => 'wordpress',
                                    'access_token' => $create['access_token'],
                                    'access_name' => $create['access_name'],
                                ]);
                                $data['message'] = lang('channel_created');
                                $data['good_message'] = true;
                            }
                        }
                    }
                }
            }
        }

        if (isset($_GET['id'])) {
            $data['channel'] = $this->db->where('id', (int) $_GET['id'])
                                        ->where('user_id', $data['userinfo']->id)
                                        ->where('type', 'target')
                                        ->get('channel')
                                        ->row();
            if (empty($data['channel'])) {
                $data['message'] = lang('channel_not_found');
            } elseif ($data['channel']->platform == 'telegram') {
                $info = $this->madeline_model->get_info('@' . $data['channel']->link);
                if (empty($info['Chat']) or empty($info['Chat']['admin_rights'])) {
                    $data['extra_messages'] =  [
                        [ 'text' => lang('userbot_not_in_channel') ]
                    ];
                }
            }
        }

        if ( ! empty($data['channel'])) {
            $data['channels'] = $this->db->where('id', $data['channel']->id)
                                         ->get('channel')
                                         ->result();
            $data['stats'] = [
                'channels' => 1,
                'subscribers' => $this->db->where('id', $data['channel']->id)
                                          ->select('subs')
                                          ->get('channel')
                                          ->row()
                                          ->subs,
                'day_subs' => 0,
                'week_subs' => 0,
            ];
            $data['sources'] = $this->db->where('type', 'source')
                                        ->where('user_id', $data['userinfo']->id)
                                        ->where("JSON_EXTRACT(config, '\$.autopost') =", $data['channel']->id)
                                        ->get('channel')
                                        ->result();
        } else {
            $data['channels'] = $this->channel_model->page(1, 0, [
                'user_id'  => $data['userinfo']->id,
                'type'     => 'target',
                'order_by' => 'subs DESC'
            ]);
            $data['stats'] = [
                'channels' => $this->db->where('type', 'target')
                                       ->where('user_id', $data['userinfo']->id)
                                       ->count_all_results('channel'),
                'subscribers' => $this->db->where('type', 'target')
                                          ->where('user_id', $data['userinfo']->id)
                                          ->select('SUM(subs) AS subs')
                                          ->get('channel')
                                          ->row()
                                          ->subs,
                'day_subs' => 0,
                'week_subs' => 0,
            ];
        }
        $data['graph'] = [];
        foreach ($data['channels'] as $channel) {
            if (empty($channel->stats))
                continue;
            $channel_stats = unjson($channel->stats);
            if (empty($channel_stats['graph']))
                continue;
            foreach ($channel_stats['graph'] as $date => $values) {
                if (empty($data['graph'][$date]))
                    $data['graph'][$date] = [
                        'join' => 0,
                        'left' => 0,
                    ];
                $data['graph'][$date] = [
                    'join' => $data['graph'][$date]['join'] + $values['join'],
                    'left' => $data['graph'][$date]['left'] + $values['left'],
                ];
                $data['stats']['week_subs'] += $values['join'] - $values['left'];
                if ($date == date('d/m'))
                    $data['stats']['day_subs'] += $values['join'] - $values['left'];
            }
        }
        $this->load->view('channels', $data);
        $this->security_model->debugger($data);
    }

    public function delete()
    {
        $channel_id = (int) $_POST['id'] ?? 0;
        $channel = $this->channel_model->get($channel_id);
        $user = $this->user_model->get();
        if (empty($channel) or $channel->type != 'target' or $channel->user_id != $user->id)
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $result = $this->db->where('id', $channel_id)->delete('channel');
        if ($result === false)
            echo json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]);
        else
            echo json_encode([ 'result' => 'ok' ]);
    }

}
