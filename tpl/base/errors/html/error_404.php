<?php defined('ROCKET_SCRIPT') OR exit( 'No direct script access allowed' ); ?>
<? $this->load->view('blocks/header'); ?>

<div class="main-content- h-100vh">
  <div class="container h-100">
    <div class="row h-100 align-items-center justify-content-center">
      <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="middle-box">
          <div class="log-header-area p-4 mb-4 text-center">
            <div class="lock-icon">
              <i class='bx bxs-error-alt'></i>
            </div>
            <h5><?= lang('page_not_found') ?></h5>
            <div class="form-group text-center my-3">
              <a href="/" class="btn btn-primary w-100 btn-lg" type="submit"><?= lang('go_home') ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<? $this->load->view('blocks/footer'); ?>