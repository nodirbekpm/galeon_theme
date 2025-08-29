<?php wp_footer(); ?>

<!-- footer -->
<footer  id="contact">
    <div class="container">
        <div class="footer_row">
            <div class="top">
                <div class="logo">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/footer_logo.svg" alt="">
                    </a>
                    <div class="logo_text">
                        Российское производство ударопрочных кейсов для критически важного оборудования
                    </div>
                </div>

                <div class="nav_row">
                    <div class="nav_item">
                        <div class="title">
                            Каталог
                        </div>
                        <a href="catalog.html">Мини кейсы</a>
                        <a href="catalog.html">Средние кейсы</a>
                        <a href="catalog.html">Большие кейсы</a>
                        <a href="catalog.html">Длинные кейсы</a>
                        <a href="catalog.html">Кейсы для ноутбуков</a>
                        <a href="catalog.html">Контейнеры</a>
                    </div>

                    <div class="nav_item">
                        <div class="title">
                            Разделы
                        </div>
                        <a href="<?php echo home_url(); ?>">Главная</a>
                        <a href="production.html">Информация</a>
                        <a href="tool.html">Производство</a>
                        <a href="contact.html">Контакты</a>
                        <a href="catalog.html">Кейсы для ноутбуков</a>
                        <a href="catalog.html">Контейнеры</a>
                    </div>

                    <div class="nav_item">
                        <div class="title">
                            Контакты
                        </div>
                        <span> Москва ул. Плеханова д.7, эт. 1, пом. I ком 25</span>
                        <div class="nav_item_block">
                            <a class="link"  href="tel:74950236793">+7 495 023 67 93</a>
                            <span>Пн-Пт: с 10:00 до 18:00</span>
                        </div>
                        <a class="link" href="mailto:info@galeoncase.ru">info@galeoncase.ru</a>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="info">
                    <div class="rights text">Все права защищены 2025© </div>
                    <a href="<?php echo get_template_directory_uri() ?>/assets/documents/Privacy_Policy_Extended.pdf" target="_blank" class="politics text">Политика конфедициальности</a>
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
            <div class="title">Мы используем  <span>cookie-файлы</span> для улучшения работы сайта </div>
            <p>Используя этот сайт, вы даете согласие на обработку  <a href="#">персональных данных</a></p>

            <div class="accept_wrapper">
                <a id="acceptCookies" class="accept_btn">Согласиться</a>
            </div>
        </div>
    </div>
</div>

<!-- application modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modalBox">
        <button class="close-btn" id="closeModal"><img src="<?php echo get_template_directory_uri() ?>/assets/images/modal_close_icon.svg" alt=""></button>

        <div class="section_title">Оставить заявку</div>
        <div class="sub_title">
            Заполните форму, мы свяжемся и проконсультируем Вас в кратчайшие сроки
        </div>
        <form action="">
            <input required type="text" placeholder="Ваше Имя*">
            <div class="input_block">
                <input required type="tel" id="phone1" placeholder="+7 999 999 99 99*">
            </div>
            <textarea name="" id="" placeholder="Комментарий"></textarea>
            <button>Оставить заявку</button>

            <!-- custom confirm -->
            <div class="confirm">
                <label class="custom-checkbox">
                    <input required checked type="checkbox" id="confirm1">
                    <span class="checkmark"></span>
                </label>
                <label for="confirm1" class="text">Нажимая на кнопку «Отправить», вы даете согласие на обработку своих <a  href="<?php echo get_template_directory_uri() ?>/assets/documents/Personal_Data_Processing_Extended.pdf" target="_blank">персональных данных</a></label>
            </div>
        </form>
    </div>
</div>

<!-- search modal -->
<div class="modal-overlay1" id="modalOverlay1">
    <div class="modal" id="modalBox1">
        <button class="close-btn" id="closeModal1"><img src="<?php echo get_template_directory_uri() ?>/assets/images/modal_close_icon.svg" alt=""></button>

        <form class="header_search">
            <button  class="search_link"></button>
            <input type="text" name="search" placeholder="Поиск по сайту...">
        </form>
    </div>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
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
<script src="<?php echo get_template_directory_uri() ?>/assets/js/scripts.js"></script>


<!-- Add to cart js -->
<script>
    (function(){
        /* =========================
         *  A) Woo standart tugma: qty ni oldindan data-quantity ga yozamiz
         * ========================= */
        document.addEventListener('click', function(e){
            const btn = e.target.closest('.add_to_cart_button');
            if (!btn) return;
            const scope =
                btn.closest('.cart_controls') ||
                btn.closest('.product_card_item') ||
                btn.closest('.catalog_item') ||
                document;

            const qtyEl = scope.querySelector('.qty') || document.querySelector('.qty');
            const qty   = Math.max(1, parseInt(qtyEl?.value || '1', 10));
            btn.setAttribute('data-quantity', String(qty));
        }, true); // capturing=true → Woo handleridan oldin ishlaydi

        /* =========================
         *  Woo success: Swal va "View cart" linkini olib tashlash
         * ========================= */
        if (typeof jQuery !== 'undefined') {
            jQuery(function($){
                $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button){
                    if ($button && $button.length) {
                        $button.removeClass('added');
                        $button.siblings('.added_to_cart').remove(); // "View cart" linkini yo'q qilamiz
                    }
                    if (window.Swal) {
                        Swal.fire({ icon:'success', title:'Товар добавлен в корзину!', showConfirmButton:false, timer:1000 });
                    }
                });
            });
        }

        /* =========================
         *  B) Oddiy .cart_btn (single page’da variable/grupped/external bo'lishi mumkin)
         * ========================= */
        document.addEventListener('click', async function(e){
            const btn = e.target.closest('.cart_btn');
            // Agar Woo'ning standart tugmasi bo'lsa (A bo'lim), bu bo'limni o'tkazib yuboramiz
            if (!btn || btn.classList.contains('add_to_cart_button')) return;

            e.preventDefault();

            const scope =
                btn.closest('.cart_controls') ||
                btn.closest('.product_card_item') || // wishlist kartasi
                btn.closest('.catalog_item') ||      // katalog kartasi
                document;
            const qtyEl    = scope.querySelector('.qty') || document.querySelector('.qty');
            const quantity = Math.max(1, parseInt(qtyEl?.value || '1', 10));
            const pType    = btn.dataset.product_type || 'simple';
            const pUrl     = btn.dataset.product_url  || window.location.href;

            // SIMPLE bo'lsa: eng oson — window.location = add_to_cart_url + qty (fallback non-AJAX),
            // lekin biz AJAX qilamiz:
            let productId = btn.dataset.product_id || scope.querySelector('input[name="add-to-cart"]')?.value;
            if (!productId) {
                if (window.Swal) Swal.fire({icon:'error', title:'Ошибка', text:'ID товара не найден.'});
                return;
            }

            // Variable product bo'lsa → variations_form dagi tanlovlardan variation_id + attributes ni yig'amiz
            const fd = new FormData();
            if (pType === 'variable') {
                const vForm = scope.querySelector('form.variations_form') || document.querySelector('form.variations_form');
                const varId = vForm?.querySelector('input[name="variation_id"]')?.value;
                if (!varId || varId === '0') {
                    if (btn.dataset.product_url) { window.location.href = btn.dataset.product_url; return; }
                    if (window.Swal) Swal.fire({icon:'warning', title:'Выберите вариант', showConfirmButton:false, timer:1200});
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
                const res  = await fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'X-Requested-With':'XMLHttpRequest'},
                    body: fd
                });
                const text = await res.text();
                let resp = null; try { resp = JSON.parse(text); } catch(e){}

                if (!res.ok || !resp) throw new Error('Bad AJAX response');

                if (resp.error && resp.product_url) { window.location.href = resp.product_url; return; }

                if (typeof jQuery !== 'undefined' && resp.fragments) {
                    jQuery(document.body).trigger('added_to_cart', [resp.fragments, resp.cart_hash, btn]);
                } else if (window.Swal) {
                    Swal.fire({ icon:'success', title:'Товар добавлен в корзину!', showConfirmButton:false, timer:1000 });
                }
            } catch (err) {
                if (window.Swal) {
                    Swal.fire({ icon:'error', title:'Ошибка', text:'Не удалось добавить товар. Попробуйте ещё раз.' });
                }
            } finally {
                btn.classList.remove('is-loading');
            }
        });

        /* =========================================================
         *  C) WISHLIST (like) + header .wishlist-count real-time
         * ========================================================= */
        const LS_KEY = 'wishlist_v1';
        const A = window.WISHLIST || {};
        const AJAX_URL = A.ajaxUrl || (window.ajaxurl || '/wp-admin/admin-ajax.php');
        const NONCE    = A.nonce || '';
        const LOGGED   = !!A.isLoggedIn;

        // helpers
        const readLS  = () => { try { return JSON.parse(localStorage.getItem(LS_KEY)) || []; } catch(e){ return []; } };
        const writeLS = (arr) => localStorage.setItem(LS_KEY, JSON.stringify(arr));
        const keyOf   = (pid, vid) => `${pid}:${vid||0}`;

        function setWishlistCount(count){
            const el = document.querySelector('.wishlist-count.header_counter');
            if (!el) return;
            el.textContent = String(count);
            el.classList.toggle('active', Number(count) > 0);
        }
        function setHeartStateFromList(items){
            const keys = new Set(items.map(it => keyOf(it.pid, it.vid||0)));
            document.querySelectorAll('.like_icon').forEach(el => {
                const pid = el.dataset.product_id;
                const vid = el.dataset.variation_id || 0;
                if (!pid) return;
                el.classList.toggle('active', keys.has(keyOf(pid, vid)));
            });
            setWishlistCount(keys.size);
        }

        async function serverToggle(pid, vid=0){
            const fd = new FormData();
            fd.append('action','my_wishlist_toggle');
            fd.append('nonce', NONCE);
            fd.append('pid', pid);
            fd.append('vid', vid);
            const res  = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd });
            const json = await res.json().catch(()=>null);
            if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Server error');
            return json.data; // {status, count}
        }
        async function serverMerge(items){
            const fd = new FormData();
            fd.append('action','my_wishlist_merge');
            fd.append('nonce', NONCE);
            fd.append('items', JSON.stringify(items));
            const res  = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd });
            const json = await res.json().catch(()=>null);
            if (!res.ok || !json || !json.success) throw new Error('Merge failed');
            return json.data; // {count}
        }
        async function serverList(){
            const fd = new FormData();
            fd.append('action','my_wishlist_list');
            fd.append('nonce', NONCE);
            const res  = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd });
            const json = await res.json().catch(()=>null);
            if (!res.ok || !json || !json.success) return [];
            return json.data.items || [];
        }
        function lsToggle(pid, vid=0){
            let list = readLS();
            const k = keyOf(pid, vid);
            const idx = list.findIndex(it => `${it.pid}:${it.vid||0}` === k);
            let status;
            if (idx >= 0) { list.splice(idx,1); status='removed'; }
            else { list.push({pid: Number(pid), vid: Number(vid||0), ts: Date.now()}); status='added'; }
            writeLS(list);
            setWishlistCount(list.length);
            return {status, count:list.length, list};
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

        // Init: DOM loaded
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                if (LOGGED) {
                    const ls = readLS();
                    if (ls.length) { await serverMerge(ls); writeLS([]); }
                    const sv = await serverList();
                    setHeartStateFromList(sv);
                } else {
                    setHeartStateFromList(readLS());
                }
            } catch(e){ /* ignore */ }
        });

        // Cross-tab sync (guest)
        window.addEventListener('storage', (e) => {
            if (e.key === LS_KEY) {
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

        // LIKE toggle (capturing: eski handlerlar ishlamasin)
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.like_icon');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();

            if (btn.dataset.wlBusy === '1') return;
            btn.dataset.wlBusy = '1';

            const pid   = btn.dataset.product_id;
            const ptype = btn.dataset.product_type || 'simple';
            const vid   = (ptype === 'variable') ? (btn.dataset.variation_id || 0) : 0;
            if (!pid) { btn.dataset.wlBusy = '0'; return; }

            try {
                if (LOGGED) {
                    const data = await serverToggle(pid, vid);
                    const added = (data.status === 'added');
                    btn.classList.toggle('active', added);
                    setWishlistCount(data.count);
                    toast(added ? 'info' : 'warning', added ? 'Товар добавлен в избранное!' : 'Удалено из избранного');
                } else {
                    const {status, count, list} = lsToggle(pid, vid);
                    const added = (status === 'added');
                    btn.classList.toggle('active', added);
                    setHeartStateFromList(list);
                    toast(added ? 'info' : 'warning', added ? 'Товар добавлен в избранное!' : 'Удалено из избранного');
                }

                // WISHLIST sahifasida turib unlike qilingan bo‘lsa — kartani darhol olib tashlaymiz
                const wlList = document.getElementById('wl_list');
                if (wlList && !btn.classList.contains('active')) {
                    const card = btn.closest('.product_card_item');
                    if (card) card.remove();
                    if (!wlList.querySelector('.product_card_item')) {
                        wlList.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                    }
                }
            } catch (err) {
                toast('error','Не удалось обновить избранное');
            } finally {
                btn.dataset.wlBusy = '0';
            }
        }, true);
    })();
</script>

<!-- Add to like -->
<!--<script>-->
<!--    (function(){-->
<!--        const LS_KEY = 'wishlist_v1';-->
<!---->
<!--        // AJAX sozlamalari-->
<!--        const A = window.WISHLIST || {};-->
<!--        const AJAX_URL = A.ajaxUrl || (window.ajaxurl || '/wp-admin/admin-ajax.php');-->
<!--        const NONCE    = A.nonce || '';-->
<!--        const LOGGED   = !!A.isLoggedIn;-->
<!---->
<!--        // LocalStorage helpers-->
<!--        const readLS = () => { try { return JSON.parse(localStorage.getItem(LS_KEY)) || []; } catch(e){ return []; } };-->
<!--        const writeLS = (arr) => localStorage.setItem(LS_KEY, JSON.stringify(arr));-->
<!--        const keyOf = (pid, vid) => `${pid}:${vid||0}`;-->
<!---->
<!--        const lsToggle = (pid, vid=0) => {-->
<!--            let list = readLS();-->
<!--            const k = keyOf(pid, vid);-->
<!--            const idx = list.findIndex(it => `${it.pid}:${it.vid||0}` === k);-->
<!--            let status;-->
<!--            if (idx >= 0) { list.splice(idx,1); status='removed'; }-->
<!--            else { list.push({pid: Number(pid), vid: Number(vid||0), ts: Date.now()}); status='added'; }-->
<!--            writeLS(list);-->
<!--            return {status, count:list.length};-->
<!--        };-->
<!---->
<!--        const serverToggle = async (pid, vid=0) => {-->
<!--            const fd = new FormData();-->
<!--            fd.append('action','my_wishlist_toggle');-->
<!--            fd.append('nonce', NONCE);-->
<!--            fd.append('pid', pid);-->
<!--            fd.append('vid', vid);-->
<!--            const res  = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd });-->
<!--            const json = await res.json().catch(()=>null);-->
<!--            if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Server error');-->
<!--            return json.data; // {status, count}-->
<!--        };-->
<!---->
<!--        const serverMerge = async (items) => {-->
<!--            const fd = new FormData();-->
<!--            fd.append('action','my_wishlist_merge');-->
<!--            fd.append('nonce', NONCE);-->
<!--            fd.append('items', JSON.stringify(items));-->
<!--            const res  = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd });-->
<!--            const json = await res.json().catch(()=>null);-->
<!--            if (!res.ok || !json || !json.success) throw new Error('Merge failed');-->
<!--            return json.data; // {count}-->
<!--        };-->
<!---->
<!--        const serverList = async () => {-->
<!--            const fd = new FormData();-->
<!--            fd.append('action','my_wishlist_list');-->
<!--            fd.append('nonce', NONCE);-->
<!--            const res  = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd });-->
<!--            const json = await res.json().catch(()=>null);-->
<!--            if (!res.ok || !json || !json.success) return [];-->
<!--            return json.data.items || [];-->
<!--        };-->
<!---->
<!--        // UI: like holatini set qilish (ENDI 'active' class bilan)-->
<!--        const setHeartStateFromList = (items) => {-->
<!--            const keys = new Set(items.map(it => keyOf(it.pid, it.vid||0)));-->
<!--            document.querySelectorAll('.like_icon').forEach(el => {-->
<!--                const pid = el.dataset.product_id;-->
<!--                const vid = el.dataset.variation_id || 0;-->
<!--                if (!pid) return;-->
<!--                const active = keys.has(keyOf(pid, vid));-->
<!--                el.classList.toggle('active', active);-->
<!--            });-->
<!--            const countEl = document.querySelector('.wishlist-count');-->
<!--            if (countEl) countEl.textContent = String(keys.size);-->
<!--        };-->
<!--        const setHeartStateFromLS = () => setHeartStateFromList(readLS());-->
<!---->
<!--        // Swal (bitta, throttled)-->
<!--        let lastToastAt = 0;-->
<!--        const toast = (type, title) => {-->
<!--            if (!window.Swal) return;-->
<!--            const now = Date.now();-->
<!--            if (now - lastToastAt < 350) return; // 2x chiqmasin-->
<!--            lastToastAt = now;-->
<!--            Swal.fire({ icon: type, title, timer: 1000, showConfirmButton: false });-->
<!--        };-->
<!---->
<!--        // Login bo‘lsa: localStorage → server MERGE (faqat bir marta), so'ng holatni chizish-->
<!--        document.addEventListener('DOMContentLoaded', async () => {-->
<!--            try {-->
<!--                if (LOGGED) {-->
<!--                    const ls = readLS();-->
<!--                    if (ls.length) { await serverMerge(ls); writeLS([]); }-->
<!--                    const sv = await serverList();-->
<!--                    setHeartStateFromList(sv);-->
<!--                } else {-->
<!--                    setHeartStateFromLS();-->
<!--                }-->
<!--            } catch(e){ /* ignore */ }-->
<!--        });-->
<!---->
<!--        // Variable product: variant tanlanganda variation_id ni yozib boramiz-->
<!--        document.addEventListener('change', (e) => {-->
<!--            const form = e.target.closest('form.variations_form');-->
<!--            if (!form) return;-->
<!--            const varId = form.querySelector('input[name="variation_id"]')?.value;-->
<!--            document.querySelectorAll('.like_icon[data-product_type="variable"]').forEach(el => {-->
<!--                if (varId && varId !== '0') el.dataset.variation_id = varId;-->
<!--                else delete el.dataset.variation_id;-->
<!--            });-->
<!--        });-->
<!---->
<!--        // LIKE toggle — CAPTURING fazada: boshqa eski handlerlar ishlamasin (2x Swal muammosi yo'q)-->
<!--        document.addEventListener('click', async (e) => {-->
<!--            const btn = e.target.closest('.like_icon');-->
<!--            if (!btn) return;-->
<!---->
<!--            // boshqa listenerlar ishlamasin:-->
<!--            e.preventDefault();-->
<!--            e.stopPropagation();-->
<!--            if (e.stopImmediatePropagation) e.stopImmediatePropagation();-->
<!---->
<!--            // double-click guard-->
<!--            if (btn.dataset.wlBusy === '1') return;-->
<!--            btn.dataset.wlBusy = '1';-->
<!---->
<!--            const pid = btn.dataset.product_id;-->
<!--            const ptype = btn.dataset.product_type || 'simple';-->
<!--            const vid = (ptype === 'variable') ? (btn.dataset.variation_id || 0) : 0;-->
<!--            if (!pid) { btn.dataset.wlBusy = '0'; return; }-->
<!---->
<!--            try {-->
<!--                if (LOGGED) {-->
<!--                    const data = await serverToggle(pid, vid);-->
<!--                    const willBeActive = (data.status === 'added');-->
<!--                    btn.classList.toggle('active', willBeActive);-->
<!--                    const countEl = document.querySelector('.wishlist-count');-->
<!--                    if (countEl) countEl.textContent = String(data.count);-->
<!--                    toast(willBeActive ? 'info' : 'warning', willBeActive ? 'Товар добавлен в избранное!' : 'Удалено из избранного');-->
<!--                } else {-->
<!--                    const {status, count} = lsToggle(pid, vid);-->
<!--                    btn.classList.toggle('active', status === 'added');-->
<!--                    const countEl = document.querySelector('.wishlist-count');-->
<!--                    if (countEl) countEl.textContent = String(count);-->
<!--                    toast(status === 'added' ? 'info' : 'warning', status === 'added' ? 'Товар добавлен в избранное!' : 'Удалено из избранного');-->
<!--                }-->
<!--            } catch(err){-->
<!--                toast('error','Не удалось обновить избранное');-->
<!--            } finally {-->
<!--                btn.dataset.wlBusy = '0';-->
<!--            }-->
<!--        }, true); // <<< capturing = true-->
<!--    })();-->
<!---->
<!---->
<!--</script>-->



<!-- plus and minus -->
<!-- plus and minus (UNIVERSAL & CONFLICT-PROOF) -->
<script>
    (function(){
        // Hamma sahifa va dinamik (AJAX) kontentda ishlasin
        const clamp = (v, mn, mx) => Math.max(mn, Math.min(mx, v));

        function findQtyInput(start){
            // .catalog_item (archive), .quantity (single), .cart_controls — hammasini qamrab olamiz
            const scope = start.closest('.cart_controls, .quantity, .product_card_item, .catalog_item') || document;
            return scope.querySelector('input.qty, input[name="quantity"][type="number"]');
        }

        function stepFor(btn, input){
            // 1) .qty_btn data-step -> 2) input.step -> 3) default ±1
            const ds = parseInt(btn.dataset.step || btn.getAttribute('data-step') || '0', 10);
            if (!isNaN(ds) && ds !== 0) return ds;
            const st = parseInt(input.step || '1', 10);
            const base = (!isNaN(st) && st > 0) ? st : 1;
            return btn.classList.contains('plus') ? base : -base;
        }

        function setQty(input, nextVal){
            const min = parseInt(input.min || '1', 10) || 1;
            const max = parseInt(input.max || '1000', 10) || 1000;
            const target = clamp(nextVal, min, max);
            input.value = String(target);
            // Boshqa skriptlar kuzatishi uchun
            input.dispatchEvent(new Event('input',  {bubbles:true}));
            input.dispatchEvent(new Event('change', {bubbles:true}));
        }

        // Bir klikda 2 marta ishlashini oldini oladigan guard (capture+bubble holatida ham)
        function recentlyHandled(el){
            const now = Date.now();
            const last = parseInt(el.dataset._qHandled || '0', 10);
            if (now - last < 120) return true;
            el.dataset._qHandled = String(now);
            return false;
        }

        function onQtyClick(ev){
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
        document.addEventListener('input', function(ev){
            const input = ev.target.closest('input.qty, input[name="quantity"][type="number"]');
            if (!input) return;
            const min = parseInt(input.min || '1',10) || 1;
            const max = parseInt(input.max || '1000',10) || 1000;
            let v = parseInt(input.value || '0', 10);
            if (isNaN(v)) v = min;
            input.value = String(clamp(v, min, max));
        });
    })();
</script>








</body>

</html>