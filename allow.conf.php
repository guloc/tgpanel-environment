<?php

$allowed_robots = [
    // '127.0.0.1',

];

if ( ! defined('BASEPATH')) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo implode("\n", $allowed_robots);
}