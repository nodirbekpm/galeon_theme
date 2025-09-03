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


// 1) My Account’da ro‘yxatdan o‘tishni yoqish va parolni foydalanuvchi kiritishi
add_filter('woocommerce_enable_myaccount_registration', '__return_true');
add_filter('woocommerce_registration_generate_password', '__return_false');

// 2) My Account URL yasovchi helper: redirect_to + ixtiyoriy "register" tab
function galeon_myaccount_url($force_action = null)
{
    $base = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');

    if (!is_user_logged_in()) {
        // Avval referer (qaerdan kelgan bo‘lsa), bo‘lmasa joriy URL
        $ref = wp_get_referer();
        $scheme = is_ssl() ? 'https://' : 'http://';
        $current = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $target = $ref ?: $current;

        // My Account’ning o‘zini redirect_to qilmaymiz
        if (strpos($target, '/my-account') === false) {
            $base = add_query_arg('redirect_to', rawurlencode($target), $base);
        }

        // Mehmonlar uchun register tab’ini darhol ochish
        if ($force_action === 'register') {
            $base = add_query_arg('action', 'register', $base);
        }
    }

    return $base;
}

// 3) Login/ro‘yxatdan o‘tishdan keyin redirect_to ni hurmat qilish
add_filter('woocommerce_login_redirect', function ($redirect, $user) {
    if (!empty($_REQUEST['redirect_to'])) {
        return esc_url_raw($_REQUEST['redirect_to']);
    }
    return $redirect;
}, 10, 2);

add_filter('woocommerce_registration_redirect', function ($redirect) {
    if (!empty($_REQUEST['redirect_to'])) {
        return esc_url_raw($_REQUEST['redirect_to']);
    }
    return $redirect;
}, 10);


// (ixtiyoriy, lekin foydali) WooCommerce temaga moslashuvi
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
});

/**
 * WooCommerce CSS faqat My Account (login/registration) sahifasida ishlasin
 */
add_filter('woocommerce_enqueue_styles', function ($styles) {
    // Admin panelda cheklamaymiz
    if (is_admin()) {
        return $styles;
    }

    // Faqat My Account sahifasida (login/register) – default WC style’larini qoldiramiz
    if (function_exists('is_account_page') && is_account_page()) {
        // Ba'zi temalarda $styles bo'shatib yuborilishi mumkin — fallback
        if (empty($styles) || !is_array($styles)) {
            $ver = defined('WC_VERSION') ? WC_VERSION : null;
            $styles = [
                'woocommerce-general' => [
                    'src' => plugins_url('woocommerce/assets/css/woocommerce.css'),
                    'deps' => [],
                    'version' => $ver,
                    'media' => 'all',
                ],
                'woocommerce-layout' => [
                    'src' => plugins_url('woocommerce/assets/css/woocommerce-layout.css'),
                    'deps' => [],
                    'version' => $ver,
                    'media' => 'all',
                ],
                'woocommerce-smallscreen' => [
                    'src' => plugins_url('woocommerce/assets/css/woocommerce-smallscreen.css'),
                    'deps' => ['woocommerce-layout'],
                    'version' => $ver,
                    'media' => 'only screen and (max-width: 768px)',
                ],
            ];
        }
        return $styles;
    }

    // Qolgan barcha sahifalarda WC CSS’ni o‘chirib qo‘yish
    return [];
}, 20);

/**
 * Zaxira: agar boshqa plagin global tarzda WC CSS’ni ulab yuborsa — My Account’dan tashqarida dequeque qilamiz
 */
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_account_page') || !is_account_page()) {
        foreach (['woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen'] as $h) {
            if (wp_style_is($h, 'enqueued')) {
                wp_dequeue_style($h);
            }
        }
    }
}, 99);


// 1) "Last name" ni majburiy emas qilish (faqat My Account sahifasida)
add_filter('woocommerce_save_account_details_required_fields', function($fields){
    if (function_exists('is_account_page') && is_account_page()) {
        unset($fields['account_last_name']); // last name endi optional
        // Agar xohlasangiz first_name ni ham optional qiling:
        // unset($fields['account_first_name']);
    }
    return $fields;
}, 10);




/**
 * My Account parol xatolarini yumshatish:
 * - "Please fill out all password fields." ni olib tashlaydi
 * - "Please enter your current password..." / "Current password is incorrect" ni olib tashlaydi
 * - Faqat bitta yangi parol maydoni to'ldirilsa, parolni o'zgartirishni bekor qiladi (xato chiqarmaydi)
 */
add_filter('woocommerce_save_account_details_errors', function( $errors, $user ){
    // Faqat My Account sahifasi
    if ( ! function_exists('is_account_page') || ! is_account_page() ) {
        return $errors;
    }

    // 1-forma (Личная информация) deb aniqlash:
    // parol formasida password_1 yoki password_2 to‘ldiriladi. Agar ikkisi ham bo‘sh bo‘lsa — bu 1-forma.
    $pass1 = isset($_POST['password_1']) ? trim((string) $_POST['password_1']) : '';
    $pass2 = isset($_POST['password_2']) ? trim((string) $_POST['password_2']) : '';
    $is_password_form = ($pass1 !== '' || $pass2 !== '');

    if ( $is_password_form ) {
        // 2-forma (parolni almashtirish) — bu filtrlashni o‘tkazib yuboramiz
        return $errors;
    }

    // 1-forma: Woo'ning parolga oid default xabarlarini tozalaymiz
    $strip_contains = [
        'please fill out all password fields',
        'заполните все поля пароля',
        'please enter your current password',
        'введите текущий пароль',
        'current password is incorrect',
        'текущий пароль указан неверно',
    ];
    foreach ( $errors->get_error_codes() as $code ) {
        $msgs = $errors->get_error_messages($code);
        $keep = [];
        foreach ( (array) $msgs as $msg ) {
            $ml = mb_strtolower($msg);
            $hit = false;
            foreach ($strip_contains as $needle) {
                if ($needle !== '' && mb_stripos($ml, $needle) !== false) { $hit = true; break; }
            }
            if ( ! $hit ) { $keep[] = $msg; }
        }
        if ( empty($keep) ) {
            $errors->remove($code);
        } else {
            $errors->errors[$code] = $keep;
        }
    }

    // Email o‘zgarganmi?
    $old_email = isset($user->user_email) ? (string) $user->user_email : '';
    $new_email = isset($_POST['account_email']) ? sanitize_email( wp_unslash($_POST['account_email']) ) : '';
    $email_changed = ($new_email !== '' && strcasecmp($new_email, $old_email) !== 0);

    // Email o‘zgarmagan bo‘lsa — Woo "current password" talab qilmasin
    if ( ! $email_changed ) {
        $_POST['password_current'] = '';
        return $errors;
    }

    // Email o‘zgargan bo‘lsa — faqat biz kiritgan maydon talab qilinadi
    $cur = isset($_POST['galeon_current_password']) ? (string) $_POST['galeon_current_password'] : '';
    if ( $cur === '' ) {
        $errors->add('galeon_current_password_required', __('Для смены e-mail введите текущий пароль.', 'galeon'));
        return $errors;
    }
    if ( ! wp_check_password( $cur, $user->user_pass, $user->ID ) ) {
        $errors->add('galeon_current_password_incorrect', __('Текущий пароль указан неверно.', 'galeon'));
        return $errors;
    }

    return $errors;
}, 50, 2);





// Xatolarni matn bo'yicha filtrlovchi yordamchi
if ( ! function_exists('galeon_wc_strip_error_strings') ) {
    function galeon_wc_strip_error_strings( WP_Error $errors, array $needles ) {
        if ( empty( $errors->errors ) ) return $errors;
        $clean = new WP_Error();
        foreach ( $errors->errors as $code => $messages ) {
            foreach ( (array) $messages as $msg ) {
                $strip = false;
                foreach ( $needles as $needle ) {
                    if ( $needle !== '' && stripos( $msg, $needle ) !== false ) { $strip = true; break; }
                }
                if ( ! $strip ) {
                    $clean->add( $code, $msg );
                }
            }
        }
        $errors->errors     = $clean->errors;
        $errors->error_data = $clean->error_data;
        return $errors;
    }
}



// My Account: joriy parolsiz parol o'rnatish (faqat login bo'lgan user uchun)
add_action('template_redirect', function () {
    if (!is_user_logged_in()) return;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    if (empty($_POST['galeon_set_password'])) return; // faqat bizning forma
    if (!isset($_POST['galeon_set_password_nonce']) || !wp_verify_nonce($_POST['galeon_set_password_nonce'], 'galeon_set_password')) {
        wc_add_notice(__('Неверный запрос. Обновите страницу и повторите.', 'your-td'), 'error');
        return;
    }

    $pass1 = isset($_POST['password_1']) ? trim((string) $_POST['password_1']) : '';
    $pass2 = isset($_POST['password_2']) ? trim((string) $_POST['password_2']) : '';

    if ($pass1 === '' || $pass2 === '') {
        wc_add_notice(__('Введите и подтвердите новый пароль.', 'your-td'), 'error');
        return;
    }
    if ($pass1 !== $pass2) {
        wc_add_notice(__('Пароли не совпадают.', 'your-td'), 'error');
        return;
    }

    $user_id = get_current_user_id();

    // Parolni o'rnatamiz (bu odatda sessiyani bekor qiladi)
    wp_set_password($pass1, $user_id);

    // "temporary password" flaglarini o'chirish
    delete_user_meta($user_id, 'default_password_nag');
    delete_user_meta($user_id, 'wc_pending_password_reset');
    delete_user_meta($user_id, 'wc_force_password_change');

    // Foydalanuvchini qayta avtorizatsiya qilamiz
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);

    // Woo sessiya cookie (ixtiyoriy, barqarorlik uchun)
    if (function_exists('WC')) {
        WC()->session->set_customer_session_cookie(true);
    }

    wc_add_notice(__('Пароль успешно обновлён.', 'your-td'), 'success');

    // My Account sahifasiga qaytamiz
    $redirect = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
    wp_safe_redirect($redirect);
    exit;
});


// My Account: foydalanuvchining o'z akkauntini o'chirish (joriy user uchun)
add_action('template_redirect', function () {
    if (!is_user_logged_in()) return;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (empty($_POST['galeon_delete_account'])) return;

    // Nonce tekshiruvi
    if (
        !isset($_POST['galeon_delete_account_nonce']) ||
        !wp_verify_nonce($_POST['galeon_delete_account_nonce'], 'galeon_delete_account')
    ) {
        wc_add_notice(__('Неверный запрос. Попробуйте ещё раз.', 'your-td'), 'error');
        wp_safe_redirect( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/') );
        exit;
    }

    $user_id = get_current_user_id();

    // Admin/shop-managerlarni o'chirishga ruxsat bermaymiz
    if (user_can($user_id, 'manage_options') || user_can($user_id, 'delete_users')) {
        wc_add_notice(__('Удаление этого аккаунта запрещено.', 'your-td'), 'error');
        wp_safe_redirect( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/') );
        exit;
    }

    // Shu so'rov uchun "o'zini o'chirish"ga vaqtincha ruxsat
    $allow_self_delete = function($caps, $cap, $user_check_id, $args) use ($user_id) {
        if ($cap === 'delete_user' && isset($args[0]) && (int)$args[0] === (int)$user_id) {
            return array('read');
        }
        return $caps;
    };

    add_filter('map_meta_cap', $allow_self_delete, 10, 4);
    require_once ABSPATH . 'wp-admin/includes/user.php';

    $deleted = wp_delete_user($user_id); // Kontentni o'chiradi; xohlasangiz reassign ID bering

    remove_filter('map_meta_cap', $allow_self_delete, 10);

    if (!$deleted) {
        wc_add_notice(__('Не удалось удалить аккаунт. Повторите попытку.', 'your-td'), 'error');
        wp_safe_redirect( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/') );
        exit;
    }

    // Sessiyani tozalash
    if (function_exists('WC') && WC()->session) {
        WC()->session->destroy_session();
    }
    wp_destroy_current_session();
    wp_clear_auth_cookie();

    // Muvaffaqiyat — bosh sahifaga, SweetAlert ko'rsatish uchun flag
    $to = add_query_arg('account_deleted', 1, home_url('/'));
    wp_safe_redirect($to);
    exit;
});


add_action('wp_footer', function () {
    if (!empty($_GET['account_deleted'])) : ?>
        <script>
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Аккаунт удалён',
                    text: 'Мы удалили ваш аккаунт и завершили сессию.',
                    showConfirmButton: false,
                    timer: 2200
                });
            }
        </script>
    <?php endif;
});


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
// 1) Yoast breadcrumb linklarini universal tarzda sozlash:
//    - Home/Shop tarjima
//    - Barcha CPT arxivlari uchun matnni labels->archives (fallback: labels->name) dan olish
add_filter('wpseo_breadcrumb_links', function($links){
    // CPT arxiv URL -> Label xaritasi
    $pt_map = [];
    $pts = get_post_types(['public' => true], 'objects');
    foreach ($pts as $pt => $obj) {
        if (!empty($obj->has_archive)) {
            $url = get_post_type_archive_link($pt);
            if ($url) {
                $label = !empty($obj->labels->archives)
                    ? $obj->labels->archives
                    : (!empty($obj->labels->name) ? $obj->labels->name : $obj->label);
                // Taqqoslash qulay bo‘lishi uchun slashes’li va slashes’siz variantlarni ham saqlaymiz
                $pt_map[trailingslashit($url)]   = $label;
                $pt_map[untrailingslashit($url)] = $label;
            }
        }
    }

    foreach ($links as &$l) {
        // Home / Shop sarlavhalarini almashtirish
        if (isset($l['text']) && $l['text'] === 'Home') { $l['text'] = 'Главная'; }
        if (isset($l['text']) && $l['text'] === 'Shop') { $l['text'] = 'Каталог'; }

        // Agar bu bo‘lak CPT arxiviga tegishli bo‘lsa — labelni CPT'ning archives/name’dan o‘rnatamiz
        if (!empty($l['url'])) {
            $u = rtrim($l['url'], '/');
            foreach ($pt_map as $archive_url => $label) {
                if (rtrim($archive_url, '/') === $u) {
                    $l['text'] = $label;
                    break;
                }
            }
        }
    }
    return $links;
});

// 2) Separator ni <span>/</span> ko‘rinishida chiqarish
add_filter('wpseo_breadcrumb_separator', function($sep){
    return ' <span>/</span> ';
});

// 3) Oxirgi bo‘lakni <a class="active"> qilish
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
//    wp_register_script('theme-wishlist', get_template_directory_uri().'/assets/js/wishlist.js', ['jquery'], null, true);
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
 *  Basket
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

// Cart URL'ini ishonchli olish (fallbacklar bilan)
// Barqaror Cart URL (faqat published sahifa, __trashed bo'lsa chetlanadi)
function galeon_cart_url() {
    // 1) Woo sozlamasidagi Cart page ID
    if ( function_exists('wc_get_page_id') ) {
        $cart_id = wc_get_page_id('cart');
        if ( $cart_id && $cart_id > 0 && get_post_status($cart_id) === 'publish' ) {
            // Published bo'lsa, bevosita permalink
            $perma = get_permalink($cart_id);
            if ( $perma ) return $perma;
        }
    }

    // 2) Published bo'lgan, ichida [woocommerce_cart] bo'lgan sahifani qidiramiz
    $q = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        's'              => '[woocommerce_cart]',
        'no_found_rows'  => true,
    ]);
    if ( $q->have_posts() ) {
        $page = $q->posts[0];
        $url  = get_permalink($page->ID);
        wp_reset_postdata();
        if ( $url ) return $url;
    }

    // 3) Slug bo'yicha published sahifa (agar nomi 'cart' bo'lsa)
    $by_path = get_page_by_path('cart', OBJECT, 'page');
    if ( $by_path && get_post_status($by_path->ID) === 'publish' ) {
        $u = get_permalink($by_path->ID);
        if ( $u ) return $u;
    }

    // 4) Oxirgi fallback
    return home_url('/cart/');
}


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




// === WISHLIST: AJAX renderer (login va guest uchun) ===
add_action('wp_ajax_nopriv_galeon_wishlist_render', 'galeon_wishlist_render');
add_action('wp_ajax_galeon_wishlist_render',        'galeon_wishlist_render');

function galeon_wishlist_render() {
    // nonce
    if ( empty($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'wishlist_nonce') ) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }

    // LS'dan kelgan items (guest yoki login, ikkala holatda ham yuboramiz)
    $raw_items = [];
    if (isset($_POST['items'])) {
        $json     = is_array($_POST['items']) ? wp_json_encode($_POST['items']) : wp_unslash($_POST['items']);
        $raw_items= json_decode($json, true) ?: [];
    }

    // sanitize + de-dupe
    $seen = [];
    $ls_items = [];
    foreach ((array)$raw_items as $it) {
        $pid = isset($it['pid']) ? absint($it['pid']) : 0;
        $vid = isset($it['vid']) ? absint($it['vid']) : 0;
        if (!$pid) continue;
        $k = $pid . ':' . $vid;
        if (isset($seen[$k])) continue;
        $seen[$k] = true;
        $ls_items[] = ['pid'=>$pid, 'vid'=>$vid, 'ts'=> isset($it['ts']) ? intval($it['ts']) : time()];
    }

    $items_to_render = [];

    if ( is_user_logged_in() ) {
        // LOGIN: LS -> user_meta MERGE, so‘ng user_meta’dan render
        $user_id = get_current_user_id();
        $current = get_user_meta($user_id, 'wishlist_v1', true);
        $current = is_array($current) ? $current : [];

        // birlashtirish + de-dupe
        $all = array_merge($current, $ls_items);

        // de-dupe by pid:vid
        $seen2 = [];
        $merged = [];
        foreach ($all as $it) {
            $pid = isset($it['pid']) ? absint($it['pid']) : 0;
            $vid = isset($it['vid']) ? absint($it['vid']) : 0;
            if (!$pid) continue;
            $key = $pid . ':' . $vid;
            if (isset($seen2[$key])) continue;
            $seen2[$key] = true;
            $merged[] = [
                'pid' => $pid,
                'vid' => $vid,
                'ts'  => isset($it['ts']) ? intval($it['ts']) : time(),
            ];
        }

        update_user_meta($user_id, 'wishlist_v1', $merged);
        $items_to_render = $merged;

    } else {
        // GUEST: faqat LS’dan render
        $items_to_render = $ls_items;
    }

    // HTML yig‘amiz
    ob_start();
    if ($items_to_render) {
        foreach ($items_to_render as $it) {
            $pid = $it['pid'];
            $vid = $it['vid'];
            $product = $vid ? wc_get_product($vid) : wc_get_product($pid);
            if ( ! $product ) continue;

            get_template_part('template-parts/product/wishlist-item', null, [
                'product'      => $product,
                'parent_id'    => $pid,
                'variation_id' => $vid,
            ]);
        }
    } else {
        echo '<p class="empty">Список избранного пуст.</p>';
    }
    $html = ob_get_clean();

    wp_send_json_success([
        'html'  => $html,
        'count' => count($items_to_render),
    ]);
}
