<?php
// ================================================================================================================================
/**
 *  lIke ajax
 */
add_action('wp_enqueue_scripts', function () {
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('jquery');
    }
    // Wishlist JS faylingizni ulaysiz (yoki pastdagi <script>ni inline qo‘yishingiz mumkin)
//    wp_register_script('theme-wishlist', get_template_directory_uri().'/assets/js/wishlist.js', ['jquery'], null, true);
    wp_localize_script('theme-wishlist', 'WISHLIST', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wishlist_nonce'),
        'isLoggedIn' => is_user_logged_in(),
        'userId'     => get_current_user_id(),
    ]);
    wp_enqueue_script('theme-wishlist');
});

// === WISHLIST HELPERS (server-side) =========================================
if (!function_exists('galeon_get_user_wishlist_raw')) {
    // Joriy foydalanuvchi wishlist'i (login bo'lsa)
    function galeon_get_user_wishlist_raw() {
        if (!is_user_logged_in()) return [];
        $list = get_user_meta(get_current_user_id(), 'wishlist_v1', true);
        return is_array($list) ? $list : [];
    }
}

if (!function_exists('galeon_current_wishlist_ids')) {
    // Qulay IDlar: "pid" yoki "pid:vid"
    function galeon_current_wishlist_ids() {
        if (!is_user_logged_in()) return [];
        $ids = [];
        foreach (galeon_get_user_wishlist_raw() as $it) {
            $pid = absint($it['pid'] ?? 0);
            $vid = absint($it['vid'] ?? 0);
            if (!$pid) continue;
            $ids[] = $vid ? ($pid . ':' . $vid) : (string)$pid;
        }
        return $ids;
    }
}

if (!function_exists('my_is_in_wishlist')) {
    function my_is_in_wishlist($product_id) {
        static $cache = null;
        if (!is_user_logged_in()) return false;

        // ✅ Normalize qilingan ro‘yxatdan kesh tuzamiz
        if ($cache === null) {
            $cache = ['pids'=>[], 'vids'=>[]];
            foreach (my_wl_user_list(get_current_user_id()) as $it) {
                $pid = absint($it['pid']);
                $vid = absint($it['vid']);
                if ($pid) $cache['pids'][$pid] = true;
                if ($vid) $cache['vids'][$vid] = true;
            }
        }

        $pid = absint($product_id);
        if (isset($cache['pids'][$pid]) || isset($cache['vids'][$pid])) return true;

        $product = wc_get_product($pid);
        if (!$product) return false;

        // Variation -> parent wishlistdami?
        if ($product->is_type('variation')) {
            $parent_id = $product->get_parent_id();
            return $parent_id && isset($cache['pids'][$parent_id]);
        }

        // Variable parent -> farzandlardan biri wishlistdami?
        if ($product->is_type('variable')) {
            foreach ((array)$product->get_children() as $vid) {
                if (isset($cache['vids'][$vid])) return true;
            }
        }
        return false;
    }
}


if (!function_exists('my_wl_user_list')) {
    function my_wl_user_list($user_id) {
        $raw = get_user_meta($user_id, 'wishlist_v1', true);
        return is_array($raw) ? $raw : [];
    }
}

if (!function_exists('my_wl_save')) {
    function my_wl_save($user_id, $items) {
        $seen = [];
        $out  = [];
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
                'ts'  => isset($it['ts']) ? intval($it['ts']) : time(),
            ];
        }
        update_user_meta($user_id, 'wishlist_v1', $out);
        return $out;
    }
}

/**
 * 🔧 MUHIM: PID/VID ni normallashtiramiz.
 * Agar $pid — variation post bo'lsa va $vid=0 bo'lsa:
 *   $vid = $pid  (variation_id)
 *   $pid = parent_id (asosiy product)
 */
if (!function_exists('my_wl_normalize_pair')) {
    function my_wl_normalize_pair($pid, $vid) {
        $pid = absint($pid);
        $vid = absint($vid);

        if ($pid) {
            $prod = wc_get_product($pid);
            if ($prod && $prod->is_type('variation')) {
                // "pid=variation, vid=0" holatini to'g'rilaymiz
                if ($vid === 0) {
                    $parent_id = absint($prod->get_parent_id());
                    if ($parent_id) {
                        $vid = $pid;      // variation_id
                        $pid = $parent_id; // parent_id
                    }
                } else {
                    // Agar noto'g'ri juftlik: pid=variation, vid=another → parentga ko'taramiz
                    $parent_id = absint($prod->get_parent_id());
                    if ($parent_id) {
                        $pid = $parent_id;
                    }
                }
            }
        }
        return [$pid, $vid];
    }
}

if (!function_exists('my_wl_toggle')) {
    /**
     * Toggle (parent/child mantiq bilan)
     * - Parent unlike → parent + barcha variatsiyalar o‘chadi
     * - Variation unlike → o‘sha variation va parent yozuvi (bo‘lsa) o‘chadi
     * + Normalizatsiya: pid=variation, vid=0 holatlarini to'g'rilash
     */
    function my_wl_toggle($user_id, $pid, $vid) {
        // 0) Normalizatsiya (ENG MUHIM QISM)
        list($pid, $vid) = my_wl_normalize_pair($pid, $vid);

        $pid = absint($pid);
        $vid = absint($vid);
        if (!$pid) return ['status' => 'error', 'count' => 0];

        // 1) Mavjud ro'yxatni o'qib olamiz
        $list = my_wl_user_list($user_id);

        // 2) Hozirgi holat
        $hasExact      = false; // aniq pid:vid bor-mi
        $hasAnyForPid  = false; // shu pid bo‘yicha istalgan yozuv bor-mi
        $hasParentOnly = false; // pid, vid=0 yozuvi bor-mi

        foreach ((array)$list as $it) {
            $pp = absint($it['pid'] ?? 0);
            $vv = absint($it['vid'] ?? 0);
            if (!$pp) continue;

            if ($pp === $pid) {
                $hasAnyForPid = true;
                if ($vv === 0) $hasParentOnly = true;
                if ($pp === $pid && $vv === $vid) $hasExact = true;
            }
        }

        // 3) UI’dagi “active” mantiqqa moslab:
        $isActiveNow = ($vid === 0) ? $hasAnyForPid : ($hasExact || $hasParentOnly);

        if ($isActiveNow) {
            // REMOVE
            if ($vid === 0) {
                // Parent unlike → shu parentga tegishli BARCHA yozuvlarni o‘chir
                foreach ($list as $i => $it) {
                    if (absint($it['pid']) === $pid) unset($list[$i]);
                }
            } else {
                // Variation unlike → o‘sha variationni o‘chir
                foreach ($list as $i => $it) {
                    if (absint($it['pid']) === $pid && absint($it['vid']) === $vid) unset($list[$i]);
                }
                // Va parent yozuvi bo‘lsa — uni ham o‘chir (aks holda my_is_in_wishlist true bo‘lib qoladi)
                foreach ($list as $i => $it) {
                    if (absint($it['pid']) === $pid && absint($it['vid']) === 0) { unset($list[$i]); break; }
                }
            }
            $status = 'removed';
        } else {
            // ADD
            $list[] = ['pid'=>$pid,'vid'=>$vid,'ts'=>time()];
            $status = 'added';
        }

        // 4) Saqlash (de-dupe bilan)
        $list = array_values($list);
        $list = my_wl_save($user_id, $list);

        // 5) Javob
        return ['status' => $status, 'count' => count($list)];
    }
}

// ====== AJAX ENDPOINTS =======================================================

// Toggle (faqat logged-in)
add_action('wp_ajax_my_wishlist_toggle', function () {
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'not logged in'], 401);
    check_ajax_referer('wishlist_nonce', 'nonce');

    $pid = isset($_POST['pid']) ? absint($_POST['pid']) : 0;
    $vid = isset($_POST['vid']) ? absint($_POST['vid']) : 0;

    $res = my_wl_toggle(get_current_user_id(), $pid, $vid);
    nocache_headers();
    wp_send_json_success($res);
});

// Merge localStorage → user_meta (faqat logged-in, bir martalik strategiya js’da)
add_action('wp_ajax_my_wishlist_merge', function () {
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'not logged in'], 401);
    check_ajax_referer('wishlist_nonce', 'nonce');

    $items = [];
    if (isset($_POST['items'])) {
        $json  = is_array($_POST['items']) ? wp_json_encode($_POST['items']) : wp_unslash($_POST['items']);
        $items = json_decode($json, true) ?: [];
    }

    $current = my_wl_user_list(get_current_user_id());
    $all     = array_merge($current, (array)$items);
    $saved   = my_wl_save(get_current_user_id(), $all);

    nocache_headers();
    wp_send_json_success(['count' => count($saved)]);
});

// Ro‘yxatni olish (logged-in uchun)
add_action('wp_ajax_my_wishlist_list', function () {
    check_ajax_referer('wishlist_nonce', 'nonce');
    if (!is_user_logged_in()) {
        wp_send_json_success(['items' => []]);
    }
    $items = my_wl_user_list(get_current_user_id());
    nocache_headers();
    wp_send_json_success(['items' => array_values($items)]);
});

// ================================================================================================================================

// Bir xil formatga keltirib, hozirgi foydalanuvchining wishlistidagi product ID'larni qaytaradi
if (!function_exists('galeon_current_wishlist_ids')) {
    function galeon_current_wishlist_ids() {
        $ids = [];

        // Logged-in foydalanuvchi
        if (is_user_logged_in()) {
            $list = get_user_meta(get_current_user_id(), 'wishlist_v1', true);
            if (is_array($list)) {
                foreach ($list as $it) {
                    if (is_array($it) && isset($it['pid'])) {
                        $ids[] = absint($it['pid']);
                    } elseif (is_numeric($it)) {
                        $ids[] = absint($it);
                    }
                }
            }
        } else {
            // Guest: agar cookie mavjud bo'lsa, undan ham o'qib ko'ramiz (ixtiyoriy)
            if (!empty($_COOKIE['wishlist_v1'])) {
                $cookie_raw = wp_unslash($_COOKIE['wishlist_v1']);
                $cookie_arr = json_decode($cookie_raw, true);
                if (is_array($cookie_arr)) {
                    foreach ($cookie_arr as $it) {
                        if (is_array($it) && isset($it['pid'])) {
                            $ids[] = absint($it['pid']);
                        } elseif (is_numeric($it)) {
                            $ids[] = absint($it);
                        }
                    }
                }
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        return $ids;
    }
}


// === WISHLIST: AJAX renderer (login va guest uchun) ===
add_action('wp_ajax_nopriv_galeon_wishlist_render', 'galeon_wishlist_render');
add_action('wp_ajax_galeon_wishlist_render', 'galeon_wishlist_render');

function galeon_wishlist_render() {
    // Keshlashni oldini olish
    nocache_headers();

    // nonce
    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wishlist_nonce')) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }

    // LS’dan kelgan items — faqat guest uchun ishlatamiz
    $raw_items = [];
    if (isset($_POST['items'])) {
        $json = is_array($_POST['items']) ? wp_json_encode($_POST['items']) : wp_unslash($_POST['items']);
        $raw_items = json_decode($json, true) ?: [];
    }

    // sanitize + de-dupe (guest holati uchun)
    $seen = [];
    $ls_items = [];
    foreach ((array)$raw_items as $it) {
        $pid = isset($it['pid']) ? absint($it['pid']) : 0;
        $vid = isset($it['vid']) ? absint($it['vid']) : 0;
        if (!$pid) continue;
        $k = $pid . ':' . $vid;
        if (isset($seen[$k])) continue;
        $seen[$k] = true;
        $ls_items[] = ['pid'=>$pid,'vid'=>$vid,'ts'=> isset($it['ts']) ? intval($it['ts']) : time()];
    }

    // === MUHIM TAMOYIL ===
    // Logged-in foydalanuvchi uchun authoritative manba — faqat user_meta.
    // Hech qachon LS bilan merge QILMAYMIZ bu yerda.
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $items_to_render = get_user_meta($user_id, 'wishlist_v1', true);
        $items_to_render = is_array($items_to_render) ? array_values($items_to_render) : [];
    } else {
        // Guest: faqat LS’dan render
        $items_to_render = $ls_items;
    }

    // HTML yig‘amiz
    ob_start();
    if ($items_to_render) {
        foreach ($items_to_render as $it) {
            $pid = absint($it['pid']);
            $vid = absint($it['vid']);
            $product = $vid ? wc_get_product($vid) : wc_get_product($pid);
            if (!$product) continue;

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
    $logged_in = is_user_logged_in();
    // Frontend LS’ni sinxronlashtirish uchun toza ro‘yxatni qaytaramiz
    wp_send_json_success([
        'html'  => $html,
        'count' => count($items_to_render),
        'logged_in'  => $logged_in ? 1 : 0,
        'items' => array_values($items_to_render), // LS shu bilan yangilanadi
    ]);
}