<?php wp_footer(); ?>

<?php
$shop_url = wc_get_page_permalink('shop');
$capabilities_url = get_post_type_archive_link('tool');

$office_address = get_field('office_address', 'option');
$working_hours = get_field('working_hours', 'option');
$footer_logo = get_field('footer_logo', 'option');
$footer_title = get_field('footer_title', 'option');
$email = get_field('email', 'option');
$copyright = get_field('copyright', 'option');
?>

<!-- footer -->
<footer id="contact">
    <div class="container">
        <div class="footer_row">
            <div class="top">
                <div class="logo">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo esc_url($footer_logo['url']) ?>" alt="">
                    </a>
                    <div class="logo_text">
                        <?php echo esc_html($footer_title); ?>
                    </div>
                </div>

                <div class="nav_row">
                    <div class="nav_item">
                        <div class="title">
                            Каталог
                        </div>
                        <?php
                        if (class_exists('WooCommerce')) {
                            $parents = get_terms([
                                'taxonomy' => 'product_cat',
                                'parent' => 0,
                                'hide_empty' => false,
                                'orderby' => 'menu_order',
                                'order' => 'ASC',
                            ]);

                            if (!is_wp_error($parents) && !empty($parents)) {
                                foreach ($parents as $cat) {

                                    if ($cat->slug === 'uncategorized' || strtolower($cat->name) === 'uncategorized') {
                                        continue;
                                    }

                                    $thumb_id = (int)get_term_meta($cat->term_id, 'thumbnail_id', true);
                                    $img_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
                                    $bg_style = $img_url ? ' style="background-image:url(' . esc_url($img_url) . ')"' : '';

                                    $children = get_terms([
                                        'taxonomy' => 'product_cat',
                                        'parent' => $cat->term_id,
                                        'hide_empty' => false,
                                        'orderby' => 'menu_order',
                                        'order' => 'ASC',
                                    ]);

                                    if (!is_wp_error($children) && !empty($children)) {
                                        $children = array_values(array_filter($children, function ($t) {
                                            return $t->slug !== 'uncategorized' && strtolower($t->name) !== 'uncategorized';
                                        }));
                                    }

                                    // Parent linki
                                    $cat_link = get_term_link($cat);
                                    if ( is_wp_error($cat_link) ) {
                                        $cat_link = '';
                                    }

                                    if (!empty($children)) { ?>
                                        <!-- sub-kategoriyalari bor -->
                                        <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat->name); ?></a>

                                        <!--                                        <ul>-->
                                        <!--                                            --><?php //foreach ($children as $child): ?>
                                        <!--                                                <li>-->
                                        <!--                                                    <a href="--><?php //echo esc_url($shop_url); ?><!--">--><?php //echo esc_html($child->name); ?><!--</a>-->
                                        <!--                                                </li>-->
                                        <!--                                            --><?php //endforeach; ?>
                                        <!--                                        </ul>-->
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat->name); ?></a>
                                    <?php }
                                }
                            }

                        } else {
                            echo '<!-- WooCommerce off: product_cat mavjud emas -->';
                        }
                        ?>
                    </div>

                    <div class="nav_item">
                        <div class="title">
                            Разделы
                        </div>
                        <a href="<?php echo home_url(); ?>">Главная</a>
                        <a href="/production">Информация</a>
                        <a href="/tool">Производство</a>
                        <a href="/contact">Контакты</a>
                        <a href="/kejsy-dlya-noutbukov">Кейсы для ноутбуков</a>
                        <a href="/kontejnery">Контейнеры</a>
                    </div>

                    <div class="nav_item">
                        <div class="title">
                            Контакты
                        </div>
                        <span><?php echo esc_html($office_address['text']) ?></span>
                        <div class="nav_item_block">
                            <a class="link" href="tel:<?php echo esc_html($office_address['phone']['phone']) ?>"><?php echo esc_html($office_address['phone']['title']) ?></a>
                            <span><?php esc_html($working_hours) ?></span>
                        </div>
                        <a class="link" href="mailto:<?php echo esc_html($email); ?>"><?php echo esc_html($email); ?></a>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="info">
                    <div class="rights text"><?php echo esc_html($copyright); ?></div>
                    <a href="/privacy-policy"
                       target="_blank" class="politics text">Политика конфедициальности</a>
                </div>
                <a href="#header" class="up_link">Наверх</a>
            </div>
        </div>
    </div>
</footer>

</div>

<!-- Cookie Modal -->
<div id="cookieModal">
    <div class="container">
        <div class="blog">
            <div class="title">Мы используем  <span>cookie-файлы</span> для улучшения работы сайта</div>
            <p>Используя этот сайт, вы даете согласие на обработку <a href="#">персональных данных</a></p>

            <div class="accept_wrapper">
                <a id="acceptCookies" class="accept_btn">Согласиться</a>
            </div>
        </div>
    </div>
</div>

<!-- application modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modalBox">
        <button class="close-btn" id="closeModal"><img
                    src="<?php echo get_template_directory_uri() ?>/assets/images/modal_close_icon.svg" alt=""></button>

        <div class="section_title">Оставить заявку</div>
        <div class="sub_title">
            Заполните форму, мы свяжемся и проконсультируем Вас в кратчайшие сроки
        </div>
        <?php
        echo do_shortcode('[contact-form-7 id="5c24369" title="Оставить заявку"]');
        ?>
    </div>
</div>

<!-- search modal -->
<div class="modal-overlay1" id="modalOverlay1">
    <div class="modal" id="modalBox1">
        <button class="close-btn" id="closeModal1"><img
                    src="<?php echo get_template_directory_uri() ?>/assets/images/modal_close_icon.svg" alt=""></button>

        <form class="header_search">
            <button class="search_link" type="button"></button>
            <input type="text" name="search" placeholder="Поиск по сайту...">
            <!-- Suggestion dropdown -->
            <div class="search_suggestions">
                <ul>

                </ul>
            </div>
        </form>
    </div>
</div>


<!-- login/register modal -->
<div class="modal-overlay-profile" id="modal1">
    <div class="modal" id="modalBox2">
        <button class="close-btn" id="profileModalClose"><img
                    src="<?php echo get_template_directory_uri() ?>/assets/images/modal_close_icon.svg" alt=""></button>

        <div class="login_blog tab_blog active">
            <div class="section_title">Вход в личный кабинет</div>
            <div class="sub_title">
                Введите ваш логин и пароль для входа
            </div>
        </div>

        <div class="register_blog tab_blog">
            <div class="section_title">Регистрация</div>
            <div class="sub_title">
                Пожалуйста, заполните необходимые поля
            </div>
        </div>

        <div class="tab_buttons">
            <div class="tab_item login active" onclick="openTab('login',this)">Войти</div>
            <div class="tab_item register" onclick="openTab('register',this)">Регистрация</div>
        </div>

        <form action="" class="form-register form_item" id="form-register">
            <input type="text" required placeholder="Имя">
            <input type="email" required placeholder="E-mail">

            <div class="password-wrapper">
                <input required type="password" placeholder="Новый пароль" class="password-input">
                <span class="material-icons toggle-password">visibility_off</span>
            </div>

            <div class="password-wrapper">
                <input required type="password" placeholder="Повторите пароль" class="password-input">
                <span class="material-icons toggle-password">visibility_off</span>
            </div>

            <button>Зарегистрироваться</button>

            <div class="message">Уже есть аккаунт? <span onclick="openTab('login',this)"> Войти</span></div>

            <!-- custom confirm -->
            <div class="confirm">
                <label class="custom-checkbox">
                    <input required checked type="checkbox" id="confirmprofile">
                    <span class="checkmark"></span>
                </label>
                <label for="confirmprofile" class="text">Нажимая на кнопку «Отправить», вы даете согласие на обработку
                    своих <a href="assets/documents/Personal_Data_Processing_Extended.pdf" target="_blank">персональных
                        данных</a></label>
            </div>
        </form>
        <script>
            (function registerConsentGuard(){
                'use strict';

                const registerForm = document.getElementById('form-register');
                if (!registerForm) return;

                const registerCheckbox = document.getElementById('confirmprofile');
                const confirmContainer = registerForm.querySelector('.confirm');

                // Xato bloki: doim .confirm dan KEYIN joylashadi
                let registerError = document.getElementById('register-consent-error');
                if (!registerError) {
                    registerError = document.createElement('div');
                    registerError.id = 'register-consent-error';
                    registerError.className = 'register-consent-error';
                    registerError.setAttribute('role', 'alert');
                    registerError.textContent = 'Подтвердите согласие на обработку персональных данных';
                    registerError.style.cssText = 'color:#e53935;font-size:12px;margin-top:8px;display:none;';
                }
                if (confirmContainer) {
                    confirmContainer.insertAdjacentElement('afterend', registerError);
                } else {
                    // fallback: form oxiriga qo‘yib turamiz
                    registerForm.appendChild(registerError);
                }

                function showRegisterError(show) {
                    registerError.style.display = show ? '' : 'none';
                    registerCheckbox?.setCustomValidity(show ? 'Требуется согласие' : '');
                }

                registerCheckbox?.addEventListener('change', () => {
                    showRegisterError(!registerCheckbox.checked);
                });

                registerForm.addEventListener('submit', (e) => {
                    if (!registerCheckbox?.checked) {
                        e.preventDefault();
                        showRegisterError(true);
                        registerCheckbox?.focus();
                        registerCheckbox?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else {
                        showRegisterError(false);
                    }
                });
            })();
        </script>



        <form action="" class="form-login form_item active" id="form-login">
            <input required type="email" placeholder="E-mail">

            <div class="password-wrapper">
                <input required type="password" placeholder="Пароль" class="password-input">
                <span class="material-icons toggle-password">visibility_off</span>
            </div>

            <button>Войти</button>

            <div class="message">Забыли пароль? <span class="js-open-forgot">Восстановить</span></div>
        </form>
    </div>
</div>


<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa"
        crossorigin="anonymous"></script>
<!-- Juqery -->
<script src="<?php echo get_template_directory_uri() ?>/assets/libs/jquery-3.6.0.min.js"></script>
<!-- Load Inputmask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"></script>
<!-- yandex JS -->
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<!-- swiper -->
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<!-- sweet alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- JS -->
<!--<script src="--><?php //echo get_template_directory_uri() ?><!--/assets/js/scripts.js"></script>-->

<!-- awesome plate -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.5/awesomplete.min.js"></script>


<!-- Add to cart and like js -->
<script>
    (function () {
        /* =========================
         *  A) Woo standart tugma: qty ni oldindan data-quantity ga yozamiz
         * ========================= */
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.add_to_cart_button');
            if (!btn) return;
            const scope =
                btn.closest('.cart_controls') ||
                btn.closest('.product_card_item') ||
                btn.closest('.catalog_item') ||
                document;

            const qtyEl = scope.querySelector('.qty') || document.querySelector('.qty');
            const qty = Math.max(1, parseInt(qtyEl?.value || '1', 10));
            btn.setAttribute('data-quantity', String(qty));
        }, true); // capturing=true → Woo handleridan oldin ishlaydi

        /* =========================
         *  Woo success: Swal va "View cart" linkini olib tashlash
         * ========================= */
        if (typeof jQuery !== 'undefined') {
            jQuery(function ($) {
                $(document.body).on('added_to_cart', function (event, fragments, cart_hash, $button) {
                    if ($button && $button.length) {
                        $button.removeClass('added');
                        $button.siblings('.added_to_cart').remove(); // "View cart" linkini yo'q qilamiz
                    }
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Товар добавлен в корзину!',
                            showConfirmButton: false,
                            timer: 1000
                        });
                    }
                });
            });
        }

        /* =========================
         *  B) Oddiy .cart_btn (single page’da variable/grupped/external bo'lishi mumkin)
         * ========================= */
        document.addEventListener('click', async function (e) {
            const btn = e.target.closest('.cart_btn');
            // Agar Woo'ning standart tugmasi bo'lsa (A bo'lim), bu bo'limni o'tkazib yuboramiz
            if (!btn || btn.classList.contains('add_to_cart_button')) return;

            e.preventDefault();

            const scope =
                btn.closest('.cart_controls') ||
                btn.closest('.product_card_item') || // wishlist kartasi
                btn.closest('.catalog_item') ||      // katalog kartasi
                document;
            const qtyEl = scope.querySelector('.qty') || document.querySelector('.qty');
            const quantity = Math.max(1, parseInt(qtyEl?.value || '1', 10));
            const pType = btn.dataset.product_type || 'simple';
            const pUrl = btn.dataset.product_url || window.location.href;

            // SIMPLE bo'lsa — AJAX
            let productId = btn.dataset.product_id || scope.querySelector('input[name="add-to-cart"]')?.value;
            if (!productId) {
                if (window.Swal) Swal.fire({icon: 'error', title: 'Ошибка', text: 'ID товара не найден.'});
                return;
            }

            // VARIABLE — variation_id va attributelarni yig'amiz
            const fd = new FormData();
            if (pType === 'variable') {
                const vForm = scope.querySelector('form.variations_form') || document.querySelector('form.variations_form');
                const varId = vForm?.querySelector('input[name="variation_id"]')?.value;
                if (!varId || varId === '0') {
                    if (btn.dataset.product_url) {
                        window.location.href = btn.dataset.product_url;
                        return;
                    }
                    if (window.Swal) Swal.fire({
                        icon: 'warning',
                        title: 'Выберите вариант',
                        showConfirmButton: false,
                        timer: 1200
                    });
                    return;
                }
                fd.append('variation_id', varId);
                (vForm ? vForm.querySelectorAll('[name^="attribute_"]') : []).forEach(el => {
                    if (el.name && el.value) fd.append(el.name, el.value);
                });
            }

            // AJAX endpoint
            let ajaxUrl = null;
            if (window.wc_add_to_cart_params?.wc_ajax_url) {
                ajaxUrl = window.wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart');
            } else if (window.wc_cart_fragments_params?.wc_ajax_url) {
                ajaxUrl = window.wc_cart_fragments_params.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart');
            } else {
                ajaxUrl = new URL('?wc-ajax=add_to_cart', window.location.origin).toString();
            }

            fd.append('product_id', productId);
            fd.append('add-to-cart', productId);
            fd.append('quantity', quantity);

            btn.classList.add('is-loading');

            try {
                const res = await fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    body: fd
                });
                const text = await res.text();
                let resp = null;
                try { resp = JSON.parse(text); } catch (e) {}

                if (!res.ok || !resp) throw new Error('Bad AJAX response');

                if (resp.error && resp.product_url) {
                    window.location.href = resp.product_url;
                    return;
                }

                if (typeof jQuery !== 'undefined' && resp.fragments) {
                    jQuery(document.body).trigger('added_to_cart', [resp.fragments, resp.cart_hash, btn]);
                } else if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Товар добавлен в корзину!',
                        showConfirmButton: false,
                        timer: 1000
                    });
                }
            } catch (err) {
                if (window.Swal) {
                    Swal.fire({icon: 'error', title: 'Ошибка', text: 'Не удалось добавить товар. Попробуйте ещё раз.'});
                }
            } finally {
                btn.classList.remove('is-loading');
            }
        });

        /* =========================================================
         *  C) WISHLIST (like) + header .wishlist-count real-time
         * ========================================================= */
        const LS_KEY      = 'wishlist_v1';
        const PENDING_KEY = 'wl_pending_merge'; // guest-da LS o‘zgarsa → 1
        const A           = window.WISHLIST || {};
        const AJAX_URL    = A.ajaxUrl || (window.ajaxurl || '/wp-admin/admin-ajax.php');
        const NONCE       = A.nonce || '';
        // Ba'zan localized flag kechikishi mumkin — cookie bilan ham tekshiramiz
        const COOKIE_LOGGED = /wordpress_logged_in_/i.test(document.cookie);
        const LOGGED     = !!A.isLoggedIn || COOKIE_LOGGED;

        // helpers
        const readLS = () => { try { return JSON.parse(localStorage.getItem(LS_KEY)) || []; } catch (e) { return []; } };
        const writeLS = (arr) => localStorage.setItem(LS_KEY, JSON.stringify(arr));
        const keyOf = (pid, vid) => `${Number(pid)}:${Number(vid || 0)}`;
        const clearGuestLS = () => {
            // guest injektsiyasini oldini olish uchun tozalaymiz
            try { localStorage.removeItem(PENDING_KEY); } catch(_) {}
            try { localStorage.setItem(LS_KEY, '[]'); } catch(_) {}
            try { document.cookie = 'wishlist_v1=; Max-Age=0; Path=/'; } catch(_) {}
        };

        function setWishlistCount(count) {
            const el = document.querySelector('.wishlist-count.header_counter');
            if (!el) return;
            el.textContent = String(count);
            el.classList.toggle('active', Number(count) > 0);
        }

        function setHeartStateFromList(items) {
            const keys = new Set((items||[]).map(it => keyOf(it.pid, it.vid)));
            document.querySelectorAll('.like_icon').forEach(el => {
                const pid = el.dataset.product_id;
                const vid = el.dataset.variation_id || 0;
                if (!pid) return;
                el.classList.toggle('active', keys.has(keyOf(pid, vid)));
            });
            setWishlistCount(keys.size);
        }

        async function serverToggle(pid, vid = 0) {
            const fd = new FormData();
            fd.append('action', 'my_wishlist_toggle');
            fd.append('nonce', NONCE);
            fd.append('pid', pid);
            fd.append('vid', vid);
            const res = await fetch(AJAX_URL, { method: 'POST', credentials: 'same-origin', body: fd, cache: 'no-store' });
            const json = await res.json().catch(() => null);
            if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Server error');
            return json.data; // {status, count}
        }

        async function serverMerge(items) {
            const fd = new FormData();
            fd.append('action', 'my_wishlist_merge');
            fd.append('nonce', NONCE);
            fd.append('items', JSON.stringify(items));
            const res = await fetch(AJAX_URL, { method: 'POST', credentials: 'same-origin', body: fd, cache: 'no-store' });
            const json = await res.json().catch(() => null);
            if (!res.ok || !json || !json.success) throw new Error('Merge failed');
            return json.data; // {count}
        }

        async function serverList() {
            const fd = new FormData();
            fd.append('action', 'my_wishlist_list');
            fd.append('nonce', NONCE);
            const res = await fetch(AJAX_URL, { method: 'POST', credentials: 'same-origin', body: fd, cache: 'no-store' });
            const json = await res.json().catch(() => null);
            if (!res.ok || !json || !json.success) return [];
            return json.data.items || [];
        }

        function lsToggle(pid, vid = 0) {
            let list = readLS();
            const k = keyOf(pid, vid);
            const idx = list.findIndex(it => keyOf(it.pid, it.vid) === k);
            let status;
            if (idx >= 0) {
                list.splice(idx, 1);
                status = 'removed';
            } else {
                list.push({ pid: Number(pid), vid: Number(vid || 0), ts: Date.now() });
                status = 'added';
            }
            writeLS(list);
            // Guest LS bor ekan — login qilganda merge qilish kerak bo‘lishi mumkin
            try {
                if (list.length) localStorage.setItem(PENDING_KEY, '1');
                else localStorage.removeItem(PENDING_KEY);
            } catch(_) {}
            setWishlistCount(list.length);
            return { status, count: list.length, list };
        }

        // Swal (throttle)
        let lastToastAt = 0;
        const toast = (type, title) => {
            if (!window.Swal) return;
            const now = Date.now();
            if (now - lastToastAt < 350) return;
            lastToastAt = now;
            Swal.fire({ icon: type, title, timer: 1000, showConfirmButton: false });
        };

        // Init: LOGIN holatda — bir martalik (flag bilan) merge, undan keyin LS-ni tozalab, faqat serverga tayanamiz
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                if (LOGGED) {
                    const ls = readLS();
                    const shouldMerge = (localStorage.getItem(PENDING_KEY) === '1') && ls.length > 0;

                    // 1) Kerak bo‘lsa — faqat bir marta merge
                    if (shouldMerge) {
                        try { await serverMerge(ls); } catch(_) {}
                    }

                    // 2) Har holda guest LS/cookie/flag ni tozalaymiz (qayta injection bo‘lmasin)
                    clearGuestLS();

                    // 3) Endi authoritative manba — server
                    const sv = await serverList();
                    setHeartStateFromList(sv);
                    // E'tibor: logged-in rejimda LS’ga server ro‘yxatini qayta yozmaymiz
                } else {
                    // Guest: faqat LS asosida
                    setHeartStateFromList(readLS());
                }
            } catch (e) { /* ignore */ }
        });

        // Cross-tab sync (guest)
        window.addEventListener('storage', (e) => {
            if (e.key === LS_KEY && !LOGGED) {
                setHeartStateFromList(readLS());
            }
        });

        // Variations: tanlanganda variation_id ni like_icon'ga yozamiz
        document.addEventListener('change', (e) => {
            const form = e.target.closest('form.variations_form');
            if (!form) return;
            const varId = form.querySelector('input[name="variation_id"]')?.value;
            document.querySelectorAll('.like_icon[data-product_type="variable"]').forEach(el => {
                if (varId && varId !== '0') el.dataset.variation_id = varId;
                else delete el.dataset.variation_id;
            });
        });

        // LIKE toggle
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.like_icon');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();

            if (btn.dataset.wlBusy === '1') return;
            btn.dataset.wlBusy = '1';

            const pid = btn.dataset.product_id;
            const ptype = btn.dataset.product_type || 'simple';
            const vid = (ptype === 'variable') ? (btn.dataset.variation_id || 0) : 0;
            if (!pid) { btn.dataset.wlBusy = '0'; return; }

            try {
                if (LOGGED) {
                    // 1) Serverda toggle
                    const data = await serverToggle(pid, vid);

                    // 2) Server authoritative → server ro‘yxatini olib UI’ni yangilaymiz
                    const sv = await serverList();
                    setHeartStateFromList(sv);

                    // 3) Har ehtimolga qarshi guest LS/cookie’ni tozalab turamiz
                    clearGuestLS();

                    const added = (data.status === 'added');
                    toast(added ? 'info' : 'warning', added ? 'Товар добавлен в избранное!' : 'Удалено из избранного');

                    // Wishlist sahifasida bo‘lsak — server ro‘yxatida yo‘q bo‘lsa kartani olib tashlash
                    const keyNow = keyOf(pid, vid);
                    const existsNow = sv.some(it => keyOf(it.pid, it.vid) === keyNow);
                    const wlList = document.getElementById('wl_list');
                    if (wlList && !existsNow) {
                        const card = btn.closest('.product_card_item');
                        if (card) card.remove();
                        if (!wlList.querySelector('.product_card_item')) {
                            wlList.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                        }
                    }
                } else {
                    // Guest: LS toggle
                    const { status, list } = lsToggle(pid, vid);
                    const added = (status === 'added');
                    btn.classList.toggle('active', added);
                    setHeartStateFromList(list);
                    toast(added ? 'info' : 'warning', added ? 'Товар добавлен в избранное!' : 'Удалено из избранного');

                    // Wishlist sahifasida bo‘lsak — unlike bo‘lsa kartani olib tashlash
                    const wlList = document.getElementById('wl_list');
                    if (wlList && !btn.classList.contains('active')) {
                        const card = btn.closest('.product_card_item');
                        if (card) card.remove();
                        if (!wlList.querySelector('.product_card_item')) {
                            wlList.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                        }
                    }
                }
            } catch (err) {
                toast('error', 'Не удалось обновить избранное');
            } finally {
                btn.dataset.wlBusy = '0';
            }
        }, true);
    })();
</script>




<!-- plus and minus -->
<!-- plus and minus (UNIVERSAL & CONFLICT-PROOF) -->
<script>
    (function () {
        // Hamma sahifa va dinamik (AJAX) kontentda ishlasin
        const clamp = (v, mn, mx) => Math.max(mn, Math.min(mx, v));

        function findQtyInput(start) {
            // .catalog_item (archive), .quantity (single), .cart_controls — hammasini qamrab olamiz
            const scope = start.closest('.cart_controls, .quantity, .product_card_item, .catalog_item') || document;
            return scope.querySelector('input.qty, input[name="quantity"][type="number"]');
        }

        function stepFor(btn, input) {
            // 1) .qty_btn data-step -> 2) input.step -> 3) default ±1
            const ds = parseInt(btn.dataset.step || btn.getAttribute('data-step') || '0', 10);
            if (!isNaN(ds) && ds !== 0) return ds;
            const st = parseInt(input.step || '1', 10);
            const base = (!isNaN(st) && st > 0) ? st : 1;
            return btn.classList.contains('plus') ? base : -base;
        }

        function setQty(input, nextVal) {
            const min = parseInt(input.min || '1', 10) || 1;
            const max = parseInt(input.max || '1000', 10) || 1000;
            const target = clamp(nextVal, min, max);
            input.value = String(target);
            // Boshqa skriptlar kuzatishi uchun
            input.dispatchEvent(new Event('input', {bubbles: true}));
            input.dispatchEvent(new Event('change', {bubbles: true}));
        }

        // Bir klikda 2 marta ishlashini oldini oladigan guard (capture+bubble holatida ham)
        function recentlyHandled(el) {
            const now = Date.now();
            const last = parseInt(el.dataset._qHandled || '0', 10);
            if (now - last < 120) return true;
            el.dataset._qHandled = String(now);
            return false;
        }

        function onQtyClick(ev) {
            const btn = ev.target.closest('.qty_btn');
            console.log('SALOM')
            if (!btn) return;

            // Faqat plus/minus bo'lsin
            if (!btn.classList.contains('plus') && !btn.classList.contains('minus')) return;

            // Dublikatni bloklaymiz (ba'zi mavzular capture+bubble’da ikkita listener qo‘yar)
            if (recentlyHandled(btn)) return;

            const input = findQtyInput(btn);
            if (!input) return;

            ev.preventDefault();
            ev.stopPropagation();

            const cur = parseInt(input.value || input.getAttribute('value') || '1', 10);
            const base = isNaN(cur) ? 1 : cur;
            const delta = stepFor(btn, input);
            setQty(input, base + delta);
        }

        // Har ehtimolga – capture HAM, bubble HAM (konflikt bo‘lsa ham ishlasin)
        document.addEventListener('click', onQtyClick, true);
        document.addEventListener('click', onQtyClick, false);

        // Manual kiritish — min/max ichida ushlab turamiz
        document.addEventListener('input', function (ev) {
            const input = ev.target.closest('input.qty, input[name="quantity"][type="number"]');
            if (!input) return;
            const min = parseInt(input.min || '1', 10) || 1;
            const max = parseInt(input.max || '1000', 10) || 1000;
            let v = parseInt(input.value || '0', 10);
            if (isNaN(v)) v = min;
            input.value = String(clamp(v, min, max));
        });
    })();
</script>



<!-- Contact form 7 checkbox -->
<script>
    // === CF7: consent checkbox (sizning avvalgi kodingiz) ===
    document.addEventListener('DOMContentLoaded', function () {
        // Submitni capture bosqichida tutamiz — CF7 AJAX'idan oldin
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.wpcf7 form');
            if (!form) return;

            const consent = form.querySelector('input[name="consent"][type="checkbox"]');
            if (!consent) return;

            if (!consent.checked) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let tip = form.querySelector('.confirm .wpcf7-not-valid-tip');
                if (!tip) {
                    tip = document.createElement('span');
                    tip.className = 'wpcf7-not-valid-tip';
                    tip.textContent = 'Чтобы отправить форму, подтвердите согласие.';
                    (form.querySelector('.confirm .text') || form.querySelector('.confirm') || form).appendChild(tip);
                }

                consent.setAttribute('aria-invalid', 'true');
                const confirmWrap = form.querySelector('.confirm');
                if (confirmWrap) confirmWrap.classList.add('is-invalid');

                try { consent.focus({ preventScroll: true }); } catch(_) {}
                confirmWrap?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, true);

        // Belgilanganda xabarni tozalash
        document.addEventListener('change', function (e) {
            if (e.target.matches('input[name="consent"][type="checkbox"]')) {
                const form = e.target.closest('form');
                e.target.removeAttribute('aria-invalid');
                const tip = form?.querySelector('.confirm .wpcf7-not-valid-tip');
                if (tip) tip.remove();
                form?.querySelector('.confirm')?.classList.remove('is-invalid');
            }
        });
    });

    // === CF7: muvaffaqiyatli yuborilganda modal yopish + SweetAlert ===
    document.addEventListener('wpcf7mailsent', function (e) {
        // e.target — bu forma elementi
        const form = e.target;

        // Agar forma modal ichida bo'lsa — yopamiz
        const modalBox    = form.closest('.modal');           // <div class="modal" id="modalBox">
        const modalOverlay= form.closest('.modal-overlay')    // <div class="modal-overlay" id="modalOverlay">
            || document.getElementById('modalOverlay');

        if (modalBox)    modalBox.classList.remove('active');
        if (modalOverlay) modalOverlay.classList.remove('active');

        // Keyin SweetAlert ko'rsatamiz
        const showSwal = () => {
            try {
                Swal.fire({
                    title: 'Спасибо!',
                    text: 'Ваша заявка отправлена. Мы свяжемся с вами в ближайшее время.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } catch (err) {
                // Agar Swal yo'q bo'lsa — hech bo'lmasa alert:
                alert('Ваша заявка отправлена. Спасибо!');
            }
        };

        // Animatsiya silliq ko'rinishi uchun kichik kechikish bilan
        if (modalBox || modalOverlay) {
            setTimeout(showSwal, 150);
        } else {
            showSwal();
        }
    }, false);
</script>



<!--wishlist uchun-->
<script>
    /** Guest holatda LS asosida like_icon'larni "active" qilish */
    window.applyWishlistActiveFromLS = function(){
        let ls=[];
        try{ ls = JSON.parse(localStorage.getItem('wishlist_v1')) || []; }catch(e){ ls=[]; }
        if (!Array.isArray(ls) || !ls.length) return;

        const set = new Set(ls.map(it => String(it.vid ? (it.pid+':'+it.vid) : it.pid)));

        document.querySelectorAll('.catalog_item .like_icon[data-product_id]').forEach(el=>{
            const pid = parseInt(el.getAttribute('data-product_id'),10);
            if (!pid) return;
            if (set.has(String(pid)) || [...set].some(k => k.startsWith(pid+':'))) {
                el.classList.add('active');
            }
        });
    };

    // DOM tayyor bo'lganda → faqat GUEST holatda ishga tushiramiz
    document.addEventListener('DOMContentLoaded', function(){
        var isLogged = (window.WISHLIST && window.WISHLIST.isLoggedIn) || /wordpress_logged_in_/i.test(document.cookie);
        if (!isLogged) {
            window.applyWishlistActiveFromLS();
        }
    });
</script>





</body>

</html>