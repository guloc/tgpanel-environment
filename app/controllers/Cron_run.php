<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Cron_run extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private $debug = false;
    private $max_process = 3;
    
    // * * * * * sudo -u www-data php -f /var/www/html/index.php cron_run initiate tgpanel > /dev/null 2>&1
    public function initiate($key = '')
    {
        if ($key == RS_CRON_KEY or is_cli()) {
            
            $t = time();
            // return;

            $tg_auth = $this->config_model->get('tg_auth');
            $unsafe_parsing = $this->config_model->get('unsafe_parsing');

            // Check updates (every 10 minutes)
            if ($t % 600 < 60) {
                @$this->migrate_model->check_updates();
            }

            // Cron lock
            $output = shell_exec('ps faux | grep -i initiate');
            $lines = explode("\n", $output);
            $pids = [];
            foreach ($lines as $line) {
                if (empty($line) or strpos($line, 'grep') !== false)
                    continue;
                if (strpos($line, 'cron_run initiate tgpanel') !== false) {
                    preg_match('/^[\w\-]+\s+(\d+)/', $line, $match);
                    $pids []= $match[1];
                }
            }
            if (count($pids) > $this->max_process) {
                $lock = (int) cached_var('cron_lock');
                if ($lock++ < 10) {
                    cache_var('cron_lock', $lock, 600);
                    if ($this->debug)
                        logg("Cron locked {$lock} " . count($pids));
                    return;
                }
                logg('Cron was locked for 10 minutes. Resetting...');
                renew_cache('cron_lock');
                foreach ($pids as $pid)
                    shell_exec('kill ' . $pid);
            }
            renew_cache('cron_lock');

            // Unmute users
            if ($this->debug and time() - $t > 60)
                logg('Unmute users');
            $muted_users = $this->db->where('muted_for <=', now())->get('tg_user')->result();
            foreach ($muted_users as $user) {
                $this->db->where('id', $user->id)->update('tg_user', [
                    'muted_for' => null
                ]);
                $group = $this->db->where('uid', $user->chat_id)->get('group')->row();
                $stats = unjson($group->stats);
                $stats['muted']--;
                $this->db->where('uid', $user->chat_id)->update('group', [
                    'stats' => json_encode($stats)
                ]);
            }

            // Send posts [!]
            if ($this->debug and time() - $t > 60)
                logg('Send posts');
            $this->post_model->send_queued();

            // Check grouped posts [!]
            if ($this->debug and time() - $t > 60)
                logg('Check grouped posts');
            $grouped = $this->db->where('created_at < ', time_jump(now(), '-30 seconds'))
                                ->get('message_group')
                                ->result_array();
            $channels = [];
            $messages = [];
            foreach ($grouped as $item) {
                if (empty($channels[$item['channel_id']])) {
                    $channels[$item['channel_id']] = $this->channel_model->get($item['channel_id']);
                    if (empty($channels[$item['channel_id']])) {
                        $this->db->where('id', $item['id'])->delete('message_group');
                        unset($channels[$item['channel_id']]);
                        continue;
                    }
                }
                $messages[$item['channel_id']] []= unjson($item['message']);
            }
            try {
                foreach ($messages as $channel_id => $items) {
                    $this->madeline_model->handle_messages($channels[$channel_id], $items);
                }
                if ( ! empty($grouped))
                    $this->db->where_in('id', array_column($grouped, 'id'))
                             ->delete('message_group');
            } catch (\Throwable $e) {
                logg($e->getMessage());
            }

            if (empty($tg_auth) or ! empty($unsafe_parsing)) {
                // Kill event handler
                if (empty($tg_auth))
                    logg('Parsing disabled: Not authorized');
                $output = shell_exec('ps faux | grep -i events');
                $lines = explode("\n", $output);
                $pids = [
                    'bash' => [],
                    'php' => [],
                ];
                foreach ($lines as $line) {
                    if (empty($line) or strpos($line, 'grep') !== false)
                        continue;
                    if (strpos($line, 'bash events.sh') !== false) {
                        preg_match('/^[\w\-]+\s+(\d+)/', $line, $match);
                        $pids['bash'] []= $match[1];
                    } elseif (strpos($line, 'php -f events.php') !== false) {
                        preg_match('/^[\w\-]+\s+(\d+)/', $line, $match);
                        $pids['php'] []= $match[1];
                    }
                }
                foreach ($pids['bash'] as $pid)
                    shell_exec('kill ' . $pid);
                foreach ($pids['php'] as $pid)
                    shell_exec('kill ' . $pid);
            }
            else {
                // Load stats
                if ($this->debug and time() - $t > 60)
                    logg('Load stats');
                if ($t % 86400 <= 60) {
                    $channels = $this->db->where('type', 'target')
                                         ->get('channel')
                                         ->result();
                    foreach ($channels as $channel) {
                        if ($channel->platform == 'wordpress')
                            continue;
                        $stats = unjson($channel->stats);
                        $subs = $channel->subs;
                        if ($channel->platform == 'telegram') {
                            $graph = $this->madeline_model->get_channel_graph('@' . $channel->link);
                            $info = $this->madeline_model->get_full_info('@' . $channel->link);
                            if (is_string($graph))
                                logg("ERROR: Can't get stats for channel #{$channel->id} ({$channel->name})");
                            else
                                $stats['graph'] = $graph;
                            if ( ! empty($info['full']['participants_count']))
                                $subs = $info['full']['participants_count'];
                        } else if ($channel->platform == 'vk') {
                            $old_subs = $subs;
                            $join = 0;
                            $left = 0;
                            $subs = @$this->vk_model->group_info($channel->link)['members_count'];
                            if ($subs > $old_subs)
                                $join = $subs - $old_subs;
                            if ($subs < $old_subs)
                                $join = $old_subs - $subs;
                            $stats['graph'][date('d/m')] = [
                                'join' => $join,
                                'left' => $left,
                            ];
                            if (count($stats['graph']) > 7)
                                $stats['graph'] = array_combine(
                                    array_slice(array_keys($stats['graph']), -7, 7),
                                    array_slice(array_values($stats['graph']), -7, 7)
                                );
                        }
                        $this->channel_model->update($channel->id, [
                            'stats' => json_encode($stats),
                            'subs' => $subs
                        ]);
                    }
                }

                // Parsing vk [!]
                if ($this->debug and time() - $t > 60)
                    logg('Parsing vk');
                if (cached_var('vk_parsing_stopped')) {
                    logg('VK parsing stopped - Limits reached');
                } else {
                    $channels_count = $this->db->where('type', 'source')
                                               ->where('active', 1)
                                               ->where('platform', 'vk')
                                               ->count_all_results('channel');
                    $interval = 2700;
                    foreach ($this->vk_model->parsing_limits as $i => $count) {
                        if ($channels_count > $count)
                            $interval = $i;
                        else
                            break;
                    }
                    if ($t % $interval <= 60) {
                        $channels = $this->db->where('type', 'source')
                                             ->where('active', 1)
                                             ->where('platform', 'vk')
                                             ->get('channel')
                                             ->result();
                        foreach ($channels as $channel) {
                            $this->vk_model->parse_posts($channel);
                        }
                    }
                }
                // Parsing telegram [!]
                if ( ! empty($unsafe_parsing)) {
                    if ($this->debug and time() - $t > 60)
                        logg('Parsing telegram');
                    // every 1 hour
                    if ($t % 3600 <= 60) {
                        // Parse posts
                        $channels = $this->db->where('type', 'source')
                                             ->where('active', 1)
                                             ->where('platform', 'telegram')
                                             ->get('channel')
                                             ->result();
                        foreach ($channels as $channel) {
                            $this->madeline_model->parse_posts($channel);
                        }
                    }
                } else {

                    // Check event handler
                    $output = shell_exec('ps faux | grep -i events.php');
                    $events_running = strpos($output, 'php -f events.php') !== false;
                    if ( ! $events_running)
                        shell_exec('bash events.sh > /dev/null 2>/dev/null &');

                }

            }
            if ($this->debug)
                logg('Done!');

            if (is_cli())
                return true;
            else
                die('Success');
        } else {
            return false;
        }
    }

}

