<?php
class Channel_model extends CI_Model
{
    function __construct() {
        parent::__construct();
    }

    public $link_regex = '/^\s*((@?(\w+))|((https\:\/\/)?t\.me\/(\w+)))\s*$/';
    public $default_config = [
        'data' => [
            'text' => true,
            'image' => true,
            'video' => true,
            'audio' => true,
            'files' => false,
        ],
        'start_words' => [],
        'stop_words' => [],
        'remove_links' => false,
        'remove_tags' => false,
        'paraphrase' => false,
        'replaces' => [],
        'moderation' => false,
        'subscript' => ''
    ];
    public $default_paraphrase_prompt = "Paraphrase text using the same language:\n{usertext}";

    private $table = 'channel';

    public function get($id)
    {
        $id = (int) $id;
        $result = $this->db->where('id', $id)
                           ->get($this->table)
                           ->row();
        if ( ! empty($result))
            $result->config = unjson($result->config);
        return $result;
    }

    public function get_all()
    {
        $result = $this->db->order_by('id', 'desc')->get($this->table)->result();
        return $result;
    }

    public function page($page = 1, $per_page = 10, $filter = [])
    {
        $page = intval($page) <= 0
              ? 1
              : (int) $page;
        $offset = ($page - 1) * $per_page;
        $result = [];

        $db = $this->db;

        if ( ! empty($filter['user_id']))
            $db->where('user_id', $filter['user_id']);

        if ( ! empty($filter['type']))
            $db->where('type', $filter['type']);

        if ( ! empty($per_page))
            $db->limit($per_page, $offset);

        if (empty($filter['order_by']))
            $db->order_by('id', 'desc');
        else
            $db->order_by($filter['order_by']);

        $result = $db->get($this->table)->result();

        return $result;
    }

    public function create($params)
    {
        if ($params['type'] == 'source') {
            if (empty($params['config']))
                $params['config'] = json_encode($this->default_config);
            elseif (is_array($params['config']) or is_object($params['config']))
                $params['config'] = json_encode($params['config']);
        }
        $this->db->insert($this->table, $params);
        return $this->db->insert_id();
    }

    public function update($id, $params)
    {
        return $this->db->where('id', $id)->update($this->table, $params);
    }

    public function delete($id)
    {
        $channel = $this->get($id);
        if ( ! empty($channel))
            if ($channel->platform == 'telegram')
                $this->madeline_model->leave_channel($channel->link);
        return $this->db->delete($this->table, [ 'id' => $id ]);
    }

}