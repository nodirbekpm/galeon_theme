<?php
/**
 * Theme Functions
 * Galeon Custom Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme setup
 */
function galeon_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption' ) );

    register_nav_menus( array(
        'header_menu' => 'Header Menu',
        'footer_menu' => 'Footer Menu',
    ) );

    add_image_size( 'service_thumb', 400, 300, true );
    add_image_size( 'team_member', 300, 300, true );
}
add_action( 'after_setup_theme', 'galeon_theme_setup' );

// WooCommerce qo'llab-quvvatlash
function galeon_add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'galeon_add_woocommerce_support' );

// WooCommerce default style'larni o'chirish
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * CPT faylini ulash
 */

require get_template_directory() . '/inc/custom-post-types.php';


/**
 * ACF faylini ulash
 */
//require get_template_directory() . '/inc/acf-fields.php';




/**
 * Breadcrumb
 */
// 1) "Home" → "Главная", "Shop" → "Каталог"
add_filter('wpseo_breadcrumb_links', function($links){
    foreach ($links as &$l) {
        if (isset($l['text']) && $l['text'] === 'Home') { $l['text'] = 'Главная'; }
        if (isset($l['text']) && $l['text'] === 'Shop') { $l['text'] = 'Каталог'; }
    }
    return $links;
});

// 2) Separator ni <span>/</span> ko‘rinishida chiqarish
add_filter('wpseo_breadcrumb_separator', function($sep){
    return ' <span>/</span> ';
});

// 3) Oxirgi bo‘lagini <a class="active"> qilish
add_filter('wpseo_breadcrumb_single_link', function($link_output, $link){
    // Yoast oxirgi bo‘lakka 'breadcrumb_last' classli <span> beradi — uni <a class="active"> ga almashtiramiz
    if (strpos($link_output, 'breadcrumb_last') !== false) {
        $url  = !empty($link['url']) ? $link['url'] : get_permalink();
        $text = isset($link['text']) ? $link['text'] : '';
        return '<a href="'.esc_url($url).'" class="active">'.$text.'</a>';
    }
    return $link_output;
}, 10, 2);

// 4) Ortiqcha wrapper <span> larni olib tashlash (faqat <a> va <span>/</span> qolsin)
add_filter('wpseo_breadcrumb_output', function($output){
    // <span> <a>...</a> </span>  ->  <a>...</a>
    $output = preg_replace('#<span[^>]*>\s*(<a[^>]+>.*?</a>)\s*</span>#', '$1', $output);
    // Keraksiz ketma-ket bo‘sh joylarni tozalash
    $output = preg_replace('/\s+/', ' ', $output);
    return $output;
});


/**
 *  Mahsulotlarni harakteristikasi bilan chiqarish
 */
// Mahsulot "Xususiyatlar" bo‘limini chiqarish (belgilangan tartib + qolganlari)
function render_product_characteristics( $product = null ) {
    if ( ! $product ) {
        $product = wc_get_product( get_the_ID() );
    }
    if ( ! $product ) return;

    // 1) Avval belgilangan tartib:
    //    slug => [Label (RU), unit_suffix (bo‘sh bo‘lsa qo‘shilmaydi)]
    $ordered_map = [
        'pa_color'   => ['Цвет',                 ''],
        'pa_length'  => ['Внутренняя длина',     ' мм'],
        'pa_width'   => ['Внутренняя ширина',    ' мм'],
        'pa_height'  => ['Внутренняя высота',    ' мм'],
        'pa_weight'  => ['Вес',                  'кг'],
        'pa_variant' => ['Вариант',              ''],
    ];

    // Qaysi slug’lar ko‘rsatib bo‘lindi — keyin dublikat bo‘lmasin
    $shown = [];

    echo '<div class="characteristics">';

    // Helper: qiymatni olish (taxonomy yoki custom)
    $get_attr_value = function($slug) use ($product) {
        // WooCommerce: attribute nomi taxonomy bo‘lsa 'pa_xxx'
        // get_attribute() string qaytaradi (names, comma-separated)
        $v = trim( wp_strip_all_tags( $product->get_attribute( $slug ) ) );
        return $v;
    };

    // Avval oldindan belgilangan atributlarni chiqaramiz
    foreach ( $ordered_map as $slug => [$label, $unit] ) {
        $val = $get_attr_value($slug);
        if ( $val !== '' ) {
            // Agar qiymatga unit qo‘shish kerak bo‘lsa, har bir elementga qo‘shamiz
            if ( $unit ) {
                // qiymat vergul bilan bo‘lingan bo‘lishi mumkin
                $parts = array_map('trim', explode(',', $val));
                $parts = array_filter($parts, fn($p) => $p !== '');
                $val   = implode(', ', array_map(fn($p) => $p . $unit, $parts));
            }

            echo '<div class="character_item">';
            echo   '<div class="name">' . esc_html($label) . ':</div>';
            echo   '<div class="value">' . esc_html($val) . '</div>';
            echo '</div>';

            $shown[] = $slug;
        }
    }

    // 2) Qolgan barcha atributlarni avtomatik chiqaramiz (bo‘sh bo‘lmaganlarini)
    foreach ( $product->get_attributes() as $attr ) {
        // $attr->get_name() taxonomy bo‘lsa 'pa_xxx' yoki custom nom
        $name = $attr->get_name();

        // Agar allaqachon ko‘rsatilgan bo‘lsa — o‘tkazamiz
        if ( in_array( $name, $shown, true ) ) {
            continue;
        }

        // Qiymatini o‘qiymiz
        if ( $attr->is_taxonomy() ) {
            $terms = wc_get_product_terms( $product->get_id(), $name, ['fields' => 'names'] );
            $value = implode(', ', $terms);
        } else {
            // Custom product attribute
            $value = implode(', ', array_map('trim', $attr->get_options() ) );
        }

        $value = trim( wp_strip_all_tags( $value ) );
        if ( $value === '' ) continue;

        // Label: agar taxonomy bo‘lsa chiroyli label, bo‘lmasa nomi
        $label = wc_attribute_label( $name );

        echo '<div class="character_item">';
        echo   '<div class="name">' . esc_html($label) . ':</div>';
        echo   '<div class="value">' . esc_html($value) . '</div>';
        echo '</div>';
    }

    echo '</div>'; // .characteristics
}


/**
 * Woo scripts
 */
add_action('wp_enqueue_scripts', function(){
    if ( class_exists('WooCommerce') ) {
        wp_enqueue_script('jquery');               // jQuery ishlatiladi
        wp_enqueue_script('wc-add-to-cart');       // AJAX add-to-cart
        wp_enqueue_script('wc-cart-fragments');    // mini-cart fragment update
    }
});


/**
 *  lIke ajax
 */
add_action('wp_enqueue_scripts', function () {
    if ( class_exists('WooCommerce') ) {
        wp_enqueue_script('jquery');
    }
    // Wishlist JS faylingizni ulaysiz (yoki pastdagi <script>ni inline qo‘yishingiz mumkin)
    wp_register_script('theme-wishlist', get_template_directory_uri().'/assets/js/wishlist.js', ['jquery'], null, true);
    wp_localize_script('theme-wishlist', 'WISHLIST', [
        'ajaxUrl'    => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('wishlist_nonce'),
        'isLoggedIn' => is_user_logged_in(),
    ]);
    wp_enqueue_script('theme-wishlist');
});

//======
// ===== Wishlist helpers (user_meta: wishlist_v1) =====
function my_wl_user_list($user_id){
    $raw = get_user_meta($user_id, 'wishlist_v1', true);
    return is_array($raw) ? $raw : [];
}
function my_wl_save($user_id, $items){
    // De-dupe by key "pid:vid"
    $seen = [];
    $out = [];
    foreach ((array)$items as $it){
        $pid = isset($it['pid']) ? absint($it['pid']) : 0;
        $vid = isset($it['vid']) ? absint($it['vid']) : 0;
        if (!$pid) continue;
        $key = $pid.':'.$vid;
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $out[] = ['pid'=>$pid, 'vid'=>$vid, 'ts'=> isset($it['ts'])? intval($it['ts']) : time()];
    }
    update_user_meta($user_id, 'wishlist_v1', $out);
    return $out;
}
function my_wl_toggle($user_id, $pid, $vid){
    $pid = absint($pid); $vid = absint($vid);
    if (!$pid) return ['status'=>'error','count'=>0];
    $list = my_wl_user_list($user_id);
    $key  = $pid.':'.$vid;
    $found = false;
    foreach ($list as $i => $it){
        if (($it['pid'].':'.$it['vid']) === $key){
            unset($list[$i]);
            $found = true;
            break;
        }
    }
    if ($found){
        $status = 'removed';
    } else {
        $list[] = ['pid'=>$pid,'vid'=>$vid,'ts'=>time()];
        $status = 'added';
    }
    $list = array_values($list);
    my_wl_save($user_id,$list);
    return ['status'=>$status,'count'=>count($list)];
}
//==========
// Toggle (faqat logged-in)
add_action('wp_ajax_my_wishlist_toggle', function(){
    if ( ! is_user_logged_in() ) wp_send_json_error(['message'=>'not logged in'], 401);
    check_ajax_referer('wishlist_nonce', 'nonce');
    $pid = isset($_POST['pid']) ? absint($_POST['pid']) : 0;
    $vid = isset($_POST['vid']) ? absint($_POST['vid']) : 0;
    $res = my_wl_toggle(get_current_user_id(), $pid, $vid);
    wp_send_json_success($res);
});

// Merge localStorage → user_meta (faqat logged-in)
add_action('wp_ajax_my_wishlist_merge', function(){
    if ( ! is_user_logged_in() ) wp_send_json_error(['message'=>'not logged in'], 401);
    check_ajax_referer('wishlist_nonce', 'nonce');
    $items = [];
    if (isset($_POST['items'])) {
        $json = is_array($_POST['items']) ? wp_json_encode($_POST['items']) : wp_unslash($_POST['items']);
        $items = json_decode($json, true) ?: [];
    }
    $current = my_wl_user_list(get_current_user_id());
    $all = array_merge($current, $items);
    $saved = my_wl_save(get_current_user_id(), $all);
    wp_send_json_success(['count'=>count($saved)]);
});

// Ro'yxatni olish (logged-in uchun)
add_action('wp_ajax_my_wishlist_list', function(){
    if ( ! is_user_logged_in() ) wp_send_json_success(['items'=>[]]);
    check_ajax_referer('wishlist_nonce', 'nonce');
    $items = my_wl_user_list(get_current_user_id());
    wp_send_json_success(['items'=>$items]);
});

// Global helper: faqat bir marta e'lon bo'lsin
if ( ! function_exists('my_is_in_wishlist') ) {
    function my_is_in_wishlist( $product_id ) {
        if ( ! is_user_logged_in() ) return false;
        $list = get_user_meta( get_current_user_id(), 'wishlist_v1', true );
        if ( ! is_array($list) ) return false;
        foreach ( $list as $it ) {
            $pid = absint( $it['pid'] ?? 0 );
            if ( $pid === absint($product_id) ) return true;
        }
        return false;
    }
}

/**
 *  Basket counter
 */
add_filter('woocommerce_add_to_cart_fragments', function($fragments){
    ob_start();
    $count = ( function_exists('WC') && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0; ?>
    <span class="header_counter basket_counter<?php echo $count ? ' active' : ''; ?>">
        <?php echo (int) $count; ?>
    </span>
    <?php
    $html = ob_get_clean();

    // Faqat basket counter(lar)i:
    $fragments['span.header_counter.basket_counter'] = $html; // ikkala klass birga bo'lsa
    $fragments['span.basket_counter']                = $html; // faqat basket_counter bo'lsa

    return $fragments;
});





/**
 *  Archive Products
 */
/**
 * ARCHIVE: AJAX bilan productlarni yuklash
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('jquery'); // bu head’da chiqadi
    wp_localize_script('jquery', 'GALEON_ARCHIVE', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('galeon_archive_nonce'),
        'per_page_default' => 9,
    ]);
});


/**
 * Helper: term nomidan raqam ajratish ( "330 мм" => 330.0 )
 */
function galeon_num_from_term_name( $name ) {
    $v = trim( preg_replace('~[^0-9\.\,]+~','', (string)$name ) );
    $v = str_replace(',', '.', $v);
    return ($v === '' || !is_numeric($v)) ? null : (float)$v;
}

/**
 * Helper: taxonomiyadan (masalan: pa_length) nomi raqam bo‘lgan
 * term_id larni [min,max] range bo‘yicha tanlash
 */
function galeon_term_ids_in_range( $taxonomy, $min = null, $max = null ) {
    if ( $min === null && $max === null ) return [];
    $terms = get_terms(['taxonomy'=>$taxonomy,'hide_empty'=>false]);
    $out = [];
    foreach ( $terms as $t ) {
        $n = galeon_num_from_term_name( $t->name );
        if ( $n === null ) continue;
        if ( $min !== null && $n < $min ) continue;
        if ( $max !== null && $n > $max ) continue;
        $out[] = (int)$t->term_id;
    }
    return $out;
}

/**
 * ARCHIVE: AJAX bilan productlarni yuklash (search + filterlar + facetlar)
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('jquery');
    wp_localize_script('jquery', 'GALEON_ARCHIVE', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('galeon_archive_nonce'),
        'per_page_default' => 9,
    ]);
});

add_action('wp_ajax_nopriv_galeon_load_products', 'galeon_load_products');
add_action('wp_ajax_galeon_load_products',        'galeon_load_products');
function galeon_load_products(){
    if ( empty($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'galeon_archive_nonce') ) {
        wp_send_json_error(['message'=>'Bad nonce'], 403);
    }

    // --- UI’dan ---
    $mode     = sanitize_text_field($_POST['mode'] ?? 'replace'); // replace | append
    $page     = max(1, intval($_POST['page'] ?? 1));
    $per_page = max(1, intval($_POST['per_page'] ?? 9));
    $search   = sanitize_text_field($_POST['search'] ?? '');
    $cat_slug = sanitize_title($_POST['category'] ?? '');

    $read_range = function($k){
        $min = isset($_POST[$k]['min']) ? trim((string)$_POST[$k]['min']) : '';
        $max = isset($_POST[$k]['max']) ? trim((string)$_POST[$k]['max']) : '';
        $min = ($min === '') ? null : (float)$min;
        $max = ($max === '') ? null : (float)$max;
        return [$min, $max];
    };
    list($price_min,$price_max) = $read_range('price');
    list($len_min,$len_max)     = $read_range('len');
    list($wid_min,$wid_max)     = $read_range('wid');
    list($hei_min,$hei_max)     = $read_range('hei');
    list($wei_min,$wei_max)     = $read_range('wei');

    $variants = [];
    if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
        foreach ($_POST['variants'] as $v) $variants[] = sanitize_title($v);
        $variants = array_values(array_unique(array_filter($variants)));
    }

    // --- tax/meta query ---
    $tax_query  = ['relation'=>'AND'];
    $meta_query = ['relation'=>'AND'];

    if ( $cat_slug !== '' ) {
        $tax_query[] = ['taxonomy'=>'product_cat','field'=>'slug','terms'=>[$cat_slug],'operator'=>'IN'];
    }
    if ( !empty($variants) ) {
        $tax_query[] = ['taxonomy'=>'pa_option','field'=>'slug','terms'=>$variants,'operator'=>'IN'];
    }

    $attr_ranges = [
        'pa_length' => [$len_min,$len_max],
        'pa_width'  => [$wid_min,$wid_max],
        'pa_height' => [$hei_min,$hei_max],
        'pa_weight' => [$wei_min,$wei_max],
    ];
    foreach ($attr_ranges as $tax => $pair) {
        list($mn,$mx) = $pair;
        if ($mn===null && $mx===null) continue;
        $term_ids = galeon_term_ids_in_range($tax, $mn, $mx);
        if (!empty($term_ids)) {
            $tax_query[] = ['taxonomy'=>$tax,'field'=>'term_id','terms'=>$term_ids,'operator'=>'IN'];
        } else {
            wp_send_json_success([
                'mode'        => $mode,
                'html'        => '',
                'total'       => 0,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => 0,
                'facets'      => [
                    'price'=>['min'=>0,'max'=>0],'len'=>['min'=>0,'max'=>0],
                    'wid'=>['min'=>0,'max'=>0],'hei'=>['min'=>0,'max'=>0],'wei'=>['min'=>0,'max'=>0],
                ],
            ]);
        }
    }

    if ($price_min !== null || $price_max !== null) {
        $cmp = [];
        if ($price_min!==null && $price_max!==null) $cmp = ['compare'=>'BETWEEN','type'=>'NUMERIC','value'=>[$price_min,$price_max]];
        elseif ($price_min!==null)                  $cmp = ['compare'=>'>=','type'=>'NUMERIC','value'=>$price_min];
        else                                        $cmp = ['compare'=>'<=','type'=>'NUMERIC','value'=>$price_max];

        $meta_query[] = [
            'relation'=>'OR',
            array_merge(['key'=>'_price'], $cmp),
            array_merge(['key'=>'_min_variation_price'], $cmp),
        ];
    }

    // --- Query args ---
    $args = [
        'post_type'           => 'product',
        'post_status'         => 'publish',
        's'                   => $search,
        'tax_query'           => $tax_query,
        'meta_query'          => $meta_query,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => false,
    ];

    // replace: paginated, append: offset/limit
    if ($mode === 'append') {
        $offset = max(0, intval($_POST['offset'] ?? 0));
        $limit  = max(1, intval($_POST['limit']  ?? 4));
        $args['posts_per_page'] = $limit;
        $args['offset']         = $offset;
    } else {
        $args['posts_per_page'] = $per_page;
        $args['paged']          = $page;
    }

    $q = new WP_Query($args);

    ob_start();
    if ($q->have_posts()){
        while ($q->have_posts()){ $q->the_post();
            $p = wc_get_product(get_the_ID());
            if ($p) get_template_part('template-parts/product/catalog-item', null, ['product'=>$p]);
        }
        wp_reset_postdata();
    }
    $html = ob_get_clean();

    // total / facets – doimiy (append/repl o‘xshash)
    $total       = intval($q->found_posts);
    $total_pages = ($mode==='append') ? 0 : ($per_page ? (int)ceil($total/$per_page) : 1);

    // Facets (mos keladigan hamma postlar bo‘yicha)
    $args_all = $args;
    unset($args_all['posts_per_page'],$args_all['paged'],$args_all['offset']);
    $args_all['posts_per_page'] = -1;
    $args_all['fields']         = 'ids';
    $args_all['no_found_rows']  = true;
    $ids = get_posts($args_all);

    $facet = [
        'price'=>['min'=>0,'max'=>0],'len'=>['min'=>0,'max'=>0],
        'wid'=>['min'=>0,'max'=>0],'hei'=>['min'=>0,'max'=>0],'wei'=>['min'=>0,'max'=>0],
    ];
    if (!empty($ids)) {
        $pmin=null;$pmax=null;
        foreach($ids as $pid){
            $pr = wc_get_product($pid);
            if (!$pr) continue;
            $v = (float)$pr->get_price();
            if ($v<=0) continue;
            if ($pmin===null || $v<$pmin) $pmin=$v;
            if ($pmax===null || $v>$pmax) $pmax=$v;
        }
        if ($pmin!==null) $facet['price'] = ['min'=>$pmin,'max'=>$pmax];

        $calc = function($tax) use ($ids){
            $terms = wp_get_object_terms($ids, $tax, ['fields'=>'all']);
            if (is_wp_error($terms) || empty($terms)) return ['min'=>0,'max'=>0];
            $vals=[]; foreach($terms as $t){ $n=galeon_num_from_term_name($t->name); if($n!==null) $vals[]=$n; }
            if (!$vals) return ['min'=>0,'max'=>0];
            return ['min'=>min($vals), 'max'=>max($vals)];
        };
        $facet['len']=$calc('pa_length');
        $facet['wid']=$calc('pa_width');
        $facet['hei']=$calc('pa_height');
        $facet['wei']=$calc('pa_weight');
    }

    wp_send_json_success([
        'mode'        => $mode,
        'html'        => $html,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $per_page,
        'total_pages' => $total_pages,
        'facets'      => $facet,
    ]);
}


/**
 *  Cart
 */

add_filter('template_include', function($template){
    if ( function_exists('is_cart') && is_cart() ) {
        $t = locate_template('pages/page-cart.php', false, false);
        if ($t) return $t;
    }
    return $template;
}, 20);


// =====================
// CART AJAX HANDLERS
// =====================
add_action('wp_ajax_nopriv_galeon_cart_update_qty', 'galeon_cart_update_qty');
add_action('wp_ajax_galeon_cart_update_qty',        'galeon_cart_update_qty');
function galeon_cart_update_qty(){
    if ( empty($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'galeon_cart_nonce') ) {
        wp_send_json_error(['message'=>'Bad nonce'], 403);
    }
    $key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $qty = max(0, intval($_POST['quantity'] ?? 0));

    $cart = WC()->cart;
    if (!$cart) wp_send_json_error(['message'=>'No cart'], 400);

    $items = $cart->get_cart();
    if ( !isset($items[$key]) ) wp_send_json_error(['message'=>'Item not found'], 404);

    // Yangilash
    $cart->set_quantity($key, $qty, true); // true => recalc totals
    $cart->calculate_totals();

    $removed = ($qty === 0) || !isset($cart->get_cart()[$key]);
    $line_html = '';
    if (!$removed) {
        $cart_item = $cart->get_cart()[$key];
        $_product  = $cart_item['data'];
        $line_html = $cart->get_product_subtotal($_product, $cart_item['quantity']);
        if ( $_product->get_price() === '' || $_product->get_price() === null ) {
            $line_html = '<span class="price-request">По запросу</span>';
        }
    }

    wp_send_json_success([
        'removed'            => $removed,
        'total_items'        => $cart->get_cart_contents_count(),
        'total_html'         => wc_price( (float) $cart->get_total('edit') ),
        'line_subtotal_html' => $line_html,
    ]);
}

add_action('wp_ajax_nopriv_galeon_cart_remove_item', 'galeon_cart_remove_item');
add_action('wp_ajax_galeon_cart_remove_item',        'galeon_cart_remove_item');
function galeon_cart_remove_item(){
    if ( empty($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'galeon_cart_nonce') ) {
        wp_send_json_error(['message'=>'Bad nonce'], 403);
    }
    $key  = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $cart = WC()->cart;
    if (!$cart) wp_send_json_error(['message'=>'No cart'], 400);

    $cart->remove_cart_item($key);
    $cart->calculate_totals();

    wp_send_json_success([
        'total_items' => $cart->get_cart_contents_count(),
        'total_html'  => wc_price( (float) $cart->get_total('edit') ),
    ]);
}

add_action('wp_ajax_nopriv_galeon_cart_clear', 'galeon_cart_clear');
add_action('wp_ajax_galeon_cart_clear',        'galeon_cart_clear');
function galeon_cart_clear(){
    if ( empty($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'galeon_cart_nonce') ) {
        wp_send_json_error(['message'=>'Bad nonce'], 403);
    }
    $cart = WC()->cart;
    if (!$cart) wp_send_json_error(['message'=>'No cart'], 400);

    $cart->empty_cart();
    $cart->calculate_totals();

    wp_send_json_success([
        'empty'       => true,
        'total_items' => 0,
        'total_html'  => wc_price(0),
    ]);
}

