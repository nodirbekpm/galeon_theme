<?php
get_header();

$current_cat = is_product_category() ? get_queried_object() : null;
$shop_url    = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
?>


    <!-- hero -->
<section class="hero page catalog">
    <div class="container">

        <?php if (function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb('<div class="breadcrumb">', '</div>');
        } ?>

        <div class="main_title">
            <?php echo $current_cat ? esc_html($current_cat->name) : 'Все кейсы'; ?>
        </div>

        <div class="sub_title">
            Каждое изделие из каталога может быть доработано и оснащено в соответствии с вашим уникальным ТЗ.
        </div>
    </div>
</section>

<!--catalog  -->
<section class="catalog">
    <div class="container">

        <div class="hidden_buttons">
            <div class="category_button button" id="category_open_button"><?php echo $current_cat ? esc_html($current_cat->name) : 'Все кейсы'; ?></div>
            <div class="filter_button button" id="filter_button">Фильтры <span>( 1 )</span></div>
        </div>

        <div class="top">
            <div class="info">Найдено: <span>199 позиций</span></div>
            <style>
                /* search offers */
                .search_offers {
                    position: absolute;
                    top: 100%; /* right under input */
                    left: 0;
                    width: 100%;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-top: none;
                    border-radius: 0 0 8px 8px;
                    -webkit-box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    max-height: 250px;
                    overflow-y: auto;
                    display: none; /* hidden by default */
                    z-index: 10;
                }
                .search_offers.open {
                    display: block;
                }

                .search_offers ul {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                }

                .search_offers li {
                    padding: 10px 15px;
                    display: -webkit-box;
                    display: -ms-flexbox;
                    display: flex;
                    -webkit-box-align: center;
                    -ms-flex-align: center;
                    align-items: center;
                    gap: 0 10px;
                    -webkit-transition: background 0.2s;
                    transition: background 0.2s;
					cursor: pointer;
                }

                .search_offers li .Suggestion_image {
                    width: 40px !important;
                    height: 30px !important;
                }

                .search_offers li a {
                    font-size: 15px;
                    color: #333;
                    cursor: pointer;
                }

                .search_offers li:hover {
                    background: #f3f3f3;
                }
            </style>
            <form class="header_search">
                <button  class="search_link"></button>
                <input type="text" name="search" placeholder="Поиск по каталогу...">
                <div class="search_offers">
                    <ul>
                    </ul>
                </div>
            </form>
        </div>

        <div class="main">
            <!-- filter -->
            <style>
                @media (max-width: 992px){
                    .catalog .main .filter .filter_main.active{
                        padding-bottom: 75px;
                    }
                    .catalog .main .filter .filter-buttons .btn-primary{
                        position: fixed;
                        width: 96.5%;
                        bottom: 0;
                    }
                }
            </style>
            <!-- <div class="filter_cover" data-sticky> -->
            <div class="filter " id="filter" >

                <div class="filter-section categories open">
                    <div class="section-title">Категории:</div>
                    <div class="section-body">
                        <ul class="category-list" id="category_list"
                            data-shop-url="<?php echo esc_url($shop_url); ?>"
                            data-current-slug="<?php echo esc_attr($current_cat ? $current_cat->slug : ''); ?>">
                            <li class="list_item <?php echo $current_cat ? '' : 'active'; ?>"
                                data-slug=""
                                data-url="<?php echo esc_url($shop_url); ?>">
                                Все кейсы
                            </li>
                            <?php
                            $cats = get_terms([
                                'taxonomy'   => 'product_cat',
                                'hide_empty' => true,
                                'parent'     => 0,
                            ]);
                            foreach ($cats as $t): ?>
                                <li class="list_item <?php echo ($current_cat && $current_cat->term_id === $t->term_id) ? 'active' : ''; ?>"
                                    data-slug="<?php echo esc_attr($t->slug); ?>"
                                    data-url="<?php echo esc_url(get_term_link($t)); ?>">
                                    <?php echo esc_html($t->name); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                    </div>
                </div>

                <div class="filter-overlay"></div> <!-- new overlay -->

                <div class="filter_main " id="filter_main">

                    <div class="filter_top ">
                        <div class="small_title">Фильтры</div>
                        <div class="close"><span>Закрыть</span><img src="<?php echo get_template_directory_uri() ?>/assets/images/close_btn_filter.svg" alt=""></div>
                    </div>

                    <!-- PRICE -->
                    <div class="filter-section open">
                        <div class="section-title">Цена ₽:</div>
                        <div class="section-body">
                            <div class="range-inputs">
                                <input type="number" class="input-min" value="0" min="0" max="300000" />
                                <input type="number" class="input-max" value="300000" min="0" max="300000" />
                            </div>
                            <div class="range-slider">
                                <input type="range" class="range-min" min="0" max="300000" value="0" step="1" />
                                <input type="range" class="range-max" min="0" max="300000" value="300000" step="1" />
                                <div class="slider-track"></div>
                            </div>
                        </div>
                    </div>

                    <!-- LENGTH -->
                    <div class="filter-section open">
                        <div class="section-title">Внутренняя длина мм:</div>
                        <div class="section-body">
                            <div class="range-inputs">
                                <input type="number" class="input-min" value="0" min="0" max="456" />
                                <input type="number" class="input-max" value="456" min="0" max="456" />
                            </div>
                            <div class="range-slider">
                                <input type="range" class="range-min" min="0" max="456" value="0" step="1" />
                                <input type="range" class="range-max" min="0" max="456" value="456" step="1" />
                                <div class="slider-track"></div>
                            </div>
                        </div>
                    </div>

                    <!-- filter item -->
                    <div class="filter-section open">
                        <div class="section-title">Внутренняя ширина мм</div>
                        <div class="section-body">
                            <div class="range-inputs">
                                <input type="number" class="input-min" value="0" min="0" max="456" />
                                <input type="number" class="input-max" value="456" min="0" max="456" />
                            </div>
                            <div class="range-slider">
                                <input type="range" class="range-min" min="0" max="456" value="0" step="1" />
                                <input type="range" class="range-max" min="0" max="456" value="456" step="1" />
                                <div class="slider-track"></div>
                            </div>
                        </div>
                    </div>

                    <!-- filter item -->
                    <div class="filter-section open">
                        <div class="section-title">Внутренняя высота мм</div>
                        <div class="section-body">
                            <div class="range-inputs">
                                <input type="number" class="input-min" value="0" min="0" max="456" />
                                <input type="number" class="input-max" value="456" min="0" max="456" />
                            </div>
                            <div class="range-slider">
                                <input type="range" class="range-min" min="0" max="456" value="0" step="1" />
                                <input type="range" class="range-max" min="0" max="456" value="456" step="1" />
                                <div class="slider-track"></div>
                            </div>
                        </div>
                    </div>


                    <div class="filter-section open">
                        <div class="section-title">Вес кг:</div>
                        <div class="section-body">
                            <div class="range-inputs">
                                <input type="number" class="input-min" value="0" min="0" max="10" />
                                <input type="number" class="input-max" value="10" min="0" max="10" />
                            </div>
                            <div class="range-slider">
                                <input type="range" class="range-min" min="0" max="10" value="0" step="0.1" />
                                <input type="range" class="range-max" min="0" max="10" value="1" step="0.1" />
                                <div class="slider-track"></div>
                            </div>
                        </div>
                    </div>


                    <div class="filter-section open open">
                        <div class="section-title">Вариант:</div>
                        <div class="section-body">
                            <div class="checkbox-list">
                                <?php
                                if ( taxonomy_exists('pa_option') ) {
                                    $opts = get_terms(['taxonomy'=>'pa_option','hide_empty'=>true]);
                                    foreach ($opts as $opt): ?>
                                        <label>
                                            <input type="checkbox" value="<?php echo esc_attr($opt->slug); ?>">
                                            <?php echo esc_html($opt->name); ?>
                                        </label>
                                    <?php endforeach;
                                } ?>
                            </div>
                        </div>
                    </div>


                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">Показать</button>
                        <button type="reset" class="btn btn-reset" id="btn-reset">Сбросить фильтры</button>
                    </div>
                </div>

            </div>
            <!-- </div> -->

            <div class="right">
                <!-- catalog row -->
                <div class="catalog_row">

                </div>



                <div class="pages">

                    <!-- bottom_info -->
                    <div class="more_button">
                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/show_more.svg" alt="">
                        <span>Загрузить еще</span>
                    </div>

                    <div class="pagination">
<!--                        <span class="pagination_item active">1</span>-->
<!--                        <span class="pagination_item">2</span>-->
<!--                        <span class="pagination_item">3</span>-->
<!--                        <span class="pagination_item">...</span>-->
<!--                        <span class="pagination_item">11</span>-->
                    </div>

                    <div class="count">
                        <span>Показать по:</span>
                        <span class="active">9</span>
                        <span>18</span>
                        <span>27</span>
                        <span>36</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

    <script>
        (function(){
            // === Config ===
            const CFG   = window.GALEON_ARCHIVE || {};
            const URL   = CFG.ajax_url || (window.ajaxurl || '/wp-admin/admin-ajax.php');
            const NONCE = CFG.nonce || '';
            const STEP  = 4; // "Загрузить ещё"

            // === Helpers ===
            const $  = (s, r=document)=>r.querySelector(s);
            const $$ = (s, r=document)=>Array.from(r.querySelectorAll(s));
            const minGapDefault = 1;

            const debounce = (fn, delay=300)=>{
                let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), delay); };
            };

            const cbVal = (el)=>{
                if (!el) return '';
                if (el.value && el.value !== 'on') return el.value.trim();
                const dataTerm = el.dataset?.term || '';
                if (dataTerm) return dataTerm.trim();
                const labelTxt = el.closest('label')?.innerText || '';
                return labelTxt.trim();
            };

            // === DOM ===
            const rowEl      = $('.catalog_row');
            const foundEl    = $('.top .info span');
            const searchForm = $('.top .header_search');
            const searchEl   = $('.top .header_search input[name="search"]');
            const offersBox  = searchForm?.querySelector('.search_offers');
            const offersUl   = offersBox?.querySelector('ul');
            const catList    = $('#category_list');

            const moreBtn    = $('.pages .more_button');
            const pagEl      = $('.pages .pagination');
            const perWrap    = $('.pages .count');

            const catBtn     = $('#category_open_button');
            const filterBtn  = $('#filter_button');

            const applyBtn   = $('.filter-buttons .btn-primary');
            const resetBtn   = $('.filter-buttons #btn-reset');

            // --- NEW: auto / manual rejimni aniqlash uchun ---
            const hiddenButtons = $('.hidden_buttons');
            const isAutoMode = () => hiddenButtons && getComputedStyle(hiddenButtons).display === 'none';
            const softUpdate = () => { readRangesFromUI(); updateTopButtons(); };
            const commitFilters = () => {
                readRangesFromUI();
                updateTopButtons();
                if (isAutoMode()) {
                    state.page = 1;
                    state.extraShown = 0;
                    fetchProducts('replace');
                }
            };
            // ---------------------------------------------------

            // === Current context (slug + URLs) ===
            const currentSlug =
                (catList?.dataset.currentSlug || '').trim() ||
                (CFG.initial_category_slug || '').trim() ||
                (document.body.className.match(/\bterm-([^\s]+)\b/)?.[1] || '');

            const shopUrl =
                (catList?.dataset.shopUrl || '').trim() ||
                (CFG.shop_url || '').trim() ||
                (window.wc_shop_url || '').trim() ||
                '/shop/';

            // === State ===
            const perFromUI = (() => {
                const a = $('.count .active');
                const n = a ? parseInt(a.textContent, 10) : (Number(CFG.per_page_default) || 9);
                return [9,18,27,36].includes(n) ? n : 9;
            })();

            const state = {
                search: '',
                category: currentSlug,
                price:{min:'',max:''}, len:{min:'',max:''}, wid:{min:'',max:''}, hei:{min:'',max:''}, wei:{min:'',max:''},
                variants: [],
                page: 1,
                perBase: perFromUI,
                extraShown: 0,
                total: 0,
                facets: null,
                hardSetFacetValues: true
            };

            // === filter-section'larni teglash
            function tagFilterSections(){
                $$('.filter-section').forEach(sec=>{
                    const t=(sec.querySelector('.section-title')?.textContent||'').trim().toLowerCase();
                    if (t.startsWith('цена')) sec.dataset.filter='price';
                    else if (t.includes('длина')) sec.dataset.filter='len';
                    else if (t.includes('ширина')) sec.dataset.filter='wid';
                    else if (t.includes('высота')) sec.dataset.filter='hei';
                    else if (t.startsWith('вес')) sec.dataset.filter='wei';
                    else if (t.startsWith('вариант')) sec.dataset.filter='variant';
                });
            }
            tagFilterSections();

            // === UI ↔ State
            function readRangesFromUI(){
                const readOne = key=>{
                    const box = $('.filter-section[data-filter="'+key+'"]'); if(!box) return {min:'',max:''};
                    const mn = box.querySelector('.input-min')?.value ?? '';
                    const mx = box.querySelector('.input-max')?.value ?? '';
                    return {min: mn, max: mx};
                };
                state.price=readOne('price');
                state.len  =readOne('len');
                state.wid  =readOne('wid');
                state.hei  =readOne('hei');
                state.wei  =readOne('wei');

                const vbox = $('.filter-section[data-filter="variant"] .checkbox-list');
                const vs = [];
                vbox?.querySelectorAll('input[type="checkbox"]:checked').forEach(i=>vs.push(cbVal(i)));
                state.variants = vs;
            }

            function writeFacetToUI(key, minV, maxV){
                const box = $('.filter-section[data-filter="'+key+'"]'); if(!box) return;
                const inMin = box.querySelector('.input-min');
                const inMax = box.querySelector('.input-max');
                const rMin  = box.querySelector('.range-min');
                const rMax  = box.querySelector('.range-max');

                const mn = Number.isFinite(minV) ? Number(minV) : 0;
                const mx = Number.isFinite(maxV) ? Number(maxV) : 0;

                if (inMin){ inMin.min=mn; inMin.max=mx; }
                if (inMax){ inMax.min=mn; inMax.max=mx; }
                if (rMin){  rMin.min=mn;  rMin.max=mx;  }
                if (rMax){  rMax.min=mn;  rMax.max=mx;  }

                if (state.hardSetFacetValues){
                    if (inMin) inMin.value = mn;
                    if (inMax) inMax.value = mx;
                    if (rMin)  rMin.value  = mn;
                    if (rMax)  rMax.value  = mx;
                }
            }

            function setDefaultsFromFacets(){
                if (!state.facets) return;
                const setDef = (key)=>{
                    const box = $('.filter-section[data-filter="'+key+'"]'); if(!box) return;
                    const mn = state.facets[key]?.min ?? '';
                    const mx = state.facets[key]?.max ?? '';
                    box.dataset.defMin = String(mn);
                    box.dataset.defMax = String(mx);
                };
                ['price','len','wid','hei','wei'].forEach(setDef);
                const vbox = $('.filter-section[data-filter="variant"] .checkbox-list');
                if (vbox){ vbox.dataset.defSel = ''; }
            }

            function countActiveFilters(){
                let n=0;
                ['price','len','wid','hei','wei'].forEach(key=>{
                    const box = $('.filter-section[data-filter="'+key+'"]'); if(!box) return;
                    const defMin = box.dataset.defMin ?? '';
                    const defMax = box.dataset.defMax ?? '';
                    const curMin = box.querySelector('.input-min')?.value ?? '';
                    const curMax = box.querySelector('.input-max')?.value ?? '';
                    if (String(curMin)!==String(defMin) || String(curMax)!==String(defMax)) n++;
                });
                const vbox = $('.filter-section[data-filter="variant"] .checkbox-list');
                if (vbox){
                    const def = vbox.dataset.defSel ?? '';
                    const cur = [];
                    vbox.querySelectorAll('input[type="checkbox"]:checked').forEach(i=>cur.push(cbVal(i)));
                    const now = cur.sort().join('|');
                    if (now !== def) n++;
                }
                return n;
            }

            function updateTopButtons(){
                const activeCat = $('#category_list .list_item.active');
                const catName   = activeCat ? activeCat.textContent.trim() : 'Все кейсы';
                if (catBtn) catBtn.textContent = catName;
                const cnt = countActiveFilters();
                if (filterBtn) filterBtn.innerHTML = `Фильтры <span>( ${cnt} )</span>`;
            }

            function buildPagination(total, perPage, current){
                if (!pagEl) return;
                pagEl.innerHTML = '';
                const totalPages = Math.max(1, Math.ceil((total||0)/(perPage||1)));
                const add = (p, label)=>{
                    const s = document.createElement('span');
                    s.className = 'pagination_item' + (p===current ? ' active' : '');
                    s.dataset.page = p;
                    s.textContent = String(label ?? p);
                    pagEl.appendChild(s);
                };
                const dots = ()=>{
                    const d = document.createElement('span');
                    d.className = 'pagination_item';
                    d.textContent = '...';
                    pagEl.appendChild(d);
                };
                if (totalPages <= 7){
                    for (let i=1;i<=totalPages;i++) add(i);
                } else {
                    add(1);
                    if (current>4) dots();
                    const start=Math.max(2,current-1), end=Math.min(totalPages-1,current+1);
                    for (let i=start;i<=end;i++) add(i);
                    if (current<totalPages-3) dots();
                    add(totalPages);
                }
            }

            function initSwipers(){
                if (typeof Swiper==='undefined') return;
                $$('.catalogSwiper:not(.js-inited)').forEach(sw=>{
                    sw.classList.add('js-inited');
                    new Swiper(sw,{ slidesPerView:1, loop:true, pagination:{ el: sw.querySelector('.swiper-pagination'), clickable:true } });
                });
            }

            // ================== RANGE (dinamik min/max bilan) ==================
            function initRangeSync(){
                document.querySelectorAll('.filter-section').forEach(section => {
                    if (section.__rangeInited) return;
                    section.__rangeInited = true;

                    const inputMin = section.querySelector('.input-min');
                    const inputMax = section.querySelector('.input-max');
                    const rangeMin = section.querySelector('.range-min');
                    const rangeMax = section.querySelector('.range-max');
                    const track = section.querySelector('.slider-track');

                    if (!inputMin || !inputMax || !rangeMin || !rangeMax || !track) return;

                    const getMinBound = ()=> Number(rangeMin.min);
                    const getMaxBound = ()=> Number(rangeMin.max);
                    const getStep     = ()=> Number(rangeMin.step) || 1;
                    const getMinGap   = ()=> getStep() * minGapDefault;

                    function updateTrack() {
                        const a = Number(rangeMin.value);
                        const b = Number(rangeMax.value);
                        const mn = getMinBound(), mx = getMaxBound();
                        const total = Math.max(1, mx - mn);
                        const left = ((a - mn) / total) * 100;
                        const right = ((b - mn) / total) * 100;
                        track.style.background =
                            `linear-gradient(90deg, #e6e6e6 ${left}%, #00a0c6 ${left}%, #00a0c6 ${right}%, #e6e6e6 ${right}%)`;
                    }

                    function setFromInputs() {
                        const mn = getMinBound(), mx = getMaxBound(), minGap = getMinGap();
                        let a = Number(inputMin.value) || mn;
                        let b = Number(inputMax.value) || mx;
                        if (a < mn) a = mn;
                        if (b > mx) b = mx;
                        if (a > b - minGap) a = b - minGap;
                        if (b < a + minGap) b = a + minGap;

                        rangeMin.value = a;
                        rangeMax.value = b;
                        inputMin.value = a;
                        inputMax.value = b;
                        updateTrack();
                    }

                    function setFromRanges(e) {
                        const mn = getMinBound(), mx = getMaxBound(), minGap = getMinGap();
                        let a = Number(rangeMin.value);
                        let b = Number(rangeMax.value);

                        if (b - a < minGap) {
                            if (e && e.target === rangeMin) {
                                a = b - minGap;
                                rangeMin.value = a;
                            } else {
                                b = a + minGap;
                                rangeMax.value = b;
                            }
                        }
                        if (a < mn) { a = mn; rangeMin.value = a; }
                        if (b > mx) { b = mx; rangeMax.value = b; }

                        inputMin.value = a;
                        inputMax.value = b;
                        updateTrack();
                    }

                    inputMin.min = rangeMin.min; inputMin.max = rangeMin.max;
                    inputMax.min = rangeMax.min; inputMax.max = rangeMax.max;

                    inputMin.addEventListener('input', setFromInputs);
                    inputMax.addEventListener('input', setFromInputs);
                    rangeMin.addEventListener('input', setFromRanges);
                    rangeMax.addEventListener('input', setFromRanges);

                    setFromInputs();
                });
            }

            function hardSyncRangesAndRepaint(){
                $$('.filter-section').forEach(section=>{
                    const inMin = section.querySelector('.input-min');
                    const inMax = section.querySelector('.input-max');
                    const rMin  = section.querySelector('.range-min');
                    const rMax  = section.querySelector('.range-max');
                    const track = section.querySelector('.slider-track');
                    if (!inMin || !inMax || !rMin || !rMax || !track) return;

                    rMin.min = inMin.min; rMin.max = inMin.max; rMin.value = inMin.value;
                    rMax.min = inMax.min; rMax.max = inMax.max; rMax.value = inMax.value;

                    const mn = Number(rMin.min), mx = Number(rMin.max);
                    const a = Number(rMin.value), b = Number(rMax.value);
                    const total = Math.max(1, mx - mn);
                    const left  = ((a - mn) / total) * 100;
                    const right = ((b - mn) / total) * 100;
                    track.style.background =
                        `linear-gradient(90deg, #e6e6e6 ${left}%, #00a0c6 ${left}%, #00a0c6 ${right}%, #e6e6e6 ${right}%)`;
                });
            }
            // ================== /RANGE ==================

            function markWishlistIcons(wishIds){
                if (!Array.isArray(wishIds) || wishIds.length === 0) return;
                const set = new Set(wishIds.map(id => String(id)));
                $$('.catalog_row .catalog_item .like_icon').forEach(el=>{
                    const pid = el?.dataset?.product_id ? String(el.dataset.product_id) : '';
                    if (set.has(pid)) el.classList.add('active');
                });
            }

            // === AJAX load
            async function fetchProducts(mode='replace'){
                const fd = new FormData();
                fd.append('action','galeon_load_products');
                fd.append('nonce', NONCE);
                fd.append('mode', mode);
                fd.append('search', state.search);
                fd.append('category', state.category);

                const addR=(k,o)=>{ fd.append(k+'[min]', o.min ?? ''); fd.append(k+'[max]', o.max ?? ''); };
                addR('price', state.price);
                addR('len',   state.len);
                addR('wid',   state.wid);
                addR('hei',   state.hei);
                addR('wei',   state.wei);
                (state.variants||[]).forEach(v=>fd.append('variants[]', v));

                if (mode==='append'){
                    const already = $$('.catalog_row .catalog_item').length;
                    fd.append('offset', already);
                    fd.append('limit',  STEP);
                } else {
                    const per = Math.max(1, state.perBase + state.extraShown);
                    fd.append('page', state.page);
                    fd.append('per_page', per);
                }

                const res  = await fetch(URL,{method:'POST',credentials:'same-origin',body:fd});
                const text = await res.text(); let json=null; try{ json=JSON.parse(text); }catch(e){}
                if(!res.ok || !json || !json.success){ console.error('Archive AJAX error', res.status, text); return; }
                const data = json.data;

                state.total = data.total || 0;
                if (foundEl) foundEl.textContent = state.total + ' позиций';

                if (mode==='append'){
                    const tmp=document.createElement('div');
                    tmp.innerHTML = data.html || '';
                    tmp.querySelectorAll('.catalog_item').forEach(n=>rowEl.appendChild(n));
                } else {
                    rowEl.innerHTML = data.html || '';
                }

                state.facets = data.facets || null;
                if (state.facets){
                    writeFacetToUI('price', state.facets.price?.min, state.facets.price?.max);
                    writeFacetToUI('len',   state.facets.len?.min,   state.facets.len?.max);
                    writeFacetToUI('wid',   state.facets.wid?.min,   state.facets.wid?.max);
                    writeFacetToUI('hei',   state.facets.hei?.min,   state.facets.hei?.max);
                    writeFacetToUI('wei',   state.facets.wei?.min,   state.facets.wei?.max);
                    setDefaultsFromFacets();
                    hardSyncRangesAndRepaint();
                }

                markWishlistIcons(data.wish);

                const effectivePer = Math.max(1, state.perBase + state.extraShown);
                buildPagination(state.total, effectivePer, state.page);

                const shown = $$('.catalog_row .catalog_item').length;
                if (moreBtn){
                    const left = Math.max(0, state.total - shown);
                    moreBtn.style.display = left > 0 ? '' : 'none';
                    const cap = moreBtn.querySelector('span');
                    if (cap) cap.textContent = left > 0 ? `Загрузить еще (${left})` : 'Загрузить еще';
                }

                updateTopButtons();
                initSwipers();
                if (!MODAL_AUTH_V2 || !MODAL_AUTH_V2.logged_in) {
                    window.applyWishlistActiveFromLS?.();
                }

                state.hardSetFacetValues = false;
            }

            // === RESET
            function resetAll(){
                $$('#category_list .list_item').forEach(x=>x.classList.remove('active'));
                const active = currentSlug
                    ? catList?.querySelector(`.list_item[data-slug="${CSS.escape(currentSlug)}"]`)
                    : catList?.querySelector('.list_item[data-slug=""]');
                if (active) active.classList.add('active');

                if (searchEl) searchEl.value = '';
                $$('.filter-section[data-filter="variant"] .checkbox-list input[type="checkbox"]').forEach(i=>i.checked=false);

                ['price','len','wid','hei','wei'].forEach(key=>{
                    const box = $('.filter-section[data-filter="'+key+'"]'); if(!box) return;
                    const inMin  = box.querySelector('.input-min');
                    const inMax  = box.querySelector('.input-max');
                    const rMin   = box.querySelector('.range-min');
                    const rMax   = box.querySelector('.range-max');
                    if (inMin) inMin.value = '';
                    if (inMax) inMax.value = '';
                    if (rMin)  rMin.value  = '';
                    if (rMax)  rMax.value  = '';
                    delete box.dataset.defMin;
                    delete box.dataset.defMax;
                });
                const vbox = $('.filter-section[data-filter="variant"] .checkbox-list');
                if (vbox) vbox.dataset.defSel = '';

                state.search     = '';
                state.category   = currentSlug;
                state.page       = 1;
                state.extraShown = 0;
                state.price={min:'',max:''}; state.len={min:'',max:''}; state.wid={min:'',max:''}; state.hei={min:'',max:''}; state.wei={min:'',max:''};
                state.variants   = [];

                state.hardSetFacetValues = true;

                updateTopButtons();
            }

            // === EVENTS ===

            // Kategoriya bosilganda — URL’ga o'tish
            catList?.addEventListener('click', e=>{
                const li = e.target.closest('.list_item'); if(!li) return;

                const slug = (li.dataset.slug || '').trim();
                const direct = (li.dataset.url || '').trim();

                let target = '';
                if (direct) {
                    target = direct;
                } else if (!slug) {
                    target = shopUrl; // "Все кейсы"
                } else if (CFG.cat_links && typeof CFG.cat_links === 'object' && CFG.cat_links[slug]) {
                    target = CFG.cat_links[slug];
                } else if (CFG.cat_base_url) {
                    target = (CFG.cat_base_url.replace(/\/+$/,'') + '/' + slug + '/');
                } else {
                    target = (window.location.origin + '/product-category/' + slug + '/');
                }
                if (target) window.location.href = target;
            });

            // === Live search (eski mantiq)
            const doLiveSearch = debounce(()=>{
                state.search     = searchEl?.value?.trim() || '';
                state.page       = 1;
                state.extraShown = 0;
                fetchProducts('replace');
            }, 300);
            searchEl?.addEventListener('input', doLiveSearch);
            searchForm?.addEventListener('submit', e=>{ e.preventDefault(); doLiveSearch(); });

            // === SEARCH SUGGESTIONS (fast)
            const suggestCache = new Map();
            let   suggestAbort = null;

            const escHTML = s => s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
            function closeOffers(){
                if (!offersBox || !offersUl) return;
                offersBox.classList.remove('open');
                offersBox.removeAttribute('data-active-index');
                offersUl.innerHTML = '';
            }
            function renderOffers(items){
                if (!offersBox || !offersUl) return;
                if (!items || !items.length){ closeOffers(); return; }
                offersUl.innerHTML = items.map(txt => `<li data-value="${escHTML(txt)}"><span>${escHTML(txt)}</span></li>`).join('');
                offersBox.classList.add('open');
                offersBox.dataset.activeIndex = '-1';
            }
            async function fetchSuggest(q){
                const key = q.toLowerCase();
                if (suggestCache.has(key)) { renderOffers(suggestCache.get(key)); return; }
                try{
                    if (suggestAbort) suggestAbort.abort();
                    suggestAbort = new AbortController();
                    const fd = new FormData();
                    fd.append('action','galeon_search_suggest');
                    fd.append('nonce', NONCE);
                    fd.append('q', q);
                    fd.append('limit','8');
                    const res = await fetch(URL,{method:'POST', credentials:'same-origin', body: fd, signal: suggestAbort.signal});
                    const json = await res.json().catch(()=>null);
                    const items = (json && json.success && Array.isArray(json.data.items)) ? json.data.items : [];
                    suggestCache.set(key, items);
                    renderOffers(items);
                }catch(_){ /* aborted or failed */ }
            }
            const onTypeSuggest = debounce(()=>{
                const val = (searchEl?.value || '').trim();
                if (!val || val.length < 2) { closeOffers(); return; }
                fetchSuggest(val);
            }, 120);
            // input har ikkala handlerni ham tetiklaydi (taklif + live)
            searchEl?.addEventListener('input', onTypeSuggest);

            // Taklifdan tanlash
            offersBox?.addEventListener('click', (e)=>{
                const li = e.target.closest('li'); if (!li) return;
                const v = li.dataset.value || li.textContent.trim();
                if (searchEl){ searchEl.value = v; }
                closeOffers();
                doLiveSearch();
            });

            // Tashqariga bosilsa yopish
            document.addEventListener('click', (e)=>{
                if (!offersBox) return;
                if (e.target.closest('.header_search')) return;
                closeOffers();
            });

            // Klaviatura bilan navigatsiya
            searchEl?.addEventListener('keydown', (e)=>{
                if (!offersBox || !offersBox.classList.contains('open')) return;
                const items = offersUl?.querySelectorAll('li'); if (!items || !items.length) return;
                let idx = Number(offersBox.dataset.activeIndex || '-1');

                if (e.key === 'ArrowDown'){
                    e.preventDefault();
                    idx = (idx + 1) % items.length;
                } else if (e.key === 'ArrowUp'){
                    e.preventDefault();
                    idx = (idx - 1 + items.length) % items.length;
                } else if (e.key === 'Enter'){
                    if (idx >= 0){
                        e.preventDefault();
                        items[idx].click();
                    }
                    return;
                } else if (e.key === 'Escape'){
                    closeOffers();
                    return;
                } else {
                    return;
                }

                items.forEach((li,i)=> li.classList.toggle('active', i===idx));
                offersBox.dataset.activeIndex = String(idx);
            });

            // === FILTER TRIGGERS (AUTO vs MANUAL) ===

            // Variantlar (checkboxlar): avto rejimda o‘zgarishi bilan ishlaydi
            document.addEventListener('change', e=>{
                const sec = e.target.closest('.filter-section[data-filter="variant"]'); if(!sec) return;
                commitFilters(); // auto: fetch; manual: faqat holat yangilanadi
            });

            // Raqamli inputlar: input → silliq UI; change/blur → auto rejimda fetch
            $$('.filter-section .range-inputs .input-min, .filter-section .range-inputs .input-max')
                .forEach(el=>{
                    el.addEventListener('input', softUpdate);
                    el.addEventListener('change', commitFilters);
                    el.addEventListener('blur', commitFilters);
                });

            // Range slayderlar: input → silliq UI; change → auto rejimda fetch
            $$('.filter-section .range-slider .range-min, .filter-section .range-slider .range-max')
                .forEach(el=>{
                    el.addEventListener('input', softUpdate);
                    el.addEventListener('change', commitFilters);
                });

            function closeFilter() {
                let filterPanel = document.querySelector(".filter_main");
                let overlay = document.querySelector(".filter-overlay");
                filterPanel.classList.remove("active");
                overlay.classList.remove("active");
            }

            // MANUAL rejim: “Показать” tugmasi
            applyBtn?.addEventListener('click', e=>{
                e.preventDefault();
                readRangesFromUI();
                state.page       = 1;
                state.extraShown = 0;
                fetchProducts('replace');
                closeFilter();
            });

            resetBtn?.addEventListener('click', e=>{
                e.preventDefault();
                resetAll();
                fetchProducts('replace');
            });

            perWrap?.addEventListener('click', e=>{
                const s=e.target.closest('.count span'); if(!s) return;
                const n=parseInt(s.textContent.trim(),10);
                if (![9,18,27,36].includes(n)) return;
                $$('.count span').forEach(x=>x.classList.remove('active'));
                s.classList.add('active');
                state.perBase   = n;
                state.page      = 1;
                state.extraShown= 0;
                fetchProducts('replace');
            });

            pagEl?.addEventListener('click', e=>{
                const it=e.target.closest('.pagination_item'); if(!it) return;
                if (it.textContent.trim()==='...') return;
                const p=parseInt(it.dataset.page||it.textContent.trim(),10);
                if (!p || p===state.page) return;
                state.page       = p;
                state.extraShown = 0;
                fetchProducts('replace');
                rowEl?.scrollIntoView({behavior:'smooth', block:'start'});
            });

            moreBtn?.addEventListener('click', ()=>{
                state.extraShown += STEP;
                fetchProducts('append');
            });

            document.addEventListener('DOMContentLoaded', ()=>{
                resetAll();
                initRangeSync();
                fetchProducts('replace');
            });
        })();
    </script>





<?php
get_footer();
