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
                <div class="d-flex align-items-center justify-content-between">
                  <div class="dashboard-header-title">
                    <h5 class="mb-0"><?= lang('llm_stats') ?></h5>
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 col-lg-6 col-xxl-3">
                <div class="card ">
                  <div class="card-body" data-intro="<?= lang('day') ?>">
                    <div class="single-widget d-flex align-items-center justify-content-between">
                      <div>
                        <div class="widget-icon">
                          <i class="bx bx-time-five"></i>
                        </div>
                        <div class="widget-desc">
                          <h5><?= lang('day') ?></h5>
                          <p class="mb-0"><?= lang('tokens_spent') ?>: <?= $stats['day']->tokens ?></p>
                        </div>
                      </div>
                      <div class="progress-report" data-title="progress">
                        <p>
                          $
                          <?= $stats['day']->usd < 0.0001
                            ? number_format($stats['day']->usd, 8)
                            : round($stats['day']->usd, 8)
                          ?>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 col-lg-6 col-xxl-3">
                <div class="card">
                  <div class="card-body" data-intro="<?= lang('month') ?>">
                    <div class="single-widget d-flex align-items-center justify-content-between">
                      <div>
                        <div class="widget-icon">
                          <i class="bx bx-calendar"></i>
                        </div>
                        <div class="widget-desc">
                          <h5><?= lang('month') ?></h5>
                          <p class="mb-0"><?= lang('tokens_spent') ?>: <?= $stats['month']->tokens ?></p>
                        </div>
                      </div>
                      <div class="progress-report" data-title="progress">
                        <p>
                          $
                          <?= $stats['month']->usd < 0.0001
                            ? number_format($stats['month']->usd, 8)
                            : round($stats['month']->usd, 8)
                          ?>
                        </p>
                      </div>
                    </div>
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