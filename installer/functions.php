<?php

function random_str($length) {
    $rand = openssl_random_pseudo_bytes($length);
    return substr(bin2hex($rand), 0, $length);
}

function save_config($config) {
    $json = json_encode($config, JSON_PRETTY_PRINT);
    return @file_put_contents('../config.php', "<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));\n\$json_config = '{$json}';");
}

function check_mysql($db, $login, $password, $host) {
    $mysql = @mysqli_connect($host, $login, $password, $db);
    
    if($mysql) {
        return $mysql;
    } else {
        return false;
    }
}

function set_mysql($constants=array()) {
    include('config.php');
    $cfg_error = 'Пожалуйста! Создайте файл config.php в корне сайта.';
    if(isset($json_config) && !empty($json_config)) {
        $json_config = @json_decode($json_config, true);

        if(!is_array($json_config)) {
            die($cfg_error);
        }
    } else {
        die($cfg_error);
    }

    $json_config['DB_HOST'] = $constants['DB_HOST'];
    $json_config['DB_LOGIN'] = $constants['DB_LOGIN'];
    $json_config['DB_PASS'] = $constants['DB_PASS'];
    $json_config['DB_NAME'] = $constants['DB_NAME'];

    // Calculate the optimal hashing cost for this server
    $timeTarget = 0.05;
    $cost = 8;
    do {
        $cost++;
        $start = microtime(true);
        password_hash("test_string", PASSWORD_DEFAULT, ["cost" => $cost]);
    } while ((microtime(true) - $start) < $timeTarget);
    $json_config['HASHING_COST'] = $cost;

    $json = json_encode($json_config, JSON_PRETTY_PRINT);
    return save_config($json_config);
}

function set_mysql_structure($mysql) {
    if(!$mysql || !($mysql instanceof mysqli)) {
        logg("[ERROR] Invalid MySQL connection in set_mysql_structure");
        return false;
    }
    
    $structure = @file_get_contents('mysql_structure.sql');
    
    if(!empty($structure)) {
        try {
            $result = @mysqli_multi_query($mysql, $structure);
            while (@mysqli_next_result($mysql)) {;}
            if (!$result) {
                logg("[ERROR] Failed to import SQL structure: " . mysqli_error($mysql));
            }
            return $result;
        } catch (Exception $e) {
            logg("[ERROR] Exception while importing SQL structure: " . $e->getMessage());
            return false;
        }
    } else {
        logg("[ERROR] SQL structure file is empty or not readable");
        return false;
    }
}

function set_system($mysql) {
    if(!$mysql || !($mysql instanceof mysqli)) {
        logg("[ERROR] Invalid MySQL connection in set_system");
        return false;
    }

    $system['HOST'] = $_SERVER['HTTP_HOST'];
    $system['PROTOCOL'] = 'https';
    $system['SITE_URL'] = $system['PROTOCOL'].'://'.$system['HOST'];
    
    // TEMPLATE SETTINGS
    $system['TEMPLATE'] = 'base';

    // FOLDERS SETTINGS
    $system['APP_FOLDER'] = 'app';
    $system['TPL_FOLDER'] = 'tpl';
    $system['VIEW_FOLDER'] = $system['TPL_FOLDER'].'/'.$system['TEMPLATE'];
    $system['SYSTEM_FOLDER'] = 'system';
    
    // SECRET SETTINGS
    $system['SALT'] = random_str(32);
    $system['CRON_KEY'] = random_str(32);
    $system['KEY'] = random_str(32);
    $system['TOKEN'] = random_str(32);
    
    // STATUS (on/off)
    $system['STATUS'] = 1;
    
    // LANGUAGES
    $system['LANGUAGES'] = 'ru';
    $system['DEFAULT_LANGUAGE'] = 'ru';
    
    try {
        // SET PROJECT NAME
        if (!empty(trim($_POST['installer_db']['project_name']))) {
            $project_name = mysqli_real_escape_string($mysql, trim($_POST['installer_db']['project_name']));
            $query = "UPDATE config SET cfg_value='$project_name' WHERE cfg_name='project_name'";
            if (!mysqli_query($mysql, $query)) {
                logg("[ERROR] Failed to update project name: " . mysqli_error($mysql));
            }
        }
        
        include('../config.php');
        if(isset($json_config) && !empty($json_config)) {
            $json_config = (array) @json_decode($json_config);

            if(!is_array($json_config)) {
                logg("[ERROR] Invalid config format in set_system");
                return false;
            }

            foreach($json_config as $key => $val) {
                if(isset($system[$key])) {
                    unset($json_config[$key]);
                }
            }
            
            $json_config = array_merge($system, $json_config);
            return save_config($json_config);
        }
    } catch (Exception $e) {
        logg("[ERROR] Exception in set_system: " . $e->getMessage());
        return false;
    }
    
    return false;
}

function set_config($cfg_data=array()) {
    include('../config.php');
    $cfg_error = 'Проблемы с загрузкой конфигурационного файла...';

    if(isset($json_config) && !empty($json_config)) {
        $json_config = (array) @json_decode($json_config);

        if(!is_array($json_config)) {
            die($cfg_error);
        }
    } else {
        die($cfg_error);
    }

    foreach($json_config as $key => $val) {
        if(isset($cfg_data[$key])) {
            unset($json_config[$key]);
        }
    }
    
    $json_config = array_merge($cfg_data, $json_config);
    return save_config($json_config);
}

function create_admin($mysql, $admin_data=array()) {
    if(!$mysql || !($mysql instanceof mysqli)) {
        logg("[ERROR] Invalid MySQL connection in create_admin");
        return false;
    }

    if(!empty($admin_data)) {
        try {
            $login = mysqli_real_escape_string($mysql, trim($admin_data['login']));
            $password = password_hash($admin_data['password'], PASSWORD_DEFAULT);
            $name = mysqli_real_escape_string($mysql, trim($admin_data['name']));
            $ip = $_SERVER['REMOTE_ADDR'];
            $date = date('Y-m-d H:i:s');

            $query = "INSERT INTO user (login, pswd, type, visible_pages, date_register, ip) 
                     VALUES ('$login', '$password', 'admin', 'all', '$date', '$ip')";
            
            $register_admin = mysqli_query($mysql, $query);
            
            if($register_admin) {
                logg("[SUCCESS] Admin account created successfully");
                return true;
            } else {
                logg("[ERROR] Failed to create admin account: " . mysqli_error($mysql));
                return false;
            }
        } catch (Exception $e) {
            logg("[ERROR] Exception while creating admin: " . $e->getMessage());
            return false;
        }
    } else {
        logg("[ERROR] Empty admin data");
        return false;
    }
}

// Функция для логирования
function logg($message) {
    $log_file = '../app/logs/install_' . date('Y-m-d') . '.log';
    $log_dir = dirname($log_file);
    
    // Создаем директорию для логов если её нет
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Форматируем сообщение
    $log_message = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
    
    // Записываем в файл
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Функция для записи данных в лог
function data_log($title, $data = null) {
    if ($data === null) {
        logg($title);
    } else {
        logg($title . ': ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
