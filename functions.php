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


