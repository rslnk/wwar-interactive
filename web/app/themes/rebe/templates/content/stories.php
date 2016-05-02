<?php

$the_query = new WP_Query([
	'post_type'			  => 'story',
	'posts_per_page'	=> -1,
	'meta_key'			  => 'story_city',
	'orderby'			    => 'meta_value',
	'order'				    => 'DESC'
]);

?>

<?php if( $the_query->have_posts() ): ?>

  <div class="c-stories__list">
    <?php while( $the_query->have_posts() ): $the_query->the_post(); ?>

      <div class="u-image-cover u-image-cover--square c-story-preview" style="background-image: url(<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>)">
        <div class="story-preview__color-overlay <?php the_field('color', $post->ID); ?>"></div>
        <div class="story-preview__content">

          <div class="story-preview__title">

            <?php
              // Construct Event posts URL
              // Example: reimaginebelonging.org/<stories-app-slug>/<post-slug>/

              $site_url             = get_bloginfo('url');
              $story_post_slug      = App\GetStoryPostTypeSlug();
              $story_post_name      = $post->post_name;

              $angular_app_permalink  = $site_url . '/' . $story_post_slug . '/' . $story_post_name . '/';
            ?>

            <h2 class="o-heading c-story-preview__title" style="color:<?php the_field('color', $post->ID); ?>">
              <a href="<?php echo $angular_app_permalink; ?>">
                <?php the_title(); ?>
              </a>
            </h2>
          </div>
					<div class="story-preview__hero">
						<h3 class="o-heading c-story-preview__title">
							<?php
								$story_post_cities 		= wp_get_object_terms($post->ID, 'story_city');
								$city_name        		= $story_post_cities[0]->name;
							 ?>
							<strong>
								<?php the_field('protagonist_name', $post->ID);  echo ', ' ?>
							</strong>
							<?php echo $city_name; ?>
						</h3>
					</div>
        </div>
      </div>

      <?php wp_reset_postdata(); ?>
    <?php endwhile; ?>
  </div>
<?php endif; ?>