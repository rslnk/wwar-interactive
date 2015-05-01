<?php

/*

  WordPress as a REST API
  -----------------------

  Based on Pete Nelson's https://gist.github.com/petenelson/4724984
  See also: http://www.billerickson.net/code/improve-performance-of-wp_query

  API calls
  =========

  1) example.com/api/?action=list-all-events
  2) List all 'stories' post type (to do!)

*/

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : false;
$path = isset($_REQUEST['path']) ? $_REQUEST['path'] : false;

$output = null;
$api = new API_Data();

switch ($action) {

  case 'site-configuration':
    $output = $api->site_configuration();
    break;

  case 'list-all-events':
    $output = $api->list_all_events();
    break;

  case 'list-all-stories':
    $output = $api->list_all_stories();
    break;

  case 'event-data':
    $output = $api->event_data($id, $path);
    break;

  case 'story-data':
    $output = $api->story_data($id, $path);
    break;

  default:
    $output = array('error' => 'invalid action');
    break;
}

if ($output) {
  // callback support for JSONP
  if (isset($_REQUEST["callback"])) {
    header("Content-Type: application/javascript");
    echo $_REQUEST['callback'] . '(' . json_encode($output) . ')';
  }
  else {
    header("Content-Type: application/json");
    echo json_encode($output);
  }
}

die();

class DataFilter {
  public static function eventSidebarContent ($content) {
    $result = array();

    foreach ($content as $item) {
      switch ($item['sidebar_content_type']){
        case 'image':
          $i = $item['image'][0];
          $i['type'] = 'image';
          $result[] = $i;
          break;
        case 'quote':
          $i = $item['quote'][0];
          $i['type'] = 'quote';
          $result[] = $i;
          break;
        case 'sidenote':
          $i = $item['sidenote'][0];
          $i['type'] = 'sidenote';
          $result[] = $i;
          break;
        case 'event':
          $i = [];
          $post = $item['related_event'][0];

          // Get WordPress post thumbnail URL (full image)
          $preview_image_id = get_post_thumbnail_id( $post->ID );
          $preview_image_url = wp_get_attachment_url($preview_image_id);

          $i['title'] = $post->post_title;
          $i['slug'] = get_permalink( $post->ID );
          $i['start_date'] = $post->start_date;
          $i['preview_image'] = $preview_image_url;
          $i['type'] = 'event';
          $result[] = $i;
          break;
        case 'story':
          $i = [];
          $post = $item['related_story'][0];

          // Get WordPress post thumbnail URL (full image)
          $preview_image_id = get_post_thumbnail_id( $post->ID );
          $preview_image_url = wp_get_attachment_url($preview_image_id);

          $i['title'] = $post->post_title;
          $i['slug'] = get_permalink( $post->ID );
          $i['hero'] = $post->hero_name;
          $i['hero_age'] = $post->hero_age;
          $i['hero_city'] = $post->hero_city;
          $i['preview_image'] = $preview_image_url;
          $i['type'] = 'story';
          $result[] = $i;
          break;
        case 'video':
          $i = $item['video'][0];
          $i['type'] = 'video';
          $result[] = $i;
          break;
      }
    }

    return $result;
  }

  public static function sources ($sources) {
    $result = array();
    foreach ($sources as $source) {
      $result[] = $source;
    }
    return $result;
  }

  public static function resources ($resources) {
    $result = array();
    foreach ($resources as $resource) {
      $result[] = $resource;
    }
    return $result;
  }
}

class API_Data {
  function __construct () {
    $this->dataFilter = new DataFilter();
  }

  // List taxonomy terms
  function terms_array($terms) {
    $arr = array();
    if ( !empty( $terms ) ) {
      foreach ( $terms as $term ) {
        $arr[] = array(
          'term_name'  => $term->name,
          'term_slug'  => $term->slug,
          'term_color' => get_field('taxonomy_term_color',  $term)
        );
      }
      return $arr;
    }
  }

  // List post data array for related posts output
  function post_data_array($posts) {
    $arr = array();
    if ( !empty( $posts ) ) {
      foreach ( $posts as $post ) {

        // Get WordPress post thumbnail URL (full image)
        $preview_image_id = get_post_thumbnail_id( $post->ID );
        $preview_image_url = wp_get_attachment_url($preview_image_id);
        $city   = get_the_terms( $post->ID , 'story_city' );

        $arr[] = array(
          'post_id'              => $post->ID,
          'post_title'           => $post->post_title,
          'post_slug'            => get_permalink( $post->ID ),
          'format'               => get_post_format( $post->ID ),
          'preview_image'        => $preview_image_url,
          'preview_image_color'  => $post->color,
          'event_start_year'     => $post->start_date,
          'story_hero'           => $post->protagonist_name,
          'city'                 => $this->terms_array($city),
        );
      }
      return $arr;
    }
  }

  // Output all event posts previews
  function site_configuration() {

    $output = array();

    // Output option pages fields
    // http://www.advancedcustomfields.com/resources/get-values-from-an-options-page

    $output[] = array(

      // Site settings
      'site_date_format'      => get_field('site_date_format', 'option'),
      'site_date_language'    => get_field('site_date_language', 'option'),
      'google_analytics_id'   => get_field('google_analytics_id', 'option'),
      'facebook_id'           => get_field('facebook_id', 'option'),

      // Timeline settings
      'default_timeline'      => get_field('default_timeline', 'option'),

      // Dictionary (UI elemets text & labels)

      // Stories
      'add_story_banner_text'                 => get_field('stories_add_story_banner_text', 'option'),
      'all_stories_filter_label'              => get_field('all_stories_filter_label', 'option'),
      'total_stories_label'                   => get_field('total_stories_label', 'option'),
      'flitered_stories_label'                => get_field('flitered_stories_label', 'option'),
      'stories_load_more_label'               => get_field('stories_load_more_label', 'option'),
      'stories_search_result_none_message'    => get_field('stories_search_result_none_message', 'option'),

      // Timeline
      'timeline_info_banner_text'              => get_field('timeline_info_banner_text', 'option'),
      'all_events_filter_label'                => get_field('timeline_all_events_filter_label', 'option'),
      'timeline_total_events_label'            => get_field('timeline_total_events_label', 'option'),
      'timeline_filtered_events_label'         => get_field('timeline_filtered_events_label', 'option'),
      'timeline_search_result_none_message'    => get_field('timeline_search_result_none_message', 'option'),

      // Events and stories filters
      'event_filters'                          => get_field('event_filters', 'option'),
      'story_filters'                          => get_field('story_filters', 'option'),

      // Story
      'story_share_story_label'                => get_field('story_share_story_label', 'option'),
      'story_related_stories_title'            => get_field('story_related_stories_title', 'option'),
      'story_related_events_title'             => get_field('story_related_events_title', 'option'),

      // Event
      'event_impact_counter_label'             => get_field('event_impact_counter_label', 'option'),
      'event_impact_button_default_label'      => get_field('event_impact_button_default_label', 'option'),
      'event_impact_button_active_label'       => get_field('event_impact_button_active_label', 'option'),
      'event_suggest_resource_button_label'    => get_field('event_suggest_resource_button_label', 'option'),
      'event_sources_title'                    => get_field('event_sources_title', 'option'),
      'event_resources_title'                  => get_field('event_resources_title', 'option'),
      'event_related_stories_title'            => get_field('event_related_stories_title', 'option'),
      'event_related_events_title'             => get_field('event_related_events_title', 'option'),

      // Common
      'toggle_navigation_button_label'         => get_field('toggle_navigation_button_screen_text', 'option'),
      'contact_us_button_label'                => get_field('contact_us_button_label', 'option'),
      'search_label'                           => get_field('search_label', 'option'),
      'close_button_label'                     => get_field('close_button_label', 'option'),
      'next_button_label'                      => get_field('next_button_label', 'option'),
      'previous_button_label'                  => get_field('previous_button_labelprevious_button_label', 'option'),

    );

    return $output;

  }

  // Output all EVENTS (posts previews)
  function list_all_events() {

    $query = new WP_Query(array(
      'post_type' => 'event',
      'no_found_rows' => true, // counts posts, remove if pagination required
      'update_post_term_cache' => false, // grabs terms, remove if terms required (category, tag...)
      'update_post_meta_cache' => false, // grabs post meta, remove if post meta required
    ));

    $output = array();

    while ($query->have_posts()) {

      $post = $query->next_post();

      // Get and format event start and end dates (month, day)
      $start_date = strtotime(get_field('start_date',$post->ID));
      $end_date = strtotime(get_field('end_date',$post->ID));

      // Get post taxonomies
      $timelines = get_the_terms( $post->ID , 'event_timeline' );
      $eras = get_the_terms( $post->ID , 'event_era' );
      $types = get_the_terms( $post->ID , 'event_type' );
      $groups = get_the_terms( $post->ID , 'event_group' );
      $topics = get_the_terms( $post->ID , 'event_topic' );
      $tags = get_the_terms( $post->ID , 'global_tag' );

      // Get WordPress post thumbnail URL (full image)
      $preview_image_id = get_post_thumbnail_id( $post->ID );
      $preview_image_url = wp_get_attachment_url($preview_image_id);

      // Output event attributes
      $output[] = array(

        // Basic post data
        'id'                    => $post->ID,
        'title'                 => $post->post_title,
        'published_date_gmt'    => $post->post_date_gmt,
        'subtitle'              => $post->subtitle,
        'permalink'             => get_permalink( $post->ID ),
        'preview_image'         => $preview_image_url,

        // Event dates
        'start_date'            => $post->start_date,

        // Event taxonomy terms
        'timelines'             => $this->terms_array($timelines),
        'eras'                  => $this->terms_array($eras),
        'types'                 => $this->terms_array($types),
        'groups'                => $this->terms_array($groups),
        'topics'                => $this->terms_array($topics),
        'tags'                  => $this->terms_array($tags),

      );

    }

    return $output;

  }

  // Output all STORIES (posts previews)
  function list_all_stories() {

    $query = new WP_Query(array(
      'post_type' => 'story',
      'no_found_rows' => true, // counts posts, remove if pagination required
      'update_post_term_cache' => false, // grabs terms, remove if terms required (category, tag...)
      'update_post_meta_cache' => false, // grabs post meta, remove if post meta required
    ));

    $output = array();

    while ($query->have_posts()) {

      $post = $query->next_post();

      // Get post taxonomies
      $groups = get_the_terms( $post->ID , 'story_group' );
      $topics = get_the_terms( $post->ID , 'story_topic' );
      $city   = get_the_terms( $post->ID , 'story_city' );
      $cities = get_the_terms( $post->ID , 'story_city' );
      $people = get_the_terms( $post->ID , 'story_person' );
      $tags   = get_the_terms( $post->ID , 'global_tag' );

      // Get WordPress post thumbnail URL (full image)
      $preview_image_id = get_post_thumbnail_id( $post->ID );
      $preview_image_url = wp_get_attachment_url($preview_image_id);

      // Output event attributes
      $output[] = array(

      // Basic post data
      'id'                           => $post->ID,
      'title'                        => $post->post_title,
      'published_date_gmt'           => $post->post_date_gmt,
      'permalink'                    => get_permalink( $post->ID ),
      'format'                       => get_post_format( $post->ID ),
      'preview_image'                => $preview_image_url,
      'preview_image_color'          => $post->color,
      'hero'                         => $post->protagonist_name,
      'city'                         => $this->terms_array($city),
      'excerpt'                      => $post->excerpt,
      );

    }

    return $output;

  }

  // Output EVENT post
  function event_data ($id, $path) {
    if (!$id && !$path) return false;

    if ($id) {
        $post_id = $id;
        $post = get_post($post_id);
    } else if ($path) {
        $args = array(
          'name' => $path,
          'post_type' => 'event',
          'numberposts' => 1
        );
        $posts = get_posts($args);
        $post = $posts[0];
    }

    // echo '<pre>';
    // var_dump ($post);
    // echo '</pre>';

    // Get post taxonomies
    $timelines = get_the_terms( $post->ID , 'event_timeline' );
    $eras = get_the_terms( $post->ID , 'event_era' );
    $types = get_the_terms( $post->ID , 'event_type' );
    $groups = get_the_terms( $post->ID , 'event_group' );
    $topics = get_the_terms( $post->ID , 'event_topic' );
    $tags = get_the_terms( $post->ID , 'global_tag' );

    // Get WordPress post thumbnail URL (full image)
    $preview_image_id = get_post_thumbnail_id( $post->ID );
    $preview_image_url = wp_get_attachment_url($preview_image_id);

    // Get all custom fields attached to the post that are not starting from '_'
    // http://www.advancedcustomfields.com/resources/get_fields
    $custom_fields = get_fields($post->ID);

    // Check if header repeater filed has subfields:
    if ( !empty (get_field('header_image')) ) {
      $header_image = $custom_fields['header_image'];
    }
    else {
      $header_image = null;
    }

    // Output event attributes
    $output = array(

      // Basic post data
      'id'                           => $post->ID,
      'title'                        => $post->post_title,
      'published_date_gmt'           => $post->post_date_gmt,
      'subtitle'                     => $post->subtitle,
      'permalink'                    => get_permalink( $post->ID ),
      'authors'                      => $post->authors,
      'preview_image'                => $preview_image_url,

      // Event dates
      'start_date'                   => $post->start_date,
      'end_date'                     => $post->end_date,
      'exact_dates_uknown'           => $post->unknown_date,

      // Post taxonomy terms
      'timelines'                    => $this->terms_array($timelines),
      'eras'                         => $this->terms_array($eras),
      'types'                        => $this->terms_array($types),
      'groups'                       => $this->terms_array($groups),
      'topics'                       => $this->terms_array($topics),
      'tags'                         => $this->terms_array($tags),

      // Header
      'display_header_image'         => $post->display_header_image,
      'header_image'                 => $header_image['url'],
      'header_image_credit'          => $header_image['credit'],
      'header_image_credit_link'     => $header_image['credit_link'],
      'display_header_image_overlay' => $header_image['display_image_overlay'],
      'header_image_overlay_opacity' => $header_image['overlay_opacity'],

      // Main content
      // Note: call needs post ID in order to output tinyMCE content with <p> tags
      'main_content'                 => get_field('main_content', $post->ID),

      'sidebar'                      => $this->dataFilter->eventSidebarContent( get_field('sidebar_content', $post->ID) ),
      'sources'                      => $this->dataFilter->sources( get_field('sources', $post->ID) ),
      'resources'                    => $this->dataFilter->resources( get_field('resources', $post->ID) ),

      // Related posts
      'related_stories'              => $this->post_data_array( $post->related_stories ),
      'related_events'               => $this->post_data_array( get_field('related_events', $post->ID ))

    );

  return $output;

  }

  // Output STORY post
  function story_data ($id, $path) {
    if (!$id && !$path) return false;

    if ($id) {
        $post_id = $id;
        $post = get_post($post_id);
    } else if ($path) {
        $args = array(
          'name' => $path,
          'post_type' => 'story',
          'numberposts' => 1
        );
        $posts = get_posts($args);
        $post = $posts[0];
    }

    // Get post taxonomies
    $groups = get_the_terms( $post->ID , 'story_group' );
    $topics = get_the_terms( $post->ID , 'story_topic' );
    $city   = get_the_terms( $post->ID , 'story_city' );
    $cities = get_the_terms( $post->ID , 'story_city' );
    $people = get_the_terms( $post->ID , 'story_person' );
    $tags   = get_the_terms( $post->ID , 'global_tag' );

    // Get WordPress post thumbnail URL (full image)
    $preview_image_id = get_post_thumbnail_id( $post->ID );
    $preview_image_url = wp_get_attachment_url($preview_image_id);

    // Get all custom fields attached to the post that are not starting from '_'
    // http://www.advancedcustomfields.com/resources/get_fields
    $custom_fields = get_fields($post->ID);

    // Output event attributes
    $output = array(

      // Basic post data
      'id'                           => $post->ID,
      'title'                        => $post->post_title,
      'published_date_gmt'           => $post->post_date_gmt,
      'permalink'                    => get_permalink( $post->ID ),
      'format'                       => get_post_format( $post->ID ),
      'preview_image'                => $preview_image_url,
      'preview_image_color'          => $post->color,
      'hero'                         => $post->protagonist_name,
      'city'                         => $this->terms_array($city),
      'excerpt'                      => $post->excerpt,

      // Post taxonomy terms
      'groups'                       => $this->terms_array($groups),
      'topics'                       => $this->terms_array($topics),
      'cities'                       => $this->terms_array($cities),
      'people'                       => $this->terms_array($people),
      'tags'                         => $this->terms_array($tags),

      // If post format is Video
      'story_video_host'             => $post->story_video_host,
      'story_video_id'               => $post->story_video_id,
      'story_oembed_video'           => $post->story_oembed_video,

      // If post format is Audio
      'story_oembed_audio'           => $post->story_oembed_audio,

      // If post format is Standart (Text)
      // Note: call needs post ID in order to output tinyMCE content with <p> tags
      'story_text'                   => get_field('text', $post->ID),

      // Related posts
      'related_stories'              => $this->post_data_array( get_field('related_stories', $post->ID ) ),
      'related_events'               => $this->post_data_array( get_field('related_events', $post->ID ) )

    );

  return $output;

  }

}
