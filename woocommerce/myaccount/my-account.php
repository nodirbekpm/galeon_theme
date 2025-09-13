<?php
defined('ABSPATH') || exit;

// Mehmon bo'lsa — WooCommerce'ning standart login/register sahifasini ko'rsatamiz
if ( ! is_user_logged_in() ) {
    echo do_shortcode('[woocommerce_my_account]');
    return;
}

$current_user = wp_get_current_user();
$first  = get_user_meta($current_user->ID, 'first_name', true);
$last   = get_user_meta($current_user->ID, 'last_name', true);
$email  = $current_user->user_email;
$disp   = $current_user->display_name ?: ($first ?: $email);

// Woo xabarlarini ko'rsatish (muvaffaqiyatli/hatolik)
wc_print_notices();


$need_current_password = !(bool)get_user_option('default_password_nag', $current_user->ID);


?>

<!-- profile -->
<section class="profile">
    <div class="container">
        <div class="section_title">Профиль</div>

        <div class="main">
            <!-- Lichnaya informatsiya -->
            <div class="title">Личная информация</div>
            <form class="first" method="post" action="">
                <input type="hidden" name="galeon_form" value="info">

                <input type="text"
                       name="account_first_name"
                       value="<?php echo esc_attr($first); ?>"
                       placeholder="Имя">

                <input type="email"
                       name="account_email"
                       value="<?php echo esc_attr($email); ?>"
                       data-orig="<?php echo esc_attr($email); ?>"
                       placeholder="E-mail">

                <!-- Faqat email o'zgarganda ko'rinsin -->
                <div class="password-wrapper" style="display:none;">
                    <input type="password" name="galeon_current_password" placeholder="Текущий пароль (только при смене e-mail)" class="password-input" autocomplete="current-password">
                    <span class="material-icons toggle-password">visibility_off</span>
                </div>

                <!-- Woo talab qilishi mumkin bo'lgan qolgan maydonlar -->
                <input type="hidden" name="account_last_name" value="<?php echo esc_attr($last); ?>">
                <input type="hidden" name="account_display_name" value="<?php echo esc_attr($disp); ?>">

                <input type="hidden" name="action" value="save_account_details">
                <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>

                <div class="button_cover">
                    <button type="submit">Сохранить изменения</button>
                </div>
            </form>




            <!-- Parolni o'zgartirish (joriy parolsiz) -->
            <div class="title">Изменить пароль</div>
            <form method="post" action="">
                <div class="password-wrapper">
                    <input type="password" name="password_1" placeholder="Новый пароль" class="password-input" autocomplete="new-password">
                    <span class="material-icons toggle-password">visibility_off</span>
                </div>

                <div class="password-wrapper">
                    <input type="password" name="password_2" placeholder="Повторите пароль" class="password-input" autocomplete="new-password">
                    <span class="material-icons toggle-password">visibility_off</span>
                </div>

                <!-- Bizning custom handler uchun bayroq va nonce -->
                <input type="hidden" name="galeon_set_password" value="1">
                <?php wp_nonce_field('galeon_set_password', 'galeon_set_password_nonce'); ?>

                <div class="button_cover">
                    <button type="submit">Изменить пароль</button>
                </div>
            </form>



            <div class="action_links">
                <a href="<?php echo esc_url( wc_logout_url( home_url('/') ) ); ?>">Выйти</a>
                <!-- "Удалить аккаунт" uchun backend qo'shilmaguncha # qoldiramiz -->
                <a href="#" class="js-delete-account">Удалить аккаунт</a><!-- Yashirin delete form -->
                <form id="galeon-delete-account-form" method="post" style="display:none">
                    <input type="hidden" name="galeon_delete_account" value="1">
                    <?php wp_nonce_field('galeon_delete_account', 'galeon_delete_account_nonce'); ?>
                </form>

            </div>
        </div>
    </div>
</section>



<script>
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-delete-account');
        if (!btn) return;
        e.preventDefault();

        if (typeof Swal === 'undefined') {
            // Fallback: confirm
            if (confirm('Аккаунт будет удалён без возможности восстановления. Продолжить?')) {
                document.getElementById('galeon-delete-account-form')?.submit();
            }
            return;
        }

        Swal.fire({
            title: 'Удалить аккаунт?',
            html: '<div style="text-align:left">Аккаунт будет удалён без возможности восстановления.<br>Это действие необратимо.</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Да, удалить',
            cancelButtonText: 'Отмена',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                const f = document.getElementById('galeon-delete-account-form');
                if (!f) return;
                Swal.fire({
                    title: 'Удаляем…',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => { Swal.showLoading(); f.submit(); }
                });
            }
        });
    });
    document.addEventListener('DOMContentLoaded', function(){
        const form  = document.querySelector('form.first');
        if (!form) return;
        const email = form.querySelector('input[name="account_email"]');
        const wrap  = form.querySelector('.password-wrapper');
        if (!email || !wrap) return;

        const orig = (email.getAttribute('data-orig') || '').trim().toLowerCase();
        function togglePw(){
            const now = (email.value || '').trim().toLowerCase();
            wrap.style.display = (now && now !== orig) ? '' : 'none';
        }
        togglePw();
        email.addEventListener('input', togglePw);
    });
</script>

