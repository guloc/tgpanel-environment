<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Not_found extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ( ! RS_STATUS)
            $this->load->view('technical_works');
        $this->security_model->check_visit();
        if ( ! page_is('/not_found'))
            die(header('Location: /not_found'));
    }

    public function index($data=array())
    {
        $data = $this->info_model->get_basic_info($data);
        http_response_code(404);
        $this->load->view('not_found', $data);
        $this->security_model->debugger($data);
    }
    
}
