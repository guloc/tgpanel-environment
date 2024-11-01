<?php
require_once 'system_check.php';
$systemCheck = new SystemCheck();
$checkResults = $systemCheck->checkAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="ie=edge" http-equiv="x-ua-compatible">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="TG Panel">
    <meta name="format-detection" content="telephone=no">
    <title>TG Panel</title>
    <link rel="icon" href="/assets/img/core-img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/animate.css">
    <link rel="stylesheet" href="/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .system-check {
            margin-bottom: 20px;
        }
        .check-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .check-icon {
            margin-right: 10px;
        }
        .check-icon.success {
            color: #28a745;
        }
        .check-icon.error {
            color: #dc3545;
        }
        .check-icon.warning {
            color: #ffc107;
        }
    </style>
</head>
<body class="login-area">
    <div class="main-content- h-100vh">
        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center">
                <div class="col-lg-8">
                    <div class="middle-box">
                        <div class="card-body">
                            <div class="log-header-area card p-4 mb-4 text-center">
                                <h5>Установка TG Panel</h5>
                            </div>
                            
                            <!-- System Requirements Check -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">Проверка системных требований</h6>
                                    <div class="system-check">
                                        <?php if (!empty($checkResults['errors'])): ?>
                                            <?php foreach ($checkResults['errors'] as $error): ?>
                                                <?php echo display_error($error['message']); ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if (!empty($checkResults['warnings'])): ?>
                                            <?php foreach ($checkResults['warnings'] as $warning): ?>
                                                <?php echo display_warning($warning['message']); ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if (empty($checkResults['errors'])): ?>
                                            <?php echo display_success('Все системные требования выполнены'); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (empty($checkResults['errors'])): ?>
                                <!-- Database Connection Form -->
                                <div class="card">
                                    <form class="card-body p-4" method="POST" action="/installer/index.php?step=1">
                                        <h6 class="card-title mb-3">Подключение к базе данных</h6>
                                        
                                        <?php if (isset($message)): ?>
                                            <?php echo display_error($message); ?>
                                        <?php endif; ?>

                                        <div class="mb-4">
                                            <label class="form-label">Имя проекта</label>
                                            <input type="text" class="form-control" name="installer_db[project_name]" value="TG Panel">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Имя базы</label>
                                            <input type="text" class="form-control" name="installer_db[name]" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Логин</label>
                                            <input type="text" class="form-control" name="installer_db[login]" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Пароль</label>
                                            <input type="password" class="form-control" name="installer_db[password]">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Хост</label>
                                            <input type="text" class="form-control" name="installer_db[host]" value="localhost" required>
                                        </div>
                                        <div class="form-group mb-3 d-flex justify-content-center">
                                            <button type="submit" class="btn btn-primary btn-lg px-5">Далее</button>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="text-center mt-4">
                                    <p class="text-danger">Пожалуйста, исправьте системные требования перед продолжением установки.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
