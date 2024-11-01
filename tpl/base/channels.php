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

              <? if ( ! empty($update_available)): ?>

                <div class="col-12">
                  <div class="alert alert-warning text-center" role="alert">
                    <?= lang('update_note') ?>
                    <a href="/settings"><?= lang('settings') ?></a>
                  </div>
                </div>

              <? endif; ?>

              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="dashboard-header-title">
                    <h5 class="mb-0"><?= empty($channel) ? lang('channel_stats') : safe($channel->name) ?></h5>
                  </div>

                  <? if ( ! empty($channel)): ?>
                    
                    <button id="del_channel_btn" class="btn btn-danger mb-1" data-id="<?= $channel->id ?>">
                      <?= lang('delete') ?>
                    </button>

                  <? endif ?>

                </div>

                <? $this->load->view('blocks/notificationBlock'); ?>

              </div>
              <!-- Channels -->
              <div class="col-sm-6 col-lg-3 col-xxl-3">
                <div class="card ">
                  <div class="card-body" data-intro="<?= lang('day') ?>">
                    <div class="single-widget d-flex align-items-center justify-content-left">
                      <div>
                        <div class="widget-icon">
                          <i class="bx bxs-megaphone"></i>
                        </div>
                      </div>
                      <div class="progress-report mx-3" data-title="progress">
                        <h5><?= lang('channels') ?></h5>
                      </div>
                    </div>
                    <div class="text-center">
                      <span class="badge text-bg-primary fs-5"><?= count($channels) ?></span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Subscribers -->
              <div class="col-sm-6 col-lg-3 col-xxl-3">
                <div class="card ">
                  <div class="card-body" data-intro="<?= lang('day') ?>">
                    <div class="single-widget d-flex align-items-center justify-content-left">
                      <div>
                        <div class="widget-icon">
                          <i class="bx bx-user"></i>
                        </div>
                      </div>
                      <div class="progress-report mx-3" data-title="progress">
                        <h5><?= lang('subscribers') ?></h5>
                      </div>
                    </div>
                    <div class="text-center">
                      <span class="badge text-bg-primary fs-5"><?= (int) $stats['subscribers'] ?></span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Day -->
              <div class="col-sm-6 col-lg-3 col-xxl-3">
                <div class="card ">
                  <div class="card-body" data-intro="<?= lang('day') ?>">
                    <div class="single-widget d-flex align-items-center justify-content-left">
                      <div>
                        <div class="widget-icon">
                          <i class="bx bx-time-five"></i>
                        </div>
                      </div>
                      <div class="progress-report mx-3" data-title="progress">
                        <h5><?= lang('daily_subs') ?></h5>
                      </div>
                    </div>
                    <div class="text-center">
                      <span class="badge text-bg-<?= $stats['day_subs'] < 0 ? 'danger' : 'success' ?> fs-5">
                        <?= $stats['day_subs'] ?>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Week -->
              <div class="col-sm-6 col-lg-3 col-xxl-3">
                <div class="card">
                  <div class="card-body" data-intro="<?= lang('week') ?>">
                    <div class="single-widget d-flex align-items-center justify-content-left">
                      <div>
                        <div class="widget-icon">
                          <i class="bx bx-calendar"></i>
                        </div>
                      </div>
                      <div class="progress-report mx-3" data-title="progress">
                        <h5><?= lang('weekly_subs') ?></h5>
                      </div>
                    </div>
                    <div class="text-center">
                      <span class="badge text-bg-<?= $stats['week_subs'] < 0 ? 'danger' : 'success' ?> fs-5">
                        <?= $stats['week_subs'] ?>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Graph -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-body">
                    <div class="card-title">
                      <h4><?= lang('weekly_subs') ?></h4>
                    </div>
                    <div id="subs-graph">
                      <!-- <canvas id="subs-graph"></canvas> -->
                    </div>
                  </div>
                </div>
              </div>

              <!-- Channels list -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-body">
                    <div class="card-title">
                      <h4><?= empty($channel) ? lang('channels') : lang('sources') ?></h4>
                    </div>
                    <ul class="list-group">

                      <? if (empty($channel)): ?>
                        
                        <? foreach ($channels as $item): ?>
                          <?
                              $platform = $item->platform;
                              if ($platform == 'telegram')
                                  $platform = 'tg';
                              if ($platform == 'wordpress')
                                  $platform = 'wp';
                          ?>
                          
                          <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                              <a href="/channels?id=<?= $item->id ?>">
                                [<?= $platform ?>]
                                <?= safe($item->name) ?>
                              </a>
                              <span><?= $item->subs ?></span>
                            </div>
                          </li>

                        <? endforeach ?>

                        <li class="list-group-item">
                          <div class="d-flex justify-content-center">
                            <button id="create_channel" class="btn btn-outline-success">
                              <?= lang('add') ?>
                            </button>
                          </div>
                        </li>

                      <? else: ?>

                        <? foreach ($sources as $item): ?>
                          
                          <li class="list-group-item">
                            <div class="d-flex justify-content-between">

                              <div class="form-check form-switch form-switch-md">
                                <input type="checkbox" class="src-toggle form-check-input me-3" <?= $item->active ? 'checked' : '' ?> data-id="<?= $item->id ?>">
                              </div>

                              <a href="/parsing?id=<?= $item->id ?>">
                                [<?= $item->platform == 'telegram' ? 'tg' : $item->platform ?>]
                                <?= safe($item->name) ?>
                              </a>
                              
                              <button class="btn-delete-src btn btn-danger btn-sm p-0"
                                      title="<?= safe(lang('delete')) ?>"
                                      data-id="<?= $item->id ?>" >
                                <i class="bx bx-trash bx-sm"></i>
                              </button>

                            </div>
                          </li>

                        <? endforeach ?>

                      <? endif ?>

                    </ul>
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