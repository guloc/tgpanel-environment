<?php
$pages = [
    // 'faq',
];

foreach($pages as $page) {
    if (preg_match('/^\/' . $page . '([^\w]|$)/', $_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = str_replace($page, '/page/open/'.$page, $_SERVER['REQUEST_URI']);
    }
}