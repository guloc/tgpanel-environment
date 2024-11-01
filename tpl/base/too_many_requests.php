<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>
<?
    $this->load->view('errors/html/error_custom', [
        'error' => lang('too_many_requests'),
        // 'no_links' => true
    ]);
?>