<?php defined('ROCKET_SCRIPT') OR exit( 'No direct script access allowed' ); ?>
<? $this->load->view('blocks/header'); ?>

<div class="main-content- h-100vh">
  <div class="container h-100">
    <div class="row h-100 align-items-center justify-content-center">
      <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="middle-box">
          <div class="card-body">
            <div class="log-header-area card p-4 mb-4 text-center">
              <h5><?= $project_name ?></h5>
              <p class="mb-0"><?= lang('sign_to_continue') ?></p>
            </div>
            <div class="card">
              <div class="card-body p-4">
                <form action="/login" method="POST">

                  <? $this->load->view('blocks/notificationBlock'); ?>

                  <div class="form-group mb-3">
                    <label class="text-muted" for="emailaddress"><?= lang('login') ?></label>
                    <input class="form-control" type="text" name="user[login]"
                      placeholder="<?= lang('enter_login') ?>">
                  </div>
                  <div class="form-group mb-3">
                    <label class="text-muted" for="password"><?= lang('password') ?></label>
                    <input class="form-control" type="password" name="user[password]"
                      placeholder="<?= lang('enter_pass') ?>">
                  </div>
                  <div class="form-group mb-3">
                    <button class="btn btn-primary btn-lg w-100" type="submit"><?= lang('sign_in') ?></button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<? $this->load->view('blocks/footer'); ?>