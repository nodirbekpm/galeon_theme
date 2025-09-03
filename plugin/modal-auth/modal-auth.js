(function($){
    const getModal = () => $('#modalBox2');

    function showCheckEmail(email, devLink) {
        const assets = MODAL_AUTH.assets_base || '';
        const devHtml = (devLink && MODAL_AUTH.is_local)
            ? `<div class="dev_notice" style="margin-top:12px;">
           <a href="${devLink}" target="_blank" style="text-decoration:underline;">Открыть ссылку (dev)</a>
           <div style="font-size:12px;opacity:0.8;">Вы на локалке: письма не отправляются, используйте эту ссылку.</div>
         </div>` : '';
        getModal().html(`
      <button class="close-btn" id="profileModalClose"><img src="${assets}/images/modal_close_icon.svg" alt=""></button>
      <div class="section_title">Проверьте почту</div>
      <div class="img_container"><img src="${assets}/images/modal_message_send_iocn.svg" alt=""></div>
      <div class="sub_title">Мы отправили письмо с ссылкой для подтверждения на адрес <span>${email}</span>. Пожалуйста, перейдите по ней, чтобы активировать ваш аккаунт</div>
      ${devHtml}
    `);
    }

    function showForgot() {
        const assets = MODAL_AUTH.assets_base || '';
        getModal().html(`
      <button class="close-btn" id="profileModalClose"><img src="${assets}/images/modal_close_icon.svg" alt=""></button>
      <div class="section_title">Восстановление пароля</div>
      <div class="sub_title">Введите email, который вы использовали при регистрации, и мы отправим вам ссылку для сброса пароля.</div>
      <form class="form-register" id="form-forgot">
        <input required type="email" name="user_email" placeholder="E-mail">
        <button type="submit">Отправить ссылку</button>
        <div class="back_link open-modal-btn-profile" data-modal="modal1">
          <img src="${assets}/images/navigation_item_arrow.svg" alt="">
          <span>Вернуться ко входу</span>
        </div>
      </form>
    `);
    }

    function showReset(login, key) {
        const assets = MODAL_AUTH.assets_base || '';
        getModal().html(`
      <button class="close-btn" id="profileModalClose"><img src="${assets}/images/modal_close_icon.svg" alt=""></button>
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
        <input type="hidden" name="login" value="${login||''}">
        <input type="hidden" name="key" value="${key||''}">
        <button type="submit">Сохранить пароль</button>
        <div class="message">Уже есть аккаунт? <span class="open-modal-btn-profile" data-modal="modal1"> Войти</span></div>
      </form>
    `);
    }

    // Header icon (auth bo'lmasa modalni ochish)
    $(document).on('click', '.header_user.header_icon_item', function(e){
        if ($(this).hasClass('open-modal-btn-profile')) {
            e.preventDefault();
            getModal().addClass('open');
        }
    });

    // Login submit
    $(document).on('submit', '#form-login', function(e){
        e.preventDefault();
        const $f = $(this);
        const email = $f.find('input[type="email"]').val().trim();
        const pwd   = $f.find('input[type="password"]').val();

        $('#auth-alert').remove();
        $f.prepend('<div id="auth-alert" style="margin-bottom:8px;font-size:14px;"></div>');
        const $alert = $('#auth-alert');

        if (!email || !pwd) { $alert.text('Введите e-mail и пароль'); return; }

        $.post(MODAL_AUTH.ajax_url, {
            action: 'auth_login',
            nonce: MODAL_AUTH.nonce,
            log: email,
            pwd: pwd
        }).done(function(r){
            if (r.ok) {
                window.location.href = r.redirect || MODAL_AUTH.my_account_url;
            } else {
                $alert.text(r.message || 'Ошибка входа');
            }
        }).fail(function(xhr){
            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Ошибка входа';
            $alert.text(msg);
        });
    });

    // Register submit
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
                showCheckEmail(email, r.dev_link);
            } else {
                alert(r.message || 'Ошибка регистрации');
            }
        }).fail(function(xhr){
            alert(xhr.responseJSON?.message || 'Ошибка регистрации');
        });
    });

    // Forgot sahnasiga o'tish
    $(document).on('click', '.open-modal-btn-profile[data-modal="modal2"]', function(e){
        e.preventDefault();
        showForgot();
    });

    // Forgot: yuborish
    $(document).on('submit', '#form-forgot', function(e){
        e.preventDefault();
        const $f = $(this);
        const email = $f.find('input[name="user_email"]').val().trim();
        $.post(MODAL_AUTH.ajax_url, {
            action: 'auth_forgot_start',
            nonce: MODAL_AUTH.nonce,
            user_email: email
        }).done(function(r){
            if (r.ok) {
                const modal = getModal();
                modal.find('.sub_title').text(`Мы отправили инструкцию по восстановлению пароля на адрес ${email}.`);
                if (r.dev_link && MODAL_AUTH.is_local) {
                    modal.find('.sub_title').after(
                        `<div class="dev_notice" style="margin-top:12px;">
               <a href="${r.dev_link}" target="_blank" style="text-decoration:underline;">Открыть ссылку (dev)</a>
               <div style="font-size:12px;opacity:0.8;">Вы на локалке: письма не отправляются, используйте эту ссылку.</div>
             </div>`
                    );
                }
            } else {
                alert(r.message || 'Ошибка');
            }
        }).fail(function(xhr){
            alert(xhr.responseJSON?.message || 'Ошибка');
        });
    });

    // Register verify polling
    function pollRegisterVerified() {
        $.post(MODAL_AUTH.ajax_url, { action:'auth_register_check', nonce: MODAL_AUTH.nonce })
            .done(function(r){
                if (r.ok && r.verified) {
                    window.location.href = r.redirect || MODAL_AUTH.my_account_url;
                }
            });
    }
    setInterval(function(){
        if (getModal().text().includes('Проверьте почту')) pollRegisterVerified();
    }, 5000);

    // Reset ready polling
    function pollResetReady() {
        $.post(MODAL_AUTH.ajax_url, { action:'auth_forgot_check', nonce: MODAL_AUTH.nonce })
            .done(function(r){
                if (r.ok && r.ready) {
                    showReset(r.login, r.key);
                }
            });
    }
    setInterval(function(){
        if (getModal().text().includes('Восстановление пароля')) pollResetReady();
    }, 5000);

    // Parolni saqlash
    $(document).on('submit', '#form-reset', function(e){
        e.preventDefault();
        const $f = $(this);
        const pass1 = $f.find('input[name="pass1"]').val();
        const pass2 = $f.find('input[name="pass2"]').val();
        const login = $f.find('input[name="login"]').val();
        const key   = $f.find('input[name="key"]').val();

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

})(jQuery);
