<?php
class Post_model extends CI_Model
{
    function __construct() {
        parent::__construct();
    }

    private $table = 'post';
    public $status_list = [ 'draft', 'queued', 'posted', 'moderation' ];

    public function get($id)
    {
        $id = (int) $id;
        $result = $this->db->where('id', $id)
                           ->get($this->table)
                           ->row();
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

        if ( ! empty($filter['status']))
            $db->where('status', $filter['status']);

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
        $post = $this->get($id);
        if ( ! empty($post) and ! empty(unjson($post->files))) {
            $files = unjson($post->files);
            foreach ($files as $file_name) {
                @unlink('assets/upload/'.$post->user_id.'/' . $file_name);
            }
        }
        $this->db->delete($this->table, [ 'id' => $id ]);
    }

    public function send_queued()
    {
        // Send queued
        $posts = $this->db->where('status', 'queued')
                          ->where('pub_date <=', now())
                          ->get('post')
                          ->result();
        foreach ($posts as $post) {
            $channel = $this->channel_model->get($post->channel_id);
            if (empty($channel)) {
                logg("ERROR: Channel not found. Post #{$post->id} was moved to drafts");
                $this->update($post->id, [
                    'status' => 'draft'
                ]);
            } else {
                try {
                    if ($channel->platform == 'vk')
                        $this->vk_model->post($channel, $post);
                    else if ($channel->platform == 'telegram')
                        $this->bot_model->send_post($channel->uid, $post);
                    else if ($channel->platform == 'wordpress')
                        $this->wp_model->post($channel, $post);
                    else {
                        $this->post_model->update($post->id, [
                            'status' => 'draft'
                        ]);
                        logg("Unknown platform: {$channel->platform}, Post #{$post->id} moved to drafts");
                    }
                } catch (\Throwable $e) {
                    $this->post_model->update($post->id, [
                        'status' => 'draft'
                    ]);
                    $error_msg = $e->getMessage();
                    data_log('Post moved to drafts', $post);
                    logg("ERROR: {$error_msg}");
                }
            }
        }

        // Send moderation
        $posts = $this->db->where('status', 'moderation')
                          ->where('updated_at = created_at')
                          ->get('post')
                          ->result();
        foreach ($posts as $post) {
            $this->bot_model->send_for_moderation($post);
        }
    }

}