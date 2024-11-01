<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>

<div class="flapt-sidemenu-wrapper">
  <!-- Desktop Logo -->
  <div class="flapt-logo">
    <h3 class="desktop-logo text-center mt-4"><?= $project_name ?></h3>
    <h3 class="small-logo text-right mx-4 mt-4"><?= mb_substr($project_name, 0, 1) ?></h3>
  </div>
  <!-- Side Nav -->
  <div class="flapt-sidenav" id="flaptSideNav">
    <div class="side-menu-area">
      <nav>
        <ul class="sidebar-menu" data-widget="tree">

          <? foreach ($main_menu as $link => $item): ?>
            <?
                if ( ! empty($item['admin']) and $userinfo->type != 'admin')
                    continue;
                $active = page_is($link)
                        ? 'class="active"'
                        : '';
                if ( ! empty($item['disabled']))
                    $link = '#';
            ?>

            <li <?= $active ?>>
              <a href="<?= $link ?>">
                <i class='bx <?= $item['icon'] ?>'></i>
                <span><?= $item['title'] ?></span>
              </a>
            </li>

          <? endforeach ?>

        </ul>
      </nav>
    </div>
  </div>
</div>