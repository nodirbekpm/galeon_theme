<?php
/**
 * Template Name: Cart (Wrapper)
 */
defined('ABSPATH') || exit;

get_header(); ?>
    <section class="product_card cart">
        <div class="container">
            <?php if ( function_exists('yoast_breadcrumb') ) {
                yoast_breadcrumb('<div class="breadcrumb">','</div>');
            } ?>

            <?php
            // Sahifa kontenti: bu yerda [woocommerce_cart] render bo'ladi
            if ( have_posts() ) : while ( have_posts() ) : the_post();
                the_content();
            endwhile; endif;
            ?>
        </div>
    </section>
<?php get_footer();
