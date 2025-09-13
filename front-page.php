<?php
get_header();

$shop_url = wc_get_page_permalink('shop');

// Banner
$banner_hidden = get_field('banner_hidden');
$banner_title = get_field('banner_title'); // Editor
$banner_text = get_field('banner_text');

// Capabilites
$capabilities_hidden = get_field('capabilities_hidden');
$capabilities_title = get_field('capabilities_title');
$capabilities_url = get_post_type_archive_link('tool');
$production_slide_title = get_field('production_slide_title');
$production_slide_text = get_field('production_slide_text');
$production_slide_img = get_field('production_slide_img');

// Catalogs
$catalogs_hidden = get_field('catalogs_hidden');
$catalogs_title = get_field('catalogs_title');

// Advantages
$advantages_hidden = get_field('advantages_hidden');
$advantages_title = get_field('advantages_title');
$advantages_text = get_field('advantages_text'); // Editor
$advantages = get_field('advantages');

// Contact
$contact_hidden = get_field('contact_hidden');
?>

<?php if ($banner_hidden !== "Да"): ?>
    <!-- hero -->
    <section class="hero">
        <div class="container">
            <div class="main_title">
                <?php echo apply_filters('the_content', $banner_title); ?>
            </div>

            <div class="sub_title">
                <?php echo esc_html($banner_text); ?>
            </div>

            <a href="<?php echo esc_url($shop_url); ?>" class="button">
                Смотреть каталог
            </a>

            <div class="information">
                <!-- info item -->
                <div class="info_item">
                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/hero_info1.svg" alt="">
                    <div class="text">Класс защиты IP65 и выше</div>
                </div>

                <!-- info item -->
                <div class="info_item">
                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/hero_info2.svg" alt="">
                    <div class="text">Мягкий поропласт</div>
                </div>

                <!-- info item -->
                <div class="info_item">
                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/hero_info3.svg" alt="">
                    <div class="text">Производятся в России</div>
                </div>
            </div>

            <a class="hero_link" href="#">

                <img src="<?php echo get_template_directory_uri() ?>/assets/images/straight_arrow.svg" alt="">
                <span>Листайте вниз</span>
            </a>


            <!-- Dots with tooltips -->
            <div class="dot first">
                <div class="tooltip">Мягкий поропласт для <br> надежной фиксации груза</div>
            </div>

            <div class="dot second">
                <div class="tooltip">Производятся в России</div>
            </div>

            <div class="dot third">
                <div class="tooltip">Класс защиты IP67 и выше</div>
            </div>

            <div class="dot fourth">
                <div class="tooltip">Созданы из высокопрочных полимеров</div>
            </div>
        </div>
    </section>
<?php endif; ?>


<?php if ($capabilities_hidden !== "Да"): ?>
    <!-- production -->
    <section class="production">
        <div class="container">
            <!-- Swiper Container -->
            <div class="swiper preproduction">
                <div class="section_title">
                    <?php echo esc_html($capabilities_title); ?>
                </div>
                <div class="swiper-wrapper">
                    <!-- slide -->
                    <a href="/production" class="swiper-slide">
                        <div class="top">
                            <?php if (is_array($production_slide_img)) : ?>
                                <img src="<?php echo $production_slide_img['url'] ?>"
                                     alt="<?php echo $production_slide_img['alt'] ?>">
                            <?php else: ?>
                                <img src="<?php echo get_template_directory_uri() ?>/assets/images/production1.png"
                                     alt="">
                            <?php endif; ?>
                        </div>
                        <div class="bottom">
                            <div class="title">
                                <?php echo esc_html($production_slide_title); ?>
                            </div>
                            <div class="info">
                                <?php echo esc_html($production_slide_text); ?>
                            </div>
                        </div>
                    </a>

                    <?php
                    $tools = new WP_Query([
                        'post_type' => 'tool',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'orderby' => 'date',
                        'order' => 'ASC',
                    ]);

                    if ($tools->have_posts()):
                        while ($tools->have_posts()): $tools->the_post();
                            $pid = get_the_ID();

                            $title = get_field('slide_title');
                            if (!$title) $title = get_the_title();

                            $info = get_field('slide_text');
                            if (!$info) $info = get_field('description');

                            $img = get_field('slide_img');
                            $img_url = '';
                            if (is_array($img)) {
                                $img_url = isset($img['ID']) ? wp_get_attachment_image_url($img['ID'], 'large') : ($img['url'] ?? '');
                            } elseif ($img) {
                                $img_url = wp_get_attachment_image_url((int)$img, 'large');
                            }
                            if (!$img_url) $img_url = get_the_post_thumbnail_url($pid, 'large');

                            $href = $capabilities_url ? $capabilities_url . '#tool' . $pid : '#';
                            ?>
                            <a href="<?php echo esc_url($href); ?>" class="swiper-slide">
                                <div class="top">
                                    <?php if ($img_url): ?>
                                        <img src="<?php echo esc_url($img_url); ?>"
                                             alt="<?php echo esc_attr($title); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="bottom">
                                    <div class="title"><?php echo esc_html($title); ?></div>
                                    <?php if ($info): ?>
                                        <div class="info"><?php echo wp_kses_post($info); ?></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>


                </div>

                <div class="navigation">
                    <!-- Navigation buttons -->
                    <div class=" swiper-button-prev navigation_item">
                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/navigation_item_arrow.svg"
                             alt="">
                    </div>
                    <div class=" swiper-button-next navigation_item">
                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/navigation_item_arrow.svg"
                             alt="">
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($catalogs_hidden !== "Да"): ?>
    <!-- products -->
    <section class="products" id="products">
        <div class="container">
            <div class="top">
                <div class="section_title"><?php echo esc_html($catalogs_title); ?></div>
                <a href="<?php echo esc_url($shop_url); ?>" class="download">
                    Смотреть каталог
                </a>
            </div>

            <div class="product_row">
                <?php
                if (class_exists('WooCommerce')) {
                    $parents = get_terms([
                        'taxonomy' => 'product_cat',
                        'parent' => 0,
                        'hide_empty' => false,
                        'orderby' => 'menu_order',
                        'order' => 'ASC',
                    ]);

                    if (!is_wp_error($parents) && !empty($parents)) {
                        foreach ($parents as $cat) {

                            if ($cat->slug === 'uncategorized' || strtolower($cat->name) === 'uncategorized') {
                                continue;
                            }

                            $thumb_id = (int)get_term_meta($cat->term_id, 'thumbnail_id', true);
                            $img_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
                            $bg_style = $img_url ? ' style="background-image:url(' . esc_url($img_url) . ')"' : '';

                            // Parent linki
                            $cat_link = get_term_link($cat);
                            if (is_wp_error($cat_link)) {
                                $cat_link = '';
                            }

                            $children = get_terms([
                                'taxonomy' => 'product_cat',
                                'parent' => $cat->term_id,
                                'hide_empty' => false,
                                'orderby' => 'menu_order',
                                'order' => 'ASC',
                            ]);

                            if (!is_wp_error($children) && !empty($children)) {
                                $children = array_values(array_filter($children, function ($t) {
                                    return $t->slug !== 'uncategorized' && strtolower($t->name) !== 'uncategorized';
                                }));
                            }

                            if (!empty($children)) { ?>
                                <!-- sub-kategoriyalari bor -->
                                <div class="product_item"<?php echo $bg_style; ?>>
                                    <div>
                                        <div class="title"><?php echo esc_html($cat->name); ?></div>
                                        <div class="info_row">
                                            <?php foreach ($children as $child):
                                                $child_link = get_term_link($child);
                                                if (is_wp_error($child_link)) {
                                                    $child_link = '';
                                                }
                                                ?>
                                                <?php if ($child_link) : ?>
                                                <a href="<?php echo esc_url($child_link); ?>"
                                                   class="info_item"><?php echo esc_html($child->name); ?></a>
                                            <?php else : ?>
                                                <span class="info_item"><?php echo esc_html($child->name); ?></span>
                                            <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <a href="<?php echo esc_url($cat_link); ?>" class="link">
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/link_arrow.svg'); ?>"
                                             alt="">
                                    </a>
                                </div>
                            <?php } else { ?>
                                <a href="<?php echo esc_url($cat_link); ?>"
                                   class="product_item"<?php echo $bg_style; ?>>
                                    <div class="title"><?php echo esc_html($cat->name); ?></div>
                                    <div class="link">
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/link_arrow.svg'); ?>"
                                             alt="">
                                    </div>
                                </a>
                            <?php }
                        }
                    }

                } else {
                    echo '<!-- WooCommerce off: product_cat mavjud emas -->';
                }
                ?>


            </div>

        </div>
    </section>
<?php endif; ?>

<?php if ($advantages_hidden !== "Да"): ?>
    <!-- advantages -->
    <div class="advantages" id="information">
        <div class="container">
            <a href="/production" class="section_title">
                <?php echo esc_html($advantages_title); ?>
            </a>
            <div class="sub_title">
                <?php echo apply_filters('the_content', $advantages_text); ?>
            </div>

            <div class="button open-modal-btn">
                Узнать подробнее
            </div>

            <div class="advantages_row">
                <?php foreach ($advantages as $advantage): ?>
                    <!-- advantage item -->
                    <a href="/production" class="advantage_item">
                        <img src="<?php echo esc_url($advantage['icon']['url']); ?>" alt="">
                        <div class="info_row">
                            <div class="title"><?php echo esc_html($advantage['title']); ?></div>
                            <div class="info">
                                <?php echo esc_html($advantage['text']); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>


        </div>
    </div>
<?php endif; ?>

    <!-- application -->
    <section class="application">
        <div class="container">
            <div class="application_row">
                <div class="left">
                    <!-- <img src="<?php echo get_template_directory_uri() ?>/assets/images/application_image.svg" alt=""> -->
                </div>
                <div class="right">
                    <div class="section_title">Свяжитесь с нами</div>
                    <div class="sub_title">
                        Заполните форму, мы свяжемся и проконсультируем Вас в кратчайшие сроки
                    </div>
                    <?php
                    echo do_shortcode('[contact-form-7 id="5c24369" title="Оставить заявку"]');
                    ?>
                </div>
            </div>
        </div>
    </section>


<?php
get_footer();