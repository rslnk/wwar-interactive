<?php use Roots\Sage\Nav\NavWalker; ?>

<header class="c-site-head">
  <div class="o-wrapper">
    <nav class="o-site-nav c-site-nav--head">
      <a href="<?= esc_url(home_url('/')); ?>" class="o-site-logo c-site-logo--nav u-icon u-icon-logo">
        <?php bloginfo('name'); ?>
      </a>
      <a class="c-site-nav__menu-toggle js-toggleNavMenu u-icon u-icon-menu-mint"></a>
      <?php
      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu(['theme_location' => 'primary_navigation', 'walker' => new NavWalker(), 'menu_class' => 'c-site-nav__list _c-site-nav__list--head']);
      endif;
      ?>
    </nav>
  </div>
</header>