<?php
get_header(); // Load the header

// Start the loop
if (have_posts()) : while (have_posts()) : the_post(); ?>

    <h1><?php the_title(); ?></h1> <!-- Display the post title -->
    <div class="post-content">
        <?php the_content(); ?> <!-- Display the post content -->
        
        <?php echo do_shortcode('[location_coordinates]'); ?> <!-- Call the shortcode -->
    </div>

<?php
endwhile;
endif;

get_footer(); // Load the footer