<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Logs extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ( ! RS_STATUS)
            $this->load->view('technical_works');
        $this->security_model->check_visit();
        $this->security_model->check_auth();
        $this->security_model->only_admin();
    }

    public function index($data=array())
    {
        $data = $this->info_model->get_basic_info($data);
        $data['logs'] = $this->logs();
        $this->load->view('logs', $data);
        $this->security_model->debugger($data);
    }

    public function logs($limit=100)
    {
        // Format date to 'Y-m-d'
        $date = date('Y-m-d');
        // Open according file
        $app = defined('RS_APP_FOLDER')
             ? RS_APP_FOLDER
             : 'app';
        $text = @file_get_contents($app."/logs/log-$date.php");
        // Split it to separate records and order by date desc 
        $content = array_reverse(explode("\nERROR - 20", $text));
        // Apply limit if exists
        if ($limit > 0) $content = array_slice($content, 0, $limit);
        // Custom formatting function for array_map
        $func = function($string) {
            if (strpos($string, '<?') === 0) return 'END';
            // Get date of record and complement year to 4-digit
            $datetime = '20' . substr($string, 0, 17);
            if (strpos($datetime, ' ') === false) {
                logg('(Logs) Empty log', false);
                return 'Empty log';
            }
            // Explode it to date and time
            list($date, $time) = explode(' ', $datetime);
            // Use just time and replace arrow by dash
            // and Prepare text for output
            $string = '<b>' . safe($time) . ' - </b>' . safe(trim(substr($string, 22)));
            // Highlight record if it is 2 min. fresh
            if ($datetime >= time_jump(now(), '-2 minutes')) {
                $string = '<i>' . $string . '</i>';
            }
            return $string;
        };
        $content = array_map($func, $content);
        $text = implode("\n", $content);
        if (page_is('/logs/logs'))
            echo '<title>Logs</title><pre>' . $text . '</pre>';

        else
            return $text;
    }

    public function viewer($filter='')
    {
        // Set filtering by File or Method
        $filter = safe($filter, 'alphadash');
        // Set blacklist of Files and Methods
        $blacklist = [];
        if (isset($_GET['blacklist'])) {
            $bl_str = preg_replace('/[^A-Za-z0-9_\-,]/', '', $_GET['blacklist']);
            $bl_str = preg_replace('/,+/', ',', $bl_str);
            $bl_str = preg_replace('/^,?(.+),?$/', '$1', $bl_str);
            if ( ! empty($bl_str)) {
                $blacklist = explode(',', $bl_str);
            }
        }
        // Set limit of records to show
        $limit = 0;
        if ( ! empty($_GET['limit'])) {
            $limit = intval($_GET['limit']);
        }
        // Set date
        $date = (isset($_GET['day']) and preg_match('/^\d{2}-\d{2}$/', $_GET['day']))
              ? date('Y') . '-' . $_GET['day']
              : date('Y-m-d');
        $day = substr($date, 5);
        // Generate navigation url
        $params = [];
        if ( ! empty($limit)) $params['limit'] = $limit;
        if ( ! empty($blacklist)) $params['blacklist'] = implode(',', $blacklist);
        $params_str = http_build_query($params);
        $link = parse_url($_SERVER['REQUEST_URI'])['path']
              . (empty($params_str)
                  ? '?day='
                  : "?{$params_str}&day="
                );
        $data['nav'] = [
            'prev' => $link . time_jump($date, '-1 day', 'm-d'),
            'next' => $link . time_jump($date, '+1 day', 'm-d')
        ];
        $data['now'] = now();
        $data['filter'] = $filter;
        $data['limit'] = $limit;
        $data['blacklist'] = implode(',', $blacklist);
        $data['day'] = $day;

        // Open according file
        $app = defined('RS_APP_FOLDER')
             ? RS_APP_FOLDER
             : 'app';
        $text = @file_get_contents($app."/logs/log-{$date}.php");
        // Split it to separate records and order by date desc 
        $content = array_reverse(explode("\nERROR - 20", $text));
        // Apply filter or blacklist if exists
        $tmp = $content;
        $content = [];
        foreach ($tmp as $n => $line) {
            preg_match('/^\d\d-\d\d-\d\d \d\d:\d\d:\d\d --> \((\w+)\/(\w+)\)/', $line, $matches);
            if (empty($matches)) {
                preg_match('/^\d\d-\d\d-\d\d \d\d:\d\d:\d\d --> \((\w+)\)/', $line, $matches);
                $match_string = empty($matches)
                              ? ''
                              : $matches[1];
            } else {
                $match_string = ($matches[1] ?? '').' '.($matches[2] ?? '');
            }
            $check = true;
            if ( ! empty($filter) and mb_strpos($match_string, $filter) === false) {
                 $check = false;
            }
            if ($check) {
                foreach ($blacklist as $item) {
                    if ($matches and mb_strpos($match_string, $item) !== false) {
                        $check = false;
                    }
                }
            }
            if ($check) $content []= $line;
        }
        // Apply limit if exists
        if ($limit > 0) $content = array_slice($content, 0, $limit);
        // Custom formatting function for array_map
        $func = function($string) {
            if (mb_strpos($string, '<?') === 0) return 'END';
            // Get date of record and complement year to 4-digit
            $datetime = '20' . mb_substr($string, 0, 17);
            if (mb_strpos($datetime, ' ') === false) {
                logg('(Logs) Empty log', false);
                return 'Empty log';
            }
            // Explode it to date and time
            list($date, $time) = explode(' ', $datetime);
            // Prepare text for output
            $fold_class = empty($_COOKIE['log_col'])
                        ? ''
                        : 'folded';
            $filter_class = empty($_COOKIE['log_filter_mode'])
                          ? 'filter_link'
                          : 'filter_link blacklist';
            $string = safe(trim(mb_substr($string, 22)));
            $string = preg_replace('/^\((\w+)\/(\w+)\)/', '<span class="'.$filter_class.'">$1</span>: <span class="'.$filter_class.'">$2</span>', $string);
            $string = preg_replace('/^\((\w+)\)/', '<span class="'.$filter_class.'">$1</span>', $string);
            $data_id = '';
            if (mb_strpos($string, "\n") !== false) {
                $hash = md5($string.rand());
                $string = mb_substr($string, 0, mb_strpos($string, "\n"))
                        . '<div id="body_'.$hash.'" class="info-body '.$fold_class.'">' . mb_substr($string, mb_strpos($string, "\n") + 1) . '</div>';
                $data_id = 'data-id="'.$hash.'"';
            }
            if (empty($data_id)) $fold_class = '';
            $string = '<b class="time '.$fold_class.'" '.$data_id.'>' . safe($time) . '</b> ' . $string;
            // Highlight record if it is 2 min. fresh
            if ($datetime >= time_jump(now(), '-2 minutes')) {
                $string = '<i>' . $string . '</i>';
            }
            return $string;
        };
        $content = array_map($func, $content);
        $text = implode("\n", $content);
        $data['text'] = $text;
        if ( ! empty($_COOKIE['log_light']))
            $this->load->view('../system/logs', $data);
        else
            $this->load->view('logs_viewer', $data);
        // $this->security_model->debugger($data);
    }
}
