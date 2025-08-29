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
            // LS o‘qish
            let items = [];
            try { items = JSON.parse(localStorage.getItem('wishlist_v1')) || []; } catch(e){ items = []; }

            const fd = new FormData();
            fd.append('action', 'galeon_wishlist_render');
            fd.append('nonce',  <?php echo json_encode( wp_create_nonce('wishlist_nonce') ); ?>);
            fd.append('items', JSON.stringify(items));

            fetch(<?php echo json_encode( admin_url('admin-ajax.php') ); ?>, {
                method: 'POST',
                credentials: 'same-origin',
                body: fd
            }).then(r=>r.json())
                .then(resp=>{
                    const list = document.getElementById('wl_list');
                    if (!list) return;
                    if (resp && resp.success) {
                        list.innerHTML = resp.data.html || '<p class="empty">Список избранного пуст.</p>';
                    } else {
                        list.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                    }
                })
                .catch(()=>{
                    const list = document.getElementById('wl_list');
                    if (list) list.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                });


            // Like o'chirilganda DOM’dan olib tashlash (sizning toggle JS'ingiz class='active' ni boshqaradi)
            document.addEventListener('click', function(e){
                const like = e.target.closest('.like_icon');
                if (!like) return;
                setTimeout(()=>{
                    if (!like.classList.contains('active')) {
                        const card = like.closest('.product_card_item');
                        if (card) card.remove();
                        if (!document.querySelector('.product_card_item')) {
                            const list = document.getElementById('wl_list');
                            if (list) list.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                        }
                    }
                }, 50);
            });
        })();
    </script>

<?php
get_footer();

