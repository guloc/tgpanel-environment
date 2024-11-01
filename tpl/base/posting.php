<?php defined('ROCKET_SCRIPT') OR exit( 'No direct script access allowed' ); ?>
<? $this->load->view('blocks/header'); ?>
<?
    $status_class = [
        'draft' => 'secondary',
        'queued' => 'primary',
        'posted' => 'success',
        'moderation' => 'warning',
    ];
    $channel_names = array_column($channels, 'name', 'id');
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
                    <h5 class="mb-0"><?= lang('posting') ?></h5>
                  </div>
                  <button id="clear_all_btn" class="btn btn-outline-danger">
                    <?= lang('clear_all') ?>
                  </button>
                </div>
              </div>

              <? $this->load->view('blocks/notificationBlock'); ?>

              <!-- Posting queue -->
              <div class="col-md-5 col-xxl-5 mt-0">
                <div class="chat-body-left">
                  <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                      aria-labelledby="pills-home-tab" tabindex="0">
                      <div class="single-chat-list" id="postsList">

                        <div class="d-flex justify-content-between">
                          <button id="create_post" class="btn btn-outline-success mb-3" type="button">
                            <i class="bx bx-plus"></i>
                            <?= lang('create_post') ?>
                          </button>
                          <div>
                            <select id="status_select" class="form-select" id="formrow-role-input">
                              <option value="" <?= empty($_GET['status']) ? 'selected' : '' ?>><?= lang('all') ?></option>
                              <option value="queued" <?= @$_GET['status'] == 'queued' ? 'selected' : '' ?>><?= lang('queue') ?></option>
                              <option value="draft" <?= @$_GET['status'] == 'draft' ? 'selected' : '' ?>><?= lang('drafts') ?></option>
                              <option value="posted" <?= @$_GET['status'] == 'posted' ? 'selected' : '' ?>><?= lang('posted') ?></option>
                              <option value="moderation" <?= @$_GET['status'] == 'moderation' ? 'selected' : '' ?>><?= lang('post_moderation') ?></option>
                            </select>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Editor -->
              <?
                  $editor_style = '';
                  if (empty($post)) {
                      $editor_style = 'style="display:none;"';
                      $post = (object) [
                          'id' => '',
                          'name' => '',
                          'content' => '',
                          'status' => 'draft',
                          'channel_id' => null,
                          'pub_date' => '',
                          'files' => '[]',
                      ];
                  }
              ?>
              <div id="editor_col" class="col-md-7" <?= $editor_style ?> >
                <div class="card card-h-100">
                  <div class="card-body">
                    <div id="toolbar"></div>
                    <div id="editor"><?= $post->content ?></div>
                    <div id="statusbar" class="d-flex justify-content-between m-1">
                      <div id="text_options" style="display:none;">
                        <button id="rewrite_button" type="button" class="btn btn-light">
                          <?= lang('rewrite') ?>
                        </button>
                        <button id="continue_button" type="button" class="btn btn-light">
                          <?= lang('continue') ?>
                        </button>
                      </div>
                      <div>
                        <div id="editor_loader" style="display:none;">
                          <i class="bx bx-loader bx-spin"></i>
                          <?= lang('saving') ?>
                        </div>
                        <div id="editor_error" style="display:none;">
                          <i class="bx bxs-error"></i>
                          <?= lang('saving_error') ?>
                        </div>
                      </div>
                      <div>
                        <a id="post_html" href="/posting/content?id=<?= $post->id ?>" target="_blank">
                          <i class="bx bx-info-circle"></i>
                          Посмотреть код
                        </a>
                      </div>
                      <div>
                        <?= lang('symbols_total') ?>:
                        <span id="words_count"></span>
                      </div>
                    </div>
                    <div class="row mb-2 main-buttons">
                      <div class="col-md-6">
                        <label class="form-label"><?= lang('channel') ?></label>
                        <select name="post[channel]" class="form-select" id="formrow-role-input">
                          <option value=""><?= lang('none') ?></option>
                          
                          <? foreach ($channels as $item): ?>
                            <?
                                $platform = $item->platform;
                                if ($platform == 'telegram')
                                    $platform = 'tg';
                                if ($platform == 'wordpress')
                                    $platform = 'wp';
                            ?>

                            <option value="<?= $item->id ?>" <?= $post->channel_id == $item->id ? 'selected' : '' ?>>
                              [<?= $platform ?>]
                              <?= safe($item->name) ?>
                            </option>
                            
                          <? endforeach ?>

                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label"><?= lang('pub_date') ?></label>
                        <input name="post[pub_date]" class="form-control datetimepicker" type="text" value="<?= $post->pub_date ?? '0000-00-00 00:00:00' ?>">
                      </div>
                    </div>
                    <div class="row mb-2 main-buttons">
                      <div class="col-md-6">
                        <label class="form-label"><?= lang('status') ?></label>
                        <select name="post[status]" class="form-select" id="formrow-role-input">
                          <option value="posted" <?= $post->status == 'posted' ? 'selected' : '' ?> >
                            <?= lang('post_posted') ?>
                          </option>
                          <option value="draft" <?= $post->status == 'draft' ? 'selected' : '' ?> >
                            <?= lang('post_draft') ?>
                          </option>
                          <option value="queued" <?= $post->status == 'queued' ? 'selected' : '' ?> >
                            <?= lang('post_queued') ?>
                          </option>
                          <option value="moderation" <?= $post->status == 'moderation' ? 'selected' : '' ?> >
                            <?= lang('post_moderation') ?>
                          </option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label"><?= lang('post_name') ?></label>
                        <input name="post[name]" type="text" class="form-control" value="<?= safe($post->name) ?>">
                      </div>
                    </div>
                    <div class="mb-2 main-buttons d-flex justify-content-between align-items-end">
                        <button id="upload_button" type="button" class="btn btn-outline-light w-md mt-2" title="<?= safe(lang('add_media')) ?>">
                          <i class='bx bx-cloud-upload bx-md'></i>
                        </button>
                        <button id="pub_now" type="button" class="btn btn-outline-primary w-md"><?= lang('pub_now') ?></button>
                        <button id="post_save" type="button" class="btn btn-primary w-md"><?= lang('save') ?></button>
                        <input type="hidden" name="post[id]" value="<?= $post->id ?>">
                    </div>
                    <div class="row mb-2 upload-zone" style="display:none;">
                      <div class="col-2 d-flex justify-content-center align-items-center">
                        <button id="upload_back" type="button" class="btn btn-outline-light w-md" title="<?= lang('back') ?>">
                          <span class="label-md"><?= lang('back') ?></span>
                          <i class="bx bx-arrow-back icon-sm"></i>
                        </button>
                      </div>
                      <div class="col-4">
                        <form id="my-dropzone" action="/posting/upload" class="dropzone upload-message-area mb-3">
                          <div class="dz-message upload-message">
                            <div class="upload-icon text-center">
                              <i class='bx bxs-cloud-upload'></i>
                            </div>
                            <p class="mb-0"><?= lang('drop_files') ?></p>
                            <p class="mx-1 fs-6"><?= sprintf(lang('supported_files'), implode(', ', $allowed_types)) ?></p>
                          </div>
                        </form>
                        <input type="hidden" id="file_names" value="<?= safe($post->files) ?>">
                      </div>
                      <div id="post-files-list" class="col-6">

                        <? $files = unjson($post->files) ? unjson($post->files) : []; ?>
                        <? foreach ($files as $file): ?>
                          <? $type = media_type($file); ?>
                          
                          <div class="post-file">
                            <i class='bx bx-x bx-sm px-2 cursor-pointer btn-del-file' data-file="<?= $file ?>"></i>
                            <a href="/assets/upload/<?= $userinfo->id ?>/<?= $file ?>" target="_blank">
                              <?= $file ?>
                            </a>
                            <div class="media-container d-flex justify-content-center">

                              <? if ($type == 'photo'): ?>

                                <img src="/assets/upload/<?= $userinfo->id ?>/<?= $file ?>">

                              <? elseif ($type == 'video'): ?>

                                <video src="/assets/upload/<?= $userinfo->id ?>/<?= $file ?>">
                                
                              <? endif ?>
                              
                            </div>
                          </div>

                        <? endforeach ?>
                        
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