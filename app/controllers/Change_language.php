<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Change_language extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        if ( ! RS_STATUS) $this->load->view('technical_works');
        $this->security_model->check_visit();
    }
    public function set($lang='')
    {
        $this->language_model->change_language($lang);
    }
}


