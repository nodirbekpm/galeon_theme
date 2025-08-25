<?php
/**
 * The main template file
 *
 * This is a fallback template for all pages and posts
 */

get_header();
?>

    <main id="main" class="site-main">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();
                get_template_part( 'template-parts/content', get_post_type() );
            endwhile;

            // Pagination
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => __('« Prev', 'galeon'),
                'next_text' => __('Next »', 'galeon'),
            ) );

        else :
            get_template_part( 'template-parts/content', 'none' );
        endif;
        ?>
    </main>

<?php
get_sidebar();
get_footer();