<?php
/**
 * Template Name: Контакты
 */
get_header();

$title = get_field('title');
$text = get_field('text');

$office_address = get_field('office_address', 'option');
$warehouse_address = get_field('warehouse_address', 'option');
?>


    <!-- contact -->
    <section class="contact">
        <div class="container">
            <?php if ( function_exists('yoast_breadcrumb') ) {
                yoast_breadcrumb('<div class="breadcrumb">','</div>');
            } ?>

            <div class="top">
                <div class="info">
                    <div class="section_title"><?php if(!empty($title)): ?><?php echo esc_html($title); ?><?php else: ?><?= get_the_title(); ?><?php endif; ?></div>
                    <div class="sub_title"><?php echo esc_html($text); ?></div>
                </div>
                <a class="button open-modal-btn">
                    Заказать звонок
                </a>
            </div>

            <div class="maps">
                <!-- Office Map -->
                <div class="map-block">
                    <div id="map1" class="map"></div>
                    <div class="info">
                        <div class="info_title">Адрес офиса магазина</div>
                        <div class="address"><?php echo esc_html($office_address['text']) ?></div>
                        <a href="tel:<?php echo esc_html($office_address['phone']['phone']) ?>"><?php echo esc_html($office_address['phone']['title']) ?></a>
                        <a href="mailto:<?php echo esc_html($office_address['email']) ?>"><?php echo esc_html($office_address['email']) ?></a>
                    </div>
                </div>

                <!-- Warehouse Map -->
                <div class="map-block">
                    <div id="map2" class="map"></div>
                    <div class="info">
                        <div class="info_title">Адрес склада</div>
                        <div class="address"><?php echo esc_html($warehouse_address['text']) ?></div>
                        <a href="tel:<?php echo esc_html($warehouse_address['phone']['phone']) ?>"><?php echo esc_html($warehouse_address['phone']['title']) ?></a>
                        <a href="mailto:<?php echo esc_html($warehouse_address['email']) ?>"><?php echo esc_html($warehouse_address['email']) ?></a>
                    </div>
                </div>
            </div>

        </div>
    </section>


<?php
get_footer();