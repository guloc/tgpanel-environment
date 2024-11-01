<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

function getExtraData() {
    return [
        'main_menu' => [
            '/channels' => [
                'icon' => 'bx-bar-chart-square',
                'title' => lang('channels_manage'),
            ],
            '/parsing' => [
                'icon' => 'bx-search-alt-2',
                'title' => lang('parsing'),
            ],
            '/posting' => [
                'icon' => 'bxs-edit',
                'title' => lang('posting'),
            ],
            '/groups' => [
                'icon' => 'bx-chat',
                'title' => lang('groups'),
            ],
            '/logs' => [
                'icon' => 'bxs-error',
                'title' => lang('error_log'),
                'admin' => true,
            ],
            '/users' => [
                'icon' => 'bxs-user',
                'title' => lang('users'),
                'admin' => true,
            ],
            '/settings' => [
                'icon' => 'bxs-cog',
                'title' => lang('settings'),
                'admin' => true,
            ],
        ],
        'languages' => [
            'en' => [
                'icon' => '/assets/img/core-img/l5.jpg',
                'title' => 'English'
            ],
            'ru' => [
                'icon' => '/assets/img/core-img/l4.jpg',
                'title' => 'Russian'
            ],
            'de' => [
                'icon' => '/assets/img/core-img/l2.jpg',
                'title' => 'German'
            ],
            'it' => [
                'icon' => '/assets/img/core-img/l3.jpg',
                'title' => 'Italian'
            ],
        ],
    ];
}