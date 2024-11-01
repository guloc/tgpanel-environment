<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Too_many_requests extends CI_Controller {
    public function __construct() {
        parent::__construct();
    }
    public function index($data=array()) { // checked
        $mode = isset($_COOKIE['ever'])
              ? 'ever'
              : 'time';
        http_response_code(429);
        $this->load->view('too_many_requests', ['mode' => $mode]);
    }
}
