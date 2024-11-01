<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>
<?
    $homepage_text = function_exists('lang')
                   ? lang('home_page')
                   : 'Home Page';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <title>Oops...</title>
  <link rel="icon" href="/assets/img/core-img/favicon.ico">
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="login-area">
<div class="main-content- h-100vh">
  <div class="container h-100">
    <div class="row h-100 align-items-center justify-content-center">
      <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="middle-box">
          <div class="log-header-area p-4 mb-4 text-center">
            <div class="lock-icon">
              <i class='bx bxs-error-alt'></i>
            </div>
            <h5><?= ! empty($error) ? $error : 'Internal server error' ?></h5>

            <? if (empty($no_links)): ?>

              <div class="form-group text-center my-3">
                <a href="/" class="btn btn-primary w-100 btn-lg" type="submit"><?= $homepage_text ?></a>
              </div>

            <? endif ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>    
</body>
</html>