<?php
header('Content-type:text/html; charset=utf-8');
ini_set('display_errors', 1);
define('ROCKET_SCRIPT', 'INSTALLER');
require_once 'functions.php';
require_once 'system_check.php';

// Функция для загрузки конфигурации
function load_config() {
    if (file_exists('../config.php')) {
        include('../config.php');
        if(isset($json_config) && !empty($json_config)) {
            return json_decode($json_config, true);
        }
    }
    return false;
}

// Проверяем системные требования перед началом установки
$systemCheck = new SystemCheck();
$checkResults = $systemCheck->checkAll();

if(!isset($_GET['step'])) {
    include_once 'welcome.php';
} else if($_GET['step'] == 1) {
    if(isset($_POST['installer_db'])) {
        // Проверяем системные требования перед подключением к БД
        if (!empty($checkResults['errors'])) {
            $message = 'Пожалуйста, исправьте системные требования перед продолжением установки.';
        } else {
            $db = trim($_POST['installer_db']['name']);
            $login = trim($_POST['installer_db']['login']);
            $password = trim($_POST['installer_db']['password']);
            $host = trim($_POST['installer_db']['host']);
            
            // Проверяем корректность введенных данных
            if (empty($db) || empty($login) || empty($host)) {
                $message = 'Пожалуйста, заполните все обязательные поля.';
            } else {
                // Проверяем подключение к базе данных
                $dbCheck = check_database_connection($host, $login, $password, $db);
                
                if ($dbCheck['success']) {
                    $connection = check_mysql($db, $login, $password, $host);
                    
                    if($connection) {
                        $file_config = array(
                            'DB_HOST' => $host,
                            'DB_LOGIN' => $login,
                            'DB_PASS' => $password,
                            'DB_NAME' => $db
                        );
                        
                        try {
                            $set_file = set_mysql($file_config);
                            
                            if($set_file) {
                                $structure = set_mysql_structure($connection);
                                
                                if($structure) {
                                    $system_settings = set_system($connection);
                                    
                                    if($system_settings) {
                                        // Логируем успешную установку
                                        logg("[INSTALL] Database setup completed successfully");
                                        die(header('Location:/installer/index.php?step=2'));
                                    } else {
                                        $message = 'К базе подключились успешно, но возникли проблемы при настройке системы.';
                                        logg("[INSTALL ERROR] Failed to set system settings");
                                    }
                                } else {
                                    $message = 'Невозможно импортировать структуру базы данных. Проверьте права доступа.';
                                    logg("[INSTALL ERROR] Failed to import database structure");
                                }
                            } else {
                                $message = 'Проблема с сохранением конфигурационных данных. Проверьте права доступа к файлам.';
                                logg("[INSTALL ERROR] Failed to save configuration file");
                            }
                        } catch (Exception $e) {
                            $message = 'Произошла ошибка при настройке: ' . $e->getMessage();
                            logg("[INSTALL ERROR] Exception: " . $e->getMessage());
                        }
                    } else {
                        $message = 'Невозможно подключиться к базе данных. Проверьте введенные данные.';
                        logg("[INSTALL ERROR] Failed to connect to database");
                    }
                } else {
                    $message = $dbCheck['error'];
                    logg("[INSTALL ERROR] Database check failed: " . $dbCheck['error']);
                }
            }
        }
    }
    
    include_once 'step1.php';
} else if($_GET['step'] == 2) {
    // Проверяем, была ли выполнена предыдущая стадия установки
    if (!file_exists('../config.php')) {
        die(header('Location:/installer/index.php?step=1'));
    }

    if(isset($_POST['installer_config'])) {
        $config = load_config();
        if (!$config) {
            die('Проблема с загрузкой конфигурационного файла');
        }
    
        $installer_config = $_POST['installer_config'];
        $set_config = set_config($installer_config);
        
        if($set_config) {
            if(isset($_POST['create_admin'])) {
                $admin_data = $_POST['create_admin'];
                
                // Валидация данных администратора
                if (empty($admin_data['login']) || empty($admin_data['password'])) {
                    $message = 'Пожалуйста, заполните все поля для создания администратора';
                } else {
                    $connection = check_mysql(
                        $config['DB_NAME'], 
                        $config['DB_LOGIN'], 
                        $config['DB_PASS'], 
                        $config['DB_HOST']
                    );
                    
                    if ($connection) {
                        $do_create = create_admin($connection, $admin_data);
                        
                        if($do_create) {
                            logg("[INSTALL] Admin account created successfully");
                            die(header('Location:/installer/index.php?step=3'));
                        } else {
                            $message = 'Произошла ошибка во время регистрации администратора!';
                            logg("[INSTALL ERROR] Failed to create admin account");
                        }
                    } else {
                        $message = 'Ошибка подключения к базе данных';
                        logg("[INSTALL ERROR] Failed to connect to database for admin creation");
                    }
                }
            }
        } else {
            $message = 'Произошла ошибка во время сохранения данных!';
            logg("[INSTALL ERROR] Failed to save config");
        }
    }
    
    include_once 'step2.php';
} else if($_GET['step'] == 3) {
    // Проверяем, была ли выполнена предыдущая стадия установки
    if (!file_exists('../config.php')) {
        die(header('Location:/installer/index.php?step=1'));
    }

    $config = load_config();
    if (!$config) {
        die('Проблема с загрузкой конфигурационного файла');
    }

    if(isset($_POST['remove_installer'])) {
        try {
            array_map('unlink', glob("../installer/*.*"));
            rmdir('../installer/');
            logg("[INSTALL] Installation completed, installer removed");
            die(header('Location:/'));
        } catch (Exception $e) {
            logg("[INSTALL ERROR] Failed to remove installer: " . $e->getMessage());
            $message = 'Ошибка при удалении установщика. Пожалуйста, удалите папку installer вручную.';
        }
    }
    include_once 'step3.php';
}
