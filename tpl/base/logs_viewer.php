<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Logs Viewer</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/style.css">
    <style>
      #nav {
          display: flex;
          align-items: center;
      }
      #nav>a {
        font-size: 1.5rem;
      }
      .current_time {
          padding: 10px;
          font-weight: 600;
          width: 100%;
          text-align: center;
          cursor: pointer;
          font-size: 1rem;
          text-decoration: none;
          color: #bcbfd0;
      }
      .word-wrap {
          white-space:pre-wrap;
      }
      .word-wrap-btn, .collapse-btn, .filter-mode-btn,
      .clear-filter-btn, .clear-limit-btn, .clear-bl-btn,
      .show-bl-btn {
          display: inline-block;
          cursor: pointer;
          font-size: 16px;
          font-weight: 800;
          margin: 5px;
          padding: 0 5px;
          border: 1px solid black;
          border-radius: 5px;
          font-size: 12px;
          transition: 1s;
      }
      .word-wrap-btn.active, .collapse-btn.active {
          background: #8c8;
      }
      .show-bl-btn.active {
        background: #fff;
      }
      .filter-mode-btn.blacklist {
          background: #ccab88;
      }
      .time {
          padding: 0 5px;
          border: 1px solid #cbd8e687;
          border-radius: 5px;
          color: #bcbfd0 !important;
          font-size: 1rem;
      }
      .info-body {
          display: block;
      }
      .info-body.folded {
          display: none;
          transition: 1s;
      }
      .info-body.folded::before {
          display: block;
          content: '…';
      }
      .time.folded {
          cursor: pointer;
          color: #d3deea;
          padding: 0 5px;
          border-radius: 7px;
          border: 1px solid #cad1d9;
      }
      .time[data-id] {
          cursor: pointer;
      }
      .filter_link {
        cursor: pointer;
        color: #d3deea;
        padding: 0 5px;
        border-radius: 7px;
        border: 1px solid #cbd8e687;
      }
      .filter_link.blacklist {
        color: #f9f0f0;
        border: 1px solid #ecc7c787;
      }
      .filter_link:hover {
        text-decoration: underline;
      }
      .hidden {
        display: none;
      }
    </style>
  </head>
  <body>

    <div class="container">
      <div id="nav">
        <a class="btn btn-secondary btn-sm" href="<?= $nav['prev'] ?>"><i class="bx bx-left-arrow-alt"></i></a>
        <a class="current_time" href="/logs/viewer">
          <?= (empty($day) or $day == time_format($now, 'm-d'))
            ? str_replace(' ', '<br>', $now)
            : date('Y-') . $day
          ?>
        </a>
        <? if ($day != date('m-d')): ?>
          <a class="btn btn-secondary btn-sm" href="<?= $nav['next'] ?>"><i class="bx bx-right-arrow-alt"></i></a>
        <? endif ?>
      </div>
      <br>
      <button class="word-wrap-btn <?= empty($_COOKIE['log_ww']) ? '' : 'active' ?>">
        WORD WRAP
      </button>
      <button class="collapse-btn <?= empty($_COOKIE['log_col']) ? '' : 'active' ?>">
        COLLAPSE
      </button>
      <button class="filter-mode-btn <?= empty($_COOKIE['log_filter_mode']) ? '' : 'blacklist' ?>">
        <?= empty($_COOKIE['log_filter_mode']) ? 'WHITELIST' : 'BLACKLIST' ?>
      </button>

      <? if ( ! empty($filter)): ?>
        
        <button class="clear-filter-btn">
          <span>❌</span> <i><?= $filter ?></i>
        </button>

      <? endif; ?>

      <? if ( ! empty($limit)): ?>
        
        <button class="clear-limit-btn">
          <span>❌</span> <i>Limit: <?= $limit ?></i>
        </button>

      <? endif; ?>

      <? if ( ! empty($blacklist)): ?>
        <? $items = explode(',', $blacklist); ?>
        
        <button class="show-bl-btn">
          <span class="folder-icon">➕</span> <i>Blacklist (<?= count( $items) ?>)</i>
        </button>

        <button class="clear-bl-btn hidden" data-item="CLEAR ALL">
          <span>❌</span> <i>CLEAR ALL</i>
        </button>

        <? foreach ($items as $item): ?>
          
          <button class="clear-bl-btn hidden" data-item="<?= safe($item, 'alphadash') ?>">
            <span>❌</span> <i><?= safe($item, 'alphadash') ?></i>
          </button>

        <? endforeach; ?>

      <? endif; ?>


      <pre id="content" <?= empty($_COOKIE['log_ww']) ? '' : 'class="word-wrap"' ?>><?= $text ?></pre>

      <? if ( ! empty($limit)): ?>
        
        <button class="clear-limit-btn">
          SHOW ALL
        </button>

      <? endif; ?>

    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
      let params = {
        <? if ( ! empty($limit)) echo "limit: \"{$limit}\","; ?>
        <? if ( ! empty($_GET['day']) and $_GET['day'] != date('m-d')) echo "day: \"{$day}\","; ?>
        <? if ( ! empty($blacklist)) echo "blacklist: \"{$blacklist}\","; ?>
      };
      function setFilter(filter) {
          let href = window.location.href,
              url = new URL(href),
              query = href.indexOf('?') > 0
                    ? href.substr(href.indexOf('?'))
                    : '';
          window.location = url.pathname
                            <? if ( ! empty($filter)): ?>
                              .replace(/\/+?<?= $filter ?>\/?/, '')
                            <? endif ?>
                          + (filter.length > 0 ? '/' : '') + filter
                          + query;
      }
      function saveReload(new_params) {
          let url = new URL(window.location.href),
              esc = encodeURIComponent,
              query = Object.keys(new_params)
                            .map(k => esc(k) + '=' + esc(new_params[k]))
                            .join('&');
          window.location = url.pathname
                          + (query.length > 0 ? '?' : '')
                          + query;
      }
      function setParam(name, value) {
          let config = {};
          $.each(params, (key, item) => {
              if (key == name) {
                  item = value;
              }
              config[key] = item;
          });
          if (typeof params[name] == 'undefined') {
              config[name] = value;
          }
          saveReload(config);
      }
      function resetParam(name) {
          let config = {};
          $.each(params, (key, item) => {
              if (key != name) {
                  config[key] = item;
              }
          });
          saveReload(config);
      }
      function addToBlacklist(item) {
          let blacklist = params.blacklist ? params.blacklist.split(',') : [];
          if ( ! blacklist.includes(item)) {
              blacklist.push(item);
              setParam('blacklist', blacklist.join(','));
          }
      }
      function removeFromBlacklist(item) {
          let blacklist = params.blacklist ? params.blacklist.split(',') : [];
          blacklist = blacklist.filter(i => i != item);
          if (blacklist.length > 0) {
              setParam('blacklist', blacklist.join(','));
          } else {
              resetParam('blacklist');
          }
      }
      function setCookie(name, value, days) {
          let expires = "";
          if (days) {
              let date = new Date();
              date.setTime(date.getTime() + (days * 24 * 3600 * 1000));
              expires = "; expires=" + date.toUTCString();
          }
          document.cookie = name + "=" + value + expires + "; path=/";
      }
      $('.word-wrap-btn').click(function() {
          if ($(this).hasClass('active')) {
              $(this).removeClass('active');
              $('#content').removeClass('word-wrap');
              setCookie('log_ww', 0, -1);
          } else {
              $(this).addClass('active');
              $('#content').addClass('word-wrap');
              setCookie('log_ww', 1, 365);
          }
      });
      $('.time[data-id]').click(function () {
          let id = $(this).attr('data-id');
          if ($(this).hasClass('folded')) {
              $(this).removeClass('folded');
              $('#body_'+id).removeClass('folded');
          } else {
              $(this).addClass('folded');
              $('#body_'+id).addClass('folded');
          }
      });
      $('.collapse-btn').click(function () {
          if ($(this).hasClass('active')) {
              setCookie('log_col', 0, -1);
              $(this).removeClass('active');
              $('.time[data-id], .info-body').removeClass('folded');
          } else {
              setCookie('log_col', 1, 365);
              $(this).addClass('active');
              $('.time[data-id], .info-body').addClass('folded');
          }
      });
      $('.filter-mode-btn').click(function () {
          if ($(this).hasClass('blacklist')) {
              setCookie('log_filter_mode', 0, -1);
              $(this).removeClass('blacklist');
              $(this).text('WHITELIST');
              $('.filter_link').removeClass('blacklist');
          } else {
              setCookie('log_filter_mode', 1, 365);
              $(this).addClass('blacklist');
              $(this).text('BLACKLIST');
              $('.filter_link').addClass('blacklist');
          }
      });
      $('.filter_link').click(function () {
          let filter = $(this).text().trim();
          if ($(this).hasClass('blacklist')) {
              addToBlacklist(filter);
          } else {
              setFilter(filter);
          }
      });
      $('.clear-filter-btn').click(function () {
          setFilter('');
      });
      $('.clear-limit-btn').click(function () {
          resetParam('limit');
      });
      $('.show-bl-btn').click(function() {
          if ($(this).hasClass('active')) {
              $(this).removeClass('active');
              $('.clear-bl-btn').addClass('hidden');
              $('.show-bl-btn .folder-icon').text('➕');
          } else {
              $(this).addClass('active');
              $('.clear-bl-btn').removeClass('hidden');
              $('.show-bl-btn .folder-icon').text('➖');
          }
      });
      $('.clear-bl-btn').click(function () {
          if ($(this).attr('data-item') == 'CLEAR ALL') {
              resetParam('blacklist');
              return false;
          }
          removeFromBlacklist($(this).attr('data-item'));
      });
      $('#nav').click({
          'refresh': function() {
              window.location = window.location;
          },
      });
      $('.current_time').click(function() {
          window.location = window.location;
          return false;
      });
    </script>
  </body>
</html>