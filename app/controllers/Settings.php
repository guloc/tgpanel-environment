<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Settings extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ( ! RS_STATUS)
            $this->load->view('technical_works');
        $this->security_model->check_visit();
        $this->security_model->check_auth();
        $this->security_model->only_admin();
    }
    
    public function index($data=array())
    {
        if (isset($_POST['settings'])) {
            $data['good_message'] = true;
            $data['message'] = lang('settings_saved');
            $settings = $_POST['settings'];
            $settings['unsafe_parsing'] = ! empty($settings['unsafe_parsing']);
            foreach ($settings as $key => $value) {
                if ($key == 'bot_users') {
                    $value = unjson(urldecode(@base64_decode($value)));
                    if (empty($value))
                        $value = [];
                    else
                        $value = array_keys($value);
                    $value = json_encode($value);
                }
                if ( ! $this->config_model->update_or_create($key, $value)) {
                    $data['good_message'] = false;
                    $data['message'] = lang('something_goes_wrong');
                } elseif ($key == 'telegram_bot_token' and $value !== $this->bot_model->token and ! empty($value)) {
                    $webhook_url = siteurl() . '/bot_handler?key=' . RS_KEY;
                    $this->bot_model->set_webhook($webhook_url);
                    renew_cache('bot_info');
                    $this->madeline_model->start_bot();
                }
            }
        } else if (isset($_POST['tg_logout'])) {
            $this->madeline_model->logout();
            $this->config_model->update_or_create('tg_auth', '');
            die(header('Location: /settings'));
        } else if (isset($_POST['vk'])) {
            $new_vk_config = $_POST['vk'];
            if (empty($new_vk_config['app_id'])
              or empty($new_vk_config['private_key'])
              or empty($new_vk_config['service_key'])
            ) {
                $data['message'] = lang('incorrect_input');
            } else {
                $vk_config = $this->config_model->get('vk_config', true);
                $vk_config['app_id'] = $new_vk_config['app_id'];
                $vk_config['private_key'] = $new_vk_config['private_key'];
                $vk_config['service_key'] = $new_vk_config['service_key'];
                $this->config_model->update('vk_config', json($vk_config));
                return $this->vk_model->connect();
            }
            
        } else if (isset($_POST['new_model'])) {
            $new_model = $_POST['new_model'];
            if (empty($new_model['name']) or empty($new_model['code']) or empty($new_model['provider'])) {
                $data['message'] = lang('incorrect_input');
            } elseif ($this->db->where('code', $new_model['code'])->count_all_results('model')) {
                $data['message'] = lang('models_exists');
            } else {
                $this->llm_model->create([
                    'name'      => $new_model['name'],
                    'code'      => $new_model['code'],
                    'provider'  => $new_model['provider'],
                ]);
                $data['good_message'] = true;
                $data['message'] = lang('model_created');
            }
        }

        $data = $this->info_model->get_basic_info($data);
        $data['settings'] = $this->config_model->get_all_config();
        $data['settings']['vk_config'] = unjson($data['settings']['vk_config']);
        $data['models'] = $this->llm_model->by_code();
        $data['update_available'] = $this->config_model->get('update_available', true);

        $data['bot_users'] = [];
        $bot_users = unjson($data['settings']['bot_users'])
                   ? unjson($data['settings']['bot_users'])
                   : [];
        foreach ($bot_users as $uid) {
            $bot_user = $this->db->where('uid', $uid)
                                 ->get('tg_user')
                                 ->row();
            $data['bot_users'][$uid] = empty($bot_user)
                                     ? ''
                                     : $bot_user->first_name
                                       . ' ' . $bot_user->last_name
                                       . ( empty($bot_user->username)
                                           ? ''
                                           : ' @' . $bot_user->username
                                         );
        }

        $data['parsing_stopped'] = cached_var('STOP PARSING');

        if (empty($data['settings']['tg_auth'])) {
            $userbot = $this->madeline_model->get_self();
            $data['settings']['tg_auth'] = '';
            if ( ! empty($userbot['username']))
                $data['settings']['tg_auth'] .= '@' . $userbot['username'];
            if ( ! empty($userbot['phone']))
                $data['settings']['tg_auth'] .= ' ' . $userbot['phone'];
            $this->config_model->update_or_create('tg_auth', trim($data['settings']['tg_auth']));
        }

        $this->load->view('settings', $data);
        $this->security_model->debugger($data);
    }

    public function restart_parsing()
    {
        renew_cache('STOP PARSING');
        echo json_encode([
            'result' => 'ok'
        ]);
    }

    public function tg_login()
    {
        $this->madeline_model->start(true);

        die(header('Location: /settings'));
    }

    public function vk_auth()
    {
        if (empty($_GET['state']) or empty($_GET['code']) or $_GET['state'] !== RS_KEY) {
            $data['message'] = lang('something_goes_wrong');
            return $this->index($data);
        }
        $code = $_GET['code'];
        $vk_config = $this->config_model->get('vk_config', true);
        $vk_config['code'] = $code;
        if ( ! empty($vk_config['access_token']))
            unset($vk_config['access_token']);
        $this->config_model->update('vk_config', json($vk_config));
        $data['good_message'] = true;
        $data['message'] = lang('vk_auth_success');
        return $this->index($data);
    }

    public function update()
    {
        $update = $this->config_model->get('update_available', true);
        if (empty($update) or empty($update['update_id']))
            die(header('Location: /settings'));
        $license_key = $this->config_model->get('license_key');
        $file = $this->migrate_model->api_request('download_update/main/' . $update['update_id'], [
            'license_code' => $license_key,
            'client_name' => $_SERVER['HTTP_HOST']
        ]);
        @file_put_contents('update.zip', $file);
        $output = null;
        $result_code = null;
        $result = shell_exec('unzip -o update.zip');
        shell_exec('rm update.zip');
        $data = [ 'message' => lang('something_goes_wrong') ];
        if ($result !== null) {
            $this->config_model->update('update_available', '');
            $data['message'] = lang('update_success');
            $data['good_message'] = true;
        } else {
            logg('ERROR: Can\'t install update. Please check filesystem permissions');
        }
        $this->index($data);
    }

    public function decline_update()
    {
        $this->config_model->update('update_available', '');
        die(header('Location: /settings'));
    }

}
