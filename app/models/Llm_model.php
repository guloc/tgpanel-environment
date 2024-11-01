<?php
class Llm_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public $error_text = 'Не удалось выполнить запрос';

    public function create($data)
    {
        if (empty(trim(@$data['name'])) or empty(trim(@$data['code'])) or empty(trim(@$data['provider'])))
            return false;
        $this->db->insert('model', [
            'name' => trim($data['name']),
            'code' => trim($data['code']),
            'provider' => trim($data['provider']),
        ]);
        $this->refresh();
        return true;
    }

    public function update($id, $data)
    {
        if ( ! empty($data)) {
            $this->db ->where('id', $id)
                      ->update('model', $data);
            $this->refresh();
            return true;
        }
        return false;
    }

    public function get($id)
    {
        if ( ! empty($id)) {
            $models = cached_data('llmodels');
            if ( ! $models) $models = $this->refresh();
            if (isset($models[$id])) {
                return $models[$id];
            }
        }
        return false;
    }

    public function get_all()
    {
        $models = cached_data('llmodels');
        if ( ! $models)
            $models = $this->refresh();
        return $models;
    }

    public function by_code()
    {
        $models = $this->get_all();
        return array_column(array_values($models), null, 'code');
    }

    public function by_provider($provider)
    {
        $models = $this->get_all();
        $filtered = [];
        foreach ($models as $key => $model) {
            if ($model['provider'] == $provider)
                $filtered[$moel['code']] = $model['name'];
        }
        return $filtered;
    }

    public function refresh()
    {
        $models = array_column($this->db->order_by('provider ASC, code ASC')->get('model')->result_array(), null, 'id');
        cache_data('llmodels', $models);
        return $models;
    }

}