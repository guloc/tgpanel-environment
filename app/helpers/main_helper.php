<?php if ( ! defined('ROCKET_SCRIPT')) exit('No direct script access allowed');

function ai_get_text(
    $prompt,
    $context = "",
    $max_tokens=3000,
    $temperature = 0.7,
    $top_p = 1,
    $frequency_penalty = 0,
    $presence_penalty = 0,
    $userid = ""
) {
    $CI =& get_instance();
    $provider = $CI->config_model->get('llm_provider');
    if ($provider == 'openrouter')
        return $CI->openrouter_model->get_text(
            $prompt,
            $context,
            $max_tokens,
            $temperature,
            $top_p,
            $frequency_penalty,
            $presence_penalty,
            $userid
        );
    else
        return $CI->openai_model->get_text(
            $prompt,
            $context,
            $max_tokens,
            $temperature,
            $top_p,
            $frequency_penalty,
            $presence_penalty,
            $userid
        );
}

function json($object, $pretty_print=true, $unicode=false) {
    if ($pretty_print)
        if($unicode)
            return json_encode($object, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        else
            return json_encode($object, JSON_PRETTY_PRINT);
    return json_encode($object);
}

function unjson($string, $return_array=true) {
    if (is_array($string))
        return $return_array ? $string : (object) $string;
    if (is_object($string))
        return $return_array ? (array) $string : $string;
    if ( ! is_string($string))
        return null;
    return @json_decode($string, $return_array);
}

function ip_is($ip) {
    return $_SERVER['REMOTE_ADDR'] == $ip;
}

function ip_in($ip_list) {
    return in_array($_SERVER['REMOTE_ADDR'], $ip_list);
}

function siteurl() {
    return RS_PROTOCOL . '://' . site_host();
}

function site_host() {
    $CI =& get_instance();
    $mirrors = $CI->config_model->get('mirrors', true);
    $host = RS_HOST;
    if (empty($mirrors)) return RS_HOST;
    foreach ($mirrors as $mirror) {
        if ((isset($_SERVER['HTTP_FROM_HOST']) and $_SERVER['HTTP_FROM_HOST'] == $mirror)
          or (isset($_COOKIE['domain']) and $_COOKIE['domain'] == $mirror)
        ) {
            $host = $mirror;
            break;
        }
    }
    return $host;
}

function get_data($path) {
    return unjson(@file_get_contents($path));
}

function save_data($path, $data) {
    return @file_put_contents($path, json($data, false));
}

function post_data($path, $data, $timeout=3, $csrf_token=false) {
    $add_header = '';
    if ( ! empty($csrf_token)) {
        $CI =& get_instance();
        $csrf_token_name = $this->config->config['csrf_token_name'];
        $csrf_cookie_name = $this->config->config['csrf_cookie_name'];
        $add_header = "\r\nCookie: $csrf_cookie_name=$csrf_token\r\n";
        $data[$csrf_token_name] = $csrf_token;
    }
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header'  => "Content-type: application/x-www-form-urlencoded".$add_header,
        'content' => http_build_query($data),
        'timeout' => $timeout
    ]]);
    $response = @file_get_contents($path, false, $ctx);
    if (unjson($response)) {
        $response = unjson($response);
    }
    return $response;
}

function cache_var($name, $data, $ttl=60) {
    $CI =& get_instance();
    if ($CI->config->config['cache_hash_names'] == 'md5') {
        $name = md5($name);
    }
    $CI->cache_model->save($name, $data, $ttl);
    return cached_var($name);
}

function cached_var($name) {
    $CI =& get_instance();
    if ($CI->config->config['cache_hash_names'] == 'md5') {
        $name = md5($name);
    }
    return $CI->cache_model->get($name);
}

function cache_data($name, $data, $ttl=60) {
    $CI =& get_instance();
    cache_var($name, json($data, false), $ttl);
    return cached_data($name);
}

function cached_data($name) {
    return unjson(cached_var($name));
}

function renew_cache($name) {
    $CI =& get_instance();
    if ($name == 'all') {
        return $CI->cache_model->clean();
    } elseif ($CI->config->config['cache_hash_names'] == 'md5') {
        $name = md5($name);
    }
    return $CI->cache_model->delete($name);
}

function error_data($message) {
    return [
        'error' => true,
        'message' => $message
    ];
}

function is_valid($type, $var) {
    $regex = [
        'int' => '/^-?\d+$/',
        'uint' => '/^\d+$/',
        'float' => '/^-?\d+(\.\d+)?$/',
        'datetime' => '/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/',
        'date' => '/^\d{4}\-\d{2}\-\d{2}$/',
        'time' => '/^\d{2}:\d{2}:\d{2}$/',
        'ip' => '/^(((\d{1,3}\.){3}\d{1,3})|(([a-z-A-Z0-9]{0,4}:){5}[a-z-A-Z0-9]{0,4}))$/',
        'ipv4' => '/^(\d{1,3}\.){3}\d{1,3}$$/',
        'ipv6' => '/^([a-z-A-Z0-9]{0,4}:){5}[a-z-A-Z0-9]{0,4}$/',
    ];
    if (isset($regex[$type])) {
        return preg_match($regex[$type], $var);
    } else {
        return false;
    }
}

// Returns XSS-safe string
// ATTENTION: Output by default isn't safe if posted in JS
function safe($string, $allowed_chars=false) {
    if ( ! $allowed_chars) {
        return htmlspecialchars($string, ENT_QUOTES);
    } elseif ($allowed_chars == 'escape') {
        return str_replace("'", "\\'", htmlspecialchars($string, ENT_QUOTES));
    } elseif ($allowed_chars == 'int') {
        $pattern = '/[^-?0-9-]/';
    } elseif ($allowed_chars == 'float') {
        $pattern = '/[^0-9-\.]/';
    } elseif ($allowed_chars == 'alpha') {
        $pattern = '/[^A-Za-z]/';
    } elseif ($allowed_chars == 'alphanum') {
        $pattern = '/[^A-Za-z0-9]/';
    } elseif ($allowed_chars == 'alphadash') {
        $pattern = '/[^A-Za-z0-9_\-]/';
    } elseif ($allowed_chars == 'email') {
        return filter_var(trim(strip_tags($string)), FILTER_SANITIZE_EMAIL);
    } elseif ($allowed_chars == 'url') {
        $string = filter_var($string, FILTER_SANITIZE_URL);
        $string = str_ireplace(['javascript:', '&#', ';', '"', '\'', '<', '>'], '', $string);
        return $string;
    } elseif ($allowed_chars == 'ip') {
        $pattern = '/[^0-9A-Za-z\.:]/';
    } elseif ($allowed_chars == 'datetime') {
        $pattern = '/[^0-9 \-:]/';
    } else {
        $pattern = '/[^' . $allowed_chars . ']/';
    }
    return preg_replace($pattern, '', $string);
}

function round_up($amount, $accuracy=0) {
    return round($amount, $accuracy, PHP_ROUND_HALF_UP);
}

function round_down($amount, $accuracy=0) {
    return round($amount, $accuracy, PHP_ROUND_HALF_DOWN);
}

function xlang($data) {
    $arr = @json_decode($data, true);
    if (is_array($arr)) $data = $arr;
    if (is_array($data)) {
        if (key_exists(lang('lang'), $data)) {
            return $data[lang('lang')];
        } elseif (key_exists(RS_DEFAULT_LANGUAGE, $data)) {
            return $data[RS_DEFAULT_LANGUAGE];
        }
    }
    return $data;
}

function group_result($query, $field='id') {
    if (empty($query->result()) OR empty($field)) {
        return [];
    }
    $result = array();
    foreach ($query->result() as $item) {
        if (empty($result[$item->{$field}]))
            $result[$item->{$field}] = [];
        $result[$item->{$field}] []= $item;
    }
    return $result;
}

function random_str($length) {
    $rand = openssl_random_pseudo_bytes($length);
    return substr(bin2hex($rand), 0, $length);
}

function tg_html($text, $reverse=false) {
    if (empty($text))
        $text = '';
    if ($reverse) {
        $text = '<p>' . str_replace("\n", '</p><p>', $text) . '</p>';
        $text = str_replace('<span class="tg-spoiler">', '<spoiler>', $text);
        $text = str_replace('</span>', '</spoiler>', $text);
        $text = str_replace('<p></p>', '<p><br></p>', $text);
        $blocks = ['pre', 'blockquote'];
        foreach ($blocks as $tag) {
            preg_match_all('/<'.$tag.'>(.+)<\/'.$tag.'>/U', $text, $match);
            foreach ($match[1] as $innerText) {
                $fixedText = str_replace('</p><p>', "\n", $innerText);
                $text = str_replace($innerText, $fixedText, $text);
            }
            $text = str_replace('<p><'.$tag.'>', '<'.$tag.'>', $text);
            $text = str_replace('</'.$tag.'></p>', '</'.$tag.'>', $text);
        }
    } else {
        $text = str_replace('<p>', '', $text);
        $text = str_replace('</p>', "\n", $text);
        $text = str_replace('<spoiler>', '<span class="tg-spoiler">', $text);
        $text = str_replace('</spoiler>', '</span>', $text);
        $text = str_replace(' rel="noopener noreferrer" target="_blank"', '', $text);
        $text = str_replace('<br>', '', $text);
        $text = str_replace(' class="ql-syntax" spellcheck="false"', '', $text);
        $text = str_replace('</blockquote><blockquote>', '', $text);
    }
    return $text;
}

function vk_format($text) {
    $text = str_replace('<p>', '', $text);
    $text = str_replace('</p>', "\n", $text);
    $text = trim(strip_tags($text));
    return $text;
}

function media_type($file_name) {
    $CI =& get_instance();
    $types = $CI->bot_model->media_extensions;
    preg_match('/\.(\w+)$/', $file_name, $match);
    foreach ($types as $type => $extensions) {
        if (in_array(strtolower($match[1]), $extensions))
            return $type;
    }
    return 'unknown';
}
