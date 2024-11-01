<?php defined('ROCKET_SCRIPT') OR exit( 'No direct script access allowed' ); ?>
<? $this->load->view('blocks/header'); ?>
<?
    $default_tab = 'stats';
    if (isset($_GET['tab']) and in_array($_GET['tab'], ['stats', 'user', 'filter', 'send_msg']))
        $default_tab = $_GET['tab'];
?>

<div class="flapt-page-wrapper">

  <? $this->load->view('blocks/menu'); ?>

  <div class="flapt-page-content">

    <? $this->load->view('blocks/top_header'); ?>

      <div class="main-content">
        <div class="content-wraper-area">
          <div class="container-fluid">
            <div class="row g-4">

              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="dashboard-header-title">
                    <h5 class="mb-0"><?= lang('groups') ?></h5>
                    </p>
                  </div>
                </div>
              </div>

              <? $this->load->view('blocks/notificationBlock'); ?>

              <div class="col-md-4">

                <!-- Search -->
                <div id="groups_filter" class="app-search">
                  <input id="groups_filter_input" type="text" class="form-control w-100" placeholder="<?= lang('search') ?>">
                  <span class="bx bx-search-alt btn-search cursor-pointer" title="<?= safe(lang('search')) ?>"></span>
                </div>

                <!-- Groups list -->
                <div class="chat-body-left">
                  <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                      aria-labelledby="pills-home-tab" tabindex="0">
                      <div class="single-chat-list" id="chatsList">

                        <div class="text-center">
                          <button id="create_group" class="btn btn-outline-success mb-3" type="button">
                            <i class="bx bx-plus"></i>
                            <?= lang('create_group') ?>
                          </button>
                        </div>

                        <? foreach ($groups as $item): ?>
                          <?
                              $active = (isset($group) and $group->id == $item->id)
                                      ? 'active'
                                      : '';
                          ?>
                          
                          <a href="/groups?id=<?= $item->id ?>" class="chat-media d-flex align-items-center <?= $active ?>">
                            <div class="chat-media-body d-flex justify-content-between align-items-center">
                              <div class="chat-user-info">
                                <h6><?= safe($item->name) ?></h6>
                                </span>
                              </div>
                              <button class="btn-delete btn btn-danger btn-sm p-0"
                                      title="<?= safe(lang('delete')) ?>"
                                      data-id="<?= $item->id ?>" >
                                <i class="bx bx-trash bx-sm"></i>
                              </button>
                            </div>
                          </a>

                        <? endforeach ?>

                      </div>
                    </div>
                  </div>
                </div>
                
              </div>

              <? if ( ! empty($group)): ?>
                <? $config = $group->config; ?>
                
                <div class="col-md-8">

                  <!-- Tabs -->
                  <div class="d-flex justify-content-around flex-wrap">
                    <button type="button" class="btn btn-outline-light mb-2 me-2 btn-tab <?= $default_tab == 'stats' ? 'active' : '' ?>" data-tab="stats">
                      <i class="bx bx-bar-chart-square"></i>
                      <?= lang('stats') ?>
                    </button>
                    <button type="button" class="btn btn-outline-light mb-2 me-2 btn-tab <?= $default_tab == 'users' ? 'active' : '' ?>" data-tab="users">
                      <i class="bx bx-user"></i>
                      <?= lang('users') ?>
                    </button>
                    <button type="button" class="btn btn-outline-light mb-2 me-2 btn-tab <?= $default_tab == 'filter' ? 'active' : '' ?>" data-tab="filter">
                      <i class="bx bx-filter"></i>
                      <?= lang('filtering') ?>
                    </button>
                    <button type="button" class="btn btn-outline-light mb-2 me-2 btn-tab <?= $default_tab == 'send_msg' ? 'active' : '' ?>" data-tab="send_msg">
                      <i class="bx bx-message"></i>
                      <?= lang('send_message') ?>
                    </button>
                  </div>

                  <!-- Stats -->
                  <div class="card tab-card <?= $default_tab == 'stats' ? 'active' : '' ?>" data-tab="stats">
                    <div class="card-header-cu">
                      <h6 class="mb-0"><?= lang('stats') ?></h6>
                    </div>
                    <div class="p-2">
                      <div class="row mb-2">
                        <!-- Members -->
                        <div class="col-sm-12 col-md-4">
                          <div class="card ">
                            <div class="card-body">
                              <div class="single-widget widget-sm d-flex align-items-center justify-content-left">
                                <div>
                                  <div class="widget-icon">
                                    <i class="bx bx-user"></i>
                                  </div>
                                </div>
                                <div class="progress-report mx-3 fs-5">
                                  <h5><?= lang('members_count') ?></h5>
                                </div>
                              </div>
                              <div class="text-center">
                                <span class="badge text-bg-primary fs-5"><?= $group->stats['members'] ?></span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- Joined -->
                        <div class="col-sm-12 col-md-4">
                          <div class="card ">
                            <div class="card-body">
                              <div class="single-widget widget-sm d-flex align-items-center justify-content-left">
                                <div>
                                  <div class="widget-icon">
                                    <i class="bx bx-user-plus"></i>
                                  </div>
                                </div>
                                <div class="progress-report mx-3">
                                  <h5><?= lang('joined') ?></h5>
                                </div>
                              </div>
                              <div class="text-center">
                                <span class="badge text-bg-primary fs-5"><?= $group->stats['joined'] ?></span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- Left -->
                        <div class="col-sm-12 col-md-4">
                          <div class="card ">
                            <div class="card-body">
                              <div class="single-widget widget-sm d-flex align-items-center justify-content-left">
                                <div>
                                  <div class="widget-icon">
                                    <i class="bx bx-user-minus"></i>
                                  </div>
                                </div>
                                <div class="progress-report mx-3" data-title="progress">
                                  <h5><?= lang('left') ?></h5>
                                </div>
                              </div>
                              <div class="text-center">
                                <span class="badge text-bg-primary fs-5"><?= $group->stats['left'] ?></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <!-- Banned -->
                        <div class="col-sm-12 col-md-6">
                          <div class="card ">
                            <div class="card-body">
                              <div class="single-widget widget-sm d-flex align-items-center justify-content-left">
                                <div>
                                  <div class="widget-icon">
                                    <i class="bx bx-user-x"></i>
                                  </div>
                                </div>
                                <div class="progress-report mx-3">
                                  <h5><?= lang('banned') ?></h5>
                                </div>
                              </div>
                              <div class="text-center">
                                <span class="badge text-bg-primary fs-5"><?= $group->stats['banned'] ?></span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- Muted -->
                        <div class="col-sm-12 col-md-6">
                          <div class="card ">
                            <div class="card-body">
                              <div class="single-widget widget-sm d-flex align-items-center justify-content-left">
                                <div>
                                  <div class="widget-icon">
                                    <i class="bx bx-volume-mute"></i>
                                  </div>
                                </div>
                                <div class="progress-report mx-3">
                                  <h5><?= lang('muted') ?></h5>
                                </div>
                              </div>
                              <div class="text-center">
                                <span class="badge text-bg-primary fs-5"><?= $group->stats['muted'] ?></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Users -->
                  <div class="card tab-card <?= $default_tab == 'users' ? 'active' : '' ?>" data-tab="users">
                    <div class="card-body">
                      <div class="card-title">
                        <h4><?= lang('users') ?></h4>
                      </div>
                      <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-end">
                          <select class="form-select per_page">

                            <? foreach ($page_lengths as $length): ?>
                              
                              <option value="<?= $length ?>"><?= $length ?></option>

                            <? endforeach ?>
                            
                          </select>
                        </div>
                        <div id="users_filter" class="app-search">
                          <input name="filter[contact]" type="text" class="form-control" placeholder="<?= lang('search') ?>">
                          <span class="bx bx-search-alt btn-search cursor-pointer" title="<?= safe(lang('search')) ?>"></span>
                        </div>
                      </div>
                      <div class="table-responsive text-nowrap">
                        <table id="users" class="table table-striped table-nowrap">
                          <thead></thead>
                          <tbody>
                            <tr><td colspan="7" class="text-center"><?= lang('loading') ?>...</td></tr>
                          </tbody>
                        </table>
                      </div>
                      <nav aria-label="...">
                        <ul class="pagination"></ul>
                      </nav>
                    </div>
                  </div>

                  <!-- Filtering settings -->
                  <div class="card tab-card <?= $default_tab == 'filter' ? 'active' : '' ?>" data-tab="filter">
                    <div class="card-header-cu">
                      <h6 class="mb-0"><?= lang('filter_settings') ?></h6>
                    </div>
                    <div class="card-body">
                      <form method="POST">

                        <div class="form-check form-switch form-switch-md mb-3">
                          <input name="active" type="checkbox" class="form-check-input me-3" id="formCheckActive" <?= $group->active ? 'checked' : '' ?> >
                          <label class="form-check-label" for="formCheckActive">
                            <?= lang('filter_active') ?>
                          </label>
                        </div>

                        <h6><?= lang('members_messages') ?></h6>
                        <div class="row p-3 mb-3">
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="bot_commands" name="config[messages][bot_commands]" class="form-check-input me-3" type="checkbox" <?= @$config['messages']['bot_commands'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="bot_commands">
                              <?= lang('del_bot_commands') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="messages_images" name="config[messages][images]" class="form-check-input me-3" type="checkbox" <?= @$config['messages']['images'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="messages_images">
                              <?= lang('groups_01') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="messages_voices" name="config[messages][voices]" class="form-check-input me-3" type="checkbox" <?= @$config['messages']['voices'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="messages_voices">
                              <?= lang('groups_02') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="messages_files" name="config[messages][files]" class="form-check-input me-3" type="checkbox" <?= @$config['messages']['files'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="messages_files">
                              <?= lang('groups_03') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="messages_stickers" name="config[messages][stickers]" class="form-check-input me-3" type="checkbox" <?= @$config['messages']['stickers'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="messages_stickers">
                              <?= lang('groups_04') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="messages_dices" name="config[messages][dices]" class="form-check-input me-3" type="checkbox" <?= @$config['messages']['dices'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="messages_dices">
                              <?= lang('groups_05') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex">
                            <input id="messages_links" name="config[messages][links]" class="form-check-input me-3" type="checkbox" <?= @$config['messages']['links'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="messages_links">
                              <?= lang('groups_06') ?>
                            </label>
                          </div>
                        </div>

                        <h6><?= lang('groups_07') ?></h6>
                        <div class="row p-3">
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="forward_media" name="config[forward][media]" class="form-check-input me-3" type="checkbox" <?= @$config['forward']['media'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="forward_media">
                              <?= lang('groups_08') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="forward_links" name="config[forward][links]" class="form-check-input me-3" type="checkbox" <?= @$config['forward']['links'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="forward_links">
                              <?= lang('groups_09') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex">
                            <input id="forward_all" name="config[forward][all]" class="form-check-input me-3" type="checkbox" <?= @$config['forward']['all'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="forward_all">
                              <?= lang('groups_10') ?>
                            </label>
                          </div>
                        </div>
                        <div class="row mb-4">
                          <label class="form-label"><?= lang('groups_11') ?></label>
                          <div class="d-flex">
                            <input type="text" name="config[restrict][mul]" class="form-control" value="<?= (int) @$config['restrict']['mul'] ?>">
                            <select name="config[restrict][time]" class="form-select">
                              <option value="60" <?= @$config['restrict']['time'] == 60 ? 'selected' : '' ?> ><?= lang('groups_12') ?></option>
                              <option value="3600" <?= @$config['restrict']['time'] == 3600 ? 'selected' : '' ?> ><?= lang('groups_13') ?></option>
                              <option value="86400" <?= @$config['restrict']['time'] == 86400 ? 'selected' : '' ?> ><?= lang('groups_14') ?></option>
                              <option value="2592000" <?= @$config['restrict']['time'] == 2592000 ? 'selected' : '' ?> ><?= lang('groups_15') ?></option>
                            </select>
                          </div>
                        </div>

                        <h6><?= lang('groups_16') ?></h6>
                        <div class="row p-3">
                          <div class="form-check form-switch form-switch-md d-flex">
                            <input id="stop_words_active" name="config[stop_words][active]" class="form-check-input me-3" type="checkbox" <?= @$config['stop_words']['active'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="stop_words_active">
                              <?= lang('groups_17') ?>
                            </label>
                          </div>
                        </div>
                        <div class="mb-4">
                          <div id="word_list" class="mb-2">
                          
                            <? foreach ($config['stop_words']['list'] as $word): ?>
                              
                              <span class="badge text-bg-dark">
                                <?= safe($word) ?>
                                <i class="bx bx-x btn-del-stopword"></i>
                              </span>

                            <? endforeach ?>

                          </div>
                          <div class="d-flex flex-row">
                            <input id="stop_word_input" type="text" class="form-control">
                            <button id="add_stop_word_btn" type="button" class="btn btn-outline-success"><?= lang('add') ?></button>
                          </div>
                          <input type="hidden" name="config[stop_words][list]" value="<?= base64_encode(json_encode($config['stop_words']['list'])) ?>">
                        </div>

                        <h6><?= lang('groups_18') ?></h6>
                        <div class="row p-3 mb-3">
                          <div class="form-check form-switch form-switch-md d-flex">
                            <input id="filter_admins" name="config[filter_admins]" class="form-check-input me-3" type="checkbox" <?= @$config['filter_admins'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="filter_admins">
                              <?= lang('groups_19') ?>
                            </label>
                          </div>
                        </div>

                        <h6><?= lang('groups_20') ?></h6>
                        <div class="row p-3 mb-3">
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="user_joined" name="config[user_joined]" class="form-check-input me-3" type="checkbox" <?= @$config['user_joined'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="user_joined">
                              <?= lang('groups_21') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="user_left" name="config[user_left]" class="form-check-input me-3" type="checkbox" <?= @$config['user_left'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="user_left">
                              <?= lang('groups_22') ?>
                            </label>
                          </div>
                        </div>

                        <h6><?= lang('groups_23') ?></h6>
                        <div class="row mb-4">
                          <label class="form-label"><?= lang('groups_24') ?></label>
                          <div class="d-flex">
                            <input type="text" name="config[joined_restrict][mul]" class="form-control" value="<?= (int) @$config['joined_restrict']['mul'] ?>">
                            <select name="config[joined_restrict][time]" class="form-select">
                              <option value="60" <?= @$config['joined_restrict']['time'] == 60 ? 'selected' : '' ?> ><?= lang('groups_12') ?></option>
                              <option value="3600" <?= @$config['joined_restrict']['time'] == 3600 ? 'selected' : '' ?> ><?= lang('groups_13') ?></option>
                              <option value="86400" <?= @$config['joined_restrict']['time'] == 86400 ? 'selected' : '' ?> ><?= lang('groups_14') ?></option>
                              <option value="2592000" <?= @$config['joined_restrict']['time'] == 2592000 ? 'selected' : '' ?> ><?= lang('groups_15') ?></option>
                            </select>
                          </div>
                        </div>
                        
                        <div class="text-center">
                          <input type="hidden" name="id" value="<?= $group->id ?>">
                          <button type="submit" class="btn btn-primary"><?= lang('save') ?></button>
                        </div>

                      </form>
                    </div>
                  </div>

                  <!-- Send message -->
                  <div class="card tab-card <?= $default_tab == 'send_msg' ? 'active' : '' ?>" data-tab="send_msg">
                    <div class="card-header-cu">
                      <h6 class="mb-0"><?= lang('send_message') ?></h6>
                    </div>
                    <div class="card-body">
                      <div id="toolbar"></div>
                      <div id="editor"></div>
                      <div id="statusbar" class="d-flex justify-content-between m-1">
                        <div>
                          <div id="editor_loader" style="display:none;">
                            <i class="bx bx-loader bx-spin"></i>
                            <?= lang('sending') ?>
                          </div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-right mt-2">
                        <button id="btn_send" type="button" class="btn btn-primary w-md"><?= lang('send') ?></button>
                      </div>
                    </div>

                  </div>

                </div>
              
              <? endif ?>

            </div>

          </div>
        </div>
      </div>

  </div>
</div>

<? $this->load->view('blocks/footer'); ?>