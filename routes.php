<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

// allow.conf
if (preg_match('/^\/allow\.conf/', $_SERVER['REQUEST_URI'])) {
    $text = @file_get_contents('allow.conf.full');
    $text = preg_replace('/(\r\n|\n)\#.+(\r\n|\n)/', "\n", $text);
    $text = preg_replace('/^\#.+(\r\n|\n)/', '', $text);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $text;
    exit();
}

$routes = array(
    '/^\/assets\/php\/ajax\/autopostProcess\.php/' => '/autopost_handler',
);
// $excludes = [
//     '/^\/$/',
// ];

foreach($routes as $from => $to) {
    if ($_SERVER['REQUEST_URI'] == $from) {
        $_SERVER['REQUEST_URI'] = $to;
    } elseif (preg_match('/^\//', $from) or preg_match('/\/$/', $from)) {
        $_SERVER['REQUEST_URI'] = preg_replace($from.'i', $to, $_SERVER['REQUEST_URI']);
    }
}
