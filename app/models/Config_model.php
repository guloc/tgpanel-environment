<?php
class Config_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function create($cfg_name='', $cfg_value='')
    {
        if ( ! empty($cfg_name)) {
            $data = array();
            $data['cfg_name'] = $cfg_name;
            $data['cfg_value'] = $cfg_value;
            $this->db->insert('config', $data);
            $this->refresh();
            return true;
        }
        return false;
    }

    public function delete($cfg_name='')
    {
        if(!empty($cfg_name)) {
            $this->db->where('cfg_name', $cfg_name)->delete('config');
            $this->refresh();
            return true;
        }
        return false;
    }

    public function update($cfg_name='', $new_value='')
    {
        if ( ! empty($cfg_name)) {
            $this->db
                ->where('cfg_name', $cfg_name)
                ->update('config', array('cfg_value' => $new_value));
            $this->refresh();
            return true;
        }
        return false;
    }

    public function update_or_create($cfg_name='', $new_value='')
    {
        if ( ! empty($cfg_name)) {
            $exist = $this->is_exist($cfg_name);
            if ($exist) {
                return $this->update($cfg_name, $new_value);
            } else {
                return $this->create($cfg_name, $new_value);
            }
        }
        return false;
    }

    public function is_exist($cfg_name='')
    {
        $config = $this->db
            ->where('cfg_name', $cfg_name)
            ->get('config')->num_rows();
        return (bool) $config;
    }

    public function get($cfg_name='', $json_decode=false)
    {
        if(!empty($cfg_name)) {
            $config = cached_data('global_config');
            if ( ! $config) $config = $this->refresh();
            if (isset($config[$cfg_name])) {
                return $json_decode ? unjson($config[$cfg_name]) : $config[$cfg_name];
            }
        }
        return false;
    }

    public function get_all_config()
    {
        $config = cached_data('global_config');
        if ( ! $config) $config = $this->refresh();
        return $config;
    }

    public function refresh()
    {
        $config = [];
        $get_config = $this->db->get('config')->result();
        foreach($get_config as $item) {
            $config[$item->cfg_name] = $item->cfg_value;
        }
        cache_data('global_config', $config);
        return $config;
    }

}