<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Page extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ( ! RS_STATUS)
            $this->load->view('technical_works');
        $this->security_model->check_visit();
    }
    
    public function open($page_url='')
    {
        $data = array();
        $page_url = safe($page_url, 'a-zA-Z0-9_\-\.');
        if ( ! empty($page_url)) {
            $data = $this->info_model->get_basic_info($data);
            $this->load->view('static/' . $page_url, $data);
            $this->security_model->debugger($data);
        } else {
            die(header('Location: /not_found'));
        }
    }

}

