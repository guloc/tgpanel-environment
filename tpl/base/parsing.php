<?php defined('ROCKET_SCRIPT') OR exit( 'No direct script access allowed' ); ?>
<? $this->load->view('blocks/header'); ?>
<?
    if ( ! empty($channel))
      $channel->config['replaces'] = $channel->config['replaces'] ?? [];
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
                    <h5 class="mb-0"><?= lang('parsing') ?></h5>
                    </p>
                  </div>
                </div>
              </div>

              <? $this->load->view('blocks/notificationBlock'); ?>

              <!-- Parsing queue -->
              <div class="col-md-5 col-xxl-5 mt-0">

                <div class="chat-body-left">
                  <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                      aria-labelledby="pills-home-tab" tabindex="0">
                      <div class="single-chat-list" id="chatsList">

                        <div class="d-flex justify-content-left">
                          <button id="add_channel" class="btn btn-outline-success mb-3" type="button">
                            <i class="bx bx-plus"></i>
                            <?= lang('create_channel') ?>
                          </button>
                        </div>

                        <? foreach ($channels as $item): ?>
                          <?
                              $active = (isset($channel) and $channel->id == $item->id)
                                      ? 'active'
                                      : '';
                          ?>
                          
                          <a href="/parsing?id=<?= $item->id ?>" class="chat-media d-flex align-items-center <?= $active ?>">
                            <div class="chat-media-body d-flex justify-content-between align-items-center">
                              <div class="chat-user-info">
                                <h6>
                                  [<?= $item->platform == 'telegram' ? 'tg' : $item->platform ?>]
                                  <?= safe($item->name) ?>
                                </h6>
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

              <? if ( ! empty($channel)): ?>
                
                <!-- Channel settings -->
                <div class="col-md-7">
                  <div class="card">
                    <div class="card-body">
                      <div class="card-title">
                        <h4><?= safe($channel->name) ?></h4>
                      </div>
                      <form method="POST">
                        
                        <div class="form-check form-switch form-switch-md mb-3">
                          <input name="active" type="checkbox" class="form-check-input me-3" id="formCheckActive" <?= $channel->active ? 'checked' : '' ?> >
                          <label class="form-check-label" for="formCheckActive">
                            <?= lang('parsing_active') ?>
                          </label>
                        </div>

                        <div class="row mb-3">
                          <h6><?= lang('copy_data') ?></h6>
                          <div class="col-md-5">
                            <div class="form-check form-switch form-switch-md mb-2">
                              <input name="config[data][text]" class="form-check-input me-3" type="checkbox" id="formCheck1" <?= $channel->config['data']['text'] ? 'checked' : '' ?> >
                              <label class="form-check-label" for="formCheck1">
                                <?= lang('text') ?>
                              </label>
                            </div>
                            <div class="form-check form-switch form-switch-md mb-2">
                              <input name="config[data][image]" class="form-check-input me-3" type="checkbox" id="formCheck2" <?= $channel->config['data']['image'] ? 'checked' : '' ?> >
                              <label class="form-check-label" for="formCheck2">
                                <?= lang('images') ?>
                              </label>
                            </div>
                            <div class="form-check form-switch form-switch-md mb-2">
                              <input name="config[data][files]" class="form-check-input me-3" type="checkbox" id="formCheck5" <?= $channel->config['data']['files'] ? 'checked' : '' ?> >
                              <label class="form-check-label" for="formCheck5">
                                <?= lang('files') ?>
                              </label>
                            </div>
                          </div>

                          <div class="col-md-6 ms-auto">
                            <div class="mt-md-0">
                              <div class="form-check form-check-right form-switch form-switch-md mb-2">
                                <input name="config[data][video]" class="form-check-input me-3" type="checkbox" id="formCheckRight1" <?= $channel->config['data']['video'] ? 'checked' : '' ?> >
                                <label class="form-check-label" for="formCheckRight1">
                                  <?= lang('video') ?>
                                </label>
                              </div>
                              <div class="form-check form-check-right form-switch form-switch-md mb-2">
                                <input name="config[data][audio]" class="form-check-input me-3" type="checkbox" id="formCheckRight2" <?= $channel->config['data']['audio'] ? 'checked' : '' ?> >
                                <label class="form-check-label" for="formCheckRight2">
                                  <?= lang('audio') ?>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="mb-3">
                          <h6><?= lang('start_words') ?></h6>
                          <div class="d-flex flex-row mb-2">
                            <input id="start_word_input" type="text" class="form-control">
                            <button id="add_start_word_btn" type="button" class="btn btn-outline-success"><?= lang('add') ?></button>
                          </div>
                          <div id="start_word_list">
                          
                            <? foreach ($channel->config['start_words'] as $word): ?>
                              
                              <span class="badge text-bg-dark">
                                <?= safe($word) ?>
                                <i class="bx bx-x btn-del-startword"></i>
                              </span>

                            <? endforeach ?>

                          </div>
                          <input type="hidden" name="config[start_words]" value="<?= base64_encode(json_encode($channel->config['start_words'])) ?>">
                        </div>

                        <div class="mb-3">
                          <h6><?= lang('stop_words') ?></h6>
                          <div class="d-flex flex-row mb-2">
                            <input id="stop_word_input" type="text" class="form-control">
                            <button id="add_stop_word_btn" type="button" class="btn btn-outline-success"><?= lang('add') ?></button>
                          </div>
                          <div id="word_list">
                          
                            <? foreach ($channel->config['stop_words'] as $word): ?>
                              
                              <span class="badge text-bg-dark">
                                <?= safe($word) ?>
                                <i class="bx bx-x btn-del-stopword"></i>
                              </span>

                            <? endforeach ?>

                          </div>
                          <input type="hidden" name="config[stop_words]" value="<?= base64_encode(json_encode($channel->config['stop_words'])) ?>">
                        </div>

                        <div class="mb-3">
                          <h6><?= lang('add_subscript') ?></h6>
                          <div id="toolbar"></div>
                          <div id="editor"><?= $channel->config['subscript'] ?? '' ?></div>
                          <input type="hidden" name="config[subscript]" value="<?= safe($channel->config['subscript']) ?>">
                        </div>

                        <div class="mb-3">
                          <h6><?= lang('replacements') ?></h6>
                          <div class="d-flex mb-2">
                            <input id="replace_from_input" type="text" class="form-control">
                            <input id="replace_to_input" type="text" class="form-control">
                            <button id="add_replace_btn" type="button" class="btn btn-outline-success"><?= lang('add') ?></button>
                          </div>
                          <input type="hidden" name="config[replaces]" value="<?= base64_encode(json_encode($channel->config['replaces'])) ?>">

                          <div class="table-responsive text-nowrap">
                            <table class="table table-striped table-nowrap">
                              <thead>
                                <tr>
                                  <th><?= lang('replace_from') ?></th>
                                  <th><?= lang('replace_to') ?></th>
                                  <th></th>
                                </tr>
                              </thead>
                              <tbody id="replaces_list">

                                <? foreach ($channel->config['replaces'] as $item): ?>
                                  
                                  <tr class="replace-item">
                                    <td class="replace-from"><?= safe($item['from']) ?></td>
                                    <td class="replace-to"><?= safe($item['to']) ?></td>
                                    <td class="text-right">
                                      <i class="del_replace_btn bx bx-x bx-md text-danger cursor-pointer"></i>
                                    </td>
                                  </tr>

                                <? endforeach ?>

                              </tbody>
                            </table>

                          </div>
                        </div>

                        <div class="row mb-2 px-3 py-2">
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="remove_links" name="config[remove_links]" class="form-check-input me-3" type="checkbox" <?= $channel->config['remove_links'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="remove_links">
                              <?= lang('del_links') ?>
                            </label>
                          </div>
                          <div class="form-check form-switch form-switch-md d-flex col-md-6">
                            <input id="remove_tags" name="config[remove_tags]" class="form-check-input me-3" type="checkbox" <?= $channel->config['remove_tags'] ? 'checked' : '' ?> >
                            <label class="form-check-label" for="remove_tags">
                              <?= lang('del_tags') ?>
                            </label>
                          </div>
                        </div>

                        <div class="row mb-2 px-3 py-2">
                          <div class="form-check form-switch form-switch-md mb-2">
                            <input name="config[paraphrase_active]" class="form-check-input me-3" type="checkbox" id="formCheckParaphrase" <?= empty($channel->config['paraphrase']) ? '' : 'checked' ?> >
                            <label class="form-check-label" for="formCheckParaphrase">
                              <?= lang('rephrase_text') ?>
                            </label>
                          </div>
                          <div id="prompt_block" class="form-group" <?= empty($channel->config['paraphrase']) ? 'style="display: none;"' : '' ?> >
                            <label class="form-label">
                              <?= lang('prompt') ?>
                            </label>
                            <input name="config[paraphrase_prompt]" type="text" class="form-control" value="<?= empty($channel->config['paraphrase']) ? safe($default_paraphrase_prompt) : safe($channel->config['paraphrase']) ?>">
                          </div>
                        </div>

                        <div class="row mb-2 px-3 py-2">
                          <div class="form-check form-switch form-switch-md mb-2 col-md-6">
                            <input name="config[autopost_on]" class="form-check-input me-3" type="checkbox" id="formCheckAutopost" <?= empty($channel->config['autopost']) ? '' : 'checked' ?> >
                            <label class="form-check-label" for="formCheckAutopost">
                              <?= lang('autoposting_channel') ?>
                            </label>
                          </div>
                          <div id="moderation_block" class="form-check form-switch form-switch-md mb-2 col-md-6" <?= empty($channel->config['autopost']) ? 'style="display: none;"' : '' ?> >
                            <input name="config[moderation]" class="form-check-input me-3" type="checkbox" id="formCheckModeration" <?= empty($channel->config['moderation']) ? '' : 'checked' ?> >
                            <label class="form-check-label" for="formCheckModeration">
                              <?= lang('moderation') ?>
                            </label>
                          </div>
                          <div class="form_group mb-2">
                            <label class="form-label"><?= lang('channel') ?></label>
                            <select name="config[autopost]" class="form-select" id="formrow-role-input">
                              <option value="" <?= empty($channel->config['autopost']) ? 'selected' : '' ?> >
                                <?= lang('none') ?>
                              </option>

                              <? foreach ($target_channels as $item): ?>
                                <?
                                    $platform = $item->platform;
                                    if ($platform == 'telegram')
                                        $platform = 'tg';
                                    if ($platform == 'wordpress')
                                        $platform = 'wp';
                                ?>

                                <option value="<?= $item->id ?>" <?= @$channel->config['autopost'] == $item->id ? 'selected' : '' ?> >
                                  [<?= $platform ?>]
                                  <?= safe($item->name) ?>
                                </option>
                                
                              <? endforeach ?>

                            </select>
                          </div>
                        </div>

                        <div class="text-center">
                          <input type="hidden" name="id" value="<?= $channel->id ?>">
                          <button type="submit" class="btn btn-primary"><?= lang('save') ?></button>
                        </div>

                      </form>
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