<?php
get_header();
?>


    <!-- hero -->
<section class="hero page catalog">
    <div class="container">

        <!-- breadcrumb -->
        <div class="breadcrumb">
            <a href="index.html">Главная</a>
            <span>/</span>
            <a href="catalog.html" class="active">Все кейсы</a>
        </div>

        <div class="main_title">
            Все кейсы
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
            <div class="category_button button" id="category_open_button">Все кейсы</div>
            <div class="filter_button button" id="filter_button">Фильтры <span>( 1 )</span></div>
        </div>

        <div class="top">
            <div class="info">Найдено: <span>199 позиций</span></div>
            <form class="header_search">
                <button  class="search_link"></button>
                <input type="text" name="search" placeholder="Поиск по каталогу...">
            </form>
        </div>

        <div class="main">
            <!-- filter -->
            <!-- <div class="filter_cover" data-sticky> -->
            <div class="filter " id="filter" >

                <div class="filter-section categories open">
                    <div class="section-title">Категории:</div>
                    <div class="section-body">
                        <ul class="category-list" id="category_list">
                            <li class="list_item active" data-slug="">Все кейсы</li>
                            <?php
                            $cats = get_terms([
                                'taxonomy'   => 'product_cat',
                                'hide_empty' => true,
                                'parent'     => 0,
                            ]);
                            foreach ($cats as $t): ?>
                                <li class="list_item" data-slug="<?php echo esc_attr($t->slug); ?>">
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
                        <div class="close"><span>Закрыть</span><img src="<?php echo get_template_directory_uri() ?>/<?php echo get_template_directory_uri() ?>/assets/images/close_btn_filter.svg" alt=""></div>
                    </div>

                    <!-- PRICE -->
                    <div class="filter-section open">
                        <div class="section-title">Цена ₽:</div>
                        <div class="section-body">
                            <div class="range-inputs">
                                <input type="number" class="input-min" value="0" min="0" max="300000" />
                                <input type="number" class="input-max" value="274000" min="0" max="300000" />
                            </div>
                            <div class="range-slider">
                                <input type="range" class="range-min" min="0" max="300000" value="0" step="1" />
                                <input type="range" class="range-max" min="0" max="300000" value="274000" step="1" />
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
                                <input type="range" class="range-min" min="0" max="10" value="0" step="1" />
                                <input type="range" class="range-max" min="0" max="10" value="10" step="1" />
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

            // Checkbox value normalizer: value || data-term || label text
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
            const catList    = $('#category_list');

            const moreBtn    = $('.pages .more_button');
            const pagEl      = $('.pages .pagination');
            const perWrap    = $('.pages .count');

            const catBtn     = $('#category_open_button');
            const filterBtn  = $('#filter_button');

            const applyBtn   = $('.filter-buttons .btn-primary'); // “Показать”
            const resetBtn   = $('.filter-buttons #btn-reset');   // “Сбросить фильтры”

            // === State ===
            const perFromUI = (() => {
                const a = $('.count .active');
                const n = a ? parseInt(a.textContent, 10) : (Number(CFG.per_page_default) || 9);
                return [9,18,27,36].includes(n) ? n : 9;
            })();

            const state = {
                search: '',
                category: '',
                price:{min:'',max:''}, len:{min:'',max:''}, wid:{min:'',max:''}, hei:{min:'',max:''}, wei:{min:'',max:''},
                variants: [],
                page: 1,
                perBase: perFromUI,
                extraShown: 0,
                total: 0,
                facets: null
            };

            // === Filter sections’ga data-filter qo‘yish (HTML o‘zgarmaydi) ===
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

            // === UI ↔ State ===
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

            // Facet min/max ni UI'ga yozish
            function writeFacetToUI(key, minV, maxV){
                const box = $('.filter-section[data-filter="'+key+'"]'); if(!box) return;
                const inMin = box.querySelector('.input-min');
                const inMax = box.querySelector('.input-max');
                const rMin  = box.querySelector('.range-min');
                const rMax  = box.querySelector('.range-max');
                const mn = typeof minV==='number' ? minV : 0;
                const mx = typeof maxV==='number' ? maxV : 0;

                if (inMin){ inMin.min=mn; inMin.max=mx; inMin.value=mn; }
                if (inMax){ inMax.min=mn; inMax.max=mx; inMax.value=mx; }
                if (rMin){  rMin.min=mn;  rMin.max=mx;  rMin.value=mn; }
                if (rMax){  rMax.min=mn;  rMax.max=mx;  rMax.value=mx; }
            }

            // === Baseline (NO-FILTER) ni saqlash: defMin/defMax (facetsdan) + variants def=bo'sh ===
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
                if (vbox){
                    // NO-FILTER baseline: hech biri tanlanmagan
                    vbox.dataset.defSel = '';
                }
            }

            // === “Фильтры ( X )” — NO-FILTER baseline’dan farqni sanash ===
            function countActiveFilters(){
                let n=0;
                ['price','len','wid','hei','wei'].forEach(key=>{
                    const box = $('.filter-section[data-filter="'+key+'"]'); if(!box) return;
                    const defMin = box.dataset.defMin ?? '';
                    const defMax = box.dataset.defMax ?? '';
                    const curMin = box.querySelector('.input-min')?.value ?? '';
                    const curMax = box.querySelector('.input-max')?.value ?? '';
                    // Agar foydalanuvchi qiymatni to'liq diapazonga tenglashtirmagan bo'lsa — filtr aktiv
                    if (String(curMin)!==String(defMin) || String(curMax)!==String(defMax)) n++;
                });
                const vbox = $('.filter-section[data-filter="variant"] .checkbox-list');
                if (vbox){
                    const def = vbox.dataset.defSel ?? ''; // '' → hech biri tanlanmagan
                    const cur = [];
                    vbox.querySelectorAll('input[type="checkbox"]:checked').forEach(i=>cur.push(cbVal(i)));
                    const now = cur.sort().join('|');
                    if (now !== def) n++;
                }
                return n;
            }

            function updateTopButtons(){
                // 1) Kategoriya nomi
                const activeCat = $('#category_list .list_item.active');
                const catName   = activeCat ? activeCat.textContent.trim() : 'Все кейсы';
                if (catBtn) catBtn.textContent = catName;

                // 2) “Фильтры ( X )”
                const cnt = countActiveFilters();
                if (filterBtn) filterBtn.innerHTML = `Фильтры <span>( ${cnt} )</span>`;
            }

            // === Paginatsiya ===
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

            // === Swiper ===
            function initSwipers(){
                if (typeof Swiper==='undefined') return;
                $$('.catalogSwiper:not(.js-inited)').forEach(sw=>{
                    sw.classList.add('js-inited');
                    new Swiper(sw,{ slidesPerView:1, loop:true, pagination:{ el: sw.querySelector('.swiper-pagination'), clickable:true } });
                });
            }

            // === AJAX yuklash (mode='replace' | 'append') ===
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

                // "Найдено"
                state.total = data.total || 0;
                if (foundEl) foundEl.textContent = state.total + ' позиций';

                // Kartalar
                if (mode==='append'){
                    const tmp=document.createElement('div');
                    tmp.innerHTML = data.html || '';
                    tmp.querySelectorAll('.catalog_item').forEach(n=>rowEl.appendChild(n));
                } else {
                    rowEl.innerHTML = data.html || '';
                }

                // Facets & defaults
                state.facets = data.facets || null;
                if (state.facets){
                    // UI'ga to'liq diapazonlarni yozib qo'yamiz (shunda reset va count to'g'ri ishlaydi)
                    writeFacetToUI('price', state.facets.price?.min, state.facets.price?.max);
                    writeFacetToUI('len',   state.facets.len?.min,   state.facets.len?.max);
                    writeFacetToUI('wid',   state.facets.wid?.min,   state.facets.wid?.max);
                    writeFacetToUI('hei',   state.facets.hei?.min,   state.facets.hei?.max);
                    writeFacetToUI('wei',   state.facets.wei?.min,   state.facets.wei?.max);
                    // NO-FILTER baseline
                    setDefaultsFromFacets();
                }

                // Pagination
                const effectivePer = Math.max(1, state.perBase + state.extraShown);
                buildPagination(state.total, effectivePer, state.page);

                // “Загрузить ещё”
                const shown = $$('.catalog_row .catalog_item').length;
                if (moreBtn){
                    const left = Math.max(0, state.total - shown);
                    moreBtn.style.display = left > 0 ? '' : 'none';
                    const cap = moreBtn.querySelector('span');
                    if (cap) cap.textContent = left > 0 ? `Загрузить еще (${left})` : 'Загрузить еще';
                }

                updateTopButtons();
                initSwipers();
            }

            // === RESET: hammasini tozalash, ALL PRODUCTS ko'rsatish ===
            function resetAll(){
                // 1) UI ni tozalash
                // Kategoriya
                $$('#category_list .list_item').forEach(x=>x.classList.remove('active'));
                const first = $('#category_list .list_item'); if (first) first.classList.add('active');

                // Qidiruv
                if (searchEl) searchEl.value = '';

                // Variants — hammasini bekor qilish
                $$('.filter-section[data-filter="variant"] .checkbox-list input[type="checkbox"]').forEach(i=>i.checked=false);

                // Rangelarni vaqtincha tozalaymiz (fetchdan keyin facets bilan to'ldiramiz)
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
                    // baseline ham fetchdan keyin facets bilan o'rnatiladi
                    delete box.dataset.defMin;
                    delete box.dataset.defMax;
                });
                const vbox = $('.filter-section[data-filter="variant"] .checkbox-list');
                if (vbox) vbox.dataset.defSel = '';

                // 2) STATE ni tozalash
                state.search     = '';
                state.category   = '';
                state.page       = 1;
                state.extraShown = 0;
                state.price={min:'',max:''}; state.len={min:'',max:''}; state.wid={min:'',max:''}; state.hei={min:'',max:''}; state.wei={min:'',max:''};
                state.variants = [];

                updateTopButtons();
            }

            // === EVENTS ===

            // Kategoriya — DARHOL AJAX
            catList?.addEventListener('click', e=>{
                const li = e.target.closest('.list_item'); if(!li) return;
                $$('#category_list .list_item').forEach(x=>x.classList.remove('active'));
                li.classList.add('active');
                state.category   = li.dataset.slug || '';
                state.page       = 1;
                state.extraShown = 0;
                fetchProducts('replace');
            });

            // Qidiruv — DARHOL AJAX
            searchForm?.addEventListener('submit', e=>{
                e.preventDefault();
                state.search     = searchEl?.value?.trim() || '';
                state.page       = 1;
                state.extraShown = 0;
                fetchProducts('replace');
            });

            // Variantlar — real vaqt rejimi: faqat counter yangilansin (AJAX faqat “Показать”da)
            document.addEventListener('change', e=>{
                const sec = e.target.closest('.filter-section[data-filter="variant"]'); if(!sec) return;
                readRangesFromUI();
                updateTopButtons();
            });

            // Range inputlar — real vaqt rejimi: faqat counter yangilansin (AJAX faqat “Показать”da)
            const onFilterInput = ()=>{ readRangesFromUI(); updateTopButtons(); };
            $$('.filter-section .range-inputs .input-min, .filter-section .range-inputs .input-max, .filter-section .range-slider .range-min, .filter-section .range-slider .range-max')
                .forEach(el=>{
                    el.addEventListener('input', onFilterInput);
                    el.addEventListener('change', onFilterInput);
                });

            // “Показать” — SHU PAYTDA AJAX
            applyBtn?.addEventListener('click', e=>{
                e.preventDefault();
                readRangesFromUI();
                state.page       = 1;
                state.extraShown = 0;
                fetchProducts('replace');
            });

            // “Сбросить фильтры” — ALL products
            resetBtn?.addEventListener('click', e=>{
                e.preventDefault();
                resetAll();
                fetchProducts('replace'); // facets qaytgach UI to'liq diapazonga o'rnatiladi
            });

            // “Показать по”
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

            // Pagination
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

            // “Загрузить ещё”
            moreBtn?.addEventListener('click', ()=>{
                state.extraShown += STEP;
                fetchProducts('append');
            });

            // Dastlabki yuklash — toza holat (ALL products)
            document.addEventListener('DOMContentLoaded', ()=>{
                resetAll();              // UI + state toza
                fetchProducts('replace'); // facets keladi → UI diapazon + baseline yangilanadi
            });
        })();
    </script>







<?php
get_footer();
