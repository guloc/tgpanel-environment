<?php
class Info_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    public $url_regex = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|(([^\s()<>]+|(([^\s()<>]+)))*))+(?:(([^\s()<>]+|(([^\s()<>]+)))*)|[^\s`!()[]{};:'\".,<>?«»“”‘’]))/i";

    public function get_basic_info($data=array())
    {
        require_once('tpl/base/config/extra.php');
        $extra = getExtraData();
        if (is_array($extra)) {
            foreach ($extra as $key => $value) {
                $data[$key] = $value;
            }
        }

        $data['project_name'] = $this->config_model->get('project_name');
        $data['csrf_token_name'] = $this->config->config['csrf_token_name'];
        $data['userinfo'] = $this->user_model->get();

        return $data;
    }


}
