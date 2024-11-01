<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>

<header class="top-header-area d-flex align-items-center justify-content-between">
  <div class="left-side-content-area d-flex align-items-center">
    <!-- Triggers -->
    <div class="flapt-triggers">
      <div class="menu-collasped" id="menuCollasped">
        <i class='bx bx-grid-alt'></i>
      </div>
      <div class="mobile-menu-open" id="mobileMenuOpen">
        <i class='bx bx-grid-alt'></i>
      </div>
    </div>
  </div>
  <div class="d-flex align-items-center justify-content-end">
    <!-- Top Bar Nav -->
    <div class="right-side-content d-flex align-items-center">
      <div class="user-profile-area">
        <a href="/logout" class="dropdown-item"><i class="bx bx-power-off font-15"
            aria-hidden="true"></i> <?= lang('logout') ?></a>
      </div>
    </div>
  </div>
</header>