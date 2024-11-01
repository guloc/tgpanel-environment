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
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="login-area">
	<div class="main-content- h-100vh">
	  <div class="container h-100">
	    <div class="row h-100 align-items-center justify-content-center">
	      <div class="col-sm-10 col-md-7 col-lg-5">
	        <div class="middle-box">
	          <div class="card-body">
	            <div class="log-header-area card p-4 mb-4 text-center">
	              <h5>Завершение установки</h5>
	              <p class="mb-0">Сохраните данные для доступа в административную панель и запустите удаление установщика!</p>
	            </div>
	            <div class="card">
	              <?= isset($message) ? '<p style="font-weight: bold; color:red">'.$message.'</p>' : ''; ?>
	              <form class="card-body p-4" method="POST" action="/installer/index.php?step=3">
        			<input type='hidden' name='remove_installer' value='1'>
	                <div class="form-group mb-3">
	                  <button type="submit" class="btn btn-primary btn-lg w-100">Завершить</button>
	                </div>
	              </form>
	            </div>
	          </div>
	        </div>
	      </div>
	    </div>
	  </div>
	</div>   
</body>
</html>