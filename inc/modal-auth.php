<?php
/** ================================
 *  MODAL AUTH (AJAX + MAGIC LINKS)
 * ================================= */
if ( ! class_exists('Theme_Modal_Auth')) {
    class Theme_Modal_Auth {
        const NONCE    = 'modal_auth_nonce';
        const C_VERIFY = 'ml_email_verified';   // email verify cookie flag
        const C_RESET  = 'ml_pw_reset_ready';   // password reset cookie flag
        const TTL      = 900; // 15 min

        public function __construct() {
            add_action('init',              [$this, 'add_routes']);
            add_action('template_redirect', [$this, 'handle_magic']);

            // AJAX (guest)
            add_action('wp_ajax_nopriv_auth_login',             [$this, 'auth_login']);
            add_action('wp_ajax_nopriv_auth_register_start',    [$this, 'auth_register_start']);
            add_action('wp_ajax_nopriv_auth_register_check',    [$this, 'auth_register_check']);
            add_action('wp_ajax_nopriv_auth_forgot_start',      [$this, 'auth_forgot_start']);
            add_action('wp_ajax_nopriv_auth_forgot_check',      [$this, 'auth_forgot_check']);
            add_action('wp_ajax_nopriv_auth_save_new_password', [$this, 'auth_save_new_password']);

            // REST fallback (Imunify admin-ajax’ni bloklaganda)
            add_action('rest_api_init', [$this, 'register_rest']);

            // Assets
            add_action('wp_enqueue_scripts',  [$this, 'assets']);

            // Pretty permalinklar ishlashi uchun
            add_action('after_switch_theme', function(){ flush_rewrite_rules(); });
            add_action('init', function () {
                if (!get_option('ml_magic_rules_flushed')) {
                    flush_rewrite_rules(false);
                    update_option('ml_magic_rules_flushed', 1);
                }
            }, 99);

            // Logoutdan keyin bosh sahifaga
            add_filter('woocommerce_logout_default_redirect_url', function ($url) {
                return home_url('/');
            });
        }

        private function ok($d = [])       { wp_send_json(array_merge(['ok'=>true], $d)); }
        private function err($m,$c='ERR')  { wp_send_json(['ok'=>false,'error'=>$c,'message'=>$m], 400); }
        private function setc($n,$v)       { setcookie($n,$v,time()+self::TTL, COOKIEPATH?:'/', COOKIE_DOMAIN?:'', is_ssl(), false); }
        private function delc($n)          { setcookie($n,'',time()-3600,      COOKIEPATH?:'/', COOKIE_DOMAIN?:'', is_ssl(), false); }
        private function sign($s)          { return hash_hmac('sha256',$s, wp_salt('auth')); }
        private function myacc()           { return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/'); }

        /** ---------- Routes: /magic/verify , /magic/reset ---------- */
        public function add_routes() {
            // Pretty VERIFY: /magic/verify/u/123/t/abc/exp/1700000000/sig/HASH/
            add_rewrite_rule(
                '^magic/verify/u/([0-9]+)/t/([^/]+)/exp/([0-9]+)/sig/([^/]+)/?$',
                'index.php?magic_action=verify&u=$matches[1]&t=$matches[2]&exp=$matches[3]&sig=$matches[4]',
                'top'
            );

            // Pretty RESET: /magic/reset/login/USER/key/KEY/
            add_rewrite_rule(
                '^magic/reset/login/([^/]+)/key/([^/]+)/?$',
                'index.php?magic_action=reset&login=$matches[1]&key=$matches[2]',
                'top'
            );

            // Fallbacklar
            add_rewrite_rule('^magic/(verify|reset)/?', 'index.php?magic_action=$matches[1]', 'top');

            add_filter('query_vars', function ($v) {
                foreach (['magic_action','u','t','exp','sig','login','key','token','rt'] as $k) $v[]=$k;
                return $v;
            });
        }

        public function handle_magic() {
            $a = get_query_var('magic_action');
            if (!$a) return;

            if ($a === 'verify') {
                $u   = absint( get_query_var('u')   ?: ($_GET['u']   ?? 0) );
                $t   = sanitize_text_field( get_query_var('t')   ?: ($_GET['t']   ?? '') );
                $exp = absint( get_query_var('exp') ?: ($_GET['exp'] ?? 0) );
                $sig = sanitize_text_field( get_query_var('sig') ?: ($_GET['sig'] ?? '') );

                // &amp;xxx fallback
                if (!$sig && !empty($_GET['amp;sig'])) $sig = sanitize_text_field($_GET['amp;sig']);
                if (!$t   && !empty($_GET['amp;t']))   $t   = sanitize_text_field($_GET['amp;t']);
                if (!$exp && !empty($_GET['amp;exp'])) $exp = absint($_GET['amp;exp']);
                if (!$u   && !empty($_GET['amp;u']))   $u   = absint($_GET['amp;u']);

                if (!$u || !$t || !$exp || !$sig) wp_die('Bad params');
                if ($exp < time())                wp_die('Link expired');

                $payload = "$u|$t|$exp";
                if (!hash_equals($this->sign($payload), $sig))         wp_die('Bad signature');
                if (get_user_meta($u, 'ml_email_token_used_'.$t, true)) wp_die('Already used');

                update_user_meta($u, 'email_verified', 1);
                update_user_meta($u, 'ml_email_token_used_'.$t, time());

                $rt = wp_generate_password(12, false);
                set_transient('ml_verify_'.$rt, $u, self::TTL);
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
                set_transient('ml_reset_'.$rt, ['login'=>$login,'key'=>$key], self::TTL);
                $this->setc(self::C_RESET, $rt);

                wp_safe_redirect(home_url('/?reset=1')); exit;
            }
        }

        /** ---------- AJAX: login ---------- */
        public function auth_login() {
            check_ajax_referer(self::NONCE, 'nonce');

            $login_raw = trim((string)($_POST['log'] ?? ''));
            $pwd       = (string)($_POST['pwd'] ?? '');

            if (!$login_raw || !$pwd) {
                $this->err('Неверный адрес электронной почты или пароль');
            }

            // username/email mapping
            $login_try = $login_raw;
            $user_by_email = null;
            if (is_email($login_raw)) {
                $user_by_email = get_user_by('email', $login_raw);
                if ($user_by_email) $login_try = $user_by_email->user_login;
            }

            $creds = [
                'user_login'    => $login_try,
                'user_password' => $pwd,
                'remember'      => true,
            ];
            $u = wp_signon($creds);

            // Agar birinchi urinish xato va foydalanuvchi email bo‘lsa
            if (is_wp_error($u) && !$user_by_email && is_email($login_raw)) {
                $user_by_email = get_user_by('email', $login_raw);
                if ($user_by_email) {
                    $creds['user_login'] = $user_by_email->user_login;
                    $u = wp_signon($creds);
                }
            }

            // Manual fallback: parol to‘g‘ri bo‘lsa sessiya o‘rnatamiz
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

        /** ---------- AJAX: register/verify ---------- */
        public function auth_register_start() {
            check_ajax_referer(self::NONCE, 'nonce');
            $email = sanitize_email($_POST['user_email'] ?? '');
            $pass  = (string)($_POST['user_pass']  ?? '');
            $name  = sanitize_text_field($_POST['first_name'] ?? '');

            if (!is_email($email))    $this->err('Неверный e-mail');
            if (email_exists($email)) $this->err('E-mail уже зарегистрирован');
            if (strlen($pass) < 6)    $this->err('Пароль слишком короткий');

            $uid = wp_create_user($email, $pass, $email);
            if (is_wp_error($uid))    $this->err($uid->get_error_message());
            if ($name) update_user_meta($uid, 'first_name', $name);

            $t   = wp_generate_password(20, false);
            $exp = time() + self::TTL;
            $sig = $this->sign($uid.'|'.$t.'|'.$exp);
            update_user_meta($uid, 'ml_email_token_'.$t, $exp);

            $url = home_url(sprintf(
                '/magic/verify/u/%d/t/%s/exp/%d/sig/%s/',
                $uid, rawurlencode($t), $exp, rawurlencode($sig)
            ));

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

            // rt param (agar verify redirect rt bilan kelsa)
            $rt = sanitize_text_field($_POST['rt'] ?? '');
            if ($rt) {
                $uid = get_transient('ml_verify_'.$rt);
                if ($uid) {
                    delete_transient('ml_verify_'.$rt);
                    $this->delc(self::C_VERIFY);
                    wp_set_current_user($uid);
                    wp_set_auth_cookie($uid);
                    if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($uid);
                    $this->ok(['verified'=>true, 'redirect'=>$this->myacc()]);
                }
            }

            // cookie orqali
            $rt = $_COOKIE[self::C_VERIFY] ?? '';
            if ($rt) {
                $uid = get_transient('ml_verify_'.$rt);
                if ($uid) {
                    delete_transient('ml_verify_'.$rt);
                    $this->delc(self::C_VERIFY);
                    wp_set_current_user($uid);
                    wp_set_auth_cookie($uid);
                    if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($uid);
                    $this->ok(['verified'=>true, 'redirect'=>$this->myacc()]);
                }
            }
            $this->ok(['verified'=>false]);
        }

        /** ---------- AJAX: forgot/reset ---------- */
        public function auth_forgot_start() {
            check_ajax_referer(self::NONCE, 'nonce');
            $email = sanitize_email($_POST['user_email'] ?? '');
            if (!is_email($email)) $this->err('Неверный e-mail');

            $user = get_user_by('email', $email);
            if (!$user) $this->ok(['email'=>$email]); // leaky info bermaymiz

            $key = get_password_reset_key($user);
            if (is_wp_error($key)) $this->err($key->get_error_message());

            $url = home_url(sprintf(
                '/magic/reset/login/%s/key/%s/',
                rawurlencode($user->user_login), rawurlencode($key)
            ));

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
                $d = get_transient('ml_reset_'.$rt);
                if ($d && !empty($d['login']) && !empty($d['key'])) {
                    delete_transient('ml_reset_'.$rt);
                    $this->delc(self::C_RESET);
                    $this->ok(['ready'=>true,'login'=>$d['login'],'key'=>$d['key']]);
                }
            }

            $rt = $_COOKIE[self::C_RESET] ?? '';
            if ($rt) {
                $d = get_transient('ml_reset_'.$rt);
                if ($d && !empty($d['login']) && !empty($d['key'])) {
                    delete_transient('ml_reset_'.$rt);
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

            if ($p1 !== $p2)  $this->err('Пароли не совпадают');
            if (strlen($p1)<6)$this->err('Пароль слишком короткий');

            $user = check_password_reset_key($key, $login);
            if (is_wp_error($user)) $this->err('Ссылка недействительна или устарела');

            reset_password($user, $p1);

            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($user->ID);

            $this->ok(['redirect' => $this->myacc()]);
        }

        /** ---------- REST fallback ---------- */
        public function register_rest() {
            register_rest_route('ml/v1', '/login', [
                'methods'  => 'POST',
                'permission_callback' => '__return_true',
                'callback' => function (WP_REST_Request $req) {
                    $login_raw = trim((string)$req->get_param('log'));
                    $pwd       = (string)$req->get_param('pwd');

                    if (!$login_raw || !$pwd) {
                        return new WP_REST_Response(['ok'=>false,'message'=>'Неверный адрес электронной почты или пароль'], 400);
                    }

                    $login_try = $login_raw;
                    $user_by_email = null;
                    if (is_email($login_raw)) {
                        $user_by_email = get_user_by('email', $login_raw);
                        if ($user_by_email) $login_try = $user_by_email->user_login;
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

                    if (function_exists('wc_set_customer_auth_cookie')) {
                        wc_set_customer_auth_cookie($u->ID);
                    }
                    return new WP_REST_Response(['ok'=>true,'redirect'=>$this->myacc()], 200);
                }
            ]);
        }

        /** ---------- Assets ---------- */
        public function assets() {
            $path = get_stylesheet_directory().'/assets/js/auth-otp.js';
            $ver  = file_exists($path) ? filemtime($path) : null;

            wp_enqueue_script(
                'auth-otp',
                get_stylesheet_directory_uri().'/assets/js/auth-otp.js',
                ['jquery'], $ver, true
            );
            wp_localize_script('auth-otp', 'MODAL_AUTH', [
                'ajax_url'       => admin_url('admin-ajax.php'),
                'rest_login'     => rest_url('ml/v1/login'),
                'nonce'          => wp_create_nonce(self::NONCE),
                'my_account_url' => $this->myacc(),
                'assets'         => get_stylesheet_directory_uri().'/assets',
            ]);
        }
    }
    new Theme_Modal_Auth();
}