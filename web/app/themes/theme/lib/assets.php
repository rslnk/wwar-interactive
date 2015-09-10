<?php

namespace Roots\Sage\Assets;

/**
 * Scripts and stylesheets
 *
 * Enqueue stylesheets in the following order:
 * 1. /theme/dist/styles/main.css
 *
 * Enqueue scripts in the following order:
 * 1. Latest jQuery via Google CDN (if enabled in config.php)
 * 2. /theme/dist/scripts/modernizr.js
 * 3. /theme/dist/scripts/main.js
 *
 * Google Analytics is loaded after enqueued scripts if:
 * - An ID has been defined in config.php
 * - You're not logged in as an administrator
 */

class JsonManifest {
  private $manifest;

  public function __construct($manifest_path) {
    if (file_exists($manifest_path)) {
      $this->manifest = json_decode(file_get_contents($manifest_path), true);
    } else {
      $this->manifest = [];
    }
  }

  public function get() {
    return $this->manifest;
  }

  public function getPath($key = '', $default = null) {
    $collection = $this->manifest;
    if (is_null($key)) {
      return $collection;
    }
    if (isset($collection[$key])) {
      return $collection[$key];
    }
    foreach (explode('.', $key) as $segment) {
      if (!isset($collection[$segment])) {
        return $default;
      } else {
        $collection = $collection[$segment];
      }
    }
    return $collection;
  }
}

function asset_path($filename) {
  $dist_path = get_template_directory_uri() . DIST_DIR;
  $directory = dirname($filename) . '/';
  $file = basename($filename);
  static $manifest;

  if (empty($manifest)) {
    $manifest_path = get_template_directory() . DIST_DIR . 'assets.json';
    $manifest = new JsonManifest($manifest_path);
  }

  if (array_key_exists($file, $manifest->get())) {
    return $dist_path . $directory . $manifest->get()[$file];
  } else {
    return $dist_path . $directory . $file;
  }
}

function bower_map_to_cdn($dependency, $fallback) {
  static $bower;

  if (empty($bower)) {
    $bower_path = get_template_directory() . '/bower.json';
    $bower = new JsonManifest($bower_path);
  }

  $templates = [
    'google' => '//ajax.googleapis.com/ajax/libs/%name%/%version%/%file%'
  ];

  $version = $bower->getPath('dependencies.' . $dependency['name']);

  if (isset($version) && preg_match('/^(\d+\.){2}\d+$/', $version)) {
    $search = ['%name%', '%version%', '%file%'];
    $replace = [$dependency['name'], $version, $dependency['file']];
    return str_replace($search, $replace, $templates[$dependency['cdn']]);
  } else {
    return $fallback;
  }

}

function assets() {
  wp_enqueue_style('sage_css', asset_path('styles/main.css'), false, null);

  wp_enqueue_style('owl.carousel.main', 'https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css', false, null);
  wp_enqueue_style('owl.carousel.theme', 'https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.theme.min.css', false, null);
  wp_enqueue_style('owl.carousel.transitions', 'https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.transitions.min.css', false, null);

  /**
   * Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
   * jQuery & Modernizr load in the footer per HTML5 Boilerplate's recommendation: http://goo.gl/nMGR7P
   * If a plugin enqueues jQuery-dependent scripts in the head, jQuery will load in the head to meet the plugin's dependencies
   * To explicitly load jQuery in the head, change the last wp_enqueue_script parameter to false
   */
  if (!is_admin() && current_theme_supports('jquery-cdn')) {
    wp_deregister_script('jquery');

    wp_register_script('jquery', bower_map_to_cdn([
      'name' => 'jquery',
      'cdn' => 'google',
      'file' => 'jquery.min.js'
    ], asset_path('scripts/jquery.js')), [], null, true);

    add_filter('script_loader_src', __NAMESPACE__ . '\\jquery_local_fallback', 10, 2);
  }

  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }

  wp_enqueue_script('modernizr', asset_path('scripts/modernizr.js'), [], null, true);
  wp_enqueue_script('jquery');
  wp_enqueue_script('sage_js', asset_path('scripts/main.js'), [], null, true);
  wp_enqueue_script('events_js', asset_path('scripts/events.js'), [], null, true);
  wp_enqueue_script('stories_js', asset_path('scripts/stories.js'), [], null, true);
  wp_enqueue_script('owl_carousel', 'https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js', [], null, true);

  wp_enqueue_style('icons_svg_css', asset_path('styles/icons.svg.css'), false, null);
  wp_enqueue_style('fonts_css', 'http://fast.fonts.net/cssapi/dae2ada1-fb62-4216-ab20-8072b137a586.css', false, null);
  wp_enqueue_style('google_fonts_css', 'http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,400,300,600,700', false, null);

}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\assets', 100);

// http://wordpress.stackexchange.com/a/12450
function jquery_local_fallback($src, $handle = null) {
  static $add_jquery_fallback = false;

  if ($add_jquery_fallback) {
    echo '<script>window.jQuery || document.write(\'<script src="' . $add_jquery_fallback .'"><\/script>\')</script>' . "\n";
    $add_jquery_fallback = false;
  }

  if ($handle === 'jquery') {
    $add_jquery_fallback = apply_filters('script_loader_src', asset_path('scripts/jquery.js'), 'jquery-fallback');
  }

  return $src;
}
add_action('wp_head', __NAMESPACE__ . '\\jquery_local_fallback');
