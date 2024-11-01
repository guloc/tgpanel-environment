<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

$config = [
	'error_prefix' => '<div>• ',
	'error_suffix' => '</div>',

	'users/index' => [
		[
			'field' => 'login',
			'label' => 'lang:login',
			'rules' => 'trim|required|alpha_dash|is_unique[user.login]|min_length[4]|max_length[64]|regex_match[/[a-zA-Z]/]'
		],
		[
			'field' => 'password',
			'label' => 'lang:password',
			'rules' => 'trim|required|min_length[8]|max_length[1024]|differs[login]'
		],
		[
			'field' => 'type',
			'label' => 'lang:role',
			'rules' => 'required|in_list[user,admin]'
		]
	],

	'settings/index' => [
		[
			'field' => 'phone',
			'label' => 'lang:phone',
			'rules' => 'trim|numeric|is_unique[user.phone]|min_length[11]|max_length[32]'
		],
		[
			'field' => 'telegram',
			'label' => 'lang:telegram',
			'rules' => 'trim|alpha_dash|min_length[5]|max_length[255]'
		],
		[
			'field' => 'skype',
			'label' => 'lang:skype',
			'rules' => 'trim|alpha_dash|min_length[5]|max_length[255]'
		],
		[
			'field' => 'first_name',
			'label' => 'lang:first_name',
			'rules' => 'trim|max_length[255]|xss_clean'
		],
		[
			'field' => 'last_name',
			'label' => 'lang:last_name',
			'rules' => 'trim|max_length[255]|xss_clean'
		],
		[
			'field' => 'gender',
			'label' => 'lang:gender',
			'rules' => 'trim|in_list[male,female]'
		],
		[
			'field' => 'region',
			'label' => 'lang:country',
			'rules' => 'trim|exact_length[2]|alpha'
		],
		[
			'field' => 'birth_date',
			'label' => 'lang:birth_date',
			'rules' => 'trim|exact_length[10]|regex_match[/^\d{4}\-\d{2}\-\d{2}$/]'
		],
	],

];