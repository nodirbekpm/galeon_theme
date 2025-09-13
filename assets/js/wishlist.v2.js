(function(){
    // ===== Localized config =====
    const CFG = (window.WISHLIST || {});
    const AJAX_URL = CFG.ajaxUrl || (window.ajaxurl || '/wp-admin/admin-ajax.php');
    const NONCE    = CFG.nonce || '';
    const LOGGED   = !!CFG.isLoggedIn || /wordpress_logged_in_/i.test(document.cookie);
    const SEL      = Object.assign({ icon: '.like_icon', count: '.wishlist-count.header_counter', listId: '#wl_list' }, CFG.selectors||{});

    // ===== LocalStorage (guest) =====
    const LS_KEY = 'wishlist_v1';
    const PENDING_KEY = 'wl_pending_merge';
    const readLS  = ()=>{ try{ return JSON.parse(localStorage.getItem(LS_KEY))||[]; }catch(e){ return []; } };
    const writeLS = (arr)=> localStorage.setItem(LS_KEY, JSON.stringify(arr));
    const keyOf   = (pid,vid)=> `${Number(pid)}:${Number(vid||0)}`;
    const clearGuestLS = ()=>{
        try { localStorage.removeItem(PENDING_KEY); } catch(_){}
        try { localStorage.setItem(LS_KEY,'[]'); } catch(_){}
        try { document.cookie = 'wishlist_v1=; Max-Age=0; Path=/'; } catch(_){}
    };

    // ===== UI helpers =====
    function setWishlistCount(count){
        const el = document.querySelector(SEL.count);
        if (!el) return;
        el.textContent = String(count);
        el.classList.toggle('active', Number(count)>0);
    }
    function setHearts(items){
        const keys = new Set((items||[]).map(it => keyOf(it.pid, it.vid)));
        document.querySelectorAll(SEL.icon).forEach(el=>{
            const pid = el.dataset.product_id;
            const vid = el.dataset.variation_id || 0;
            if (!pid) return;
            el.classList.toggle('active', keys.has(keyOf(pid, vid)));
        });
        setWishlistCount(keys.size);
    }

    // ===== Server AJAX =====
    async function serverToggle(pid, vid){
        const fd = new FormData();
        fd.append('action','my_wishlist_toggle');
        fd.append('nonce', NONCE);
        fd.append('pid', pid);
        fd.append('vid', vid||0);
        const res = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd, cache:'no-store' });
        const json = await res.json().catch(()=>null);
        if (!res.ok || !json || !json.success) throw new Error(json?.data?.message || 'Server error');
        return json.data; // {status,count}
    }
    async function serverList(){
        const fd = new FormData();
        fd.append('action','my_wishlist_list');
        fd.append('nonce', NONCE);
        const res = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd, cache:'no-store' });
        const json = await res.json().catch(()=>null);
        if (!res.ok || !json || !json.success) return [];
        return json.data.items||[];
    }
    async function serverMerge(items){
        const fd = new FormData();
        fd.append('action','my_wishlist_merge');
        fd.append('nonce', NONCE);
        fd.append('items', JSON.stringify(items||[]));
        const res = await fetch(AJAX_URL, { method:'POST', credentials:'same-origin', body:fd, cache:'no-store' });
        const json = await res.json().catch(()=>null);
        if (!res.ok || !json || !json.success) throw new Error('Merge failed');
        return json.data; // {count}
    }

    // ===== Guest LS toggle =====
    function lsToggle(pid, vid){
        let list = readLS();
        const k = keyOf(pid,vid);
        const idx = list.findIndex(it => keyOf(it.pid,it.vid)===k);
        let status;
        if (idx>=0){ list.splice(idx,1); status='removed'; }
        else { list.push({pid:Number(pid),vid:Number(vid||0),ts:Date.now()}); status='added'; }
        writeLS(list);
        try { list.length ? localStorage.setItem(PENDING_KEY,'1') : localStorage.removeItem(PENDING_KEY); } catch(_){}
        setWishlistCount(list.length);
        return {status, list};
    }

    // ===== Toast (optional) =====
    let lastToast=0;
    function toast(type, title){
        if (!window.Swal) return;
        const now=Date.now(); if (now-lastToast<350) return;
        lastToast=now;
        Swal.fire({icon:type,title,showConfirmButton:false,timer:1000});
    }

    // ===== Wishlist sahifasini AJAX bilan render qilish (inline script o‘rniga) =====
    async function renderWishlistPage(){
        const wrap = document.querySelector(SEL.listId);
        if (!wrap) return;

        const fd = new FormData();
        fd.append('action', 'galeon_wishlist_render');   // PHP’da galeon_wishlist_render_v2 ga ulangan
        fd.append('nonce', NONCE);
        if (!LOGGED) {
            // Guest: LS dagi elementlarni yuboramiz
            fd.append('items', JSON.stringify(readLS()));
        }
        fd.append('ts', Date.now()); // cache buster

        try {
            const res = await fetch(AJAX_URL, {
                method: 'POST',
                credentials: 'same-origin',
                body: fd,
                cache: 'no-store'
            });
            const json = await res.json();

            if (json && json.success) {
                const html = (json.data && json.data.html) ? json.data.html : '<p class="empty">Список избранного пуст.</p>';
                wrap.innerHTML = html;

                const cnt = Number(json.data && json.data.count || 0);
                setWishlistCount(cnt);

                // Guest: server normalize qilgan ro'yxatni LS ga yozib qo'yamiz
                if (!LOGGED && json.data && Array.isArray(json.data.items)) {
                    writeLS(json.data.items);
                }
            } else {
                wrap.innerHTML = '<p class="empty">Список избранного пуст.</p>';
            }
        } catch (e) {
            wrap.innerHTML = '<p class="empty">Список избранного пуст.</p>';
        }
    }

    // ===== Init on DOM ready =====
    document.addEventListener('DOMContentLoaded', async ()=>{
        try{
            if (LOGGED){
                // Merge guest LS faqat bir marta (agar bo‘lsa)
                const ls = readLS();
                const shouldMerge = (localStorage.getItem(PENDING_KEY)==='1') && ls.length>0;
                if (shouldMerge){ try{ await serverMerge(ls); }catch(_){/*ignore*/} }
                clearGuestLS();

                const sv = await serverList();
                setHearts(sv);
            } else {
                setHearts(readLS());
            }
        }catch(_){}

        // Wishlist sahifasi bo‘lsa — shu yerda to‘ldiramiz
        renderWishlistPage();
    });

    // Cross-tab sync (guest)
    window.addEventListener('storage', (e)=>{
        if (!LOGGED && e.key===LS_KEY) setHearts(readLS());
    });

    // Variations: tanlanganda active formadagi like'ga variation_id yozamiz
    document.addEventListener('change', (e)=>{
        const form = e.target.closest('form.variations_form');
        if (!form) return;
        const varId = form.querySelector('input[name="variation_id"]')?.value;
        document.querySelectorAll(`${SEL.icon}[data-product_type="variable"]`).forEach(el=>{
            if (varId && varId!=='0') el.dataset.variation_id = varId;
            else el.removeAttribute('data-variation_id');
        });
    });

    // Toggle handler
    document.addEventListener('click', async (e)=>{
        const btn = e.target.closest(SEL.icon);
        if (!btn) return;

        e.preventDefault(); e.stopPropagation();
        if (btn.dataset.wlBusy==='1') return;
        btn.dataset.wlBusy='1';

        const pid   = btn.dataset.product_id;
        const ptype = btn.dataset.product_type || 'simple';
        const vid   = (ptype==='variable') ? (btn.dataset.variation_id||0) : 0;
        if (!pid){ btn.dataset.wlBusy='0'; return; }

        try{
            if (LOGGED){
                await serverToggle(pid,vid);
                const sv = await serverList();
                setHearts(sv);

                // Agar wishlist sahifadamisiz — o‘chirilgan kartani olib tashlang
                const listWrap = document.querySelector(SEL.listId);
                if (listWrap){
                    const exists = sv.some(it => keyOf(it.pid,it.vid)===keyOf(pid,vid));
                    if (!exists){
                        const card = btn.closest('.product_card_item');
                        if (card) card.remove();
                        if (!listWrap.querySelector('.product_card_item')){
                            listWrap.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                        }
                    }
                }
                const still = sv.some(it => keyOf(it.pid,it.vid)===keyOf(pid,vid));
                toast(still?'info':'warning', still?'Товар добавлен в избранное!':'Удалено из избранного');
            } else {
                const {status, list} = lsToggle(pid,vid);
                btn.classList.toggle('active', status==='added');
                setHearts(list);
                toast(status==='added'?'info':'warning', status==='added'?'Товар добавлен в избранное!':'Удалено из избранного');

                // Wishlist sahifasida bo‘lsa — unlike bo‘lganda kartani olib tashlash
                const listWrap = document.querySelector(SEL.listId);
                if (listWrap && status==='removed'){
                    const card = btn.closest('.product_card_item');
                    if (card) card.remove();
                    if (!listWrap.querySelector('.product_card_item')){
                        listWrap.innerHTML = '<p class="empty">Список избранного пуст.</p>';
                    }
                }
            }
        } catch(_){
            toast('error','Не удалось обновить избранное');
        } finally {
            btn.dataset.wlBusy='0';
        }
    }, true);
})();
