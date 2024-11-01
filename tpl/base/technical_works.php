<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>
<? $this->load->view('blocks/header'); ?>

<div class="main-content- h-100vh">
  <div class="container h-100">
    <div class="row h-100 align-items-center justify-content-center">
      <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="middle-box">
          <div class="log-header-area p-4 mb-4 text-center">
            <div class="lock-icon">
              <i class='bx bxs-wrench'></i>
            </div>
            <h5><?= lang('technical_works') ?></h5>
            <p class="mb-0"><?= lang('site_temp_unavailable') ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<? $this->load->view('blocks/footer'); ?>
<? die(); ?>