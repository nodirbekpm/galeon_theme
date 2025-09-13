<?php
/**
 * Theme Functions
 * Galeon Custom Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function galeon_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'gallery', 'caption'));

    register_nav_menus(array(
        'header_menu' => 'Header Menu',
        'footer_menu' => 'Footer Menu',
    ));

    add_image_size('service_thumb', 400, 300, true);
    add_image_size('team_member', 300, 300, true);
}

add_action('after_setup_theme', 'galeon_theme_setup');

/**
 * Enqueue scripts and styles
 */
add_action('wp_enqueue_scripts', function () {
    // scripts.js ulangan bo'lsin (agar allaqachon ulangan bo'lsa, bu qatorni tashlab ketishingiz mumkin)
    wp_enqueue_script(
        'site-scripts',
        get_stylesheet_directory_uri() . '/assets/js/scripts.js',
        [], // kerak bo'lsa ['jquery']
        null,
        true
    );

    // ACF options dan o'qish
    $office = get_field('office_address', 'option') ?: [];
    $warehouse = get_field('warehouse_address', 'option') ?: [];

    // Fallbacklar (sizdagi oldingi konstantalarga mos)
    $office_lat = isset($office['lat']) ? (float)$office['lat'] : 55.755348;
    $office_lng = isset($office['lng']) ? (float)$office['lng'] : 37.759533;
    $warehouse_lat = isset($warehouse['lat']) ? (float)$warehouse['lat'] : 55.748838;
    $warehouse_lng = isset($warehouse['lng']) ? (float)$warehouse['lng'] : 37.757386;

    // JS ga yuboramiz
    wp_localize_script('site-scripts', 'MAP_CFG', [
        'office' => ['lat' => $office_lat, 'lng' => $office_lng],
        'warehouse' => ['lat' => $warehouse_lat, 'lng' => $warehouse_lng],
    ]);
}, 20); // 20 — boshqa enqueue'lardan keyin ishga tushsin

/**
 * Contact form 7
 */
// 1) CF7 formda <p> va <br> qo'shilmasin
add_filter('wpcf7_use_p_tag_in_form', '__return_false');
add_filter('wpcf7_use_br_tag_in_form', '__return_false');
add_filter('wpcf7_autop_or_not', '__return_false');

// 2) CF7 qo'yadigan <span class="wpcf7-form-control-wrap ..."> WRAP ni olib tashlaymiz
add_filter('wpcf7_form_elements', function ($html) {
    return preg_replace('/<span class="wpcf7-form-control-wrap[^"]*?">(.*?)<\/span>/s', '$1', $html);
});

// 3) Inputlarga CF7 ning default classlarini qo'shmaslik (faqat siz bergan class:… qoladi)
// CF7 5.4+ da $tag obyektida get_class_option() bor
add_filter('wpcf7_form_tag_class', function ($class, $tag) {
    // Foydalanuvchi teglarda ko'rsatgan class’lar (masalan class:my-input) qoladi
    $user_classes = method_exists($tag, 'get_class_option') ? $tag->get_class_option() : [];
    $user_classes = is_array($user_classes) ? implode(' ', $user_classes) : trim((string)$user_classes);
    return trim($user_classes);
}, 10, 2);
// —— CF7 custom form tag: [assets_pdf_url]
add_action('wpcf7_init', function () {
    if (function_exists('wpcf7_add_form_tag')) {
        wpcf7_add_form_tag('assets_pdf_url', function ($tag) {
            // ACF'dan olish (options page). FIELD nomini o'zingiznikiga moslang:
            $val = function_exists('get_field') ? get_field('personal_data_pdf', 'option') : '';

            // ACF File field turiga mos qaytish (array/url)
            if (is_array($val) && !empty($val['url'])) {
                $url = $val['url'];
            } else {
                $url = is_string($val) ? $val : '';
            }

            // Agar ACF da nisbiy yo'l saqlasangiz: "assets/documents/..." — absolute ga aylantiramiz
            if ($url && strpos($url, 'http') !== 0) {
                $url = trailingslashit(get_stylesheet_directory_uri()) . ltrim($url, '/');
            }

            // Fallback: temadagi default fayl
            if (!$url) {
                $url = trailingslashit(get_stylesheet_directory_uri()) . 'assets/documents/Personal_Data_Processing_Extended.pdf';
            }

            return esc_url($url);
        }, ['name-attr' => false]);
    }
});

// CF7 yuborilganda Flamingo'ga yozamiz (agar o'zi yozmayotgan bo'lsa)
add_action('wpcf7_mail_sent', function ($contact_form) {
    if (!class_exists('Flamingo_Inbound_Message')) return;

    $submission = WPCF7_Submission::get_instance();
    if (!$submission) return;

    $posted = $submission->get_posted_data();

    // Maydon nomlarini formangizga moslang:
    $name = $posted['your-name'] ?? ($posted['name'] ?? '');
    $phone = $posted['your-phone'] ?? ($posted['phone'] ?? '');
    $msg = $posted['your-comment'] ?? ($posted['message'] ?? '');

    $subject = sprintf('CF7: %s', $contact_form->title());

    Flamingo_Inbound_Message::add([
        'channel' => 'contact-form-7',
        'subject' => $subject,
        'from' => trim($name . ' ' . $phone),
        'from_email' => '',                 // ixtiyoriy: agar email maydoni bo'lsa shu yerga qo'ying
        'message' => $msg,
        'fields' => $posted,            // hamma jo‘natilgan maydonlar
        'meta' => ['form_id' => $contact_form->id()],
    ]);
}, 10, 1);


// WooCommerce qo'llab-quvvatlash
function galeon_add_woocommerce_support()
{
    add_theme_support('woocommerce');
}

add_action('after_setup_theme', 'galeon_add_woocommerce_support');

// WooCommerce default style'larni o'chirish
add_filter('woocommerce_enqueue_styles', '__return_empty_array');


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
add_filter('woocommerce_save_account_details_required_fields', function ($fields) {
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
add_filter('woocommerce_save_account_details_errors', function ($errors, $user) {
    // Faqat My Account sahifasi
    if (!function_exists('is_account_page') || !is_account_page()) {
        return $errors;
    }

    // 1-forma (Личная информация) deb aniqlash:
    // parol formasida password_1 yoki password_2 to‘ldiriladi. Agar ikkisi ham bo‘sh bo‘lsa — bu 1-forma.
    $pass1 = isset($_POST['password_1']) ? trim((string)$_POST['password_1']) : '';
    $pass2 = isset($_POST['password_2']) ? trim((string)$_POST['password_2']) : '';
    $is_password_form = ($pass1 !== '' || $pass2 !== '');

    if ($is_password_form) {
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
    foreach ($errors->get_error_codes() as $code) {
        $msgs = $errors->get_error_messages($code);
        $keep = [];
        foreach ((array)$msgs as $msg) {
            $ml = mb_strtolower($msg);
            $hit = false;
            foreach ($strip_contains as $needle) {
                if ($needle !== '' && mb_stripos($ml, $needle) !== false) {
                    $hit = true;
                    break;
                }
            }
            if (!$hit) {
                $keep[] = $msg;
            }
        }
        if (empty($keep)) {
            $errors->remove($code);
        } else {
            $errors->errors[$code] = $keep;
        }
    }

    // Email o‘zgarganmi?
    $old_email = isset($user->user_email) ? (string)$user->user_email : '';
    $new_email = isset($_POST['account_email']) ? sanitize_email(wp_unslash($_POST['account_email'])) : '';
    $email_changed = ($new_email !== '' && strcasecmp($new_email, $old_email) !== 0);

    // Email o‘zgarmagan bo‘lsa — Woo "current password" talab qilmasin
    if (!$email_changed) {
        $_POST['password_current'] = '';
        return $errors;
    }

    // Email o‘zgargan bo‘lsa — faqat biz kiritgan maydon talab qilinadi
    $cur = isset($_POST['galeon_current_password']) ? (string)$_POST['galeon_current_password'] : '';
    if ($cur === '') {
        $errors->add('galeon_current_password_required', __('Для смены e-mail введите текущий пароль.', 'galeon'));
        return $errors;
    }
    if (!wp_check_password($cur, $user->user_pass, $user->ID)) {
        $errors->add('galeon_current_password_incorrect', __('Текущий пароль указан неверно.', 'galeon'));
        return $errors;
    }

    return $errors;
}, 50, 2);


// Xatolarni matn bo'yicha filtrlovchi yordamchi
if (!function_exists('galeon_wc_strip_error_strings')) {
    function galeon_wc_strip_error_strings(WP_Error $errors, array $needles)
    {
        if (empty($errors->errors)) return $errors;
        $clean = new WP_Error();
        foreach ($errors->errors as $code => $messages) {
            foreach ((array)$messages as $msg) {
                $strip = false;
                foreach ($needles as $needle) {
                    if ($needle !== '' && stripos($msg, $needle) !== false) {
                        $strip = true;
                        break;
                    }
                }
                if (!$strip) {
                    $clean->add($code, $msg);
                }
            }
        }
        $errors->errors = $clean->errors;
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

    $pass1 = isset($_POST['password_1']) ? trim((string)$_POST['password_1']) : '';
    $pass2 = isset($_POST['password_2']) ? trim((string)$_POST['password_2']) : '';

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
        wp_safe_redirect(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'));
        exit;
    }

    $user_id = get_current_user_id();

    // Admin/shop-managerlarni o'chirishga ruxsat bermaymiz
    if (user_can($user_id, 'manage_options') || user_can($user_id, 'delete_users')) {
        wc_add_notice(__('Удаление этого аккаунта запрещено.', 'your-td'), 'error');
        wp_safe_redirect(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'));
        exit;
    }

    // Shu so'rov uchun "o'zini o'chirish"ga vaqtincha ruxsat
    $allow_self_delete = function ($caps, $cap, $user_check_id, $args) use ($user_id) {
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
        wp_safe_redirect(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'));
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



add_filter('woocommerce_save_account_details_errors', function (WP_Error $errors, $user) {
    if (!function_exists('is_account_page') || !is_account_page()) return $errors;

    $dn = isset($_POST['account_display_name']) ? trim(wp_unslash($_POST['account_display_name'])) : '';
    $em = isset($_POST['account_email']) ? sanitize_email(wp_unslash($_POST['account_email'])) : '';

    if ($dn !== '' && $em !== '' && strcasecmp($dn, $em) === 0) {
        $needles = [
            'Display name cannot be changed to email address due to privacy concern',
            'Display name cannot be changed to email address',
            'privacy concern',
        ];

        // WP_Error ichidan mos xabarlarni olib tashlash
        $clean = new WP_Error();
        foreach ($errors->errors as $code => $msgs) {
            $keep = [];
            foreach ((array)$msgs as $msg) {
                $drop = false;
                foreach ($needles as $n) {
                    if ($n !== '' && stripos($msg, $n) !== false) { $drop = true; break; }
                }
                if (!$drop) $keep[] = $msg;
            }
            if (!empty($keep)) {
                foreach ($keep as $m) $clean->add($code, $m);
            }
        }
        $errors->errors     = $clean->errors;
        $errors->error_data = $clean->error_data;
    }
    return $errors;
}, 100000, 2);

// 2) Agar kimdir WP darajasida ham xato qo‘yayotgan bo‘lsa — uni ham tozalaymiz
add_filter('user_profile_update_errors', function (WP_Error $errors) {
    // faqat foydalanuvchi o‘zi My Account'da o‘zgartirayotganda
    if (!is_user_logged_in() || (function_exists('is_account_page') && !is_account_page())) return $errors;

    $dn = isset($_POST['account_display_name']) ? trim(wp_unslash($_POST['account_display_name'])) : '';
    $em = isset($_POST['account_email']) ? sanitize_email(wp_unslash($_POST['account_email'])) : '';

    if ($dn !== '' && $em !== '' && strcasecmp($dn, $em) === 0) {
        $needles = [
            'Display name cannot be changed to email address due to privacy concern',
            'Display name cannot be changed to email address',
            'privacy concern',
        ];
        $clean = new WP_Error();
        foreach ($errors->errors as $code => $msgs) {
            foreach ((array)$msgs as $msg) {
                $drop = false;
                foreach ($needles as $n) {
                    if ($n !== '' && stripos($msg, $n) !== false) { $drop = true; break; }
                }
                if (!$drop) $clean->add($code, $msg);
            }
        }
        $errors->errors     = $clean->errors;
        $errors->error_data = $clean->error_data;
    }
    return $errors;
}, 100000, 1);

// 3) "Oldindan" qiymatni aynan post’dan olish (kimdir o‘zgartirsa ham, biznikini qaytaramiz)
add_filter('pre_user_display_name', function ($value) {
    if (function_exists('is_account_page') && is_account_page() && isset($_POST['account_display_name'])) {
        return wp_kses_data(wp_unslash($_POST['account_display_name']));
    }
    return $value;
}, 100000);

// 4) Saqlash bosqichida display_name va nickname’ni first/last’dan yasab, majburan yozamiz
add_action('woocommerce_save_account_details', function ($user_id) {
    if (!is_user_logged_in() || (int)$user_id !== get_current_user_id()) return;

    $fn = isset($_POST['account_first_name']) ? trim(wp_unslash($_POST['account_first_name'])) : '';
    $ln = isset($_POST['account_last_name'])  ? trim(wp_unslash($_POST['account_last_name']))  : '';
    $em = isset($_POST['account_email'])      ? sanitize_email(wp_unslash($_POST['account_email'])) : '';
    $dn_post = isset($_POST['account_display_name']) ? trim(wp_unslash($_POST['account_display_name'])) : '';

    $is_info_form = isset($_POST['galeon_form']) && $_POST['galeon_form'] === 'info';

    $dn_candidate = trim($fn . ' ' . $ln);
    if ($dn_candidate === '') $dn_candidate = ($dn_post !== '' ? $dn_post : $em);
    if ($dn_candidate === '') return;

    $dn_sanitized = wp_kses_data($dn_candidate);

    if ($is_info_form) {
        if ($fn !== '') update_user_meta($user_id, 'first_name', $fn); else delete_user_meta($user_id, 'first_name');
        if ($ln !== '') update_user_meta($user_id, 'last_name',  $ln); else delete_user_meta($user_id, 'last_name');
    }

    wp_update_user([
        'ID'           => $user_id,
        'display_name' => $dn_sanitized,
        'nickname'     => $dn_sanitized,
    ]);
}, 100000);



// My Account: display_name = email bo'lsa, validatsiyadan OLDIN "yaxshi" qiymatga almashtiramiz
add_action('init', function () {
    if (empty($_POST['action']) || $_POST['action'] !== 'save_account_details') return;
    if (!is_user_logged_in()) return;

    $em = isset($_POST['account_email']) ? sanitize_email(wp_unslash($_POST['account_email'])) : '';
    $dn = isset($_POST['account_display_name']) ? trim(wp_unslash($_POST['account_display_name'])) : '';
    if ($em === '' || $dn === '') return;

    // display_name emailga aynan teng bo'lsa — almashtiramiz
    if (strcasecmp($dn, $em) === 0) {
        $fn = isset($_POST['account_first_name']) ? trim(wp_unslash($_POST['account_first_name'])) : '';
        $ln = isset($_POST['account_last_name'])  ? trim(wp_unslash($_POST['account_last_name']))  : '';
        $alt = trim($fn . ' ' . $ln);
        if ($alt === '') {
            // emailning @ gacha bo'lgan qismi
            $alt = strstr($em, '@', true);
            if (!$alt) $alt = 'User';
        }
        $_POST['account_display_name'] = $alt;
    }
}, 1);



/**
 * CHECKOUT (minimal, to'lovsiz)
 */

/* 0) Place Order tugmasi matni (agar Woo tugmasi ishlatilsa) */
add_filter('woocommerce_order_button_text', function () {
    return 'Оставить заявку';
});

/* 1) Kuponlarni o'chirish (banner ham ketadi) */
add_filter('woocommerce_coupons_enabled', '__return_false');
add_action('init', function () {
    remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
}, 20);

/* 2) Minimal "nopay" gateway — klassni aynan shu yerning o'zida e'lon qilamiz */
add_filter('woocommerce_payment_gateways', function ($gateways) {
    if (class_exists('WC_Payment_Gateway') && !class_exists('WC_Gateway_Nopay')) {

        class WC_Gateway_Nopay extends WC_Payment_Gateway
        {
            public function __construct()
            {
                $this->id = 'nopay';
                $this->method_title = 'Без оплаты';
                $this->method_description = 'Оформление без онлайн-оплаты (менеджер свяжется).';
                $this->title = 'Без оплаты';
                $this->enabled = 'yes';
                $this->has_fields = false;
                $this->supports = ['products'];
            }

            public function init_form_fields()
            {
                $this->form_fields = [];
            }

            public function is_available()
            {
                return true;
            }

            public function process_payment($order_id)
            {
                $order = wc_get_order($order_id);

                if (function_exists('wc_reduce_stock_levels')) wc_reduce_stock_levels($order_id);
                $order->update_status('on-hold', 'Оформлено без онлайн-оплаты.');
                if (WC()->cart) WC()->cart->empty_cart();

                // ⬇️ THANK YOU o'rniga checkout sahifasiga qaytamiz, flag bilan
                $redirect = add_query_arg(
                    ['order_ok' => 1, 'oid' => $order->get_id()],
                    wc_get_checkout_url()
                );

                return ['result' => 'success', 'redirect' => $redirect];
            }
        }
    }

    // Gateway ro'yxatiga qo'shamiz
    if (class_exists('WC_Gateway_Nopay')) {
        $gateways[] = 'WC_Gateway_Nopay';
    }
    return $gateways;
});

/* 3) Default gateway sifatida 'nopay' */
add_filter('woocommerce_default_gateway', function () {
    return 'nopay';
});

/* 4) Checkout maydonlarini soddalashtirish: faqat Ism/Telefon majburiy */
add_filter('woocommerce_checkout_fields', function ($fields) {
    // Billing
    foreach ($fields['billing'] as &$f) {
        $f['required'] = false;
    }
    if (isset($fields['billing']['billing_first_name'])) $fields['billing']['billing_first_name']['required'] = true;
    if (isset($fields['billing']['billing_phone'])) $fields['billing']['billing_phone']['required'] = true;
    if (isset($fields['billing']['billing_email'])) $fields['billing']['billing_email']['required'] = false;

    // Shipping — barchasi optional; courier tanlanganda serverda tekshiramiz
    if (!empty($fields['shipping'])) {
        foreach ($fields['shipping'] as &$f) {
            $f['required'] = false;
        }
    }
    // Order comments optional
    if (isset($fields['order']['order_comments'])) $fields['order']['order_comments']['required'] = false;

    return $fields;
}, 20);

/* 5) Server-side validatsiya: courier bo'lsa city/street majburiy */
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    $method = isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'manager';

    if (empty($_POST['billing_first_name'])) $errors->add('billing_first_name', 'Укажите имя.');
    if (empty($_POST['billing_phone'])) $errors->add('billing_phone', 'Укажите телефон.');

    if ($method === 'courier') {
        if (empty($_POST['shipping_city'])) $errors->add('shipping_city', 'Укажите город доставки.');
        if (empty($_POST['shipping_address_1'])) $errors->add('shipping_address_1', 'Укажите улицу и дом.');
    }
}, 10, 2);

/* 6) Buyurtmaga saqlash (shipping va custom meta) */
add_action('woocommerce_checkout_create_order', function ($order, $data) {
    $method = isset($_POST['delivery_method']) ? sanitize_text_field($_POST['delivery_method']) : 'manager';
    $order->update_meta_data('_delivery_method', $method);

    if ($method === 'courier') {
        if (!empty($_POST['shipping_city'])) $order->set_shipping_city(sanitize_text_field($_POST['shipping_city']));
        if (!empty($_POST['shipping_address_1'])) $order->set_shipping_address_1(sanitize_text_field($_POST['shipping_address_1']));

        foreach ([
                     '_delivery_building' => 'delivery_building',
                     '_delivery_apartment' => 'delivery_apartment',
                     '_delivery_entrance' => 'delivery_entrance',
                     '_delivery_floor' => 'delivery_floor',
                 ] as $meta_key => $post_key) {
            if (!empty($_POST[$post_key])) {
                $order->update_meta_data($meta_key, sanitize_text_field($_POST[$post_key]));
            }
        }
    } elseif ($method === 'pickup') {
        $order->update_meta_data('_pickup_address', 'Электродная улица, 13с2А');
    }
}, 10, 2);

/* 7) Admin buyurtma sahifasida ko'rsatish (infoga qulay) */
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    $method = $order->get_meta('_delivery_method');
    if (!$method) return;

    echo '<div class="order-delivery-meta">';
    echo '<p><strong>Способ доставки:</strong> ' . esc_html($method) . '</p>';

    if ($method === 'courier') {
        $pairs = [
            'Корпус' => '_delivery_building',
            'Квартира' => '_delivery_apartment',
            'Подъезд' => '_delivery_entrance',
            'Этаж' => '_delivery_floor',
        ];
        foreach ($pairs as $label => $key) {
            $v = $order->get_meta($key);
            if ($v) echo '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html($v) . '</p>';
        }
    }

    if ($method === 'pickup') {
        $addr = $order->get_meta('_pickup_address');
        if ($addr) echo '<p><strong>Адрес самовывоза:</strong> ' . esc_html($addr) . '</p>';
    }
    echo '</div>';
}, 10);


/** =========================
 *  SITE SETTINGS via ACF
 *  ========================= */
add_action('init', function () {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Site Settings',
            'menu_title' => 'Site Settings',
            'menu_slug' => 'site-settings',   // xohlasangiz 'site_settings' deb ham qo'yishingiz mumkin
            'capability' => 'manage_options',
            'redirect' => false,
            'position' => 60,
            'icon_url' => 'dashicons-admin-generic',
        ]);
    }
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
 *  EMAIL (WP Mail SMTP orqali)
 */
/* ================== FROM: Galeon <no-reply@your-domain> ================== */
// From Name (WP va Woo ham)
add_filter('wp_mail_from_name', function ($name) {
    return 'Галеон Кейсы';
}, 20);

add_filter('woocommerce_email_from_name', function ($name) {
    return 'Галеон Кейсы';
}, 20);

// From Email (WP)
add_filter('wp_mail_from', function ($email) {
    $host = parse_url(home_url(), PHP_URL_HOST);
    if (!$host) return $email; // fallback
    $from = sanitize_email('no-reply@' . $host);
    return $from ?: $email;    // agar yaroqsiz bo'lsa, mavjudini qoldiramiz
}, 20);

// From Email (Woo) — admin paneldagisini ham bosib ketadi
add_filter('woocommerce_email_from_address', function ($address) {
    $host = parse_url(home_url(), PHP_URL_HOST);
    if (!$host) return $address;
    $from = sanitize_email('no-reply@' . $host);
    return $from ?: $address;
}, 20);

// Ba'zi serverlar Return-Path (Sender) talab qiladi — PHP mail()da muhim
add_action('phpmailer_init', function ($phpmailer) {
    if (!empty($phpmailer->From) && empty($phpmailer->Sender)) {
        $phpmailer->Sender = $phpmailer->From; // Return-Path ni From ga tenglaymiz
    }
});


/**
 * Breadcrumb
 */
// 1) Yoast breadcrumb linklarini universal tarzda sozlash:
//    - Home/Shop tarjima
//    - Barcha CPT arxivlari uchun matnni labels->archives (fallback: labels->name) dan olish
add_filter('wpseo_breadcrumb_links', function ($links) {
    // Home linkni yangilaymiz yoki qo‘shamiz
    if (!empty($links)) {
        // Eski "Home" linkni olib tashlab, o‘rniga "Главная страница" qo‘shamiz
        $home_link = [
            'url'  => home_url('/'),
            'text' => 'Главная страница',
        ];

        // Birinchi elementni tekshirib, almashtiramiz yoki yangisini boshiga qo‘shamiz
        if (isset($links[0]) && $links[0]['url'] === home_url('/')) {
            $links[0] = $home_link;
        } else {
            array_unshift($links, $home_link);
        }
    }

    // Faqat WooCommerce shop arxivi uchun – oxirgi elementni "Каталог" deb o‘zgartiramiz
    if ((function_exists('is_shop') && is_shop()) || is_post_type_archive('product')) {
        if (!empty($links)) {
            $last = count($links) - 1;
            $links[$last]['text'] = 'Каталог';
        }
    }

    return $links;
}, 20);


// 2) Separator ni <span>/</span> ko‘rinishida chiqarish
add_filter('wpseo_breadcrumb_separator', function ($sep) {
    return ' <span>/</span> ';
});

// 3) Oxirgi bo‘lakni <a class="active"> qilish
add_filter('wpseo_breadcrumb_single_link', function ($link_output, $link) {
    // Yoast oxirgi bo‘lakka 'breadcrumb_last' classli <span> beradi — uni <a class="active"> ga almashtiramiz
    if (strpos($link_output, 'breadcrumb_last') !== false) {
        $url = !empty($link['url']) ? $link['url'] : get_permalink();
        $text = isset($link['text']) ? $link['text'] : '';
        return '<a href="' . esc_url($url) . '" class="active">' . $text . '</a>';
    }
    return $link_output;
}, 10, 2);

// 4) Ortiqcha wrapper <span> larni olib tashlash (faqat <a> va <span>/</span> qolsin)
add_filter('wpseo_breadcrumb_output', function ($output) {
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
function render_product_characteristics($product = null)
{
    if (!$product) {
        $product = wc_get_product(get_the_ID());
    }
    if (!$product) return;

    // 1) Avval belgilangan tartib:
    //    slug => [Label (RU), unit_suffix (bo‘sh bo‘lsa qo‘shilmaydi)]
    $ordered_map = [
        'pa_color' => ['Цвет', ''],
        'pa_length' => ['Внутренняя длина', ' мм'],
        'pa_width' => ['Внутренняя ширина', ' мм'],
        'pa_height' => ['Внутренняя высота', ' мм'],
        'pa_weight' => ['Вес', ' кг'],
        'pa_variant' => ['Вариант', ''],
    ];

    // Qaysi slug’lar ko‘rsatib bo‘lindi — keyin dublikat bo‘lmasin
    $shown = [];

    echo '<div class="characteristics">';

    // Helper: qiymatni olish (taxonomy yoki custom)
    $get_attr_value = function ($slug) use ($product) {
        // WooCommerce: attribute nomi taxonomy bo‘lsa 'pa_xxx'
        // get_attribute() string qaytaradi (names, comma-separated)
        $v = trim(wp_strip_all_tags($product->get_attribute($slug)));
        return $v;
    };

    // Avval oldindan belgilangan atributlarni chiqaramiz
    foreach ($ordered_map as $slug => [$label, $unit]) {
        $val = $get_attr_value($slug);
        if ($val !== '') {
            // Agar qiymatga unit qo‘shish kerak bo‘lsa, har bir elementga qo‘shamiz
            if ($unit) {
                // qiymat vergul bilan bo‘lingan bo‘lishi mumkin
                $parts = array_map('trim', explode(',', $val));
                $parts = array_filter($parts, fn($p) => $p !== '');
                $val = implode(', ', array_map(fn($p) => $p . $unit, $parts));
            }

            echo '<div class="character_item">';
            echo '<div class="name">' . esc_html($label) . ':</div>';
            echo '<div class="value">' . esc_html($val) . '</div>';
            echo '</div>';

            $shown[] = $slug;
        }
    }

    // 2) Qolgan barcha atributlarni avtomatik chiqaramiz (bo‘sh bo‘lmaganlarini)
    foreach ($product->get_attributes() as $attr) {
        // $attr->get_name() taxonomy bo‘lsa 'pa_xxx' yoki custom nom
        $name = $attr->get_name();

        // Agar allaqachon ko‘rsatilgan bo‘lsa — o‘tkazamiz
        if (in_array($name, $shown, true)) {
            continue;
        }

        // Qiymatini o‘qiymiz
        if ($attr->is_taxonomy()) {
            $terms = wc_get_product_terms($product->get_id(), $name, ['fields' => 'names']);
            $value = implode(', ', $terms);
        } else {
            // Custom product attribute
            $value = implode(', ', array_map('trim', $attr->get_options()));
        }

        $value = trim(wp_strip_all_tags($value));
        if ($value === '') continue;

        // Label: agar taxonomy bo‘lsa chiroyli label, bo‘lmasa nomi
        $label = wc_attribute_label($name);

        echo '<div class="character_item">';
        echo '<div class="name">' . esc_html($label) . ':</div>';
        echo '<div class="value">' . esc_html($value) . '</div>';
        echo '</div>';
    }

    echo '</div>'; // .characteristics
}


/**
 * Woo scripts
 */
add_action('wp_enqueue_scripts', function () {
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('jquery');               // jQuery ishlatiladi
        wp_enqueue_script('wc-add-to-cart');       // AJAX add-to-cart
        wp_enqueue_script('wc-cart-fragments');    // mini-cart fragment update
    }
});


/**
 *  Basket
 */
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    ob_start();
    $count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0; ?>
    <span class="header_counter basket_counter<?php echo $count ? ' active' : ''; ?>">
        <?php echo (int)$count; ?>
    </span>
    <?php
    $html = ob_get_clean();

    // Faqat basket counter(lar)i:
    $fragments['span.header_counter.basket_counter'] = $html; // ikkala klass birga bo'lsa
    $fragments['span.basket_counter'] = $html; // faqat basket_counter bo'lsa

    return $fragments;
});

// Cart URL'ini ishonchli olish (fallbacklar bilan)
// Barqaror Cart URL (faqat published sahifa, __trashed bo'lsa chetlanadi)
function galeon_cart_url()
{
    // 1) Woo sozlamasidagi Cart page ID
    if (function_exists('wc_get_page_id')) {
        $cart_id = wc_get_page_id('cart');
        if ($cart_id && $cart_id > 0 && get_post_status($cart_id) === 'publish') {
            // Published bo'lsa, bevosita permalink
            $perma = get_permalink($cart_id);
            if ($perma) return $perma;
        }
    }

    // 2) Published bo'lgan, ichida [woocommerce_cart] bo'lgan sahifani qidiramiz
    $q = new WP_Query([
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        's' => '[woocommerce_cart]',
        'no_found_rows' => true,
    ]);
    if ($q->have_posts()) {
        $page = $q->posts[0];
        $url = get_permalink($page->ID);
        wp_reset_postdata();
        if ($url) return $url;
    }

    // 3) Slug bo'yicha published sahifa (agar nomi 'cart' bo'lsa)
    $by_path = get_page_by_path('cart', OBJECT, 'page');
    if ($by_path && get_post_status($by_path->ID) === 'publish') {
        $u = get_permalink($by_path->ID);
        if ($u) return $u;
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
        'nonce' => wp_create_nonce('galeon_archive_nonce'),
        'per_page_default' => 9,
    ]);
});


/**
 * Helper: term nomidan raqam ajratish ( "330 мм" => 330.0 )
 */
function galeon_num_from_term_name($name)
{
    $v = trim(preg_replace('~[^0-9\.\,]+~', '', (string)$name));
    $v = str_replace(',', '.', $v);
    return ($v === '' || !is_numeric($v)) ? null : (float)$v;
}

/**
 * Helper: taxonomiyadan (masalan: pa_length) nomi raqam bo‘lgan
 * term_id larni [min,max] range bo‘yicha tanlash
 */
function galeon_term_ids_in_range($taxonomy, $min = null, $max = null)
{
    if ($min === null && $max === null) return [];
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
    $out = [];
    foreach ($terms as $t) {
        $n = galeon_num_from_term_name($t->name);
        if ($n === null) continue;
        if ($min !== null && $n < $min) continue;
        if ($max !== null && $n > $max) continue;
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
        'nonce' => wp_create_nonce('galeon_archive_nonce'),
        'per_page_default' => 9,
    ]);
});

add_action('wp_ajax_nopriv_galeon_load_products', 'galeon_load_products');
add_action('wp_ajax_galeon_load_products', 'galeon_load_products');
function galeon_load_products(){
nocache_headers();

    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'galeon_archive_nonce')) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }

    // --- UI’dan ---
    $mode     = sanitize_text_field($_POST['mode'] ?? 'replace'); // replace | append
    $page     = max(1, intval($_POST['page'] ?? 1));
    $per_page = max(1, intval($_POST['per_page'] ?? 9));
    $search   = sanitize_text_field($_POST['search'] ?? '');
    $cat_slug = sanitize_title($_POST['category'] ?? '');

    $read_range = function ($k) {
        $min = isset($_POST[$k]['min']) ? trim((string)$_POST[$k]['min']) : '';
        $max = isset($_POST[$k]['max']) ? trim((string)$_POST[$k]['max']) : '';
        $min = ($min === '') ? null : (float)$min;
        $max = ($max === '') ? null : (float)$max;
        return [$min, $max];
    };
    list($price_min, $price_max) = $read_range('price');
    list($len_min, $len_max)     = $read_range('len');
    list($wid_min, $wid_max)     = $read_range('wid');
    list($hei_min, $hei_max)     = $read_range('hei');
    list($wei_min, $wei_max)     = $read_range('wei');

    $variants = [];
    if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
        foreach ($_POST['variants'] as $v) $variants[] = sanitize_title($v);
        $variants = array_values(array_unique(array_filter($variants)));
    }

    // --- tax/meta query ---
    $tax_query  = ['relation' => 'AND'];
    $meta_query = ['relation' => 'AND'];

    if ($cat_slug !== '') {
        $tax_query[] = ['taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => [$cat_slug], 'operator' => 'IN'];
    }
    if (!empty($variants)) {
        $tax_query[] = ['taxonomy' => 'pa_option', 'field' => 'slug', 'terms' => $variants, 'operator' => 'IN'];
    }

    $attr_ranges = [
        'pa_length' => [$len_min, $len_max],
        'pa_width'  => [$wid_min, $wid_max],
        'pa_height' => [$hei_min, $hei_max],
        'pa_weight' => [$wei_min, $wei_max],
    ];
    foreach ($attr_ranges as $tax => $pair) {
        list($mn, $mx) = $pair;
        if ($mn === null && $mx === null) continue;
        $term_ids = galeon_term_ids_in_range($tax, $mn, $mx);
        if (!empty($term_ids)) {
            $tax_query[] = ['taxonomy' => $tax, 'field' => 'term_id', 'terms' => $term_ids, 'operator' => 'IN'];
        } else {
            wp_send_json_success([
                'mode' => $mode,
                'html' => '',
                'total' => 0,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => 0,
                'wish'        => galeon_current_wishlist_ids(),
                'facets' => [
                    'price' => ['min' => 0, 'max' => 0], 'len' => ['min' => 0, 'max' => 0],
                    'wid' => ['min' => 0, 'max' => 0], 'hei' => ['min' => 0, 'max' => 0], 'wei' => ['min' => 0, 'max' => 0],
                ],
            ]);
        }
    }

    if ($price_min !== null || $price_max !== null) {
        $cmp = [];
        if ($price_min !== null && $price_max !== null) $cmp = ['compare' => 'BETWEEN', 'type' => 'NUMERIC', 'value' => [$price_min, $price_max]];
        elseif ($price_min !== null)                    $cmp = ['compare' => '>=',      'type' => 'NUMERIC', 'value' => $price_min];
        else                                            $cmp = ['compare' => '<=',      'type' => 'NUMERIC', 'value' => $price_max];

        $meta_query[] = [
            'relation' => 'OR',
            array_merge(['key' => '_price'], $cmp),
            array_merge(['key' => '_min_variation_price'], $cmp),
        ];
    }

// === [B] SEARCH (title OR SKU OR category-name) ===
    $post__in = null;
    if ($search !== '') {
        $search = trim($search);

        // 1) Title bo‘yicha
        $ids_title = get_posts([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            's'              => $search,
            'fields'         => 'ids',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
        ]);

        // 2) SKU bo‘yicha (SIMPLE/VARIABLE parent productlarda _sku)
        $ids_sku_prod = get_posts([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'meta_query'     => [[
                'key'     => '_sku',
                'value'   => $search,
                'compare' => 'LIKE',
            ]],
            'fields'         => 'ids',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
        ]);

        // 3) SKU bo‘yicha (VARIATION postlarida _sku ko‘p hollarda shu yerda turadi)
        $ids_sku_var = get_posts([
            'post_type'      => 'product_variation',
            'post_status'    => 'publish',
            'meta_query'     => [[
                'key'     => '_sku',
                'value'   => $search,
                'compare' => 'LIKE',
            ]],
            'fields'         => 'ids',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
        ]);

        // 3a) Topilgan variation’larning parent product ID’lari
        $ids_sku_parent = [];
        if (!empty($ids_sku_var)) {
            foreach ($ids_sku_var as $vid) {
                $parent_id = (int) get_post_field('post_parent', $vid);
                if ($parent_id > 0) {
                    $ids_sku_parent[] = $parent_id;
                }
            }
        }

        // 4) Term nomi bo‘yicha qidiruv (product_cat name LIKE '%$search%'), topilgan term’larga tegishli product ID’lar
        $ids_cats = [];
        $cat_terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'search'     => $search, // term name LIKE
            'number'     => 50,
        ]);
        if (!empty($cat_terms) && !is_wp_error($cat_terms)) {
            $term_ids = wp_list_pluck($cat_terms, 'term_id');
            if (!empty($term_ids)) {
                $ids_cats = get_posts([
                    'post_type'      => 'product',
                    'post_status'    => 'publish',
                    'fields'         => 'ids',
                    'posts_per_page' => -1,
                    'no_found_rows'  => true,
                    'tax_query'      => [[
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => $term_ids,
                        'include_children' => true,
                        'operator'         => 'IN',
                    ]],
                ]);
            }
        }

        // 5) Barchasini birlashtiramiz (unique)
        $post__in = array_values(array_unique(array_map('intval', array_merge(
            $ids_title ?: [],
            $ids_sku_prod ?: [],
            $ids_sku_parent ?: [], // ← variation SKU parent productlari
            $ids_cats ?: []
        ))));

        // 6) Hech narsa topilmasa — bo‘sh javob
        if (empty($post__in)) {
            wp_send_json_success([
                'mode'        => $mode,
                'html'        => '',
                'total'       => 0,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => 0,
                'facets'      => galeon_global_facets(), // global facets baribir qaytadi
            ]);
        }
    }

// --- Query args ---
    $args = [
        'post_type'           => 'product',
        'post_status'         => 'publish',
        // 's' => $search, // ❌ kerak emas: post__in bilan filterlayapmiz
        'tax_query'           => $tax_query,
        'meta_query'          => $meta_query,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => false,
    ];
    if (!empty($post__in)) {
        $args['post__in'] = $post__in;
    }

    if ($mode === 'append') {
        $offset = max(0, intval($_POST['offset'] ?? 0));
        $limit  = max(1, intval($_POST['limit'] ?? 4));
        $args['posts_per_page'] = $limit;
        $args['offset'] = $offset;
    } else {
        $args['posts_per_page'] = $per_page;
        $args['paged'] = $page;
    }

    $q = new WP_Query($args);


    ob_start();
    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            $p = wc_get_product(get_the_ID());
            if ($p) get_template_part('template-parts/product/catalog-item', null, ['product' => $p]);
        }
        wp_reset_postdata();
    }
    $html = ob_get_clean();

    $total = intval($q->found_posts);
    $total_pages = ($mode === 'append') ? 0 : ($per_page ? (int)ceil($total / $per_page) : 1);

    // === [A] GLOBAL FACETS — butun baza bo‘yicha (filtrlardan mustaqil)
    $facet = galeon_global_facets();

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
 *  Archive product search offers
 */
add_action('wp_ajax_nopriv_galeon_search_suggest', 'galeon_search_suggest');
add_action('wp_ajax_galeon_search_suggest',        'galeon_search_suggest');

function galeon_search_suggest(){
    nocache_headers();

    // nonce tekshiruvi (xuddi archive AJAX’dagi kabi)
    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'galeon_archive_nonce')) {
        wp_send_json_error(['message'=>'Bad nonce'], 403);
    }

    $q     = isset($_POST['q']) ? sanitize_text_field(wp_unslash($_POST['q'])) : '';
    $limit = isset($_POST['limit']) ? max(1, min(20, intval($_POST['limit']))) : 8;

    if ($q === '' || mb_strlen($q) < 2) {
        wp_send_json_success(['items' => []]);
    }

    $suggestions = [];

    // 1) Mahsulot sarlavhalari bo'yicha
    $title_ids = get_posts([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $q,
        'fields'         => 'ids',
        'posts_per_page' => $limit * 2, // biroz zaxira
        'no_found_rows'  => true,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    foreach ((array)$title_ids as $pid){
        $t = get_the_title($pid);
        if ($t) $suggestions[] = $t;
    }

    // 2) SKU bo'yicha (LIKE)
    global $wpdb;
    $like = '%' . $wpdb->esc_like( $q ) . '%';
    $sku_rows = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT pm.meta_value
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = '_sku'
               AND pm.meta_value LIKE %s
               AND p.post_type IN ('product','product_variation')
               AND p.post_status = 'publish'
             LIMIT %d",
            $like, $limit
        )
    );
    foreach ((array)$sku_rows as $sku){
        if (!empty($sku)) $suggestions[] = $sku;
    }

    // 3) Kategoriyalar va atribut terminlari (name LIKE)
    $taxes = ['product_cat'];
    $all_tax = get_taxonomies([], 'objects');
    foreach ($all_tax as $name=>$obj){
        if (strpos($name, 'pa_') === 0) $taxes[] = $name;
    }

    // 'name__like' — tez va to'g'ridan-to'g'ri nom bo'yicha bog'lash
    $term_args = [
        'taxonomy'   => $taxes,
        'hide_empty' => false,
        'number'     => $limit,
    ];
    if (property_exists('WP_Term_Query', 'name__like') || true){
        // ko'p installlarda bor; bo'lmasa pastdagi 'search' ishlaydi
        $term_args['name__like'] = $q;
    } else {
        $term_args['search'] = $q;
    }
    $terms = get_terms($term_args);
    if (!is_wp_error($terms)){
        foreach ($terms as $t){
            $nm = trim($t->name);
            if ($nm !== '') $suggestions[] = $nm;
        }
    }

    // Unikal + boshlanishiga moslarni yuqoriga
    $q_lower = mb_strtolower($q);
    $uniq = [];
    $out  = [];
    foreach ($suggestions as $s){
        $s = trim(wp_strip_all_tags($s));
        if ($s === '') continue;
        $k = mb_strtolower($s);
        if (isset($uniq[$k])) continue;
        $uniq[$k] = true;
        $out[] = $s;
    }

    usort($out, function($a,$b) use ($q_lower){
        $al = mb_strtolower($a); $bl = mb_strtolower($b);
        $as = (mb_strpos($al, $q_lower) === 0);
        $bs = (mb_strpos($bl, $q_lower) === 0);
        if ($as && !$bs) return -1;
        if ($bs && !$as) return 1;
        return strcmp($a, $b);
    });

    $out = array_slice($out, 0, $limit);

    wp_send_json_success(['items' => $out]);
}




/**
 * GLOBAL facets: barcha publish qilingan productlar bo‘yicha min/max.
 * (price, pa_length, pa_width, pa_height, pa_weight)
 */
function galeon_global_facets(){
    // Hamma product ID’lari
    $ids_all = get_posts([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
    ]);

    $facet = [
        'price' => ['min' => 0, 'max' => 0],
        'len'   => ['min' => 0, 'max' => 0],
        'wid'   => ['min' => 0, 'max' => 0],
        'hei'   => ['min' => 0, 'max' => 0],
        'wei'   => ['min' => 0, 'max' => 0],
    ];

    if (!empty($ids_all)) {
        // Price (product yoki variation min price)
        $pmin = null; $pmax = null;
        foreach ($ids_all as $pid) {
            $pr = wc_get_product($pid);
            if (!$pr) continue;
            $v = (float)$pr->get_price();
            if ($v <= 0) continue;
            if ($pmin === null || $v < $pmin) $pmin = $v;
            if ($pmax === null || $v > $pmax) $pmax = $v;
        }
        if ($pmin !== null) $facet['price'] = ['min' => $pmin, 'max' => $pmax];

        // Helper: attribute termlaridan raqam chiqarish
        $calc = function ($tax) use ($ids_all) {
            $terms = wp_get_object_terms($ids_all, $tax, ['fields' => 'all']);
            if (is_wp_error($terms) || empty($terms)) return ['min' => 0, 'max' => 0];
            $vals = [];
            foreach ($terms as $t) {
                $n = galeon_num_from_term_name($t->name);
                if ($n !== null) $vals[] = $n;
            }
            if (!$vals) return ['min' => 0, 'max' => 0];
            return ['min' => min($vals), 'max' => max($vals)];
        };
        $facet['len'] = $calc('pa_length');
        $facet['wid'] = $calc('pa_width');
        $facet['hei'] = $calc('pa_height');
        $facet['wei'] = $calc('pa_weight');
    }

    return $facet;
}



/**
 *  Cart
 */

add_filter('template_include', function ($template) {
    if (function_exists('is_cart') && is_cart()) {
        $t = locate_template('pages/page-cart.php', false, false);
        if ($t) return $t;
    }
    return $template;
}, 20);


// =====================
// CART AJAX HANDLERS
// =====================
add_action('wp_ajax_nopriv_galeon_cart_update_qty', 'galeon_cart_update_qty');
add_action('wp_ajax_galeon_cart_update_qty', 'galeon_cart_update_qty');
function galeon_cart_update_qty()
{
    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'galeon_cart_nonce')) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }
    $key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $qty = max(0, intval($_POST['quantity'] ?? 0));

    $cart = WC()->cart;
    if (!$cart) wp_send_json_error(['message' => 'No cart'], 400);

    $items = $cart->get_cart();
    if (!isset($items[$key])) wp_send_json_error(['message' => 'Item not found'], 404);

    // Yangilash
    $cart->set_quantity($key, $qty, true); // true => recalc totals
    $cart->calculate_totals();

    $removed = ($qty === 0) || !isset($cart->get_cart()[$key]);
    $line_html = '';
    if (!$removed) {
        $cart_item = $cart->get_cart()[$key];
        $_product = $cart_item['data'];
        $line_html = $cart->get_product_subtotal($_product, $cart_item['quantity']);
        if ($_product->get_price() === '' || $_product->get_price() === null) {
            $line_html = '<span class="price-request">По запросу</span>';
        }
    }

    wp_send_json_success([
        'removed' => $removed,
        'total_items' => $cart->get_cart_contents_count(),
        'total_html' => wc_price((float)$cart->get_total('edit')),
        'line_subtotal_html' => $line_html,
    ]);
}

add_action('wp_ajax_nopriv_galeon_cart_remove_item', 'galeon_cart_remove_item');
add_action('wp_ajax_galeon_cart_remove_item', 'galeon_cart_remove_item');
function galeon_cart_remove_item()
{
    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'galeon_cart_nonce')) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }
    $key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $cart = WC()->cart;
    if (!$cart) wp_send_json_error(['message' => 'No cart'], 400);

    $cart->remove_cart_item($key);
    $cart->calculate_totals();

    wp_send_json_success([
        'total_items' => $cart->get_cart_contents_count(),
        'total_html' => wc_price((float)$cart->get_total('edit')),
    ]);
}

add_action('wp_ajax_nopriv_galeon_cart_clear', 'galeon_cart_clear');
add_action('wp_ajax_galeon_cart_clear', 'galeon_cart_clear');
function galeon_cart_clear()
{
    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'galeon_cart_nonce')) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }
    $cart = WC()->cart;
    if (!$cart) wp_send_json_error(['message' => 'No cart'], 400);

    $cart->empty_cart();
    $cart->calculate_totals();

    wp_send_json_success([
        'empty' => true,
        'total_items' => 0,
        'total_html' => wc_price(0),
    ]);
}






/**
 *  SEARCH
 */
// --- Live Search skriptini ulash va AJAX ma'lumotlar ---
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'live-search',
        get_stylesheet_directory_uri() . '/assets/js/live-search.js',
        ['jquery'],
        '1.0',
        true
    );
    wp_localize_script('live-search', 'LS', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ls_nonce'),
    ]);
});

// --- AJAX: faqat mahsulotlar bo'yicha qidiruv ---
add_action('wp_ajax_nopriv_live_product_search', 'theme_live_product_search');
add_action('wp_ajax_live_product_search', 'theme_live_product_search');

function theme_live_product_search() {
    if (!check_ajax_referer('ls_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }

    $q     = isset($_POST['q']) ? sanitize_text_field(wp_unslash($_POST['q'])) : '';
    $limit = isset($_POST['limit']) ? max(1, min(20, intval($_POST['limit']))) : 8;

    if (mb_strlen($q) < 2) {
        wp_send_json_success(['html' => '<li class="no-results">Введите минимум 2 символа</li>']);
    }

    // Woo visibility: exclude-from-search (agar bo‘lsa) ni chiqarib tashlaymiz
    $vis     = function_exists('wc_get_product_visibility_term_ids') ? wc_get_product_visibility_term_ids() : [];
    $exclude = !empty($vis['exclude-from-search']) ? (int) $vis['exclude-from-search'] : 0;
    $tax_exclude = [];
    if ($exclude) {
        $tax_exclude[] = [
            'taxonomy' => 'product_visibility',
            'field'    => 'term_taxonomy_id',
            'terms'    => [$exclude],
            'operator' => 'NOT IN',
        ];
    }

    // ===== 1) TITLE bo‘yicha ID’lar (relevance uchun fallback)
    // Limitni sal kengroq olamiz (filtrlashdan keyin yetarli bo‘lsin)
    $ids_title = get_posts([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $q,
        'fields'         => 'ids',
        'posts_per_page' => $limit * 3,
        'no_found_rows'  => true,
        'suppress_filters' => false,
        'tax_query'      => $tax_exclude,
    ]);

    // ===== 2) SKU bo‘yicha qidiruv (PRODUCT)
    $ids_sku_prod = get_posts([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'meta_query'     => [[
            'key'     => '_sku',
            'value'   => $q,
            'compare' => 'LIKE',
        ]],
        'fields'         => 'ids',
        'posts_per_page' => $limit * 3,
        'no_found_rows'  => true,
        'suppress_filters' => false,
        'tax_query'      => $tax_exclude,
    ]);

    // ===== 3) SKU bo‘yicha qidiruv (VARIATION) → parent product ID’lari
    $ids_sku_var = get_posts([
        'post_type'      => 'product_variation',
        'post_status'    => 'publish',
        'meta_query'     => [[
            'key'     => '_sku',
            'value'   => $q,
            'compare' => 'LIKE',
        ]],
        'fields'         => 'ids',
        'posts_per_page' => $limit * 5, // ko‘proq olamiz, keyin parentlarga aylantiramiz
        'no_found_rows'  => true,
        'suppress_filters' => false,
    ]);
    $ids_sku_parent = [];
    if (!empty($ids_sku_var)) {
        foreach ($ids_sku_var as $vid) {
            $p = (int) get_post_field('post_parent', $vid);
            if ($p > 0) { $ids_sku_parent[] = $p; }
        }
        // visibility exclude’ni parentlarga ham qo‘llash uchun yakuniy WP_Query bosqichida filterlanadi
    }

    // ===== 4) Yakuniy ro‘yxatni tuzamiz
    // SKU hit’lar birinchi bo‘lsin → (product SKU) ∪ (variation SKU parentlari), keyin title hit’lar
    $ids_sku_any = array_values(array_unique(array_map('intval', array_merge($ids_sku_prod ?: [], $ids_sku_parent ?: []))));
    $ordered     = [];

    if (!empty($ids_sku_any)) {
        // Avval SKU bo‘yicha topilganlar
        foreach ($ids_sku_any as $id) {
            $ordered[$id] = true;
        }
        // Keyin title bo‘yicha topilganlar (takrorlarsiz)
        if (!empty($ids_title)) {
            foreach ($ids_title as $id) {
                if (!isset($ordered[$id])) {
                    $ordered[$id] = true;
                }
            }
        }
        $post__in = array_keys($ordered);
    } else {
        // SKU topilmasa — default title relevance’ga tayanamiz
        $post__in = [];
    }

    // ===== 5) Yakuniy WP_Query: agar SKU bor bo‘lsa post__in tartibida, bo‘lmasa default relevance
    $args = [
        'post_type'        => 'product',
        'post_status'      => 'publish',
        'posts_per_page'   => $limit,
        'suppress_filters' => false,
        'tax_query'        => $tax_exclude,
    ];

    if (!empty($post__in)) {
        $args['post__in'] = $post__in;
        $args['orderby']  = 'post__in'; // SKU topilganlar avval
    } else {
        // faqat title relevance
        $args['s']        = $q;
        $args['orderby']  = 'relevance';
    }

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product = wc_get_product(get_the_ID());
            if (!$product) { continue; }

            $url   = get_permalink();
            $title = get_the_title();
            $img   = get_the_post_thumbnail_url(get_the_ID(), 'woocommerce_gallery_thumbnail');
            if (!$img && function_exists('wc_placeholder_img_src')) {
                $img = wc_placeholder_img_src('woocommerce_gallery_thumbnail');
            }
            $price = $product->get_price_html();
            ?>
            <li class="ls-item">
                <?php if ($img): ?>
                    <img class="Suggestion_image" src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>">
                <?php endif; ?>
                <a class="ls-link" href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
                <?php if ($price): ?>
                    <span class="ls-price"><?php echo wp_kses_post($price); ?></span>
                <?php endif; ?>
            </li>
            <?php
        }
        wp_reset_postdata();

        // "Все результаты" — hozirgidek qoldiramiz
        $search_url = add_query_arg(['s' => $q, 'post_type' => 'product'], home_url('/'));
        echo '<li class="ls-all"><a href="' . esc_url($search_url) . '">Показать все результаты</a></li>';
    } else {
        echo '<li class="no-results">Ничего не найдено</li>';
    }

    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}


/** =========================
 *  WISHLIST (Like) – V2
 *  - User_meta: wishlist_v1  (array of {pid, vid, ts})
 *  - Logged-in: server authoritative
 *  - Guest: localStorage
 *  - AJAX: toggle / list / merge / render
 * ========================= */

// Ixtiyoriy: serverda SSR "active" uchun kerak bo‘lsa
if (!function_exists('my_is_in_wishlist')) {
    function my_is_in_wishlist($product_id) {
        if (!is_user_logged_in()) return false;
        $pid = absint($product_id);
        foreach (wl_user_list(get_current_user_id()) as $it) {
            $pp = (int)($it['pid'] ?? 0);
            $vv = (int)($it['vid'] ?? 0);
            if ($pp === $pid || $vv === $pid) return true;
        }
        return false;
    }
}


add_action('wp_enqueue_scripts', function () {
    // JS faylni ro‘yxatdan o‘tkazamiz va ulaymiz
    $handle = 'theme-wishlist';
    $path = get_stylesheet_directory() . '/assets/js/wishlist.v2.js';
    $ver = file_exists($path) ? filemtime($path) : null;

    wp_register_script(
        $handle,
        get_stylesheet_directory_uri() . '/assets/js/wishlist.v2.js',
        ['jquery'],
        $ver,
        true
    );

    wp_localize_script($handle, 'WISHLIST', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wishlist_nonce'),
        'isLoggedIn' => is_user_logged_in(),
        'userId' => get_current_user_id(),
        // ixtiyoriy: selektorlarni sozlab bo‘lishingiz uchun
        'selectors' => [
            'icon' => '.like_icon',
            'count' => '.wishlist-count.header_counter',
            'listId' => '#wl_list',
        ],
    ]);

    wp_enqueue_script($handle);
});

/** ===== Helpers: read/save/list/normalize ===== */
if (!function_exists('wl_user_list')) {
    function wl_user_list($user_id)
    {
        $raw = get_user_meta($user_id, 'wishlist_v1', true);
        return is_array($raw) ? $raw : [];
    }
}
if (!function_exists('wl_save')) {
    function wl_save($user_id, $items)
    {
        $seen = [];
        $out = [];
        foreach ((array)$items as $it) {
            $pid = isset($it['pid']) ? absint($it['pid']) : 0;
            $vid = isset($it['vid']) ? absint($it['vid']) : 0;
            if (!$pid) continue;
            $key = $pid . ':' . $vid;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $out[] = [
                'pid' => $pid,
                'vid' => $vid,
                'ts' => isset($it['ts']) ? intval($it['ts']) : time(),
            ];
        }
        update_user_meta($user_id, 'wishlist_v1', $out);
        return $out;
    }
}
if (!function_exists('wl_normalize_pair')) {
    function wl_normalize_pair($pid, $vid)
    {
        $pid = absint($pid);
        $vid = absint($vid);
        if ($pid) {
            $prod = wc_get_product($pid);
            if ($prod && $prod->is_type('variation')) {
                if ($vid === 0) {
                    $parent_id = absint($prod->get_parent_id());
                    if ($parent_id) {
                        $vid = $pid;
                        $pid = $parent_id;
                    }
                } else {
                    $parent_id = absint($prod->get_parent_id());
                    if ($parent_id) $pid = $parent_id;
                }
            }
        }
        return [$pid, $vid];
    }
}
if (!function_exists('wl_toggle')) {
    function wl_toggle($user_id, $pid, $vid)
    {
        list($pid, $vid) = wl_normalize_pair($pid, $vid);
        $pid = absint($pid);
        $vid = absint($vid);
        if (!$pid) return ['status' => 'error', 'count' => 0];

        $list = wl_user_list($user_id);

        $hasExact = false;
        $hasAnyForPid = false;
        $hasParentOnly = false;

        foreach ($list as $it) {
            $pp = absint($it['pid'] ?? 0);
            $vv = absint($it['vid'] ?? 0);
            if (!$pp) continue;
            if ($pp === $pid) {
                $hasAnyForPid = true;
                if ($vv === 0) $hasParentOnly = true;
                if ($vv === $vid) $hasExact = true;
            }
        }

        // UI mantiqi: parent active bo‘lsa — istalgan child ham active hisob
        $isActiveNow = ($vid === 0) ? $hasAnyForPid : ($hasExact || $hasParentOnly);

        if ($isActiveNow) {
            if ($vid === 0) {
                // parent unlike → parent va barcha child’larni o‘chir
                foreach ($list as $i => $it) {
                    if (absint($it['pid']) === $pid) unset($list[$i]);
                }
            } else {
                // child unlike
                foreach ($list as $i => $it) {
                    if (absint($it['pid']) === $pid && absint($it['vid']) === $vid) unset($list[$i]);
                }
                // parent yozuvi bo‘lsa o‘chir (aks holda yana active ko‘rinsa)
                foreach ($list as $i => $it) {
                    if (absint($it['pid']) === $pid && absint($it['vid']) === 0) {
                        unset($list[$i]);
                        break;
                    }
                }
            }
            $status = 'removed';
        } else {
            $list[] = ['pid' => $pid, 'vid' => $vid, 'ts' => time()];
            $status = 'added';
        }

        $list = array_values($list);
        $list = wl_save($user_id, $list);
        return ['status' => $status, 'count' => count($list)];
    }
}

/** ===== AJAX: toggle / list / merge ===== */
add_action('wp_ajax_my_wishlist_toggle', function () {
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'not logged in'], 401);
    check_ajax_referer('wishlist_nonce', 'nonce');

    $pid = isset($_POST['pid']) ? absint($_POST['pid']) : 0;
    $vid = isset($_POST['vid']) ? absint($_POST['vid']) : 0;

    $res = wl_toggle(get_current_user_id(), $pid, $vid);
    nocache_headers();
    wp_send_json_success($res);
});

add_action('wp_ajax_my_wishlist_list', function () {
    check_ajax_referer('wishlist_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_success(['items' => []]);
    $items = wl_user_list(get_current_user_id());
    nocache_headers();
    wp_send_json_success(['items' => array_values($items)]);
});

add_action('wp_ajax_my_wishlist_merge', function () {
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'not logged in'], 401);
    check_ajax_referer('wishlist_nonce', 'nonce');

    $items = [];
    if (isset($_POST['items'])) {
        $json = is_array($_POST['items']) ? wp_json_encode($_POST['items']) : wp_unslash($_POST['items']);
        $items = json_decode($json, true) ?: [];
    }
    $current = wl_user_list(get_current_user_id());
    $saved = wl_save(get_current_user_id(), array_merge($current, (array)$items));

    nocache_headers();
    wp_send_json_success(['count' => count($saved)]);
});

/** ===== AJAX: renderer (wishlist sahifasi uchun) ===== */
add_action('wp_ajax_nopriv_galeon_wishlist_render', 'galeon_wishlist_render_v2');
add_action('wp_ajax_galeon_wishlist_render', 'galeon_wishlist_render_v2');

function galeon_wishlist_render_v2()
{
    nocache_headers();
    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wishlist_nonce')) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }

    // Guest LS elementlari (faqat guest uchun)
    $raw_items = [];
    if (isset($_POST['items'])) {
        $json = is_array($_POST['items']) ? wp_json_encode($_POST['items']) : wp_unslash($_POST['items']);
        $raw_items = json_decode($json, true) ?: [];
    }

    $seen = [];
    $ls_items = [];
    foreach ((array)$raw_items as $it) {
        $pid = isset($it['pid']) ? absint($it['pid']) : 0;
        $vid = isset($it['vid']) ? absint($it['vid']) : 0;
        if (!$pid) continue;
        $k = $pid . ':' . $vid;
        if (isset($seen[$k])) continue;
        $seen[$k] = true;
        $ls_items[] = ['pid' => $pid, 'vid' => $vid, 'ts' => isset($it['ts']) ? intval($it['ts']) : time()];
    }

    if (is_user_logged_in()) {
        $items_to_render = wl_user_list(get_current_user_id());
    } else {
        $items_to_render = $ls_items;
    }
    $items_to_render = array_values($items_to_render);

    ob_start();
    if ($items_to_render) {
        foreach ($items_to_render as $it) {
            $pid = absint($it['pid']);
            $vid = absint($it['vid']);
            $product = $vid ? wc_get_product($vid) : wc_get_product($pid);
            if (!$product) continue;

            // O'zingizdagi template part
            get_template_part('template-parts/product/wishlist-item', null, [
                'product' => $product,
                'parent_id' => $pid,
                'variation_id' => $vid,
            ]);
        }
    } else {
        echo '<p class="empty">Список избранного пуст.</p>';
    }
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'count' => count($items_to_render),
        'logged_in' => is_user_logged_in() ? 1 : 0,
        'items' => $items_to_render, // Guest LS ni yangilash uchun
    ]);
}


/**
 * Modal Auth V2 (AJAX + Magic Links) + localStorage sync (wishlist + cart)
 */
if ( ! class_exists('Theme_Modal_Auth_V2') ) {
    class Theme_Modal_Auth_V2 {
        const NONCE    = 'modal_auth_v2_nonce';
        const C_VERIFY = 'ml2_email_verified';
        const C_RESET  = 'ml2_pw_reset_ready';
        const TTL      = 900; // 15 daqiqa

        public function __construct() {
            add_action('init',              [$this, 'add_routes']);
            add_action('template_redirect', [$this, 'handle_magic']);

            // AJAX for guests
            add_action('wp_ajax_nopriv_auth_login',             [$this, 'auth_login']);
            add_action('wp_ajax_nopriv_auth_register_start',    [$this, 'auth_register_start']);
            add_action('wp_ajax_nopriv_auth_register_check',    [$this, 'auth_register_check']);
            add_action('wp_ajax_nopriv_auth_forgot_start',      [$this, 'auth_forgot_start']);
            add_action('wp_ajax_nopriv_auth_forgot_check',      [$this, 'auth_forgot_check']);
            add_action('wp_ajax_nopriv_auth_save_new_password', [$this, 'auth_save_new_password']);

            // Storage sync (after login) — also for logged-in (idempotent)
            add_action('wp_ajax_auth_sync_storage',             [$this, 'auth_sync_storage']);
            add_action('wp_ajax_nopriv_auth_sync_storage',      [$this, 'auth_sync_storage']);

            // REST fallback for login
            add_action('rest_api_init', [$this, 'register_rest']);

            // Assets
            add_action('wp_enqueue_scripts', [$this, 'assets']);

            // Pretty rules
            add_action('after_switch_theme', function(){ flush_rewrite_rules(false); });
            add_action('init', function () {
                if (!get_option('ml2_rules_flushed')) {
                    flush_rewrite_rules(false);
                    update_option('ml2_rules_flushed', 1);
                }
            }, 99);

            // Woo logout redirect → home
            add_filter('woocommerce_logout_default_redirect_url', function ($url) {
                return home_url('/');
            });
        }

        /* ---------- Utils ---------- */
        private function ok($d = [])      { wp_send_json(array_merge(['ok'=>true], $d)); }
        private function err($m,$c='ERR') { wp_send_json(['ok'=>false,'error'=>$c,'message'=>$m], 400); }
        private function setc($n,$v)      { setcookie($n,$v,time()+self::TTL, COOKIEPATH?:'/', COOKIE_DOMAIN?:'', is_ssl(), false); }
        private function delc($n)         { setcookie($n,'',time()-3600,      COOKIEPATH?:'/', COOKIE_DOMAIN?:'', is_ssl(), false); }
        private function sign($s)         { return hash_hmac('sha256',$s, wp_salt('auth')); }
        private function myacc()          { return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/'); }

        /* ---------- Routes: /magic/verify , /magic/reset ---------- */
        public function add_routes() {
            add_rewrite_rule(
                '^magic/verify/u/([0-9]+)/t/([^/]+)/exp/([0-9]+)/sig/([^/]+)/?$',
                'index.php?magic_action=verify&u=$matches[1]&t=$matches[2]&exp=$matches[3]&sig=$matches[4]',
                'top'
            );
            add_rewrite_rule(
                '^magic/reset/login/([^/]+)/key/([^/]+)/?$',
                'index.php?magic_action=reset&login=$matches[1]&key=$matches[2]',
                'top'
            );
            add_rewrite_rule('^magic/(verify|reset)/?', 'index.php?magic_action=$matches[1]', 'top');

            add_filter('query_vars', function ($v) {
                foreach (['magic_action','u','t','exp','sig','login','key','rt'] as $k) $v[]=$k;
                return $v;
            });
        }

        public function handle_magic() {
            $a = get_query_var('magic_action');
            if (!$a) return;

            if ($a === 'verify') {
                $u   = absint( get_query_var('u')   ?: ($_GET['u'] ?? 0) );
                $t   = sanitize_text_field( get_query_var('t')   ?: ($_GET['t'] ?? '') );
                $exp = absint( get_query_var('exp') ?: ($_GET['exp'] ?? 0) );
                $sig = sanitize_text_field( get_query_var('sig') ?: ($_GET['sig'] ?? '') );

                // &amp; fallback
                foreach (['u','t','exp','sig'] as $k) {
                    if (empty($$k) && !empty($_GET['amp;'.$k])) {
                        $$k = $k === 'exp' ? absint($_GET['amp;'.$k]) : sanitize_text_field($_GET['amp;'.$k]);
                    }
                }

                if (!$u || !$t || !$exp || !$sig) wp_die('Bad params');
                if ($exp < time())                wp_die('Link expired');

                $payload = "$u|$t|$exp";
                if ( ! hash_equals($this->sign($payload), $sig) )   wp_die('Bad signature');
                if ( get_user_meta($u, 'ml2_email_token_used_'.$t, true) ) wp_die('Already used');

                update_user_meta($u, 'email_verified', 1);
                update_user_meta($u, 'ml2_email_token_used_'.$t, time());

                $rt = wp_generate_password(12, false);
                set_transient('ml2_verify_'.$rt, $u, self::TTL);
                $this->setc(self::C_VERIFY, $rt);

                wp_safe_redirect( add_query_arg(['verified'=>1,'rt'=>$rt], home_url('/')) );
                exit;
            }

            if ($a === 'reset') {
                $login = get_query_var('login') ?: ($_GET['login'] ?? '');
                $key   = get_query_var('key')   ?: ($_GET['key']   ?? '');

                if (!$login && !empty($_GET['amp;login'])) $login = $_GET['amp;login'];
                if (!$key   && !empty($_GET['amp;key']))   $key   = $_GET['amp;key'];

                $login = sanitize_text_field( rawurldecode($login) );
                $key   = sanitize_text_field( str_replace(' ', '+', $key) );

                $user = check_password_reset_key($key, $login);
                if (is_wp_error($user)) wp_die('Invalid or expired reset key');

                $rt = wp_generate_password(12, false);
                set_transient('ml2_reset_'.$rt, ['login'=>$login,'key'=>$key], self::TTL);
                $this->setc(self::C_RESET, $rt);

                wp_safe_redirect( home_url('/?reset=1') );
                exit;
            }
        }

        /* ---------- AJAX: Login ---------- */
        public function auth_login() {
            check_ajax_referer(self::NONCE, 'nonce');

            $login_raw = trim((string)($_POST['log'] ?? ''));
            // Yangi: p_enc (base64 JSON) yoki fallback pwd
            $pwd       = (string)($_POST['pwd'] ?? '');
            $penc      = (string)($_POST['p_enc'] ?? '');

            // p_enc → decode
            if (!$pwd && $penc) {
                $dec = base64_decode($penc, true);
                if ($dec !== false) {
                    $j = json_decode($dec, true);
                    if (is_array($j) && isset($j['p'])) {
                        $pwd = (string)$j['p'];
                        // ixtiyoriy: vaqt tekshiruv
                        // if (isset($j['ts']) && (time()*1000 - (int)$j['ts'] > 5*60*1000)) { /* eskirgan */ }
                    } else {
                        // eski format uchun: "password|..."
                        $parts = explode('|', $dec, 2);
                        $pwd = (string)($parts[0] ?? '');
                    }
                }
            }

            if (!$login_raw || !$pwd) {
                $this->err('Неверный адрес электронной почты или пароль');
            }

            // Email bo‘lsa, mavjud emasligini darhol aytamiz
            $user_by_email = null;
            if (is_email($login_raw)) {
                $user_by_email = get_user_by('email', $login_raw);
                if (!$user_by_email) {
                    $this->err('Аккаунт с таким e-mail не найден');
                }
            }

            // username/email mapping
            $login_try = $user_by_email ? $user_by_email->user_login : $login_raw;

            $creds = [
                'user_login'    => $login_try,
                'user_password' => $pwd,
                'remember'      => true,
            ];
            $u = wp_signon($creds);

            // fallback: agar yuqorida email keltirilmagan bo‘lsa va birinchi urinish xato bo‘lsa
            if (is_wp_error($u) && !$user_by_email && is_email($login_raw)) {
                $user_by_email = get_user_by('email', $login_raw);
                if ($user_by_email) {
                    $creds['user_login'] = $user_by_email->user_login;
                    $u = wp_signon($creds);
                }
            }

            // Manual sessiya
            if (is_wp_error($u)) {
                if ($user_by_email && wp_check_password($pwd, $user_by_email->user_pass, $user_by_email->ID)) {
                    wp_set_current_user($user_by_email->ID);
                    wp_set_auth_cookie($user_by_email->ID, true);
                    if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($user_by_email->ID);
                    $this->ok(['redirect' => $this->myacc(), 'forced' => true]);
                }
                $this->err('Неверный адрес электронной почты или пароль');
            }

            if (function_exists('wc_set_customer_auth_cookie')) {
                wc_set_customer_auth_cookie($u->ID);
            }
            $this->ok(['redirect' => $this->myacc()]);
        }



        /* ---------- AJAX: Register start / verify check ---------- */
        public function auth_register_start() {
            check_ajax_referer(self::NONCE, 'nonce');

            $email = sanitize_email($_POST['user_email'] ?? '');
            $pass  = (string)($_POST['user_pass']  ?? '');
            $name  = sanitize_text_field($_POST['first_name'] ?? '');

            if (!is_email($email))    $this->err('Неверный e-mail');
            if (email_exists($email)) $this->err('E-mail уже зарегистрирован');
            if (strlen($pass) < 6)    $this->err('Пароль слишком короткий');

            // username sifatida email
            $uid = wp_create_user($email, $pass, $email);
            if (is_wp_error($uid)) $this->err($uid->get_error_message());
            if ($name) update_user_meta($uid, 'first_name', $name);

            // Magic verify
            $t   = wp_generate_password(20, false);
            $exp = time() + self::TTL;
            $sig = $this->sign($uid.'|'.$t.'|'.$exp);
            update_user_meta($uid, 'ml2_email_token_'.$t, $exp);

            $url = home_url(sprintf('/magic/verify/u/%d/t/%s/exp/%d/sig/%s/', $uid, rawurlencode($t), $exp, rawurlencode($sig)));

            $subject = 'Подтверждение регистрации';
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            $body = '<p>Для завершения регистрации нажмите кнопку ниже:</p>
<p><a href="'.esc_url($url).'" target="_blank" style="display:inline-block;padding:10px 16px;background:#1e87f0;color:#fff;text-decoration:none;border-radius:4px">Активировать аккаунт</a></p>
<p>Если кнопка не работает, скопируйте ссылку в адресную строку:<br>'.esc_html($url).'</p>';

            if ( ! wp_mail($email, $subject, $body, $headers) ) {
                $this->err('Не удалось отправить письмо. Проверьте SMTP.');
            }
            $this->ok(['email' => $email]);
        }

        public function auth_register_check() {
            check_ajax_referer(self::NONCE, 'nonce');

            // via rt (query) first
            $rt = sanitize_text_field($_POST['rt'] ?? '');
            if ($rt) {
                $uid = get_transient('ml2_verify_'.$rt);
                if ($uid) {
                    delete_transient('ml2_verify_'.$rt);
                    $this->delc(self::C_VERIFY);
                    wp_set_current_user($uid);
                    wp_set_auth_cookie($uid);
                    if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($uid);
                    $this->ok(['verified'=>true, 'redirect'=>$this->myacc()]);
                }
            }
            // via cookie
            $rt = $_COOKIE[self::C_VERIFY] ?? '';
            if ($rt) {
                $uid = get_transient('ml2_verify_'.$rt);
                if ($uid) {
                    delete_transient('ml2_verify_'.$rt);
                    $this->delc(self::C_VERIFY);
                    wp_set_current_user($uid);
                    wp_set_auth_cookie($uid);
                    if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($uid);
                    $this->ok(['verified'=>true, 'redirect'=>$this->myacc()]);
                }
            }
            $this->ok(['verified'=>false]);
        }

        /* ---------- AJAX: Forgot / Reset ---------- */
        public function auth_forgot_start() {
            check_ajax_referer(self::NONCE, 'nonce');

            $email = sanitize_email($_POST['user_email'] ?? '');
            if (!is_email($email)) $this->err('Неверный e-mail');

            $user = get_user_by('email', $email);
            if (!$user) $this->ok(['email'=>$email]); // leaky info bermaymiz

            $key = get_password_reset_key($user);
            if (is_wp_error($key)) $this->err($key->get_error_message());

            $url = home_url(sprintf('/magic/reset/login/%s/key/%s/', rawurlencode($user->user_login), rawurlencode($key)));

            $subject = 'Сброс пароля';
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            $body = '<p>Чтобы сбросить пароль, нажмите кнопку ниже:</p>
<p><a href="'.esc_url($url).'" target="_blank" style="display:inline-block;padding:10px 16px;background:#1e87f0;color:#fff;text-decoration:none;border-radius:4px">Сбросить пароль</a></p>
<p>Если кнопка не работает, скопируйте ссылку:<br>'.esc_html($url).'</p>';

            if ( ! wp_mail($email, $subject, $body, $headers) ) {
                $this->err('Не удалось отправить письмо. Проверьте SMTP.');
            }
            $this->ok(['email'=>$email]);
        }

        public function auth_forgot_check() {
            check_ajax_referer(self::NONCE, 'nonce');

            $rt = sanitize_text_field($_POST['rt'] ?? '');
            if ($rt) {
                $d = get_transient('ml2_reset_'.$rt);
                if ($d && !empty($d['login']) && !empty($d['key'])) {
                    delete_transient('ml2_reset_'.$rt);
                    $this->delc(self::C_RESET);
                    $this->ok(['ready'=>true,'login'=>$d['login'],'key'=>$d['key']]);
                }
            }
            $rt = $_COOKIE[self::C_RESET] ?? '';
            if ($rt) {
                $d = get_transient('ml2_reset_'.$rt);
                if ($d && !empty($d['login']) && !empty($d['key'])) {
                    delete_transient('ml2_reset_'.$rt);
                    $this->delc(self::C_RESET);
                    $this->ok(['ready'=>true,'login'=>$d['login'],'key'=>$d['key']]);
                }
            }
            $this->ok(['ready'=>false]);
        }

        public function auth_save_new_password() {
            check_ajax_referer(self::NONCE, 'nonce');

            $login = sanitize_text_field($_POST['login'] ?? '');
            $key   = sanitize_text_field($_POST['key']   ?? '');
            $p1    = (string)($_POST['pass1'] ?? '');
            $p2    = (string)($_POST['pass2'] ?? '');

            if ($p1 !== $p2)   $this->err('Пароли не совпадают');
            if (strlen($p1)<6) $this->err('Пароль слишком короткий');

            $user = check_password_reset_key($key, $login);
            if (is_wp_error($user)) $this->err('Ссылка недействительна или устарела');

            reset_password($user, $p1);

            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($user->ID);

            $this->ok(['redirect' => $this->myacc()]);
        }

        /* ---------- AJAX: localStorage Sync (likes + cart) ---------- */
        public function auth_sync_storage() {
            // Nonce majburiy emas (login keyin ham chaqiriladi); lekin bo‘lsa tekshiramiz
            $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
            if ($nonce && !wp_verify_nonce($nonce, self::NONCE)) {
                $this->err('Bad nonce');
            }

            $likes = isset($_POST['likes']) ? wp_unslash($_POST['likes']) : '[]';
            $cart  = isset($_POST['cart'])  ? wp_unslash($_POST['cart'])  : '[]';

            $likes = json_decode($likes, true);
            $cart  = json_decode($cart,  true);
            if (!is_array($likes)) $likes = [];
            if (!is_array($cart))  $cart  = [];

            // 1) Wish/Like → user_meta (dedupe)
            $user_id = get_current_user_id();
            if ($user_id) {
                $meta_key = 'theme_wishlist_ids';
                $existing = get_user_meta($user_id, $meta_key, true);
                if (!is_array($existing)) $existing = [];
                $incoming = array_filter(array_map('absint', $likes));
                $merged   = array_values(array_unique(array_merge($existing, $incoming)));
                update_user_meta($user_id, $meta_key, $merged);
            }

            // 2) Cart merge → Woo cart (sessionga)
            if (class_exists('WooCommerce') && function_exists('WC') && !empty($cart)) {
                if (!WC()->cart) wc_load_cart();

                foreach ($cart as $row) {
                    $pid  = isset($row['product_id']) ? absint($row['product_id']) : 0;
                    $qty  = isset($row['quantity'])   ? max(1, absint($row['quantity'])) : 1;
                    $vid  = isset($row['variation_id']) ? absint($row['variation_id']) : 0;
                    $vars = [];

                    // optional: variations (array of {attr_name: value})
                    if (!empty($row['variation']) && is_array($row['variation'])) {
                        foreach ($row['variation'] as $k=>$v) {
                            $vars[$k] = sanitize_text_field($v);
                        }
                    }

                    if ($vid) {
                        // variable product
                        @WC()->cart->add_to_cart($pid, $qty, $vid, $vars);
                    } else {
                        // simple product
                        @WC()->cart->add_to_cart($pid, $qty);
                    }
                }
            }

            $this->ok(['synced'=>true]);
        }

        /* ---------- REST: /wp-json/ml2/v1/login ---------- */
        public function register_rest() {
            register_rest_route('ml2/v1', '/login', [
                'methods'  => 'POST',
                'permission_callback' => '__return_true',
                'callback' => function (WP_REST_Request $req) {
                    $login_raw = trim((string)$req->get_param('log'));
                    // Yangi: p_enc yoki pwd
                    $pwd  = (string)$req->get_param('pwd');
                    $penc = (string)$req->get_param('p_enc');

                    if (!$pwd && $penc) {
                        $dec = base64_decode($penc, true);
                        if ($dec !== false) {
                            $j = json_decode($dec, true);
                            if (is_array($j) && isset($j['p'])) {
                                $pwd = (string)$j['p'];
                            } else {
                                $parts = explode('|', $dec, 2);
                                $pwd = (string)($parts[0] ?? '');
                            }
                        }
                    }

                    if (!$login_raw || !$pwd) {
                        return new WP_REST_Response(['ok'=>false,'message'=>'Неверный адрес электронной почты или пароль'], 400);
                    }

                    $login_try = $login_raw;
                    $user_by_email = null;
                    if (is_email($login_raw)) {
                        $user_by_email = get_user_by('email', $login_raw);
                        if (!$user_by_email) {
                            return new WP_REST_Response(['ok'=>false,'message'=>'Аккаунт с таким e-mail не найден'], 400);
                        }
                        $login_try = $user_by_email->user_login;
                    }

                    $creds = [
                        'user_login'    => $login_try,
                        'user_password' => $pwd,
                        'remember'      => true,
                    ];
                    $u = wp_signon($creds);

                    if (is_wp_error($u)) {
                        if ($user_by_email && wp_check_password($pwd, $user_by_email->user_pass, $user_by_email->ID)) {
                            wp_set_current_user($user_by_email->ID);
                            wp_set_auth_cookie($user_by_email->ID, true);
                            if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($user_by_email->ID);
                            return new WP_REST_Response(['ok'=>true,'redirect'=>$this->myacc(),'forced'=>true], 200);
                        }
                        return new WP_REST_Response(['ok'=>false,'message'=>'Неверный адрес электронной почты или пароль'], 400);
                    }

                    if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($u->ID);
                    return new WP_REST_Response(['ok'=>true,'redirect'=>$this->myacc()], 200);
                }

            ]);
        }

        /* ---------- Assets ---------- */
        public function assets() {
            $path = get_stylesheet_directory().'/assets/js/auth-modal.v2.js';
            $ver  = file_exists($path) ? filemtime($path) : null;

            wp_enqueue_script(
                'auth-modal-v2',
                get_stylesheet_directory_uri().'/assets/js/auth-modal.v2.js',
                ['jquery'], $ver, true
            );

            wp_localize_script('auth-modal-v2', 'MODAL_AUTH_V2', [
                'ajax_url'       => admin_url('admin-ajax.php'),
                'rest_login'     => rest_url('ml2/v1/login'),
                'nonce'          => wp_create_nonce(self::NONCE),
                'my_account_url' => $this->myacc(),
                'assets'         => get_stylesheet_directory_uri().'/assets',
            ]);
        }
    }
    new Theme_Modal_Auth_V2();
}
