<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ( ! RS_STATUS)
            $this->load->view('technical_works');
        $this->security_model->check_visit();
        if ($this->user_model->check_user() and ! isset($_POST['user']))
            die(header('Location: /'));
    }

    public function index($target='/')
    {
        $data = array();
        if (isset($_POST['user'])) {
            $data['good_message'] = false;
            if (isset($_POST['user']['login']) and isset($_POST['user']['password'])) {
                $login = trim($_POST['user']['login']);
                $password = isset($_POST['user']['password'])
                          ? trim($_POST['user']['password'])
                          : null;
                $try = $this->user_model->login($login, $password);
                if ($try !== true) {
                    $data['message'] = $try;
                } else {
                    if ( ! empty($_COOKIE['target'])) {
                        $target = $_COOKIE['target'];
                        setcookie('target', '/', time() - 1, '/');
                    }
                    die(header('Location:' . $target));
                }
            } else {
                $data['message'] = lang('incorrect_input');
            }
        }
        
        $data = $this->info_model->get_basic_info($data);
        $this->load->view('login', $data);
    }
}