<?php defined('ROCKET_SCRIPT') OR exit( 'No direct script access allowed' ); ?>
<? $this->load->view('blocks/header'); ?>

<div class="flapt-page-wrapper">

  <? $this->load->view('blocks/menu'); ?>

  <div class="flapt-page-content">

    <? $this->load->view('blocks/top_header'); ?>

      <div class="main-content">
        <div class="content-wraper-area">
          <div class="container-fluid">
            <div class="row g-4">
              <div class="col-12">

                <? $this->load->view('blocks/notificationBlock'); ?>

                <div class="card">
                  <div class="card-body">
                    <div class="card-title">
                      <h4><?= lang('users') ?></h4>
                    </div>

                    <div class="table-responsive text-nowrap">
                      <table class="table table-striped table-nowrap">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th><?= lang('login') ?></th>
                            <th><?= lang('role') ?></th>
                            <th><?= lang('date_register') ?></th>
                            <th><?= lang('actions') ?></th>
                          </tr>
                        </thead>
                        <tbody>

                          <? foreach ($users as $item): ?>
                            <? $type_class = $item->type == 'admin' ? 'text-bg-primary' : 'text-bg-success' ?>
                            
                            <tr>
                              <td><?= $item->id ?></td>
                              <td><?= $item->login ?></td>
                              <td><span class="badge <?= $type_class ?>"><?= $item->type ?></span></td>
                              <td><?= $item->date_register ?></td>
                              <td>
                                
                                <button type="button" data-id="<?= $item->id ?>" class="change_pass btn btn-outline-warning mb-2 me-2"><?= lang('change_pass') ?></button>

                                <? if ($item->id != 1): ?>
                                  
                                  <button type="button" data-id="<?= $item->id ?>" class="delete_user btn btn-outline-danger mb-2 me-2"><?= lang('delete') ?></button>
                                  
                                <? endif ?>
                                
                              </td>
                            </tr>

                          <? endforeach ?>

                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row g-4 mt-4">
              <div class="col-xl-6">
                <div class="card card-h-100">
                  <div class="card-body">
                    <div class="card-title">
                      <h4 class="card-title"><?= lang('create_user') ?></h4>
                    </div>
                    <form method="POST">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label" for="formrow-login-input"><?= lang('login') ?></label>
                            <input type="text" name="user[login]" class="form-control" id="formrow-login-input">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label" for="formrow-role-input"><?= lang('role') ?></label>
                            <select name="user[type]" class="form-select" id="formrow-role-input">
                              <option value="user" selected><?= lang('user') ?></option>
                              <option value="admin"><?= lang('admin') ?></option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label" for="formrow-password-input"><?= lang('password') ?></label>
                        <input type="text" name="user[password]" value="<?= random_str(32) ?>" class="form-control" id="formrow-password-input">
                      </div>
                      <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-md"><?= lang('create') ?></button>
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
</div>

<? $this->load->view('blocks/footer'); ?>