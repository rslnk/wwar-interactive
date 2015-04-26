<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Config;

// Add <body> classes
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Config\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');


// Clean up the_excerpt()
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');


/*

  List custom taxonomy terms
  --------------------------

  Get taxonomies terms list (without links)
  See "Get terms for all custom taxonomies" section:
  http://codex.wordpress.org/Function_Reference/get_the_terms

*/

function list_custom_taxonomies_terms(){
  global $wp_query, $post;
  // get post by post id
  $post = get_post($post->ID);
  // get post type by post
  $post_type = $post->post_type;
  // get post type taxonomies
  $taxonomies = get_object_taxonomies( $post_type, 'objects' );

  $out = array();
  foreach ($taxonomies as $taxonomy_slug => $taxonomy){
    // get the terms related to post
    $terms = get_the_terms($post->ID, $taxonomy_slug);
    if (!empty($terms)) {
      $out[] = "\n<ul>";
      foreach ( $terms as $term ) {
        $out[] =
          '  <li><a href="'
        .    get_term_link( $term->slug, $taxonomy_slug ) .'">'
        .    $term->name
        . "</a></li>\n";
      }
      $out[] = "</ul>\n";
    }
  }
  echo implode('', $out );
}