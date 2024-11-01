<?php if ( ! defined('ROCKET_SCRIPT')) exit('No direct script access allowed');

function initiator_path() {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    for($i = 0; $i < count($backtrace); $i++) {
        if (isset($backtrace[$i]['class'])) {
            $info = $backtrace[$i];
            break;
        }
    }
    if (empty($info)) return '(Unknown) ';
    $method = $info['function'];
    $class = $info['class'];
    $initiator = trim($method) == 'index'
               ? '('.$class.') '
               : '('.$class.'/'.$method.') ';
    return $initiator;
}

function logg($message, $show_initiator=true){
    if ($show_initiator) {
        $message = initiator_path() . $message;
    }
    log_message('error', $message);
}

function data_log($item, $data='~no~data~passed~') {
    if ($data === '~no~data~passed~') {
        $message = json_encode($item, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    } else {
        $message = $item . ' ' . json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }
    logg($message);
}

function time_log($title, &$start_time, $round=6) {
    $seconds = microtime(true) - $start_time;
    if ($round !== false) {
        $seconds = round($seconds, $round);
    }
    $message = $title . ' ' . $seconds . ' sec.';
    logg($message);
    $start_time = microtime(true);
}

function echo_pre($text, $html_safe=true) {
    echo '<pre>'
         . ( $html_safe ? safe($text) : $text )
         . '</pre>';
}

function data_echo($item, $data='~no~data~passed~') {
    if ($data === '~no~data~passed~') {
        echo '<pre>' . safe(json($item, true, true)) . '</pre>';
        return true;
    }
    $message = '<pre>' . safe($item)
             . ': ' . safe(json($data, true, true)) . '</pre>';
    echo $message;
}

function time_echo($title, &$start_time, $round=6) {
    $seconds = microtime(true) - $start_time;
    if ($round !== false) {
        $seconds = number_format($seconds, $round);
    }
    echo $title . ' ' . $seconds . ' sec.<br><br>';
    $start_time = microtime(true);
    return $start_time;
}