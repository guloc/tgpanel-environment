<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

/*
| -------------------------------------------------------------------------
| Pagination Sections
| -------------------------------------------------------------------------
|
|	http://codeigniter.co/guide/libraries/pagination
|
*/

$config['num_links'] = 5;
$config['per_page'] = 10;
$config['use_page_numbers'] = TRUE;
$config['attributes']['rel'] = FALSE;
// Container
$config['full_tag_open'] = '<ul class="pagination">';
$config['full_tag_close'] = '</ul>';
// Number Links
$config['display_pages'] = TRUE;
$config['num_tag_open'] = '<li class="page-item">';
$config['num_tag_close'] = '</li>';
// Current Link
$config['cur_tag_open'] = '<li class="page-item active"><div>';
$config['cur_tag_close'] = '</div></li>';
// Next/Prev Links
$config['next_link'] = '&gt;';
$config['prev_link'] = '&lt;';
$config['next_tag_open'] = '<li class="page-item">';
$config['next_tag_close'] = '</li>';
$config['prev_tag_open'] = '<li class="page-item">';
$config['prev_tag_close'] = '</li>';
// First/Last Links
$config['first_link'] = FALSE;
$config['last_link'] = FALSE;