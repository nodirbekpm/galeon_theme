<?php
get_header();

global $product;
if (!$product) {
    $product = wc_get_product(get_the_ID());
}
$price_html = $product ? $product->get_price_html() : '';
$attachment_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
$back_url = wp_get_referer() ? wp_get_referer() : wc_get_page_permalink('shop');

$current_id = $product ? $product->get_id() : 0;

// Kategoriyalar bo‘yicha related (9 ta)
$terms = wp_get_post_terms($current_id, 'product_cat', ['fields' => 'ids']);

$args = [
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => 9,
    'post__not_in' => [$current_id],
    'ignore_sticky_posts' => true,
    'orderby' => 'rand',
];

if (!is_wp_error($terms) && !empty($terms)) {
    $args['tax_query'] = [[
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => $terms,
        'operator' => 'IN',
    ]];
}

$q = new WP_Query($args);

// Agar topilmasa — fallback: kategoriya filtrlovsiz 9 ta
if (!$q->have_posts()) {
    $args_fallback = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 9,
        'post__not_in' => [$current_id],
        'ignore_sticky_posts' => true,
        'orderby' => 'rand',
    ];
    $q = new WP_Query($args_fallback);
}
?>

    <!-- product -->
    <section class="product">
        <div class="container">
            <?php if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<div class="breadcrumb">', '</div>');
            } ?>


            <div class="product_view">
                <div class="gallery">
                    <!-- Left thumbnails -->
                    <div class="swiper thumbs-swiper">
                        <div class="swiper-wrapper">
                            <?php if ($main_image_id): ?>
                                <div class="swiper-slide">
                                    <?php echo wp_get_attachment_image($main_image_id, 'thumbnail'); ?>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($attachment_ids as $id): ?>
                                <div class="swiper-slide">
                                    <?php echo wp_get_attachment_image($id, 'thumbnail'); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Main image -->
                    <div class="swiper main-swiper">
                        <div class="swiper-wrapper">
                            <?php if ($main_image_id): ?>
                                <div class="swiper-slide">
                                    <?php echo wp_get_attachment_image($main_image_id, 'large'); ?>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($attachment_ids as $id): ?>
                                <div class="swiper-slide">
                                    <?php echo wp_get_attachment_image($id, 'large'); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Arrows -->
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>

                    <a href="<?php echo esc_url($back_url); ?>" class="back_link">
                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/navigation_item_arrow.svg"
                             alt="">
                        <span>Назад</span>
                    </a>
                </div>

                <div class="information">
                    <div class="main_title"><?php the_title(); ?></div>
                    <div class="code">
                        Артикул:<?php echo $product && $product->get_sku() ? esc_html($product->get_sku()) : '—'; ?></div>

                    <?php render_product_characteristics($product); ?>

                    <div class="price">
                        <?php esc_html_e('Цена:', 'your-td'); ?>
                        <?php if ($price_html === '') : ?>
                            <span class="price-request">по запросу</span>
                        <?php else : ?>
                            <?php
                            // WooCommerce narx HTML’ini to‘g‘ridan-to‘g‘ri chiqaramiz (valyuta, chegirma va h.k. bilan)
                            echo wp_kses_post($price_html);
                            ?>
                        <?php endif; ?>
                    </div>

                    <div class="cart_controls">
                        <div class="quantity">
                            <div class="qty_btn minus" id="minus">-</div>
                            <input type="number" max="1000" min="1" value="1" id="qty" class="qty">
                            <div class="qty_btn plus" id="plus">+</div>
                        </div>

                        <div class="button_container">
                            <?php if ( $product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock() ) : ?>
                                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
                                   class="cart_btn add_to_cart_button ajax_add_to_cart"
                                   data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
                                   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                                   data-product_type="simple"
                                   data-quantity="1">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cart_icon.svg" alt="">
                                    <span>В корзину</span>
                                </a>
                            <?php else : ?>
                                <!-- variable / grouped / external uchun product sahifaga yo'naltirish -->
                                <a class="cart_btn"
                                   data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
                                   data-product_type="<?php echo esc_attr( $product->get_type() ); ?>"
                                   data-product_url="<?php echo esc_url( get_permalink() ); ?>">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cart_icon.svg" alt="">
                                    <span>В корзину</span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- recommended products -->
<?php if ($q->have_posts()) : ?>
    <section class="production recomend_products">
        <div class="container">
            <div class="swiper envy">
                <div class="section_title">Смотрите так же:</div>

                <div class="swiper-wrapper">
                    <?php while ($q->have_posts()) : $q->the_post(); ?>
                        <div class="swiper-slide">
                            <?php
                            $p = wc_get_product(get_the_ID());
                            get_template_part('template-parts/product/catalog-item', null, ['product' => $p]);
                            ?>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>

                <!-- navigation -->
                <div class="navigation">
                    <div class="swiper-button-prev navigation_item">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/navigation_item_arrow.svg'); ?>"
                             alt="">
                    </div>
                    <div class="swiper-button-next navigation_item">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/navigation_item_arrow.svg'); ?>"
                             alt="">
                    </div>
                </div>

                <!-- Pagination -->
                <div class="swiper-pagination pagination_block"></div>
            </div>
        </div>
    </section>
<?php endif; ?>




<?php
get_footer();