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

              <div class="card card-body">

                <!-- <pre><?= $logs ?></pre> -->
                <iframe src="/logs/viewer" id="viewer" class="w-100"></iframe>
            </div>
          </div>
        </div>
      </div>

  </div>
</div>

<? $this->load->view('blocks/footer'); ?>