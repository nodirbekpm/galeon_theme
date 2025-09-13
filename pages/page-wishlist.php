<?php
/**
 * Template Name: Wishlist
 */
get_header();


?>

    <!-- product card -->
    <section class="product_card">

        <div class="container">
            <?php if ( function_exists('yoast_breadcrumb') ) {
                yoast_breadcrumb('<div class="breadcrumb">','</div>');
            } ?>

            <div class="top">
                <div class="info">
                    <div class="section_title">Избранное</div>
                </div>
                <!-- <a href="#" class="download">
                    Очистить корзину
                </a> -->
            </div>

            <div class="product_card_row" id="wl_list">
                <p class="empty">Загружается…</p>
            </div>

        </div>
    </section>




<?php
get_footer();

