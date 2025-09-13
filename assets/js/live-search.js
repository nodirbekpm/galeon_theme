(function ($) {
    const SEL_FORM = '.header_search';
    const DEBOUNCE_MS = 250;

    function bind($ctx) {
        const $input = $ctx.find('input[name="search"]');
        const $box = $ctx.find('.search_suggestions');
        const $list = $box.find('ul');
        let timer;

        $ctx.on('submit', function (e) {
            e.preventDefault();
        });

// 2) Inputda Enter bosilsa — submit bermasin
        $input.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

        // Helperlar
        const openBox = () => $box.addClass('open');
        const closeBox = () => {
            $box.removeClass('open');
            $list.empty();
        };

        // Fokus: bo'sh bo'lsa ochmaymiz, matn bo'lsa ochamiz
        $input.on('focus', function () {
            if ($(this).val().trim().length > 0 && $list.children().length) openBox();
        });

        // Input: bo'sh bo'lsa yopamiz, aks holda ochamiz va AJAX
        $input.on('input', function () {
            const q = $(this).val().trim();
            clearTimeout(timer);

            if (q.length === 0) {
                closeBox();
                return;
            }      // <-- bo'sh: yopish
            openBox();                                       // <-- kamida 1 belgi: ochish

            timer = setTimeout(function () {
                $.post(LS.ajax_url, {action: 'live_product_search', nonce: LS.nonce, q: q, limit: 8})
                    .done(function (r) {
                        if (r && r.success) {
                            $list.html(r.data.html || '');
                            // Natija bo'lmasa ham quti ochiq qoladi, xabar ko'rinadi
                            if (!$list.children().length) {
                                $list.html('<li class="no-results">Ничего не найдено</li>');
                            }
                        } else {
                            $list.html('<li class="no-results">Ошибка поиска</li>');
                        }
                    })
                    .fail(function () {
                        $list.html('<li class="no-results">Ошибка соединения</li>');
                    });
            }, DEBOUNCE_MS);
        });

        // Tashqariga klik: yopamiz
        $(document).on('click', function (e) {
            if (!$.contains($ctx.get(0), e.target)) closeBox();
        });

        // ESC: yopamiz
        $input.on('keydown', function (e) {
            if (e.key === 'Escape') closeBox();
        });
    }

    $(function () {
        $(SEL_FORM).each(function () {
            bind($(this));
        });
    });
})(jQuery);
