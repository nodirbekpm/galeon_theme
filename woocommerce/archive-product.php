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
                            <li class="list_item active">Все кейсы</li>
                            <li class="list_item">Мини кейсы</li>
                            <li class="list_item">Средние кейсы</li>
                            <li class="list_item">Большие кейсы</li>
                            <li class="list_item">Длинные кейсы</li>
                            <li class="list_item">Кейсы для ноутбуков</li>
                            <li class="list_item">Контейнеры</li>
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
                                <label><input type="checkbox" checked> Пустой</label>
                                <label><input type="checkbox"> С пролпастом</label>
                                <label><input type="checkbox"> С делителями</label>
                                <label><input type="checkbox"> С колесами</label>
                                <label><input type="checkbox"> С органайзером</label>
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
                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_1.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_1.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_1.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_1.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_1.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_1.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_3.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_3.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_3.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_4.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_4.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_4.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_5.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_5.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_5.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_6.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_6.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_6.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_7.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_7.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_7.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_8.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_8.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_8.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- catalog item -->
                    <div class="catalog_item">
                        <div class="top_block">
                            <div class="like_icon"></div>

                            <div class="swiper catalogSwiper">
                                <a href="product.html" class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_9.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_9.png" alt="case img">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/product_item_9.png" alt="case img">
                                    </div>
                                </a>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>

                        <div class="info_block">
                            <a href="product.html" class="title">Галеон 0021 пустой</a>
                            <a href="product.html" class="dimensions">
                                <span>Внутренние габариты:</span><br>
                                330 мм/234 мм/152 мм
                            </a>

                            <a href="product.html" class="dimensions">
                                <span>Цена:</span> <br>
                                24 000₽
                            </a>

                            <div class="cart_controls">
                                <div class="qty_btn minus">-</div>
                                <input type="number" max="1000" min="1" value="1" class="qty">
                                <div class="qty_btn plus">+</div>

                                <div class="cart_btn">
                                    <span>В корзину</span>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/cart_icon.svg" alt="">
                                </div>
                            </div>
                        </div>


                    </div>
                </div>



                <div class="pages">

                    <!-- bottom_info -->
                    <div class="more_button">
                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/show_more.svg" alt="">
                        <span>Загрузить еще</span>
                    </div>

                    <div class="pagination">
                        <span class="pagination_item active">1</span>
                        <span class="pagination_item">2</span>
                        <span class="pagination_item">3</span>
                        <span class="pagination_item">...</span>
                        <span class="pagination_item">11</span>
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


<?php
get_footer();
