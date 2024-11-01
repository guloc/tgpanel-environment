<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>

<? if (isset($message) or !empty($extra_messages)): ?>
    <?
        $messages = empty($extra_messages)
                  ? []
                  : $extra_messages;
        if ( ! empty($message)) {
            $type = empty($good_message)
                   ? 'danger'
                   : 'success';
            array_unshift($messages, [
              'type' => $type,
              'text' => $message
            ]);
        }
    ?>

    <? foreach ($messages as $item): ?>
      <? if ( ! empty($item['text'])): ?>
          <?
            $msg_class = empty($item['type'])
                       ? 'alert alert-primary'
                       : 'alert alert-' . $item['type'];
          ?>
          <div class="<?= $msg_class ?> text-center" role="alert">
            <?= $item['text'] ?>
          </div>
      <? endif; ?>
  <? endforeach; ?>

<? endif; ?>