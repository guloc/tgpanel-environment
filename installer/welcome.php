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
            margin: 20px 0;
        }
        .check-item {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .check-item i {
            margin-right: 10px;
        }
        .check-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .check-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .check-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .requirements-list {
            margin-top: 20px;
        }
        .requirement-group {
            margin-bottom: 15px;
        }
        .requirement-group h6 {
            margin-bottom: 10px;
            color: #495057;
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
                                <p class="mb-0">Для того чтобы панель начала работать на вашем сервере - следуйте инструкциям установщика. Перед началом установки будет выполнена проверка системных требований.</p>
                            </div>
                            
                            <div class="card">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-4">Проверка системных требований</h5>
                                    
                                    <div class="requirements-list">
                                        <?php if (!empty($checkResults['errors'])): ?>
                                            <div class="requirement-group">
                                                <h6><i class="fa fa-times-circle text-danger"></i> Критические ошибки</h6>
                                                <?php foreach ($checkResults['errors'] as $error): ?>
                                                    <div class="check-item check-error">
                                                        <i class="fa fa-times-circle"></i>
                                                        <?php echo htmlspecialchars($error['message']); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($checkResults['warnings'])): ?>
                                            <div class="requirement-group">
                                                <h6><i class="fa fa-exclamation-triangle text-warning"></i> Предупреждения</h6>
                                                <?php foreach ($checkResults['warnings'] as $warning): ?>
                                                    <div class="check-item check-warning">
                                                        <i class="fa fa-exclamation-triangle"></i>
                                                        <?php echo htmlspecialchars($warning['message']); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (empty($checkResults['errors']) && empty($checkResults['warnings'])): ?>
                                            <div class="check-item check-success">
                                                <i class="fa fa-check-circle"></i>
                                                Все системные требования выполнены
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-center mt-4">
                                        <?php if (empty($checkResults['errors'])): ?>
                                            <p class="text-success mb-3">
                                                <i class="fa fa-check-circle"></i>
                                                Система готова к установке
                                            </p>
                                            <a href="/installer/index.php?step=1" class="btn btn-primary btn-lg px-5">
                                                Начать установку
                                            </a>
                                        <?php else: ?>
                                            <p class="text-danger mb-3">
                                                <i class="fa fa-times-circle"></i>
                                                Пожалуйста, исправьте критические ошибки перед продолжением установки
                                            </p>
                                            <button class="btn btn-secondary btn-lg px-5" disabled>
                                                Начать установку
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>   
</body>
</html>
