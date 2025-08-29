<?php
/**
 * Reusable product card (catalog_item)
 * Usage: get_template_part('template-parts/product/catalog-item', null, ['product' => $product]);
 */
if (!isset($args['product']) || !$args['product'] instanceof WC_Product) return;
$product     = $args['product'];
$product_id  = $product->get_id();
$permalink   = get_permalink($product_id);
$title       = $product->get_name();

// Dimensions (attributes) → "330 мм/234 мм/152 мм"
$len = trim(wp_strip_all_tags($product->get_attribute('pa_length')));
$wid = trim(wp_strip_all_tags($product->get_attribute('pa_width')));
$hei = trim(wp_strip_all_tags($product->get_attribute('pa_height')));
$dim_parts = [];
if ($len !== '') $dim_parts[] = $len . ' мм';
if ($wid !== '') $dim_parts[] = $wid . ' мм';
if ($hei !== '') $dim_parts[] = $hei . ' мм';
$dim_text = implode('/', $dim_parts);

// Price yoki "по запросу"
$price_html = $product->get_price_html();
if ($price_html === '') $price_html = '<span class="price-request">по запросу</span>';

// Images: featured + gallery
$imgs = [];
$main_image_id = $product->get_image_id();
if ($main_image_id) $imgs[] = $main_image_id;
$gallery_ids = $product->get_gallery_image_ids();
if (!empty($gallery_ids)) $imgs = array_merge($imgs, $gallery_ids);
if (empty($imgs)) {
    $placeholder = wc_placeholder_img_src('woocommerce_single');
    $imgs = ['placeholder' => $placeholder];
}
?>

<div class="catalog_item">
    <div class="top_block">
        <div class="like_icon <?php echo my_is_in_wishlist( $product->get_id() ) ? 'active' : ''; ?>"
             data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
             data-product_type="<?php echo esc_attr( $product->get_type() ); ?>"></div>

        <div class="swiper catalogSwiper">
            <div class="swiper-wrapper">
                <?php if (isset($imgs['placeholder'])): ?>
                    <div class="swiper-slide">
                        <img src="<?php echo esc_url($imgs['placeholder']); ?>" alt="<?php echo esc_attr($title); ?>">
                    </div>
                <?php else: ?>
                    <?php foreach ($imgs as $img_id): ?>
                        <div class="swiper-slide">
                            <?php echo wp_get_attachment_image($img_id, 'medium', false, ['alt' => $title]); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <div class="info_block">
        <a href="<?php echo esc_url($permalink); ?>" class="title"><?php echo esc_html($title); ?></a>

        <?php if ($dim_text): ?>
            <a href="<?php echo esc_url($permalink); ?>" class="dimensions">
                <span>Внутренние габариты:</span><br>
                <?php echo esc_html($dim_text); ?>
            </a>
        <?php endif; ?>

        <a href="<?php echo esc_url($permalink); ?>" class="dimensions">
            <span>Цена:</span><br>
            <?php echo wp_kses_post($price_html); ?>
        </a>

        <!-- Dizayn saqlanadi: qty +/- , number input, cart_btn -->
        <form class="cart_controls" action="<?php echo esc_url( $product->add_to_cart_url() ); ?>" method="post">
            <div class="qty_btn minus" data-step="-1">-</div>
            <?php
            $min = apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product);
            $max = apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product);
            if ($product->is_sold_individually()) $max = 1;
            if (empty($max)) $max = 1000;
            $val = max(1, (int)$min);
            ?>
            <input type="number"
                   name="quantity"
                   class="qty"
                   max="1000"
                   min="1"
                   value="1">

            <div class="qty_btn plus" data-step="1">+</div>

            <?php if ( $product->is_purchasable() && $product->is_in_stock() && $product->is_type('simple') ) : ?>
                <!-- Woo standart AJAX qo'llab-quvvatlaydigan tugma -->
                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
                   data-quantity="<?php echo esc_attr($val); ?>"
                   data-product_id="<?php echo esc_attr( $product_id ); ?>"
                   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                   class="cart_btn add_to_cart_button ajax_add_to_cart"
                   aria-label="<?php esc_attr_e('Add to cart','your-td'); ?>"
                   rel="nofollow">
                    <img src="<?php echo esc_url( get_template_directory_uri().'/assets/images/cart_icon.svg' ); ?>" alt="">
                </a>
            <?php endif; ?>

            <!-- Fallback uchun (JS o‘chirilsa) oddiy POST ishlashi mumkin -->
            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
        </form>
    </div>
</div>




