<?php defined('ROCKET_SCRIPT') OR exit( 'No direct script access allowed' ); ?>
<?
    $enabled_langs = explode(',', str_replace(' ' , '', RS_LANGUAGES));
?>
<? if (count($enabled_langs) > 1): ?>
  
  <li class="nav-item dropdown">
    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
      aria-haspopup="true" aria-expanded="false"><span><i
          class='bx bx-world'></i></span></button>
    <div class="dropdown-menu language-dropdown dropdown-menu-right">
      <div class="user-profile-area">

        <? foreach ($languages as $code => $item): ?>
          <? if ( ! in_array($code, $enabled_langs)) continue; ?>

          <a href="/change_language/set/<?= $code ?>" class="dropdown-item mb-15">
            <img src="<?= $item['icon'] ?>" alt="Image">
            <span><?= $item['title'] ?></span>
          </a>

        <? endforeach ?>
        
      </div>
    </div>
  </li>

<? endif ?>