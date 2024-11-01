<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Send_posts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('post_model');
        $this->load->model('bot_model');
        $this->load->model('channel_model');
    }
    
    public function index()
    {
        if (!is_cli()) {
            die('This script can only be run from command line');
        }

        echo "Starting to send posts...\n";
        
        // Get queued posts
        $posts = $this->db->where('status', 'queued')
                         ->where('pub_date <=', now())
                         ->get('post')
                         ->result();
        
        echo "Found " . count($posts) . " posts to send\n";
        
        foreach ($posts as $post) {
            echo "\nProcessing post #{$post->id}:\n";
            echo "Content: " . substr($post->content, 0, 100) . "...\n";
            
            $channel = $this->channel_model->get($post->channel_id);
            if (empty($channel)) {
                echo "ERROR: Channel not found for post #{$post->id}\n";
                continue;
            }
            
            echo "Channel: {$channel->name} (ID: {$channel->id}, UID: {$channel->uid})\n";
            
            try {
                if ($channel->platform == 'telegram') {
                    echo "Sending to Telegram channel...\n";
                    $result = $this->bot_model->send_post($channel->uid, $post);
                    echo "Send result: " . print_r($result, true) . "\n";
                }
            } catch (Exception $e) {
                echo "Error sending post #{$post->id}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nFinished sending posts.\n";
    }
}
