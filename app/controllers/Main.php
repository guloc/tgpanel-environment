<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Main extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        if ( ! RS_STATUS) $this->load->view('technical_works');
        $this->security_model->check_visit();
        $this->security_model->check_auth();
    }
    
    public function index($data=array())
    {
        $user = $this->user_model->get();
        $bot_token = $this->config_model->get('telegram_bot_token');
        $tg_auth = $this->config_model->get('tg_auth');
        if ($user->type == 'admin' and (empty($bot_token or empty($tg_auth)))) {
            die(header('Location: /settings'));
        } else
            die(header('Location: /channels'));

    }
    
}