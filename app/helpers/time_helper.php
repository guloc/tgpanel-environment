<?php if ( ! defined('ROCKET_SCRIPT')) exit('No direct script access allowed');

function now($mask='Y-m-d H:i:s') {
    return date($mask);
}

function iso_time($datetime, $tz=0) {
    if ($tz >= 10) {
        $time_zone = '+' . $tz . ':00';
    } elseif ($tz >= 0) {
        $time_zone = '+0' . $tz . ':00';
    } elseif ($tz >= -10) {
        $time_zone = '-0' . $tz . ':00';
    } else {
        $time_zone = '-' . $tz . ':00';
    }
    return str_replace(' ', 'T', $datetime) . $time_zone;
}

function time_jump($datetime, $interval, $mask='Y-m-d H:i:s') {
    return date($mask, strtotime($datetime . ' ' . $interval));
}

function time_format($datetime, $mask='Y-m-d') {
    return date($mask, strtotime($datetime));
}