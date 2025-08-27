<?php
defined('ABSPATH') || exit;

$cart = WC()->cart;

// Bo'sh savat holati
do_action('woocommerce_before_cart');
if ( $cart->is_empty() ) {
    wc_get_template('cart/cart-empty.php');
    do_action('woocommerce_after_cart');
    return;
}

// Umumiy hisob-kitoblar
$total_items = $cart->get_cart_contents_count();
$total_raw   = (float) $cart->get_total('edit');
$total_html  = wc_price( $total_raw );

// Shop/Checkout URL’lar
$shop_url     = wc_get_page_permalink('shop');
$checkout_url = wc_get_checkout_url();
?>

<!-- TOP BAR -->
<div class="top">
    <div class="info">
        <div class="section_title">Корзина</div>
    </div>
    <a href="#" class="download js-clear-cart">Очистить корзину</a>
</div>

<!-- ITEMS -->
<div class="product_card_row" id="cart_items">
    <?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ):
        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) continue;

        $product_id = $_product->get_id();
        $qty        = (int) $cart_item['quantity'];

        // Permalink
        $permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

        // Rasm
        $thumb_html = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key );

        // Nom
        $title = $_product->get_name();

        // SKU
        $sku = $_product->get_sku();
        if ($sku === '') $sku = '—';

        // Rang (Цвет) — avval variation’dan, bo‘lmasa product atributidan
        $color = '';
        if ( $_product->is_type('variation') && !empty($cart_item['variation']) ) {
            foreach ($cart_item['variation'] as $vk => $vv) {
                if (strpos($vk, 'attribute_pa_color') !== false) {
                    $term = get_term_by('slug', $vv, 'pa_color');
                    $color = $term ? $term->name : $vv;
                }
            }
        }
        if ($color === '') {
            $color = trim( wp_strip_all_tags( $_product->get_attribute('pa_color') ) );
        }
        if ($color === '') $color = '—';

        // Line subtotal (narx yo‘q bo‘lsa — "По запросу")
        $line_subtotal_html = $cart->get_product_subtotal( $_product, $qty );
        if ( $_product->get_price() === '' || $_product->get_price() === null ) {
            $line_subtotal_html = '<span class="price-request">По запросу</span>';
        }
        ?>
        <!-- product card -->
        <div class="product_card_item"
             data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>"
             data-product_id="<?php echo esc_attr($product_id); ?>">

            <div class="top_buttons">
                <div class="like_icon" data-product_id="<?php echo esc_attr( $product_id ); ?>"></div>
                <div class="delete_icon js-remove-item" role="button" aria-label="<?php esc_attr_e('Удалить', 'your-td'); ?>"></div>
            </div>

            <a href="<?php echo esc_url($permalink ?: '#'); ?>" class="left">
                <?php echo $thumb_html; ?>
            </a>

            <div class="right">
                <div class="info_row">
                    <a href="<?php echo esc_url($permalink ?: '#'); ?>" class="title"><?php echo esc_html($title); ?></a>

                    <div class="characteristics">
                        <!-- Артикул -->
                        <div class="item">
                            <div class="name">Артикул</div>
                            <div class="value"><?php echo esc_html($sku); ?></div>
                        </div>

                        <!-- Цвет -->
                        <div class="item">
                            <div class="name">Цвет</div>
                            <div class="value"><?php echo esc_html($color); ?></div>
                        </div>

                        <!-- Кол-во -->
                        <div class="item">
                            <div class="name">Кол-во:</div>
                            <div class="cart_controls">
                                <div class="qty_btn minus" data-step="-1">-</div>
                                <input type="number" class="qty js-qty"
                                       min="1" max="1000"
                                       value="<?php echo esc_attr($qty); ?>">
                                <div class="qty_btn plus" data-step="1">+</div>
                            </div>
                        </div>

                        <!-- Стоимость -->
                        <div class="item">
                            <div class="name">Стоимость:</div>
                            <div class="value js-line-subtotal"><?php echo wp_kses_post($line_subtotal_html); ?></div>
                        </div>
                    </div>
                </div>

                <div class="button"><!-- bo'sh, dizayn joyi --></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- TOTALS -->
<div class="total_row" id="cart_totals_row">
    <div class="info_row">
        <div class="count">
            <span>Итого:</span>
            <span class="js-total-items"><?php echo (int)$total_items; ?></span> шт
        </div>
        <div class="count">
            <span>Итоговая стоимость:</span>
            <span class="js-total-price"><?php echo wp_kses_post($total_html); ?></span>
        </div>
    </div>

    <div class="right">
        <a href="<?php echo esc_url( $shop_url ); ?>" class="back_link">
            <img src="<?php echo esc_url( get_template_directory_uri().'/assets/images/navigation_item_arrow.svg' ); ?>" alt="">
            <span>Вернуться в каталог</span>
        </a>

        <a href="<?php echo esc_url( $checkout_url ); ?>" class="button">
            К оформлению
        </a>

        <a href="<?php echo esc_url( $shop_url ); ?>" class="button hidden">
            Вернуться в каталог
        </a>
    </div>
</div>

<?php do_action('woocommerce_after_cart'); ?>

<!-- CART AJAX JS -->
<script>
    (function(){
        const AJAX = {
            url: '<?php echo esc_js( admin_url('admin-ajax.php') ); ?>',
            nonce: '<?php echo esc_js( wp_create_nonce('galeon_cart_nonce') ); ?>'
        };

        // Helper: Swal toast
        function toast(type, title){
            if (!window.Swal) return;
            Swal.fire({icon:type, title, showConfirmButton:false, timer:1000});
        }

        // Helper: header basket counter – Woo fragments refresh
        function refreshFragments() {
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).trigger('wc_fragment_refresh');
            }
        }

        // UI: totals va item subtotal yangilash
        function updateTotalsUI(payload){
            try {
                if (payload.total_items !== undefined) {
                    const t = document.querySelector('.js-total-items');
                    if (t) t.textContent = String(payload.total_items);
                }
                if (payload.total_html) {
                    const p = document.querySelector('.js-total-price');
                    if (p) p.innerHTML = payload.total_html;
                }
            } catch(e){}
        }

        function updateLineSubtotalUI(itemEl, line_html){
            const el = itemEl.querySelector('.js-line-subtotal');
            if (el && line_html) el.innerHTML = line_html;
        }

        // AJAX: qty yangilash
        async function setQty(cartKey, qty) {
            const fd = new FormData();
            fd.append('action', 'galeon_cart_update_qty');
            fd.append('nonce', AJAX.nonce);
            fd.append('cart_item_key', cartKey);
            fd.append('quantity', String(qty));

            const res  = await fetch(AJAX.url, { method:'POST', credentials:'same-origin', body:fd });
            const json = await res.json().catch(()=>null);
            if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Update failed');
            return json.data; // {total_items,total_html,line_subtotal_html,removed}
        }

        // AJAX: item o‘chirish
        async function removeItem(cartKey){
            const fd = new FormData();
            fd.append('action', 'galeon_cart_remove_item');
            fd.append('nonce', AJAX.nonce);
            fd.append('cart_item_key', cartKey);

            const res  = await fetch(AJAX.url, { method:'POST', credentials:'same-origin', body:fd });
            const json = await res.json().catch(()=>null);
            if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Remove failed');
            return json.data; // {total_items,total_html}
        }

        // AJAX: savatni tozalash
        async function clearCart(){
            const fd = new FormData();
            fd.append('action', 'galeon_cart_clear');
            fd.append('nonce', AJAX.nonce);

            const res  = await fetch(AJAX.url, { method:'POST', credentials:'same-origin', body:fd });
            const json = await res.json().catch(()=>null);
            if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Clear failed');
            return json.data; // {total_items,total_html,empty:true}
        }

        // Plus/Minus click
        document.addEventListener('click', async function(e){
            const btn = e.target.closest('.qty_btn');
            if (!btn) return;

            const item = btn.closest('.product_card_item');
            const input = item ? item.querySelector('.js-qty') : null;
            if (!item || !input) return;

            const step = parseInt(btn.dataset.step || '0', 10);
            let val = parseInt(input.value || '1', 10);
            val = isNaN(val) ? 1 : val + step;
            if (val < 1) val = 1;
            if (val > 1000) val = 1000;
            input.value = String(val);

            const key = item.dataset.cart_item_key;
            try {
                const data = await setQty(key, val);
                if (data.removed) {
                    // qty 0 bo'lgan — item DOMdan ketadi
                    item.remove();
                } else {
                    updateLineSubtotalUI(item, data.line_subtotal_html);
                }
                updateTotalsUI(data);
                refreshFragments();
                toast('success','Обновлено');
                // Agar savat bo'sh bo'lib qolsa — bo'sh templatega refresh:
                if (data.total_items === 0) window.location.reload();
            } catch(err) {
                toast('error','Не удалось обновить количество');
            }
        });

        // Qty input manual change (debounced)
        let qtyTimer;
        document.addEventListener('input', function(e){
            const input = e.target.closest('.js-qty');
            if (!input) return;

            let v = parseInt(input.value || '1', 10);
            if (isNaN(v) || v < 1) v = 1;
            if (v > 1000) v = 1000;
            input.value = String(v);

            clearTimeout(qtyTimer);
            qtyTimer = setTimeout(async ()=>{
                const item = input.closest('.product_card_item');
                if (!item) return;
                const key = item.dataset.cart_item_key;
                try {
                    const data = await setQty(key, v);
                    if (data.removed) {
                        item.remove();
                    } else {
                        updateLineSubtotalUI(item, data.line_subtotal_html);
                    }
                    updateTotalsUI(data);
                    refreshFragments();
                    if (data.total_items === 0) window.location.reload();
                } catch(err){
                    toast('error','Не удалось обновить количество');
                }
            }, 350);
        });

        // Delete icon
        document.addEventListener('click', async function(e){
            const del = e.target.closest('.js-remove-item');
            if (!del) return;
            const item = del.closest('.product_card_item');
            if (!item) return;
            const key = item.dataset.cart_item_key;

            try {
                const data = await removeItem(key);
                item.remove();
                updateTotalsUI(data);
                refreshFragments();
                toast('success','Удалено из корзины');
                if (data.total_items === 0) window.location.reload();
            } catch(err){
                toast('error','Не удалось удалить товар');
            }
        });

        // Clear cart
        document.addEventListener('click', async function(e){
            const clearBtn = e.target.closest('.js-clear-cart');
            if (!clearBtn) return;
            e.preventDefault();
            try {
                const data = await clearCart();
                refreshFragments();
                toast('success','Корзина очищена');
                window.location.reload(); // bo'sh layoutni ko'rsatamiz
            } catch(err){
                toast('error','Не удалось очистить корзину');
            }
        });

    })();
</script>
