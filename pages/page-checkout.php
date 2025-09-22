<?php
/**
 * Template Name: Checkout
 */
defined('ABSPATH') || exit;
get_header();

if (!function_exists('WC')) {
    wp_die('WooCommerce required');
}
$checkout = WC()->checkout();

// Delivery select uchun default qiymat (reload bo'lsa POST dan olamiz)
$delivery_default = isset($_POST['delivery_method'])
    ? sanitize_text_field($_POST['delivery_method'])
    : 'manager';

// Bizning "nopay" gateway ID (quyida functions.php da ro'yxatdan o'tkazamiz)
$nopay_id = 'nopay';

/** Agar bu "order received" bo'lsa, formni ko'rsatmaymiz (swal chiqadi) */
$is_received = ( isset($_GET['order_ok']) && $_GET['order_ok'] == '1' );

// Xatolar/success notice'lar (coupon bannerini chaqirmaslik uchun faqat notices)
if (function_exists('wc_print_notices')) wc_print_notices();

// Default payment gateway (hidden input uchun)
$pgws = WC()->payment_gateways();
$available = $pgws ? $pgws->get_available_payment_gateways() : [];
$default_gateway_id = $pgws && method_exists($pgws, 'get_default_payment_gateway') && $pgws->get_default_payment_gateway()
    ? $pgws->get_default_payment_gateway()->get_id()
    : ($available ? array_key_first($available) : '');

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    get_footer();
    return;
}

$cart = WC()->cart;
?>


    <div class="container">
        <?php if (function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb('<div class="breadcrumb">', '</div>');
        } ?>
    </div>

    <style>
        .visually-hidden {
            display: none;
        }
    </style>
    <!-- buy -->
    <section class="buy">
        <div class="container">
            <div class="section_title">
                Оформление заказа
            </div>
            <div class="sub_title">Пожалуйста, заполните простую форму и мы свяжемся с вами в ближайшее время</div>

            <?php if ( isset($_GET['order_ok']) && $_GET['order_ok'] == '1' ) : ?>
                <script>
                    (function(){
                        function showAlert(){
                            if (window.Swal && typeof Swal.fire === 'function') {
                                // SweetAlert2
                                Swal.fire({
                                    title: 'Спасибо!',
                                    text: 'Ваш заказ отправлен.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                            } else if (typeof window.swal === 'function') {
                                // SweetAlert (v1)
                                window.swal('Спасибо!', 'Ваш заказ отправлен.', 'success');
                            } else {
                                console.log('SweetAlert не загружен на этой странице');
                            }

                            // URL dan ?order_ok & oid ni tozalash (reloadda yana chiqmasin)
                            try {
                                var url = new URL(window.location.href);
                                url.searchParams.delete('order_ok');
                                url.searchParams.delete('oid');
                                var q = url.searchParams.toString();
                                history.replaceState({}, '', url.pathname + (q ? '?' + q : '') + url.hash);
                            } catch(e){}
                        }

                        if (document.readyState === 'complete' || document.readyState === 'interactive') {
                            setTimeout(showAlert, 0);
                        } else {
                            document.addEventListener('DOMContentLoaded', showAlert);
                        }
                    })();
                </script>
            <?php else : ?>

            <!-- >>> Sizning form HTML – faqat name= qo'yildi va action/metod -->
            <form method="post" action="<?php echo esc_url(wc_get_checkout_url()); ?>">

                <div class="input_row">
                    <input required type="text" name="billing_first_name" placeholder="Имя*"
                           value="<?php echo esc_attr($checkout->get_value('billing_first_name')); ?>">

                    <input type="email"
                           name="billing_email"
                           placeholder="E-mail*"
                           value="<?php echo esc_attr($checkout->get_value('billing_email')); ?>"
                           autocomplete="email" required>

                    <input required type="tel" id="phone" name="billing_phone" placeholder="Телефон*"
                           value="<?php echo esc_attr($checkout->get_value('billing_phone')); ?>">

                    <select id="delivery_method" name="delivery_method">
                        <option value="manager" <?php selected($delivery_default, 'manager'); ?>>По согласованию с
                            менеджером
                        </option>
                        <option value="courier" <?php selected($delivery_default, 'courier'); ?>>Доставка курьером
                        </option>
                        <option value="pickup" <?php selected($delivery_default, 'pickup'); ?>>Самовывоз</option>
                    </select>
                </div>

                <!-- Поля для доставки -->
                <div id="courier_fields" class="courier_fields"
                     style="<?php echo($delivery_default === 'courier' ? '' : 'display:none'); ?>">
                    <input type="text" id="city" name="shipping_city" placeholder="Город*"
                           value="<?php echo esc_attr($checkout->get_value('shipping_city')); ?>">
                    <input type="text" id="street" name="shipping_address_1" placeholder="Улица и дом*"
                           value="<?php echo esc_attr($checkout->get_value('shipping_address_1')); ?>">
                    <input type="text" name="delivery_building" placeholder="Корпус"
                           value="<?php echo isset($_POST['delivery_building']) ? esc_attr($_POST['delivery_building']) : ''; ?>">
                    <input type="text" name="delivery_apartment" placeholder="Квартира"
                           value="<?php echo isset($_POST['delivery_apartment']) ? esc_attr($_POST['delivery_apartment']) : ''; ?>">
                    <input type="text" name="delivery_entrance" placeholder="Подъезд"
                           value="<?php echo isset($_POST['delivery_entrance']) ? esc_attr($_POST['delivery_entrance']) : ''; ?>">
                    <input type="text" name="delivery_floor" placeholder="Этаж"
                           value="<?php echo isset($_POST['delivery_floor']) ? esc_attr($_POST['delivery_floor']) : ''; ?>">
                    <textarea name="order_comments" placeholder="Комментарий к доставке"><?php
                        echo esc_textarea($checkout->get_value('order_comments'));
                        ?></textarea>
                </div>

                <!-- Инфо о самовывозе -->
                <div id="pickup_info" class="pickup_info"
                     style="<?php echo($delivery_default === 'pickup' ? '' : 'display:none'); ?>">
                    <p>
                        Адрес склада: <b>Электродная улица, 13с2А</b>.<br>
                        Перед самовывозом необходимо согласовать получение с менеджером.
                    </p>
                </div>

                <div class="bottom">
                    <button class="button">Оставить заявку</button>
                    <div class="text">Нажимая на кнопку «Отправить», вы даете согласие на обработку своих персональных
                        данных
                    </div>
                </div>

                <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
                <input type="hidden" name="woocommerce_checkout_place_order" value="1">
                <input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>">

                <!-- To'lov kerak emas: bizning "nopay" gateway ishlaydi -->
                <input type="hidden" name="payment_method" value="<?php echo esc_attr($nopay_id); ?>">

                <?php
                // Agar Terms majburiy bo'lsa, server xato bermasin
                if (wc_terms_and_conditions_checkbox_enabled()) {
                    echo '<input type="hidden" name="terms" value="on">';
                }
                ?>

            </form>
        </div>
    </section>

    <!-- product card -->
    <section class="product_card cart">

        <div class="container">

            <div class="top">
                <div class="info">
                    <div class="section_title">Состав заказа</div>
                </div>
            </div>

            <!-- ITEMS -->
            <div class="product_card_row" id="cart_items">
                <?php foreach ($cart->get_cart() as $cart_item_key => $cart_item):
                    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0) continue;

                    $product_id = $_product->get_id();
                    $qty = (int)$cart_item['quantity'];

                    // Permalink
                    $permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);

                    // Rasm
                    $thumb_html = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);

                    // Nom
                    $title = $_product->get_name();

                    // SKU
                    $sku = $_product->get_sku();
                    if ($sku === '') $sku = '—';

                    // Rang (Цвет) — avval variation’dan, bo‘lmasa product atributidan
                    $color = '';
                    if ($_product->is_type('variation') && !empty($cart_item['variation'])) {
                        foreach ($cart_item['variation'] as $vk => $vv) {
                            if (strpos($vk, 'attribute_pa_color') !== false) {
                                $term = get_term_by('slug', $vv, 'pa_color');
                                $color = $term ? $term->name : $vv;
                            }
                        }
                    }
                    if ($color === '') {
                        $color = trim(wp_strip_all_tags($_product->get_attribute('pa_color')));
                    }
                    if ($color === '') $color = '—';

                    // Line subtotal (narx yo‘q bo‘lsa — "По запросу")
                    $line_subtotal_html = $cart->get_product_subtotal($_product, $qty);
                    if ($_product->get_price() === '' || $_product->get_price() === null) {
                        $line_subtotal_html = '<span class="price-request">По запросу</span>';
                    }
                    ?>
                    <!-- product card -->
                    <div class="product_card_item"
                         data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>"
                         data-product_id="<?php echo esc_attr($product_id); ?>">

                        <div class="top_buttons">
                            <div class="like_icon" data-product_id="<?php echo esc_attr($product_id); ?>"></div>
                            <div class="delete_icon js-remove-item" role="button"
                                 aria-label="<?php esc_attr_e('Удалить', 'your-td'); ?>"></div>
                        </div>

                        <a href="<?php echo esc_url($permalink ?: '#'); ?>" class="left">
                            <?php echo $thumb_html; ?>
                        </a>

                        <div class="right">
                            <div class="info_row">
                                <a href="<?php echo esc_url($permalink ?: '#'); ?>"
                                   class="title"><?php echo esc_html($title); ?></a>

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
        </div>
    </section>


    <!-- CART AJAX JS -->
    <script>
        (function () {
            const AJAX = {
                url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                nonce: '<?php echo esc_js(wp_create_nonce('galeon_cart_nonce')); ?>'
            };

            // Helper: Swal toast
            function toast(type, title) {
                if (!window.Swal) return;
                Swal.fire({icon: type, title, showConfirmButton: false, timer: 1000});
            }

            // Helper: header basket counter – Woo fragments refresh
            function refreshFragments() {
                if (typeof jQuery !== 'undefined') {
                    jQuery(document.body).trigger('wc_fragment_refresh');
                }
            }

            // UI: totals va item subtotal yangilash
            function updateTotalsUI(payload) {
                try {
                    if (payload.total_items !== undefined) {
                        const t = document.querySelector('.js-total-items');
                        if (t) t.textContent = String(payload.total_items);
                    }
                    if (payload.total_html) {
                        const p = document.querySelector('.js-total-price');
                        if (p) p.innerHTML = payload.total_html;
                    }
                } catch (e) {
                }
            }

            function updateLineSubtotalUI(itemEl, line_html) {
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

                const res = await fetch(AJAX.url, {method: 'POST', credentials: 'same-origin', body: fd});
                const json = await res.json().catch(() => null);
                if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Update failed');
                return json.data; // {total_items,total_html,line_subtotal_html,removed}
            }

            // AJAX: item o‘chirish
            async function removeItem(cartKey) {
                const fd = new FormData();
                fd.append('action', 'galeon_cart_remove_item');
                fd.append('nonce', AJAX.nonce);
                fd.append('cart_item_key', cartKey);

                const res = await fetch(AJAX.url, {method: 'POST', credentials: 'same-origin', body: fd});
                const json = await res.json().catch(() => null);
                if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Remove failed');
                return json.data; // {total_items,total_html}
            }

            // AJAX: savatni tozalash
            async function clearCart() {
                const fd = new FormData();
                fd.append('action', 'galeon_cart_clear');
                fd.append('nonce', AJAX.nonce);

                const res = await fetch(AJAX.url, {method: 'POST', credentials: 'same-origin', body: fd});
                const json = await res.json().catch(() => null);
                if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Clear failed');
                return json.data; // {total_items,total_html,empty:true}
            }

            // Plus/Minus click
            document.addEventListener('click', async function (e) {
                const btn = e.target.closest('.product_card.cart .qty_btn');
                if (!btn) return;

                e.preventDefault();
                e.stopPropagation();
                if (e.stopImmediatePropagation) e.stopImmediatePropagation();

                const item = btn.closest('.product_card_item');
                const input = item ? item.querySelector('.js-qty') : null;
                if (!item || !input) return;

                const isPlus = btn.classList.contains('plus');
                const delta = isPlus ? 1 : -1;

                let val = parseInt(input.value || '1', 10);
                if (isNaN(val)) val = 1;
                val = Math.max(1, Math.min(1000, val + delta));
                input.value = String(val);

                const key = item.dataset.cart_item_key;
                try {
                    const data = await setQty(key, val); // sizdagi setQty()
                    if (data.removed) {
                        item.remove();
                    } else {
                        updateLineSubtotalUI(item, data.line_subtotal_html);
                    }
                    updateTotalsUI(data);
                    refreshFragments();
                    if (data.total_items === 0) window.location.reload();
                } catch (err) {
                    toast('error', 'Не удалось обновить количество');
                }
            }, true); // capturing — boshqa handlerlar qo'shilib ketmasin

            // Qty input manual change (debounced)
            let qtyTimer;
            document.addEventListener('input', function (e) {
                const input = e.target.closest('.js-qty');
                if (!input) return;

                let v = parseInt(input.value || '1', 10);
                if (isNaN(v) || v < 1) v = 1;
                if (v > 1000) v = 1000;
                input.value = String(v);

                clearTimeout(qtyTimer);
                qtyTimer = setTimeout(async () => {
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
                    } catch (err) {
                        toast('error', 'Не удалось обновить количество');
                    }
                }, 350);
            });

            // Delete icon
            document.addEventListener('click', async function (e) {
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
                    toast('success', 'Удалено из корзины');
                    if (data.total_items === 0) window.location.reload();
                } catch (err) {
                    toast('error', 'Не удалось удалить товар');
                }
            });

            // Clear cart
            document.addEventListener('click', async function (e) {
                const clearBtn = e.target.closest('.js-clear-cart');
                if (!clearBtn) return;
                e.preventDefault();
                try {
                    const data = await clearCart();
                    refreshFragments();
                    toast('success', 'Корзина очищена');
                    window.location.reload(); // bo'sh layoutni ko'rsatamiz
                } catch (err) {
                    toast('error', 'Не удалось очистить корзину');
                }
            });

        })();
    </script>

<?php endif; // end: is_order_received_page ?>

<?php
do_action('woocommerce_after_checkout_form', $checkout);
get_footer();
