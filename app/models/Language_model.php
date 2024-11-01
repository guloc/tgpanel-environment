<?php
class Language_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    public function change_language($new_lang='')
    {
        $new_lang = safe($new_lang, 'alpha');
        $languages = RS_LANGUAGES;
        if ( ! @preg_match('/'.$new_lang.'/', $languages)) die(header('Location: /not_found'));
        setcookie('language', $new_lang, time() + (3600*24*30*12), '/');
        $url = isset($_SERVER['HTTP_REFERER'])
             ? $_SERVER['HTTP_REFERER']
             : '/';
        die(header('Location: ' . $url));
    }
}