<?php defined('ROCKET_SCRIPT') OR exit( 'No direct script access allowed' ); ?>
<? $this->load->view('blocks/header'); ?>
<?
    // $img_sizes = [ '1024x1024', '512x512', '256x256' ];
    $img_sizes = [ '1024x1024', '1024x1792', '1792x1024' ];
?>

<div class="flapt-page-wrapper">

  <? $this->load->view('blocks/menu'); ?>

  <div class="flapt-page-content">

    <? $this->load->view('blocks/top_header'); ?>

    <div class="main-content">
      <div class="content-wraper-area">
        <div class="container-fluid">
          <div class="row g-4">
            <div class="col-lg-12">

              <? $this->load->view('blocks/notificationBlock'); ?>

              <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"
                  aria-labelledby="v-pills-home-tab" tabindex="0">
                  
                  <? if ( ! empty($update_available)): ?>
                    
                    <!-- Update -->
                    <div id="update_card" class="card mb-3">
                      <div class="card-header-cu">
                        <h6 class="mb-0"><?= lang('update_available') ?></h6>
                      </div>
                      <div class="card-body">
                        <h4 class="mb-3">
                          <?= lang('version') ?>
                          <?= $update_available['version'] ?>
                        </h4>
                        <div class="profile-item-list mb-2">
                          <b><?= safe($update_available['summary']) ?></b>
                        </div>
                        <div class="profile-item-list mb-2">
                          <b><?= lang('changelog') ?></b>
                          <?= $update_available['changelog'] ?>
                        </div>
                        <div class="d-flex justify-content-between">
                          <a href="/settings/update" class="btn btn-primary btn-lg mb-2 me-2">
                            <?= lang('install') ?>
                          </a>
                          <a href="/settings/decline_update" class="btn btn-danger btn-lg mb-2 me-2">
                            <?= lang('decline') ?>
                          </a>
                        </div>
                      </div>
                    </div>

                  <? endif ?>

                  <!-- Main settings -->
                  <div class="card mb-3">
                    <div class="card-header-cu">
                      <h6 class="mb-0"><?= lang('settings') ?></h6>
                    </div>
                    <div class="card-body">
                      <form method="POST">
                        
                        <div class="mb-4">
                          <label class="form-label"><?= lang('site_name') ?></label>
                          <input type="text" class="form-control" name="settings[project_name]" value="<?= $settings['project_name'] ?? '' ?>">
                        </div>


                        <!-- Telegram -->

                        <label class="form-label"><b>Telegram</b></label>
                        
                        <div class="mb-3">
                          
                          <? if ( ! empty($settings['tg_auth'])): ?>

                            <div class="d-flex justify-content-between">
                              <p><?= lang('authorized_as') ?>: <b><?= $settings['tg_auth'] ?></b></p>
                              <button id="tg_logout_btn" type="button" class="btn btn-outline-danger"><?= lang('log_out') ?></button>
                            </div>

                          <? else: ?>

                            <a class="btn btn-outline-primary"><?= lang('log_in') ?></a>

                          <? endif ?>

                        </div>

                        <div class="mb-3">
                          <label class="form-label"><?= lang('telegram_bot_token') ?></label>
                          <input type="text" class="form-control" name="settings[telegram_bot_token]" value="<?= $settings['telegram_bot_token'] ?? '' ?>">
                          <!-- <a href="/" target="_blank"><?= lang('install_webhook') ?></a> -->
                        </div>
                        <div class="mb-3">
                          <h6><?= lang('bot_users') ?></h6>
                          <div class="d-flex flex-row mb-2">
                            <input id="bot_user_input" type="text" class="form-control">
                            <button id="add_bot_user_btn" type="button" class="btn btn-outline-success"><?= lang('add') ?></button>
                          </div>
                          <div id="users_list">
                          
                            <? foreach ($bot_users as $id => $name): ?>
                              
                              <span class="badge text-bg-dark">
                                <?= $id ?>
                                <?= empty($name) ? '' : '(' . safe($name) . ')' ?>
                                <i class="bx bx-x btn-del-bot_user"></i>
                              </span>

                            <? endforeach ?>

                          </div>
                          <input type="hidden" name="settings[bot_users]" value="<?= base64_encode(urlencode(json_encode($bot_users))) ?>">
                        </div>

                        <div class="mb-4">
                          <label class="form-label"><?= lang('posts_moderator') ?></label>
                          <input type="text" class="form-control" name="settings[posts_moderator]" value="<?= $settings['posts_moderator'] ?? '' ?>" placeholder="<?= lang('user_id') ?>">
                        </div>

                        <div class="d-flex justify-content-between">
                          <div class="form-check form-switch form-switch-md mb-4">
                            <input name="settings[unsafe_parsing]" type="checkbox" class="form-check-input me-3" id="formCheckUnsafe" <?= empty($settings['unsafe_parsing']) ? '' : 'checked' ?> >
                            <label class="form-check-label" for="formCheckUnsafe">
                              <?= lang('enable_unsafe_parsing') ?>
                            </label>
                          </div>

                          <? if ($parsing_stopped and ! empty($settings['unsafe_parsing'])): ?>
                            
                            <div class="d-flex align-items-center">
                              <input id="restart_parsing_btn" type="button" class="btn btn-outline-warning mt-2" value="<?= lang('restart_parsing') ?>">
                            </div>

                          <? endif ?>

                        </div>

                        <!-- AI -->
                        
                        <label class="form-label"><b><?= lang('ai_settings') ?></b></label>
                        <div class="mb-3">
                          <label class="form-label"><?= lang('openai_key') ?></label>
                          <input type="text" class="form-control" name="settings[openai_api_key]" value="<?= $settings['openai_api_key'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label"><?= lang('openrouter_key') ?></label>
                          <input type="text" class="form-control" name="settings[openrouter_api_key]" value="<?= $settings['openrouter_api_key'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label"><?= lang('select_model') ?></label>
                          <select id="llm_model" class="form-select" name="settings[<?= $settings['llm_provider'] ?? 'openai' ?>_ai_model]">

                            <? foreach ($models as $code => $model): ?>
                              <?
                                  $selected = ( $code == @$settings[$model['provider'] . '_ai_model']
                                                and @$settings['llm_provider'] == $model['provider']
                                              )
                                            ? 'selected'
                                            : ''
                              ?>
                              
                              <option value="<?= $code ?>" <?= $selected ?> data-provider="<?= $model['provider'] ?>">
                                [<?= ucfirst($model['provider']) ?>] <?= $model['name'] ?>
                              </option>

                            <? endforeach ?>

                          </select>
                          <input type="hidden" name="settings[llm_provider]" value="<?= $settings['llm_provider'] ?? '' ?>">
                        </div>
                        <div class="mb-4">
                          <label class="form-label"><?= lang('select_img_size') ?></label>
                          <select class="form-select" name="settings[openai_img_size]">

                            <? foreach ($img_sizes as $size): ?>
                              <? $selected = $size == @$settings['openai_img_size'] ? 'selected' : '' ?>
                              
                              <option value="<?= $size ?>" <?= $selected ?>><?= $size ?></option>

                            <? endforeach ?>

                          </select>
                        </div>

                        <!-- Proxy -->
                        
                        <label class="form-label"><b><?= lang('https_proxy') ?></b></label>
                        <div class="mb-3">
                          <label class="form-label"><?= lang('proxy_ip') ?></label>
                          <input type="text" class="form-control" name="settings[proxy_ip]" value="<?= $settings['proxy_ip'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label"><?= lang('proxy_port') ?></label>
                          <input type="text" class="form-control" name="settings[proxy_port]" value="<?= $settings['proxy_port'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label"><?= lang('proxy_login') ?></label>
                          <input type="text" class="form-control" name="settings[proxy_login]" value="<?= $settings['proxy_login'] ?? '' ?>">
                        </div>
                        <div class="mb-4">
                          <label class="form-label"><?= lang('proxy_pass') ?></label>
                          <input type="text" class="form-control" name="settings[proxy_pass]" value="<?= $settings['proxy_pass'] ?? '' ?>">
                        </div>

                        <input type="submit" class="btn btn-primary mt-2"
                          value="<?= lang('save') ?>">

                      </form>
                    </div>
                  </div>

                  <!-- VK auth -->
                  <div id="vk_auth_card" class="card mb-3">
                    <div class="card-header-cu">
                      <h6 class="mb-0"><?= lang('vk_connect') ?></h6>
                    </div>
                    <div class="card-body">
                      <form method="POST">
                        <div class="mb-3">
                          <label class="form-label"><?= lang('app_id') ?></label>
                          <input type="text" class="form-control mb-2"
                                 value="<?= $settings['vk_config']['app_id'] ?? '' ?>"
                                 name="vk[app_id]" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label"><?= lang('vk_private_key') ?></label>
                          <input type="text" class="form-control mb-2"
                                 value="<?= $settings['vk_config']['private_key'] ?? '' ?>"
                                 name="vk[private_key]" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label"><?= lang('vk_service_key') ?></label>
                          <input type="text" class="form-control mb-2"
                                 value="<?= $settings['vk_config']['service_key'] ?? '' ?>"
                                 name="vk[service_key]" required>
                        </div>
                        <input type="submit" class="btn btn-primary mt-2"
                          value="<?= lang('save') ?>">
                      </form>
                    </div>
                  </div>

                  <!-- Add AI model -->
                  <div id="create_model_card" class="card">
                    <div class="card-header-cu">
                      <h6 class="mb-0"><?= lang('create_model') ?></h6>
                    </div>
                    <div class="card-body">
                      <form method="POST">
                        <div class="mb-3">
                          <label class="form-label"><?= lang('service') ?></label>
                          <select name="new_model[provider]" class="form-select">
                            <option value="openrouter" selected>OpenRouter</option>
                            <option value="openai">OpenAI</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <div class="row">
                            <div class="col-md-6 col-lg-6 px-2 mb-3">
                              <label class="form-label"><?= lang('model_name') ?></label>
                              <input type="text" class="form-control mb-2"
                                     name="new_model[name]" required>
                            </div>
                            <div class="col-md-6 col-lg-6 px-2 mb-3">
                              <label class="form-label"><?= lang('model_code') ?></label>
                              <input type="text" class="form-control"
                                     name="new_model[code]" required>
                            </div>
                          </div>
                        </div>
                        <input type="submit" class="btn btn-primary mt-2"
                          value="<?= lang('save') ?>">
                      </form>
                    </div>
                  </div>

                  <!-- TG Logout form -->
                  <form id="tg_logout_form" method="POST">
                    <input type="hidden" name="tg_logout" value="1">
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