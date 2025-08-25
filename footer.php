<?php wp_footer(); ?>

<!-- footer -->
<footer  id="contact">
    <div class="container">
        <div class="footer_row">
            <div class="top">
                <div class="logo">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/footer_logo.svg" alt="">
                    </a>
                    <div class="logo_text">
                        Российское производство ударопрочных кейсов для критически важного оборудования
                    </div>
                </div>

                <div class="nav_row">
                    <div class="nav_item">
                        <div class="title">
                            Каталог
                        </div>
                        <a href="catalog.html">Мини кейсы</a>
                        <a href="catalog.html">Средние кейсы</a>
                        <a href="catalog.html">Большие кейсы</a>
                        <a href="catalog.html">Длинные кейсы</a>
                        <a href="catalog.html">Кейсы для ноутбуков</a>
                        <a href="catalog.html">Контейнеры</a>
                    </div>

                    <div class="nav_item">
                        <div class="title">
                            Разделы
                        </div>
                        <a href="<?php echo home_url(); ?>">Главная</a>
                        <a href="production.html">Информация</a>
                        <a href="tool.html">Производство</a>
                        <a href="contact.html">Контакты</a>
                        <a href="catalog.html">Кейсы для ноутбуков</a>
                        <a href="catalog.html">Контейнеры</a>
                    </div>

                    <div class="nav_item">
                        <div class="title">
                            Контакты
                        </div>
                        <span> Москва ул. Плеханова д.7, эт. 1, пом. I ком 25</span>
                        <div class="nav_item_block">
                            <a class="link"  href="tel:74950236793">+7 495 023 67 93</a>
                            <span>Пн-Пт: с 10:00 до 18:00</span>
                        </div>
                        <a class="link" href="mailto:info@galeoncase.ru">info@galeoncase.ru</a>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="info">
                    <div class="rights text">Все права защищены 2025© </div>
                    <a href="<?php echo get_template_directory_uri() ?>/assets/documents/Privacy_Policy_Extended.pdf" target="_blank" class="politics text">Политика конфедициальности</a>
                </div>
                <a href="#header" class="up_link">Наверх</a>
            </div>
        </div>
    </div>
</footer>

</div>

<!-- Cookie Modal -->
<div id="cookieModal">
    <div class="container">
        <div class="blog">
            <div class="title">Мы используем  <span>cookie-файлы</span> для улучшения работы сайта </div>
            <p>Используя этот сайт, вы даете согласие на обработку  <a href="#">персональных данных</a></p>

            <div class="accept_wrapper">
                <a id="acceptCookies" class="accept_btn">Согласиться</a>
            </div>
        </div>
    </div>
</div>

<!-- application modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modalBox">
        <button class="close-btn" id="closeModal"><img src="<?php echo get_template_directory_uri() ?>/assets/images/modal_close_icon.svg" alt=""></button>

        <div class="section_title">Оставить заявку</div>
        <div class="sub_title">
            Заполните форму, мы свяжемся и проконсультируем Вас в кратчайшие сроки
        </div>
        <form action="">
            <input required type="text" placeholder="Ваше Имя*">
            <div class="input_block">
                <input required type="tel" id="phone1" placeholder="+7 999 999 99 99*">
            </div>
            <textarea name="" id="" placeholder="Комментарий"></textarea>
            <button>Оставить заявку</button>

            <!-- custom confirm -->
            <div class="confirm">
                <label class="custom-checkbox">
                    <input required checked type="checkbox" id="confirm1">
                    <span class="checkmark"></span>
                </label>
                <label for="confirm1" class="text">Нажимая на кнопку «Отправить», вы даете согласие на обработку своих <a  href="<?php echo get_template_directory_uri() ?>/assets/documents/Personal_Data_Processing_Extended.pdf" target="_blank">персональных данных</a></label>
            </div>
        </form>
    </div>
</div>

<!-- search modal -->
<div class="modal-overlay1" id="modalOverlay1">
    <div class="modal" id="modalBox1">
        <button class="close-btn" id="closeModal1"><img src="<?php echo get_template_directory_uri() ?>/assets/images/modal_close_icon.svg" alt=""></button>

        <form class="header_search">
            <button  class="search_link"></button>
            <input type="text" name="search" placeholder="Поиск по сайту...">
        </form>
    </div>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
<!-- Juqery -->
<script src="<?php echo get_template_directory_uri() ?>/assets/libs/jquery-3.6.0.min.js"></script>
<!-- Load Inputmask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"></script>
<!-- yandex JS -->
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<!-- swiper -->
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<!-- sweet alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- JS -->
<script src="<?php echo get_template_directory_uri() ?>/assets/js/scripts.js"></script>
</body>

</html>