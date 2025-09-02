<?php
/* Template Name: My Account (Woo) */
get_header();
?>
    <div class="container">
        <?php if (function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb('<div class="breadcrumb">', '</div>');
        } ?>
<!--        <div class="profile">-->
<!--            <div class="main">-->
                <?php
                while (have_posts()) : the_post();
                    // Variant 1: sahifa kontentidagi shortkodni ishlatish
                    the_content();

                    // YOKI Variant 2: shortkodni bevosita chaqirish (agar sahifada shortkod bo'lmasa):
                    // echo do_shortcode('[woocommerce_my_account]');
                endwhile;
                ?>
<!--            </div>-->
<!--        </div>-->
    </div>
<?php


get_footer();