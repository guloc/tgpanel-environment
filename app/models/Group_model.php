<?php
class Group_model extends CI_Model
{
    function __construct() {
        parent::__construct();
    }

    private $table = 'group';

    public $default_config = [
        'filter_admins' => false,
        'messages' => [
            'bot_commands' => false,
            'images' => false,
            'voices' => false,
            'files' => false,
            'stickers' => false,
            'dices' => false,
            'links' => false,
        ],
        'forward' => [
            'all' => false,
            'media' => false,
            'links' => false,
        ],
        'restrict' => [
            'time' => 60,
            'mul' => 10,
        ],
        'joined_restrict' => [
            'time' => 60,
            'mul' => 10,
        ],
        'stop_words' => [
            'active' => false,
            'list' => [],
        ],
        'user_joined' => false,
        'user_left' => false,
    ];

    public function get($id)
    {
        $id = (int) $id;
        $result = $this->db->where('id', $id)
                           ->get($this->table)
                           ->row();
        if ( ! empty($result)) {
            $result->config = unjson($result->config);
            $result->stats = unjson($result->stats);
        }
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
        $this->db->insert($this->table, $params);
        return $this->db->insert_id();
    }

    public function update($id, $params)
    {
        $this->db->where('id', $id)->update($this->table, $params);
    }

    public function delete($id)
    {
        $group = $this->db->where('id', $id)->get('group')->row();
        if ( ! empty($group))
            $this->db->where('chat_id', $group->uid)->delete('tg_user');
        $this->db->delete($this->table, [ 'id' => $id ]);
    }
}