(function($){
    console.log('AUTH CORE READY');

    // === State ===
    const state = { lastEmail: '', mode: null }; // mode: 'verify' | 'reset'

    // === Helpers ===
    function img(path){ return (MODAL_AUTH?.assets || '/assets') + '/images/' + path; }
    const hasLoginCookie = () => document.cookie.split('; ').some(c => c.startsWith('wordpress_logged_in_'));

    // Hozirgi modal konteyner: #modalBox2 (ichini almashtiramiz)
    let INITIAL_AUTH_HTML = null;

    function $box(){
        const $open = $('.modal-overlay-profile.open, .modal-overlay-profile.is-open, .modal-overlay-profile[style*="display: block"]');
        if ($open.length) {
            const $m = $open.find('#modalBox2,.modal').first();
            if ($m.length) return $m;
        }
        const $m2 = $('#modalBox2');
        if ($m2.length) return $m2;
        return $('.modal-overlay-profile .modal').first();
    }

    function captureInitialAuth(){
        const $m = $box();
        if ($m.length && !INITIAL_AUTH_HTML) {
            INITIAL_AUTH_HTML = $m.html(); // login/register bo'lgan holat
        }
    }

    // Modal ochilganda bir marta snapshot olamiz
    $(document).on('click', '.open-modal-btn-profile[data-modal="modal1"]', function(){
        setTimeout(captureInitialAuth, 0);
    });

    // “Вернуться ко входу”
    $(document).on('click',
        '.back_link.open-modal-btn-profile[data-modal="modal1"], .open-modal-btn-profile[data-modal="modal1"].go-login',
        function(e){
            e.preventDefault();
            const $m = $box();
            if (INITIAL_AUTH_HTML && $m.length) {
                $m.html(INITIAL_AUTH_HTML);
            } else {
                window.location.reload();
            }
        }
    );

    // === Views (faqat ichki kontent) ===
    function view_CheckEmail(email, withConfirm){
        return `
      <button class="close-btn" id="profileModalClose"><img src="${img('modal_close_icon.svg')}" alt=""></button>
      <div class="section_title">Проверьте почту</div>
      <div class="img_container"><img src="${img('modal_message_send_iocn.svg')}" alt=""></div>
      <div class="sub_title">
        Мы отправили инструкцию по восстановлению пароля на адрес <span>${email}</span>.
        ${withConfirm ? '<br> Пожалуйста, перейдите по ней, чтобы активировать ваш аккаунт.' : ''}
      </div>
      ${withConfirm ? `
        <form id="form-confirm">
          <button type="submit" style="width:auto;">Подтвердить</button>
        </form>
        <div class="message" style="margin-bottom:0;">Не&nbsp;пришло письмо? Проверьте папку «спам»</div>
      ` : ``}
    `;
    }

    function view_Forgot(){
        return `
      <button class="close-btn" id="profileModalClose"><img src="${img('modal_close_icon.svg')}" alt=""></button>
      <div class="section_title">Восстановление пароля</div>
      <div class="sub_title">
        Введите email, который вы использовали при регистрации, и мы отправим вам ссылку для сброса пароля.
      </div>
      <form class="form-register" id="form-forgot">
        <input required type="email" name="user_email" placeholder="E-mail">
        <button type="submit">Отправить ссылку</button>
        <div class="back_link open-modal-btn-profile" data-modal="modal1">
          <img src="${img('navigation_item_arrow.svg')}" alt="">
          <span>Вернуться ко входу</span>
        </div>
      </form>
    `;
    }

    function view_Reset(login, key){
        return `
      <button class="close-btn" id="profileModalClose"><img src="${img('modal_close_icon.svg')}" alt=""></button>
      <div class="section_title">Создайте новый пароль</div>
      <form class="form-register" id="form-reset">
        <div class="password-wrapper">
          <input required type="password" name="pass1" placeholder="Пароль" class="password-input">
          <span class="material-icons toggle-password">visibility_off</span>
        </div>
        <div class="password-wrapper">
          <input required type="password" name="pass2" placeholder="Повторите пароль" class="password-input">
          <span class="material-icons toggle-password">visibility_off</span>
        </div>
        <input type="hidden" name="login" value="${login}">
        <input type="hidden" name="key"   value="${key}">
        <button type="submit">Сохранить пароль</button>
        <div class="message">Уже есть аккаунт? <span class="open-modal-btn-profile" data-modal="modal1"> Войти</span></div>
      </form>
    `;
    }

    // Windowga qaytsak — “Confirm” varianti
    $(window).on('focus', function(){
        const $m = $box();
        if (!$m.length) return;
        if (state.mode && state.lastEmail) {
            $m.html( view_CheckEmail(state.lastEmail, true) );
        }
    });

    // ======= LOGIN (robust, no-false-error) =======
    function pollLoggedIn(maxMs = 2500, step = 150){
        return new Promise((resolve, reject) => {
            const t0 = Date.now();
            const id = setInterval(() => {
                if (hasLoginCookie()) {
                    clearInterval(id); resolve(true);
                } else if (Date.now() - t0 > maxMs) {
                    clearInterval(id); reject();
                }
            }, step);
        });
    }

    function restLogin(email, pwd){
        return $.ajax({
            url: MODAL_AUTH.rest_login,
            method: 'POST',
            dataType: 'json',
            xhrFields: { withCredentials: true },
            data: { log: email, pwd: pwd }
        });
    }

// === Login submit (firewall-safe: poll + fallback) ===
    let loginBusy = false;

    function fallbackLoginViaForm(user, pass){
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = (window.wp_login_url || '/wp-login.php');

        const add = (n,v)=>{ const i=document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; form.appendChild(i); };
        add('log', user);
        add('pwd', pass);
        add('redirect_to', MODAL_AUTH.my_account_url);

        document.body.appendChild(form);
        form.submit();
    }

    $(document).on('submit', '#form-login', function(e){
        e.preventDefault();
        e.stopPropagation();
        if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        if (loginBusy) return;
        loginBusy = true;

        const $f = $(this);
        const email = $f.find('input[type="email"]').val().trim();
        const pwd   = $f.find('input[type="password"]').val();

        $.ajax({
            url: MODAL_AUTH.ajax_url,
            method: 'POST',
            dataType: 'json',
            data: { action:'auth_login', nonce: MODAL_AUTH.nonce, log: email, pwd: pwd }
        }).always(function(){
            // Cookie paydo bo'lishini kutamiz (max 4s)
            const STEP = 200, MAX = 4000;
            let waited = 0;
            const t = setInterval(function(){
                if (document.cookie.indexOf('wordpress_logged_in_') !== -1 ||
                    document.cookie.indexOf('wordpress_logged_in')   !== -1) {
                    clearInterval(t);
                    loginBusy = false;
                    window.location.href = MODAL_AUTH.my_account_url;
                    return;
                }
                waited += STEP;
                if (waited >= MAX) {
                    clearInterval(t);
                    loginBusy = false;
                    // AJAX bloklangan bo'lishi mumkin — wp-login.php orqali form bilan kirib yuboramiz
                    fallbackLoginViaForm(email, pwd);
                }
            }, STEP);
        });
    });


    // === Register submit ===
    $(document).on('submit', '#form-register', function(e){
        e.preventDefault();
        const $f = $(this);
        const first_name = $f.find('input[placeholder="Имя"]').val()?.trim() || '';
        const email = $f.find('input[type="email"]').val().trim();
        const pass1 = $f.find('input[placeholder="Новый пароль"]').val();
        const pass2 = $f.find('input[placeholder="Повторите пароль"]').val();
        if (pass1 !== pass2) { alert('Пароли не совпадают'); return; }

        $.post(MODAL_AUTH.ajax_url, {
            action: 'auth_register_start',
            nonce: MODAL_AUTH.nonce,
            first_name: first_name,
            user_email: email,
            user_pass: pass1
        }).done(function(r){
            if (r.ok) {
                state.lastEmail = email; state.mode = 'verify';
                $box().html( view_CheckEmail(email, false) );
            } else {
                alert(r.message || 'Ошибка регистрации');
            }
        }).fail(function(xhr){
            alert(xhr.responseJSON?.message || 'Ошибка регистрации');
        });
    });

    // === “Забыли пароль? Восстановить” — robust catch-all ===
    function handleForgotClick(ev){
        const t = ev.target;
        const cand = t.closest('[data-modal], .open-modal-btn-profile, .message span, .message a, .message button');
        if (!cand) return;

        const dm = (cand.getAttribute('data-modal') || '').trim();
        const text = (cand.textContent || '').trim();
        const isForgot = (dm === 'modal2') || /Восстановить/i.test(text);
        if (!isForgot) return;

        ev.preventDefault();
        ev.stopPropagation();
        if (ev.stopImmediatePropagation) ev.stopImmediatePropagation();

        state.mode = null; state.lastEmail = '';
        $box().html( view_Forgot() );
    }
    document.addEventListener('click', handleForgotClick, true);
    document.addEventListener('click', handleForgotClick, false);

    // === Forgot submit → emailga link jo‘natish ===
    $(document).on('submit', '#form-forgot', function(e){
        e.preventDefault();
        const email = $(this).find('input[name="user_email"]').val().trim();

        $.post(MODAL_AUTH.ajax_url, {
            action: 'auth_forgot_start',
            nonce: MODAL_AUTH.nonce,
            user_email: email
        }).done(function(r){
            if (r.ok) {
                state.lastEmail = email; state.mode = 'reset';
                $box().html( view_CheckEmail(email, false) );
            } else {
                alert(r.message || 'Ошибка');
            }
        }).fail(function(xhr){
            alert(xhr.responseJSON?.message || 'Ошибка');
        });
    });

    // === “Подтвердить” (register yoki forgotdan keyin) ===
    $(document).on('submit', '#form-confirm', function(e){
        e.preventDefault();

        // rt paramni URLdan olib yuboramiz (verify uchun)
        const urlRt = new URLSearchParams(location.search).get('rt') || '';

        if (state.mode === 'verify') {
            $.post(MODAL_AUTH.ajax_url, { action:'auth_register_check', nonce: MODAL_AUTH.nonce, rt: urlRt })
                .done(function(r){
                    if (r.ok && r.verified) {
                        window.location.href = r.redirect || MODAL_AUTH.my_account_url;
                    } else {
                        alert('Пока не подтверждено. Откройте ссылку из письма и попробуйте снова.');
                    }
                }).fail(function(){
                alert('Ошибка запроса.');
            });
        } else if (state.mode === 'reset') {
            $.post(MODAL_AUTH.ajax_url, { action:'auth_forgot_check', nonce: MODAL_AUTH.nonce })
                .done(function(r){
                    if (r.ok && r.ready) {
                        $box().html( view_Reset(r.login, r.key) );
                    } else {
                        alert('Пока не подтверждено. Откройте ссылку из письма и попробуйте снова.');
                    }
                }).fail(function(){
                alert('Ошибка запроса.');
            });
        }
    });

    // === Yangi parolni saqlash ===
    $(document).on('submit', '#form-reset', function(e){
        e.preventDefault();
        const pass1 = $(this).find('input[name="pass1"]').val();
        const pass2 = $(this).find('input[name="pass2"]').val();
        const login = $(this).find('input[name="login"]').val();
        const key   = $(this).find('input[name="key"]').val();

        $.post(MODAL_AUTH.ajax_url, {
            action: 'auth_save_new_password',
            nonce: MODAL_AUTH.nonce,
            pass1, pass2, login, key
        }).done(function(r){
            if (r.ok) {
                window.location.href = r.redirect || MODAL_AUTH.my_account_url;
            } else {
                alert(r.message || 'Ошибка');
            }
        }).fail(function(xhr){
            alert(xhr.responseJSON?.message || 'Ошибка');
        });
    });

    // ===================== MAGIC LINK AUTOCLOSE (yangi tabni yopish) =====================
    // Emaildagi link ochilgan sahifada URL ?verified=1&rt=... yoki ?reset=1 bo‘ladi.
    // Bu tab foydalanuvchi tomonidan ochilgani uchun, window.close() har doim ham ishlamasligi mumkin,
    // lekin har ikkala usulni sinab ko‘ramiz. Qo‘shimcha oqimlar qo‘shilmagan.
    document.addEventListener('DOMContentLoaded', function(){
        try {
            const qs = new URLSearchParams(window.location.search || '');
            if (qs.get('verified') === '1' || qs.get('reset') === '1') {
                // Server cookie/transientlarni allaqachon o‘rnatgan bo‘ladi
                setTimeout(function(){
                    // 1-urinish
                    window.close();
                    // 2-urinish (ba’zi brauzerlarda)
                    try { window.open('', '_self'); window.close(); } catch(e){}
                }, 150);
            }
        } catch(_){}
    });
    // =====================================================================================

})(jQuery);
