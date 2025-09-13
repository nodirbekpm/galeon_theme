<?php
/**
 * Plugin Name: Modal Auth (AJAX)
 * Description: Modal + AJAX login/register/forgot for WooCommerce with email verification and password reset flows.
 */

if (!defined('ABSPATH')) exit;

class Modal_Auth_Ajax {
    const NONCE_ACTION = 'modal_auth_nonce';
    const COOKIE_VERIFY = 'ml_email_verified';   // email verify flag (token id)
    const COOKIE_RESET  = 'ml_pw_reset_ready';   // reset flag (token id)
    const VERIFY_TTL    = 900; // 15 min

    public function __construct() {
        add_action('init', [$this, 'register_query_vars']);
        add_action('template_redirect', [$this, 'handle_magic_endpoints']);

        // AJAX (nopriv + priv)
        add_action('wp_ajax_nopriv_auth_login',              [$this,'auth_login']);
        add_action('wp_ajax_auth_login',                      [$this,'auth_login']);
        add_action('wp_ajax_nopriv_auth_register_start',     [$this,'auth_register_start']);
        add_action('wp_ajax_auth_register_start',             [$this,'auth_register_start']);
        add_action('wp_ajax_nopriv_auth_register_check',     [$this,'auth_register_check']);
        add_action('wp_ajax_auth_register_check',             [$this,'auth_register_check']);
        add_action('wp_ajax_nopriv_auth_forgot_start',       [$this,'auth_forgot_start']);
        add_action('wp_ajax_auth_forgot_start',               [$this,'auth_forgot_start']);
        add_action('wp_ajax_nopriv_auth_forgot_check',       [$this,'auth_forgot_check']);
        add_action('wp_ajax_auth_forgot_check',               [$this,'auth_forgot_check']);
        add_action('wp_ajax_nopriv_auth_save_new_password',  [$this,'auth_save_new_password']);
        add_action('wp_ajax_auth_save_new_password',          [$this,'auth_save_new_password']);

        // Skript + localized data
        add_action('wp_enqueue_scripts', function () {
            wp_register_script('modal-auth', plugins_url('modal-auth.js', __FILE__), ['jquery'], null, true);
            $my_account = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
            wp_localize_script('modal-auth', 'MODAL_AUTH', [
                'ajax_url'       => admin_url('admin-ajax.php'),
                'nonce'          => wp_create_nonce(self::NONCE_ACTION),
                'my_account_url' => $my_account,
                'assets_base'    => trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images',
                // Frontendga dev muhit ekanini ham yetkazamiz (istasa ko'rsatsin)
                'is_local'       => $this->is_local(),
            ]);
            wp_enqueue_script('modal-auth');
        });

        // Email xatolarini loglash (WP_DEBUG logga)
        add_action('wp_mail_failed', [$this, 'mail_failed_logger']);

        // Rewrite flush (aktiv/deaktiv)
        register_activation_hook(__FILE__, function(){ flush_rewrite_rules(); });
        register_deactivation_hook(__FILE__, function(){ flush_rewrite_rules(); });
    }

    /* ---------- Helpers ---------- */
    private function json_ok($data = []) {
        wp_send_json(array_merge(['ok' => true], $data));
    }
    private function json_err($msg, $code = 'ERR') {
        wp_send_json(['ok' => false, 'error' => $code, 'message' => $msg], 400);
    }
    private function set_flag_cookie($name, $val='1') {
        setcookie($name, $val, time() + self::VERIFY_TTL, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '', is_ssl(), false);
    }
    private function clear_flag_cookie($name) {
        setcookie($name, '', time() - 3600, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '', is_ssl(), false);
    }
    private function sign($data) {
        return hash_hmac('sha256', $data, wp_salt('auth'));
    }
    private function is_local() {
        // WP 5.5+: wp_get_environment_type(); yoki WP_DEBUG
        if (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'local') return true;
        if (defined('WP_DEBUG') && WP_DEBUG && !empty($_SERVER['SERVER_NAME']) && (strpos($_SERVER['SERVER_NAME'],'localhost')!==false || strpos($_SERVER['SERVER_NAME'],'127.0.0.1')!==false)) return true;
        return false;
    }
    public function mail_failed_logger($wp_error){
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[modal-auth] wp_mail_failed: ' . print_r($wp_error, true));
        }
    }

    /* ---------- Routing ---------- */
    public function register_query_vars() {
        add_rewrite_rule('^magic/(verify|reset)/?', 'index.php?magic_action=$matches[1]', 'top');
        add_filter('query_vars', function ($vars) {
            array_push($vars, 'magic_action','u','t','exp','sig','login','key');
            return $vars;
        });
    }

    public function handle_magic_endpoints() {
        $action = get_query_var('magic_action');
        if (!$action) return;

        if ($action === 'verify') {
            // /magic/verify?u=ID&t=TOKEN&exp=TS&sig=SIG
            $u   = absint(get_query_var('u'));
            $t   = sanitize_text_field(get_query_var('t'));
            $exp = absint(get_query_var('exp'));
            $sig = sanitize_text_field(get_query_var('sig'));
            if (!$u || !$t || !$exp || !$sig) wp_die('Bad params');
            $check = $u . '|' . $t . '|' . $exp;
            if ($exp < time()) wp_die('Link expired');
            if (!hash_equals($this->sign($check), $sig)) wp_die('Bad signature');

            // single-use
            $used = get_user_meta($u, 'ml_email_token_used_' . $t, true);
            if ($used) wp_die('Already used');

            update_user_meta($u, 'email_verified', 1);
            update_user_meta($u, 'ml_email_token_used_' . $t, time());

            // Auto-login uchun transient token id
            $rt = wp_generate_password(12, false);
            set_transient('ml_verify_' . $rt, $u, self::VERIFY_TTL);
            $this->set_flag_cookie(self::COOKIE_VERIFY, $rt);

            wp_safe_redirect(home_url('/?verified=1'));
            exit;
        }

        if ($action === 'reset') {
            // /magic/reset?login=...&key=...
            $login = sanitize_text_field(get_query_var('login'));
            $key   = sanitize_text_field(get_query_var('key'));
            $user  = check_password_reset_key($key, $login);
            if (is_wp_error($user)) wp_die('Invalid or expired reset key');

            // Reset form uchun transient token
            $rt = wp_generate_password(12, false);
            set_transient('ml_reset_'.$rt, ['login'=>$login, 'key'=>$key], self::VERIFY_TTL);
            $this->set_flag_cookie(self::COOKIE_RESET, $rt);

            wp_safe_redirect(home_url('/?reset=1'));
            exit;
        }
    }

    /* ---------- AJAX handlers ---------- */

    public function auth_login() {
        $nonce_ok = check_ajax_referer(self::NONCE_ACTION, 'nonce', false);
        if (!$nonce_ok) return $this->json_err('Security check failed (nonce).', 'NONCE');

        $creds = [
            'user_login'    => sanitize_text_field($_POST['log'] ?? ''),
            'user_password' => $_POST['pwd'] ?? '',
            'remember'      => true,
        ];
        $user = wp_signon($creds);
        if (is_wp_error($user)) return $this->json_err($user->get_error_message(), 'LOGIN');
        if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($user->ID);
        $this->json_ok(['redirect' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/')]);
    }

    public function auth_register_start() {
        $nonce_ok = check_ajax_referer(self::NONCE_ACTION, 'nonce', false);
        if (!$nonce_ok) return $this->json_err('Security check failed (nonce).', 'NONCE');

        $email = sanitize_email($_POST['user_email'] ?? '');
        $pass  = $_POST['user_pass'] ?? '';
        $name  = sanitize_text_field($_POST['first_name'] ?? '');
        if (!is_email($email)) return $this->json_err('Неверный e-mail', 'EMAIL');
        if (email_exists($email)) return $this->json_err('E-mail уже зарегистрирован', 'EMAIL_EXISTS');

        $user_id = wp_create_user($email, $pass, $email);
        if (is_wp_error($user_id)) return $this->json_err($user_id->get_error_message(), 'REGISTER');
        if ($name) update_user_meta($user_id, 'first_name', $name);

        // Verify link
        $t = wp_generate_password(20, false);
        $exp = time() + self::VERIFY_TTL;
        $payload = $user_id . '|' . $t . '|' . $exp;
        $sig = $this->sign($payload);
        update_user_meta($user_id, 'ml_email_token_' . $t, $exp);

        $url   = home_url("/magic/verify?u={$user_id}&t={$t}&exp={$exp}&sig={$sig}");
        $sent = wp_mail($email, 'Подтверждение регистрации', "Перейдите по ссылке: {$url}");
        $resp = ['email' => $email];
        if ($this->is_local()) {
            $resp['dev_link'] = $url; // faqat lokalda
        } elseif (!$sent) {
            return $this->json_err('Не удалось отправить письмо. Проверьте SMTP настройки.', 'MAIL');
        }
        $this->json_ok($resp);

    }

    public function auth_register_check() {
        $nonce_ok = check_ajax_referer(self::NONCE_ACTION, 'nonce', false);
        if (!$nonce_ok) return $this->json_err('Security check failed (nonce).', 'NONCE');

        $rt = $_COOKIE[self::COOKIE_VERIFY] ?? '';
        if ($rt) {
            $user_id = get_transient('ml_verify_'.$rt);
            if ($user_id) {
                delete_transient('ml_verify_'.$rt);
                $this->clear_flag_cookie(self::COOKIE_VERIFY);
                // Auto-login
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($user_id);
                return $this->json_ok(['verified'=>true, 'redirect'=> function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/') ]);
            }
        }
        $this->json_ok(['verified'=>false]);
    }

    public function auth_forgot_start() {
        $nonce_ok = check_ajax_referer(self::NONCE_ACTION, 'nonce', false);
        if (!$nonce_ok) return $this->json_err('Security check failed (nonce).', 'NONCE');

        $email = sanitize_email($_POST['user_email'] ?? '');
        if (!is_email($email)) return $this->json_err('Неверный e-mail', 'EMAIL');

        $user = get_user_by('email', $email);
        if (!$user) {
            // leaky info bermaslik
            return $this->json_ok(['email' => $email]);
        }
        $key = get_password_reset_key($user);
        if (is_wp_error($key)) return $this->json_err($key->get_error_message(), 'RESET');

        $url  = home_url("/magic/reset?login={$user->user_login}&key={$key}");
        $sent = wp_mail($email, 'Сброс пароля', "Ссылка для сброса пароля: {$url}");

        $resp = ['email' => $email];
        if (!$sent || $this->is_local()) { $resp['dev_link'] = $url; }
        $this->json_ok($resp);
    }

    public function auth_forgot_check() {
        $nonce_ok = check_ajax_referer(self::NONCE_ACTION, 'nonce', false);
        if (!$nonce_ok) return $this->json_err('Security check failed (nonce).', 'NONCE');

        $rt = $_COOKIE[self::COOKIE_RESET] ?? '';
        if ($rt) {
            $data = get_transient('ml_reset_'.$rt);
            if ($data && !empty($data['login']) && !empty($data['key'])) {
                delete_transient('ml_reset_'.$rt);
                $this->clear_flag_cookie(self::COOKIE_RESET);
                return $this->json_ok(['ready'=>true, 'login'=>$data['login'], 'key'=>$data['key']]);
            }
        }
        $this->json_ok(['ready'=>false]);
    }

    public function auth_save_new_password() {
        $nonce_ok = check_ajax_referer(self::NONCE_ACTION, 'nonce', false);
        if (!$nonce_ok) return $this->json_err('Security check failed (nonce).', 'NONCE');

        $login = sanitize_text_field($_POST['login'] ?? '');
        $key   = sanitize_text_field($_POST['key'] ?? '');
        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';
        if ($pass1 !== $pass2) return $this->json_err('Пароли не совпадают', 'PASS_MISMATCH');

        $user = check_password_reset_key($key, $login);
        if (is_wp_error($user)) return $this->json_err('Ссылка недействительна или устарела', 'RESET_BAD');

        reset_password($user, $pass1);

        // ixtiyoriy: auto-login
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        if (function_exists('wc_set_customer_auth_cookie')) wc_set_customer_auth_cookie($user->ID);

        $this->json_ok(['redirect' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/')]);
    }
}

new Modal_Auth_Ajax();
