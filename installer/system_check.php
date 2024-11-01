<?php
defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class SystemCheck {
    private $requirements = [];
    private $errors = [];
    private $warnings = [];

    public function __construct() {
        $this->requirements = [
            'php_version' => '8.2.0',
            'extensions' => [
                'curl',
                'json',
                'mbstring',
                'openssl',
                'pdo',
                'xml',
                'zip',
                'gd',
                'iconv',
                'mysqli'
            ],
            'functions' => [
                'proc_open'
            ],
            'optional_functions' => [
                'posix_kill',
                'pcntl_signal',
                'pcntl_alarm'
            ],
            'binaries' => [
                'ffmpeg' => 'FFmpeg для обработки медиафайлов'
            ]
        ];
    }

    public function checkAll() {
        $this->checkPHPVersion();
        $this->checkExtensions();
        $this->checkFunctions();
        $this->checkOptionalFunctions();
        $this->checkBinaries();
        $this->checkIonCube();
        $this->checkCLIVersion();
        $this->checkOpenBasedir();
        $this->checkWritableDirectories();
        
        return [
            'success' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
    }

    private function checkPHPVersion() {
        if (version_compare(PHP_VERSION, $this->requirements['php_version'], '<')) {
            $this->errors[] = [
                'type' => 'php_version',
                'message' => "Требуется PHP версии {$this->requirements['php_version']} или выше. Текущая версия: " . PHP_VERSION
            ];
        }
    }

    private function checkExtensions() {
        foreach ($this->requirements['extensions'] as $ext) {
            if (!extension_loaded($ext)) {
                $this->errors[] = [
                    'type' => 'extension',
                    'message' => "Отсутствует обязательное расширение PHP: {$ext}"
                ];
            }
        }
    }

    private function checkFunctions() {
        foreach ($this->requirements['functions'] as $func) {
            if (!function_exists($func)) {
                $this->errors[] = [
                    'type' => 'function',
                    'message' => "Функция {$func} отключена или недоступна. Эта функция необходима для работы скрипта."
                ];
            }
        }
    }

    private function checkOptionalFunctions() {
        $missing_functions = [];
        foreach ($this->requirements['optional_functions'] as $func) {
            if (!function_exists($func)) {
                $missing_functions[] = $func;
            }
        }
        
        if (!empty($missing_functions)) {
            $this->warnings[] = [
                'type' => 'optional_functions',
                'message' => "Следующие функции недоступны: " . implode(', ', $missing_functions) . ". " .
                           "Это не критично, но некоторые дополнительные возможности MadelineProto могут быть ограничены."
            ];
        }
    }

    private function checkBinaries() {
        foreach ($this->requirements['binaries'] as $binary => $description) {
            $command = sprintf("command -v %s 2>/dev/null", escapeshellarg($binary));
            $path = shell_exec($command);
            
            if (empty($path)) {
                $this->errors[] = [
                    'type' => 'binary',
                    'message' => "Не найден {$description} ({$binary}). Установите его через пакетный менеджер."
                ];
            } else {
                // Для FFmpeg проверяем основные кодеки
                if ($binary === 'ffmpeg') {
                    $codecs = shell_exec("ffmpeg -codecs 2>/dev/null");
                    $required_codecs = ['libx264', 'libvpx', 'libmp3lame', 'aac'];
                    $missing_codecs = [];
                    
                    foreach ($required_codecs as $codec) {
                        if (strpos($codecs, $codec) === false) {
                            $missing_codecs[] = $codec;
                        }
                    }
                    
                    if (!empty($missing_codecs)) {
                        $this->warnings[] = [
                            'type' => 'ffmpeg_codecs',
                            'message' => "FFmpeg установлен, но отсутствуют некоторые кодеки: " . implode(', ', $missing_codecs)
                        ];
                    }
                }
            }
        }
    }

    private function checkIonCube() {
        if (!extension_loaded('ionCube Loader')) {
            $this->warnings[] = [
                'type' => 'ioncube',
                'message' => "IonCube Loader не установлен. Это может потребоваться для работы некоторых дополнительных модулей."
            ];
        }
    }

    private function checkCLIVersion() {
        $cli_version = shell_exec('php -v');
        if ($cli_version) {
            preg_match('/PHP (\d+\.\d+\.\d+)/', $cli_version, $matches);
            if (!empty($matches[1])) {
                $cli_php_version = $matches[1];
                if ($cli_php_version !== PHP_VERSION) {
                    $this->warnings[] = [
                        'type' => 'cli_version',
                        'message' => "Версия PHP CLI ({$cli_php_version}) отличается от версии PHP веб ({PHP_VERSION}). " .
                                   "Рекомендуется использовать одинаковые версии."
                    ];
                }
            }
        }
    }

    private function checkOpenBasedir() {
        $open_basedir = ini_get('open_basedir');
        if (!empty($open_basedir)) {
            $this->warnings[] = [
                'type' => 'open_basedir',
                'message' => "Установлено ограничение open_basedir. Это может вызвать проблемы с некоторыми функциями MadelineProto. " .
                           "Текущее значение: {$open_basedir}"
            ];
        }
    }

    private function checkWritableDirectories() {
        $directories = [
            '../app/cache' => 'Кэш приложения',
            '../app/logs' => 'Логи приложения',
            '../session.madeline' => 'Сессии Telegram',
            '../assets/upload' => 'Загрузка файлов'
        ];

        foreach ($directories as $dir => $description) {
            if (!file_exists($dir)) {
                $this->errors[] = [
                    'type' => 'directory',
                    'message' => "Директория {$dir} ({$description}) не существует"
                ];
            } elseif (!is_writable($dir)) {
                $this->errors[] = [
                    'type' => 'directory',
                    'message' => "Директория {$dir} ({$description}) недоступна для записи. Установите права 755 или 777."
                ];
            }
        }
    }
}

function check_database_connection($host, $user, $pass, $db) {
    try {
        $mysqli = @new mysqli($host, $user, $pass, $db);
        
        if ($mysqli->connect_error) {
            return [
                'success' => false,
                'error' => 'Ошибка подключения к базе данных: ' . $mysqli->connect_error
            ];
        }

        // Проверяем права на создание таблиц
        $test_query = "CREATE TABLE IF NOT EXISTS `_test_table` (`id` int(11) NOT NULL);";
        if (!$mysqli->query($test_query)) {
            return [
                'success' => false,
                'error' => 'Недостаточно прав для создания таблиц в базе данных. Убедитесь, что у пользователя есть права CREATE TABLE.'
            ];
        }
        $mysqli->query("DROP TABLE IF EXISTS `_test_table`");

        return ['success' => true];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Ошибка подключения к базе данных: ' . $e->getMessage()
        ];
    }
}

function display_error($message) {
    return '
    <div class="alert alert-danger" role="alert">
        <div class="d-flex">
            <div class="pe-3">
                <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div>
                <h4 class="alert-heading">Ошибка!</h4>
                <p class="mb-0">' . htmlspecialchars($message) . '</p>
            </div>
        </div>
    </div>';
}

function display_warning($message) {
    return '
    <div class="alert alert-warning" role="alert">
        <div class="d-flex">
            <div class="pe-3">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <div>
                <h4 class="alert-heading">Предупреждение</h4>
                <p class="mb-0">' . htmlspecialchars($message) . '</p>
            </div>
        </div>
    </div>';
}

function display_success($message) {
    return '
    <div class="alert alert-success" role="alert">
        <div class="d-flex">
            <div class="pe-3">
                <i class="fas fa-check-circle fa-2x"></i>
            </div>
            <div>
                <h4 class="alert-heading">Успешно!</h4>
                <p class="mb-0">' . htmlspecialchars($message) . '</p>
            </div>
        </div>
    </div>';
}
