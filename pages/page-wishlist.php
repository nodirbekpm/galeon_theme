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


    <script>
        (function(){
            // Login aniqlash: localized flag yoki cookie
            var IS_LOGGED = (window.WISHLIST && window.WISHLIST.isLoggedIn) || /wordpress_logged_in_/i.test(document.cookie);

            // Guest bo'lsa LS o'qiymiz, login bo'lsa LS yubormaymiz
            var items = [];
            if (!IS_LOGGED) {
                try { items = JSON.parse(localStorage.getItem('wishlist_v1')) || []; } catch(e){ items = []; }
            }

            var fd = new FormData();
            fd.append('action','galeon_wishlist_render');
            fd.append('nonce', <?php echo json_encode( wp_create_nonce('wishlist_nonce') ); ?>);
            // Faqat GUEST uchun yuboramiz (login bo'lsa LS yubormaymiz)
            if (!IS_LOGGED) {
                fd.append('items', JSON.stringify(items));
            }
            fd.append('ts', Date.now()); // cache-buster

            fetch(<?php echo json_encode( admin_url('admin-ajax.php') ); ?>, {
                method:'POST',
                credentials:'same-origin',
                body: fd,
                cache: 'no-store'
            })
                .then(function(r){ return r.json(); })
                .then(function(resp){
                    var list = document.getElementById('wl_list');
                    if (!list) return;

                    if (resp && resp.success) {
                        var html = (resp.data && resp.data.html) ? resp.data.html : '<p class="empty">Список избранного пуст.</p>';
                        list.innerHTML = html;

                        // Serverdan count bo'lsa — header counterni yangilaymiz
                        var cnt = (resp.data && typeof resp.data.count !== 'undefined') ? Number(resp.data.count) : 0;
                        var hc = document.querySelector('.wishlist-count.header_counter');
                        if (hc) {
                            hc.textContent = String(cnt);
                            hc.classList.toggle('active', cnt > 0);
                        }

                        // 🔐 MUHIM: LS sink faqat GUEST uchun
                        if (!IS_LOGGED) {
                            try {
                                var newItems = (resp.data && Array.isArray(resp.data.items)) ? resp.data.items : [];
                                localStorage.setItem('wishlist_v1', JSON.stringify(newItems));
                            } catch(e){}
                        } else {
                            // login holatda guest artefaktlarini ham tozalab qo'yamiz
                            try { localStorage.setItem('wishlist_v1', '[]'); } catch(e){}
                            try { document.cookie = 'wishlist_v1=; Max-Age=0; Path=/'; } catch(e){}
                        }
                    } else {
                        list.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                    }
                })
                .catch(function(){
                    var list = document.getElementById('wl_list');
                    if (list) list.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                });

            // Like o'chirilganda kartani olib tashlash (sizdagi kod – o'zgarishsiz)
            document.addEventListener('click', function(e){
                var like = e.target.closest('.like_icon');
                if (!like) return;
                setTimeout(function(){
                    if (!like.classList.contains('active')) {
                        var card = like.closest('.product_card_item');
                        if (card) card.remove();
                        if (!document.querySelector('.product_card_item')) {
                            var list = document.getElementById('wl_list');
                            if (list) list.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                        }
                        // header countni ham minuslash ixtiyoriy:
                        var hc = document.querySelector('.wishlist-count.header_counter');
                        if (hc) {
                            var n = Math.max(0, parseInt(hc.textContent || '0', 10) - 1);
                            hc.textContent = String(n);
                            hc.classList.toggle('active', n > 0);
                        }
                    }
                }, 50);
            });
        })();
    </script>




<?php
get_footer();

