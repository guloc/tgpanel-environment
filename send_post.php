<?php
define('ROCKET_SCRIPT', true);
require_once('index.php');

// Get CI instance
$CI =& get_instance();

// Load required models
$CI->load->model('post_model');
$CI->load->model('bot_model');
$CI->load->model('channel_model');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to send posts
try {
    echo "Starting to send posts...\n";
    
    // Get queued posts
    $posts = $CI->db->where('status', 'queued')
                    ->where('pub_date <=', now())
                    ->get('post')
                    ->result();
    
    echo "Found " . count($posts) . " posts to send\n";
    
    foreach ($posts as $post) {
        echo "\nProcessing post #{$post->id}:\n";
        echo "Content: " . substr($post->content, 0, 100) . "...\n";
        
        $channel = $CI->channel_model->get($post->channel_id);
        if (empty($channel)) {
            echo "ERROR: Channel not found for post #{$post->id}\n";
            continue;
        }
        
        echo "Channel: {$channel->name} (ID: {$channel->id}, UID: {$channel->uid})\n";
        
        try {
            if ($channel->platform == 'telegram') {
                echo "Sending to Telegram channel...\n";
                $result = $CI->bot_model->send_post($channel->uid, $post);
                echo "Send result: " . print_r($result, true) . "\n";
            }
        } catch (Exception $e) {
            echo "Error sending post #{$post->id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nFinished sending posts.\n";
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
