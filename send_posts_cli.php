<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define ROCKET_SCRIPT constant
define('ROCKET_SCRIPT', true);

// Load CodeIgniter
require_once('index.php');

// Get CI instance
$CI =& get_instance();

// Load models
$CI->load->database();
$CI->load->model('Post_model', 'post_model');

echo "Starting to send posts...\n";

try {
    // Call send_queued method
    $CI->post_model->send_queued();
    echo "Posts sent successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
