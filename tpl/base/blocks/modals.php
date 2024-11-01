<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>

<? if (page_is('/channels')): ?>

  <div class="modal fade" id="channel_editor" tabindex="-1" aria-hidden="true"
       data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable2 modal-lg">
      <form class="modal-content p-3" method="POST" action="/channels">
        <div class="modal-header">
          <h1 class="modal-title fs-5"><?= lang('create_channel') ?></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('close') ?>"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label"><?= lang('channel_name') ?></label>
            <input type="text" name="create[name]" class="form-control" placeholder="<?= lang('enter_channel_name') ?>" maxlength="128">
          </div>
          <div class="mb-2">
            <label class="form-label"><?= lang('platform') ?> <span class="text-danger">*</span></label>
            <select name="create[platform]" class="form-select" id="formrow-role-input">
              <option value="telegram" selected>Telegram</option>
              <option value="vk">VK</option>
              <option value="wordpress">Wordpress</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label"><?= lang('channel_link') ?> <span class="text-danger">*</span></label>
            <input type="text" name="create[link]" class="form-control" placeholder="<?= safe(lang('enter_channel_link_tg')) ?>" required maxlength="128">
          </div>
          <div class="mb-2" style="display: none;">
            <label class="form-label"><?= lang('login') ?> <span class="text-danger">*</span></label>
            <input type="text" name="create[access_name]" class="form-control" placeholder="<?= lang('enter_login') ?>" maxlength="512">
          </div>
          <div class="mb-2" style="display: none;">
            <label class="form-label"><?= lang('access_token') ?> <span class="text-danger">*</span></label>
            <input type="text" name="create[access_token]" class="form-control" placeholder="<?= lang('enter_access_token') ?>" maxlength="512">
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <input type="hidden" name="id">
          <button type="submit" name="create_channel" class="btn btn-primary"><?= lang('save') ?></button>
        </div>
      </form>
    </div>
  </div>

<? elseif (page_is('/parsing')): ?>

  <div class="modal fade" id="channel_editor" tabindex="-1" aria-hidden="true"
       data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable2 modal-lg">
      <form class="modal-content p-3" method="POST" action="/parsing">
        <div class="modal-header">
          <h1 class="modal-title fs-5"><?= lang('create_channel') ?></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('close') ?>"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label"><?= lang('platform') ?> <span class="text-danger">*</span></label>
            <select name="channel[platform]" class="form-select" id="formrow-role-input">
              <option value="telegram" selected>Telegram</option>
              <option value="vk">VK</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label"><?= lang('channel_link') ?> <span class="text-danger">*</span></label>
            <textarea type="text" name="channel[links]" class="form-control" placeholder="<?= lang('enter_channel_links') ?> <?= lang('enter_channel_link_tg') ?>" required></textarea>
          </div>
          <div class="form_group mb-3">
            <label class="form-label"><?= lang('autoposting_channel') ?></label>
            <select name="channel[autopost]" class="form-select" id="formrow-role-input">
              <option value="" selected>
                <?= lang('none') ?>
              </option>

              <? foreach ($target_channels as $item): ?>

                <option value="<?= $item->id ?>" <?= @$channel->config['autopost'] == $item->id ? 'selected' : '' ?> >
                  [<?= $item->platform == 'telegram' ? 'tg' : $item->platform ?>]
                  <?= safe($item->name) ?>
                </option>
                
              <? endforeach ?>

            </select>
          </div>
          <div class="form-check form-switch form-switch-md mb-3">
            <input name="channel[active]" type="checkbox" class="form-check-input me-3" id="formCheckActiveModal">
            <label class="form-check-label" for="formCheckActiveModal">
              <?= lang('parsing_active') ?>
            </label>
          </div>
          <!-- <div class="mb-2">
            <label class="form-label"><?= lang('channel_name') ?></label>
            <input type="text" name="channel[name]" class="form-control" placeholder="<?= lang('enter_channel_name') ?>" maxlength="128">
          </div> -->
        </div>
        <div class="modal-footer justify-content-center">
          <input type="hidden" name="id">
          <button type="submit" name="add_channel" class="btn btn-primary"><?= lang('save') ?></button>
        </div>
      </form>
    </div>
  </div>

<? elseif (page_is('/posting')): ?>

<? elseif (page_is('/groups')): ?>

  <div class="modal fade" id="group_editor" tabindex="-1" aria-hidden="true"
       data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable2 modal-lg">
      <form class="modal-content p-3" method="POST" action="/groups">
        <div class="modal-header">
          <h1 class="modal-title fs-5"><?= lang('create_group') ?></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('close') ?>"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label"><?= lang('group_id') ?> <span class="text-danger">*</span></label>
            <input type="text" name="group[id]" class="form-control" placeholder="<?= lang('enter_group_id') ?>" required maxlength="128">
          </div>
          <div class="mb-2">
            <label class="form-label"><?= lang('group_name') ?></label>
            <input type="text" name="group[name]" class="form-control" placeholder="<?= lang('enter_group_name') ?>" maxlength="128">
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="submit" name="add_group" class="btn btn-primary"><?= lang('save') ?></button>
        </div>
      </form>
    </div>
  </div>

<? elseif (page_is('/users')): ?>

  <div class="modal fade" id="user_editor" tabindex="-1" aria-hidden="true"
       data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable2 modal-lg">
      <form class="modal-content p-3" method="POST" action="/users">
        <div class="modal-header">
          <h1 class="modal-title fs-5"><?= lang('change_pass') ?></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('close') ?>"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label"><?= lang('new_pass') ?> <span class="text-danger">*</span></label>
            <input type="password" name="update_user[password]" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label"><?= lang('confirm_pass') ?> <span class="text-danger">*</span></label>
            <input type="password" name="update_user[password2]" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <input type="hidden" name="update_user[id]">
          <button type="submit" name="add_group" class="btn btn-primary"><?= lang('save') ?></button>
        </div>
      </form>
    </div>
  </div>

<? endif ?>