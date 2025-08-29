<?php
// template-parts/product/wishlist-item.php
if (empty($args['product']) || !($args['product'] instanceof WC_Product)) return;

$product   = $args['product'];
$pid       = $args['parent_id']    ?? $product->get_id();
$vid       = $args['variation_id'] ?? 0;
$is_var    = $product->is_type('variation');

$title     = $product->get_name();
$permalink = get_permalink( $is_var ? $product->get_parent_id() : $product->get_id() );
$sku       = $product->get_sku();
$color     = trim( wp_strip_all_tags( $product->get_attribute('pa_color') ) );

$price_html = $product->get_price_html();
if ($price_html === '') $price_html = '<span class="price-request">по запросу</span>';

$img_id = $is_var ? ( $product->get_image_id() ?: get_post_thumbnail_id( $product->get_parent_id() ) )
    : $product->get_image_id();
$img_html = $img_id ? wp_get_attachment_image($img_id, 'medium', false, ['alt'=>$title])
    : '<img src="'.esc_url( wc_placeholder_img_src('woocommerce_single') ).'" alt="'.esc_attr($title).'">';
?>
<div class="product_card_item">
    <div class="top_buttons">
        <div class="like_icon active"
             data-product_id="<?php echo esc_attr( $pid ); ?>"
             data-product_type="<?php echo esc_attr( $product->get_type() ); ?>"
             <?php if ($is_var): ?>data-variation_id="<?php echo esc_attr( $product->get_id() ); ?>"<?php endif; ?>
        ></div>
    </div>

    <a href="<?php echo esc_url($permalink); ?>" class="left">
        <?php echo $img_html; ?>
    </a>

    <div class="right">
        <div class="info_row">
            <a href="<?php echo esc_url($permalink); ?>" class="title"><?php echo esc_html($title); ?></a>

            <div class="characteristics">
                <?php if ($sku): ?>
                    <div class="item"><div class="name">Артикул</div><div class="value"><?php echo esc_html($sku); ?></div></div>
                <?php endif; ?>
                <?php if ($color): ?>
                    <div class="item"><div class="name">Цвет</div><div class="value"><?php echo esc_html($color); ?></div></div>
                <?php endif; ?>
                <div class="item">
                    <div class="name">Кол-во:</div>
                    <div class="cart_controls">
                        <div class="qty_btn minus">-</div>
                        <input type="number" max="1000" min="1" value="1" class="qty">
                        <div class="qty_btn plus">+</div>
                    </div>
                </div>
                <div class="item">
                    <div class="name">Стоимость:</div>
                    <div class="value"><?php echo wp_kses_post($price_html); ?></div>
                </div>
            </div>
        </div>

        <div class="button">
            <?php if ( ! $is_var && $product->is_purchasable() && $product->is_in_stock() ) : ?>
                <!-- SIMPLE: Woo standart AJAX tugma (Woo o'zi qo'shsin) -->
                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
                   data-quantity="1"
                   data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
                   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                   class="cart_btn add_to_cart_button ajax_add_to_cart"
                   rel="nofollow"
                   aria-label="<?php esc_attr_e('Add to cart','your-td'); ?>">
                    <img src="<?php echo esc_url( get_template_directory_uri().'/assets/images/cart_icon.svg' ); ?>" alt="">
                    <span>В корзину</span>
                </a>
            <?php else: ?>
                <!-- VARIABLE yoki not-purchasable: product sahifasiga olib boramiz (variant tanlash uchun) -->
                <a class="cart_btn"
                   data-product_id="<?php echo esc_attr( $is_var ? $product->get_parent_id() : $product->get_id() ); ?>"
                   data-product_type="<?php echo esc_attr( $is_var ? 'variable' : $product->get_type() ); ?>"
                   data-product_url="<?php echo esc_url($permalink); ?>">
                    <img src="<?php echo esc_url( get_template_directory_uri().'/assets/images/cart_icon.svg' ); ?>" alt="">
                    <span>В корзину</span>
                </a>
            <?php endif; ?>

            <input type="hidden" name="add-to-cart"
                   value="<?php echo esc_attr( $is_var ? $product->get_parent_id() : $product->get_id() ); ?>">
        </div>
    </div>
</div>
