<?php
/**
 * Template Name: Политика конфиденциальности
 */
get_header();
?>

    <main class="privacy-policy">
        <div class="container">
            <?php if ( function_exists('yoast_breadcrumb') ) {
                yoast_breadcrumb('<div class="breadcrumb">','</div>');
            } ?>
            
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    // Sahifa sarlavhasi
                    echo '<h1>' . get_the_title() . '</h1>';

                    // Sahifa kontenti
                    echo '<div class="page-content">';
                    the_content();
                    echo '</div>';
                endwhile;
            endif;
            ?>
        </div>
    </main>

<?php
get_footer();
