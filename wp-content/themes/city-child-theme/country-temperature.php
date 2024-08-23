<?php
/**
 * Template Name: Country Temperature Listing
 */

get_header();

do_action('before_country_temperature_table'); // Custom hook before the table

?>

<div class="container">
    <div id="temperature-table">
        <?php get_template_part('template-parts/country-temperature-table'); ?>
    </div>
</div>

<?php

do_action('after_country_temperature_table'); // Custom hook after the table

get_footer();
?>
