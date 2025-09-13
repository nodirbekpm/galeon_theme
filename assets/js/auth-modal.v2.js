(function($){
    console.log('AUTH MODAL V2');

    /* ================= COMMON FORM ERROR HELPERS ================= */
    function showFormError($form, msg){
        let $err = $form.find('.form-error');
        if (!$err.length){
            $err = $('<div class="form-error" style="color:#e53935;font-size:12px;margin-top:8px;"></div>');
            const $btn = $form.find('button[type="submit"]').last();
            if ($btn.length) $btn.after($err); else $form.append($err);
        }
        $err.text(msg);
    }
    function clearFormError($form){ $form.find('.form-error').remove(); }
    $(document).on('input change', '#form-login input, #form-reset input, #form-register input', function(){
        clearFormError($(this).closest('form'));
    });

    // ====== State ======
    const S = { lastEmail: '', mode: null }; // 'verify' | 'reset'

    // ====== Helpers ======
    const ASSETS = (MODAL_AUTH_V2?.assets || '/assets');
    const img = (p) => ASSETS + '/images/' + p;

    function $modalBox(){
        const $open = $('.modal-overlay-profile.open, .modal-overlay-profile.is-open, .modal-overlay-profile[style*="display: block"]');
        if ($open.length) {
            const $m = $open.find('#modalBox2,.modal').first();
            if ($m.length) return $m;
        }
        const $m2 = $('#modalBox2');
        if ($m2.length) return $m2;
        return $('.modal-overlay-profile .modal').first();
    }

    // Snapshot (initial login/register HTML)
    let INITIAL_HTML = null;
    function captureInitial(){
        const $m = $modalBox();
        if ($m.length && !INITIAL_HTML) INITIAL_HTML = $m.html();
    }
    $(document).on('click','.open-modal-btn-profile[data-modal="modal1"]', ()=> setTimeout(captureInitial,0));

    $(document).on('click',
        '.back_link.open-modal-btn-profile[data-modal="modal1"], .open-modal-btn-profile[data-modal="modal1"].go-login',
        function(e){
            e.preventDefault();
            const $m = $modalBox();
            if (INITIAL_HTML && $m.length) $m.html(INITIAL_HTML); else location.reload();
        }
    );

    // ====== Views (only inner content) ======
    function viewCheckEmail(email, withConfirm){
        return `
      <button class="close-btn" id="profileModalClose"><img src="${img('modal_close_icon.svg')}" alt=""></button>
      <div class="section_title">Проверьте почту</div>
      <div class="img_container"><img src="${img('modal_message_send_iocn.svg')}" alt=""></div>
      <div class="sub_title">
        Мы отправили письмо на адрес <span>${email}</span>.
        ${withConfirm ? '<br> Пожалуйста, перейдите по ссылке в письме, затем нажмите «Подтвердить».' : ''}
      </div>
      ${withConfirm ? `
        <form id="form-confirm"><button type="submit" style="width:auto;">Подтвердить</button></form>
        <div class="message" style="margin-bottom:0;">Не&nbsp;пришло письмо? Проверьте папку «спам»</div>
      `:``}
    `;
    }

    function viewForgot(){
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

    function viewReset(login, key){
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

    // Brauzer oynasi focusga qaytganda — Confirm varianti
    $(window).on('focus', function(){
        const $m = $modalBox();
        if (!$m.length) return;
        if (S.mode && S.lastEmail) $m.html( viewCheckEmail(S.lastEmail, true) );
    });

    /* ================= LOGIN ================= */

// Cookie poll helper (Imunify bloklaganda ham cookie qo'yilgan bo'lishi mumkin)
    function pollLoggedIn(maxMs = 4500, step = 180){
        return new Promise((resolve, reject) => {
            const t0 = Date.now();
            const id = setInterval(() => {
                if (document.cookie.indexOf('wordpress_logged_in') !== -1){
                    clearInterval(id); resolve(true);
                } else if (Date.now() - t0 > maxMs){
                    clearInterval(id); reject(new Error('no-cookie'));
                }
            }, step);
        });
    }

    async function syncLocalStorageThenRedirect(redirectUrl){
        try {
            const payload = collectStoragePayload();
            if (payload.likes.length || payload.cart.length) {
                await $.ajax({
                    url: MODAL_AUTH_V2.ajax_url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'auth_sync_storage',
                        nonce: MODAL_AUTH_V2.nonce,
                        likes: JSON.stringify(payload.likes),
                        cart:  JSON.stringify(payload.cart)
                    }
                });
                clearLocalCache(payload._keys);
            }
        } catch(e){}
        window.location.href = redirectUrl || MODAL_AUTH_V2.my_account_url;
    }

    // Login submit (AJAX → cookie poll → REST → cookie poll; hech qachon wp-login.php fallback yo'q)
    let loginBusy = false;
    $(document).on('submit', '#form-login', async function(e){
        e.preventDefault();
        if (loginBusy) return;
        loginBusy = true;

        const $f = $(this);
        clearFormError($f);

        const email = $f.find('input[type="email"]').val().trim();
        const pwd   = $f.find('input[type="password"]').val();
        const $btn  = $f.find('button[type="submit"]').prop('disabled', true);

        // Yangi: parolni obfuskatsiya qilib yuboramiz (WAF ni chalg‘itish uchun)
        const p_enc = btoa(JSON.stringify({ p: pwd, ts: Date.now() }));

        const cookieRace = pollLoggedIn(4500);

        try {
            // 1) admin-ajax
            let r;
            try {
                r = await $.ajax({
                    url: MODAL_AUTH_V2.ajax_url,
                    method: 'POST',
                    dataType: 'json',
                    data: { action:'auth_login', nonce: MODAL_AUTH_V2.nonce, log: email, p_enc: p_enc }
                });
            } catch (xhr) {
                r = xhr; // jqXHR bo‘lishi mumkin
            }

            if (r && r.ok){
                return syncLocalStorageThenRedirect(r.redirect);
            }

            // admin-ajax muvaffaqiyatsiz: cookie allaqachon qo'yilgan bo'lishi mumkin
            try {
                await cookieRace;
                return syncLocalStorageThenRedirect(MODAL_AUTH_V2.my_account_url);
            } catch(_){ /* cookie yo‘q, REST bilan davom */ }

            // 2) REST fallback (ham p_enc bilan)
            let r2;
            try {
                r2 = await $.ajax({
                    url: MODAL_AUTH_V2.rest_login,
                    method: 'POST',
                    dataType: 'json',
                    xhrFields: { withCredentials: true },
                    data: { log: email, p_enc: p_enc }
                });
            } catch (xhr2) {
                r2 = xhr2;
            }

            if (r2 && r2.ok){
                return syncLocalStorageThenRedirect(r2.redirect);
            }

            // REST ham xato: yana qisqa cookie poll
            try {
                await pollLoggedIn(3500);
                return syncLocalStorageThenRedirect(MODAL_AUTH_V2.my_account_url);
            } catch(_){
                const msg = (r && r.responseJSON && r.responseJSON.message)
                    || (r2 && r2.responseJSON && r2.responseJSON.message)
                    || (r2 && r2.message)
                    || 'Ошибка входа';
                showFormError($f, msg);
            }

        } finally {
            $btn.prop('disabled', false);
            loginBusy = false;
        }
    });

    /* ================= REGISTER ================= */
    $(document).on('submit', '#form-register', function(e){
        e.preventDefault();
        const $f = $(this);
        clearFormError($f);

        const first_name = $f.find('input[placeholder="Имя"]').val()?.trim() || '';
        const email = $f.find('input[type="email"]').val().trim();
        const pass1 = $f.find('input[placeholder="Новый пароль"]').val();
        const pass2 = $f.find('input[placeholder="Повторите пароль"]').val();
        const $btn  = $f.find('button[type="submit"]').prop('disabled', true);

        if (pass1 !== pass2) {
            showFormError($f, 'Пароли не совпадают');
            $btn.prop('disabled', false);
            return;
        }
        if (!pass1 || pass1.length < 6){
            showFormError($f, 'Пароль слишком короткий');
            $btn.prop('disabled', false);
            return;
        }

        $.post(MODAL_AUTH_V2.ajax_url, {
            action: 'auth_register_start',
            nonce: MODAL_AUTH_V2.nonce,
            first_name, user_email: email, user_pass: pass1
        }).done(function(r){
            if (r.ok) {
                S.lastEmail = email; S.mode = 'verify';
                $modalBox().html( viewCheckEmail(email, false) );
            } else {
                showFormError($f, r.message || 'Ошибка регистрации');
            }
        }).fail(function(xhr){
            showFormError($f, xhr.responseJSON?.message || 'Ошибка регистрации');
        }).always(function(){
            $btn.prop('disabled', false);
        });
    });

    /* ================= FORGOT ================= */
    // “Забыли пароль? Восстановить”
    function onForgotClick(ev){
        const t = ev.target;
        const cand = t.closest('.js-open-forgot, .message span, [data-modal]');
        if (!cand) return;
        const dm = (cand.getAttribute('data-modal') || '').trim();
        const text = (cand.textContent || '').trim();
        const isForgot = (dm === 'modal2') || /Восстановить/i.test(text);
        if (!isForgot) return;
        ev.preventDefault();
        S.mode = null; S.lastEmail = '';
        $modalBox().html( viewForgot() );
    }
    document.addEventListener('click', onForgotClick, true);
    document.addEventListener('click', onForgotClick, false);

    $(document).on('submit', '#form-forgot', function(e){
        e.preventDefault();
        const $f = $(this);
        clearFormError($f);
        const email = $f.find('input[name="user_email"]').val().trim();

        $.post(MODAL_AUTH_V2.ajax_url, {
            action: 'auth_forgot_start',
            nonce: MODAL_AUTH_V2.nonce,
            user_email: email
        }).done(function(r){
            if (r.ok) {
                S.lastEmail = email; S.mode = 'reset';
                $modalBox().html( viewCheckEmail(email, false) );
            } else {
                showFormError($f, r.message || 'Ошибка');
            }
        }).fail(function(xhr){
            showFormError($f, xhr.responseJSON?.message || 'Ошибка');
        });
    });

    // Confirm (register yoki reset oqimlaridan keyin)
    $(document).on('submit', '#form-confirm', function(e){
        e.preventDefault();
        const rt = new URLSearchParams(location.search).get('rt') || '';
        if (S.mode === 'verify') {
            $.post(MODAL_AUTH_V2.ajax_url, { action:'auth_register_check', nonce: MODAL_AUTH_V2.nonce, rt })
                .done(function(r){
                    if (r.ok && r.verified) {
                        syncLocalStorageThenRedirect(r.redirect);
                    } else {
                        alert('Пока не подтверждено. Откройте ссылку из письма и попробуйте снова.');
                    }
                }).fail(function(){ alert('Ошибка запроса.'); });
        } else if (S.mode === 'reset') {
            $.post(MODAL_AUTH_V2.ajax_url, { action:'auth_forgot_check', nonce: MODAL_AUTH_V2.nonce })
                .done(function(r){
                    if (r.ok && r.ready) {
                        $modalBox().html( viewReset(r.login, r.key) );
                    } else {
                        alert('Пока не подтверждено. Откройте ссылку из письма и попробуйте снова.');
                    }
                }).fail(function(){ alert('Ошибка запроса.'); });
        }
    });

    /* =============== RESET: SAVE NEW PASSWORD (with hard guards) =============== */
    $(document).on('submit', '#form-reset', function(e){
        e.preventDefault();
        const $f   = $(this);
        clearFormError($f);

        const pass1 = $f.find('input[name="pass1"]').val();
        const pass2 = $f.find('input[name="pass2"]').val();
        const login = $f.find('input[name="login"]').val();
        const key   = $f.find('input[name="key"]').val();
        const $btn  = $f.find('button[type="submit"]').prop('disabled', true);

        // FRONT tekshiruv — tenglik va minimal uzunlik
        if (pass1 !== pass2){
            showFormError($f, 'Пароли не совпадают');
            $btn.prop('disabled', false);
            return;
        }
        if (!pass1 || pass1.length < 6){
            showFormError($f, 'Пароль слишком короткий');
            $btn.prop('disabled', false);
            return;
        }

        $.post(MODAL_AUTH_V2.ajax_url, {
            action: 'auth_save_new_password',
            nonce:  MODAL_AUTH_V2.nonce,
            pass1, pass2, login, key
        }).done(function(r){
            if (r.ok) {
                syncLocalStorageThenRedirect(r.redirect);
            } else {
                showFormError($f, r.message || 'Ошибка');
            }
        }).fail(function(xhr){
            const msg = xhr && xhr.responseJSON && xhr.responseJSON.message;
            showFormError($f, msg || 'Ошибка');
        }).always(function(){
            $btn.prop('disabled', false);
        });
    });

    // Magic link tab autoclose (opened by user)
    document.addEventListener('DOMContentLoaded', function(){
        try {
            const qs = new URLSearchParams(location.search || '');
            if (qs.get('verified') === '1' || qs.get('reset') === '1') {
                setTimeout(function(){
                    window.close();
                    try { window.open('', '_self'); window.close(); } catch(e){}
                }, 150);
            }
        } catch(_){}
    });

    /* ================= localStorage helpers ================= */
    // Compatible keys: ['basket','cart'] and ['like','likes','wishlist']
    function collectStoragePayload(){
        const keys = [];
        const likes = [];
        const cart = [];

        const likeKeys = ['likes','like','wishlist'];
        const cartKeys = ['basket','cart'];

        likeKeys.forEach(k=>{
            try {
                const raw = localStorage.getItem(k);
                if (!raw) return;
                keys.push(k);
                const arr = JSON.parse(raw);
                if (Array.isArray(arr)) {
                    arr.forEach(id => { const pid = parseInt(id,10); if (pid>0) likes.push(pid); });
                }
            } catch(_){}
        });

        cartKeys.forEach(k=>{
            try {
                const raw = localStorage.getItem(k);
                if (!raw) return;
                keys.push(k);
                const arr = JSON.parse(raw);
                if (Array.isArray(arr)) {
                    arr.forEach(item=>{
                        // supports {product_id, quantity, variation_id?, variation?}
                        const pid = parseInt(item.product_id || item.id, 10);
                        const qty = parseInt(item.quantity || item.qty || 1, 10);
                        const vid = parseInt(item.variation_id || 0, 10);
                        const variation = item.variation && typeof item.variation === 'object' ? item.variation : {};
                        if (pid>0) cart.push({ product_id: pid, quantity: Math.max(1,qty||1), variation_id: vid||0, variation });
                    });
                }
            } catch(_){}
        });

        return { likes, cart, _keys: keys };
    }

    function clearLocalCache(keys){
        (keys||[]).forEach(k=>{
            try { localStorage.removeItem(k); } catch(_){}
        });
    }

})(jQuery);
