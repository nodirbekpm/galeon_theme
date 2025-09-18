<?php
/* Template Name: My Account (Woo) */
defined('ABSPATH') || exit;

// Agar login bo‘lmagan bo‘lsa — asosiy sahifaga qaytaramiz
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/') );
    exit;
}

get_header();
?>
    <div class="container">
        <?php if (function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb('<div class="breadcrumb">', '</div>');
        } ?>

        <?php
        while (have_posts()) : the_post();
            // Sahifa kontenti yoki shortkod
            the_content();
            // yoki: echo do_shortcode('[woocommerce_my_account]');
        endwhile;
        ?>
    </div>
<?php get_footer();
