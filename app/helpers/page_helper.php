<?php if ( ! defined('ROCKET_SCRIPT')) exit('No direct script access allowed');

function current_page() {
    $page = empty($_SERVER['REDIRECT_URL'])
          ? $_SERVER['REQUEST_URI']
          : $_SERVER['REDIRECT_URL'];
    if (mb_strpos($page, '/index.php/') === 0) {
        $page = mb_substr($page, 10);
    }
    return $page;
}

function page_is($request_uri) {
    if ($request_uri == '/') {
        return preg_match('/^\/(\W|$)/', $_SERVER['REQUEST_URI']);
    } else {
        return strpos(current_page(), $request_uri) === 0;
    }
}

function page_strictly_is($request_uri) {
    return $request_uri == current_page();
}

function page_in($array) {
    $result = false;
    foreach ($array as $request_uri) {
        if (page_is($request_uri)) {
            $result = true;
        }
    }
    return $result;
}

function page_strictly_in($array) {
    $result = false;
    foreach ($array as $request_uri) {
        if (page_strictly_is($request_uri)) {
            $result = true;
        }
    }
    return $result;
}

function csrf_token(){
    $CI =& get_instance();
    $input_field = '';
    if ($CI->config->item('csrf_protection') === TRUE)
    {
        $_name = $CI->security->get_csrf_token_name();
        $_value = html_escape($CI->security->get_csrf_hash());
        $input_field .= "<input type=\"hidden\""
                            . " name=\"$_name\""
                            . " value=\"$_value\""
                            . " style=\"display:none;\" />\n";
    }
    return $input_field;
}

function csrf_token_name() {
    $CI =& get_instance();
    $name = '';
    if ($CI->config->item('csrf_protection') === true) {
        $name = $CI->security->get_csrf_token_name();
    }
    return $name;
}

function csrf_token_value(){
    $CI =& get_instance();
    $token = '';
    if ($CI->config->item('csrf_protection') === TRUE)
    {
        $token = $CI->security->get_csrf_hash();
    }
    return $token;
}

function safe_href($url) {
    return ' href="' . safe($url, 'url') . '" target="_blank" rel="noopener noreferrer nofollow" ';
}

// Appends last change time to relative file url as a GET parameter
function latest($file_path) {
    return $file_path . '?' . @filemtime(substr($file_path, 1));
}

function not_found() {
    die(header('Location: /not_found'));
}

function text_short($text, $length) {
    if (mb_strlen($text) > $length) {
        $text = mb_substr($text, 0, $length - 3) . '...';
    }
    return $text;
}

