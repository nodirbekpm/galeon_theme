<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- montserrat font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&display=swap" rel="stylesheet">
    <!-- swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
          integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
          integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Font -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/assets/fonts/font.css">

    <style>
        .added_to_cart.wc-forward {
            display: none !important;
        }

        .wpcf7-response-output {
            display: none;
        }

        .open-modal-btn {
            cursor: pointer;
        }

    </style>

    <!-- Css -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/assets/css/main.css">
    <title>GALEON</title>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="wrapper">

    <?php
    $shop_url = wc_get_page_permalink('shop');
    $capabilities_url = get_post_type_archive_link('tool');

    $office_address = get_field('office_address', 'option');
    $working_hours = get_field('working_hours', 'option');
    $header_logo = get_field('header_logo', 'option');

    ?>
    <!-- header -->
    <header class="header" id="header">
        <!-- header top -->
        <div class="header_top">
            <div class="container">
                <div class="top_row">
                    <div class="left">
                        <div class="phone icon_block">
                            <img src="<?php echo get_template_directory_uri() ?>/assets/images/phone_icon.svg" alt=""
                                 class="phone_icon">
                            <a href="tel:<?php echo esc_html($office_address['phone']['phone']) ?>"><?php echo esc_html($office_address['phone']['title']) ?></a>
                        </div>
                        <div class="work_hours">
                            <?php esc_html($working_hours) ?>
                        </div>
                        <div class="address icon_block">
                            <img src="<?php echo get_template_directory_uri() ?>/assets/images/location_icon.svg" alt=""
                                 class="phone_icon">
                            <a href="/contact"><?php echo esc_html($office_address['text']) ?></a>
                        </div>
                    </div>

                    <div class="right">
                        <a class="call_button open-modal-btn" href="#">
                            <span>Заказать звонок</span>
                            <img src="<?php echo get_template_directory_uri() ?>/assets/images/link_arrow.svg" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- header navbar -->
        <div class="header_navbar">
            <div class="container">
                <div class="header_block">
                    <div class="header_logo">
                        <a href="<?php echo home_url(); ?>">
                            <img src="<?php echo esc_url($header_logo['url']) ?>" alt="">
                        </a>
                    </div>

                    <!-- hamburger_mobile -->
                    <div class="hamburger_menu" id="line_row" onclick="toggleNav()">
                        <div class="line_row">
                            <div class="line"></div>
                            <div class="line"></div>
                            <div class="line"></div>
                        </div>
                        <span>Меню</span>
                    </div>
                    <style>
                        .search_suggestions.open {
                            display: block;
                        }
                    </style>
                    <form class="header_search">
                        <button class="search_link" type="button"></button>
                        <input type="text" name="search" placeholder="Поиск по сайту...">
                        <!-- Suggestion dropdown -->
                        <div class="search_suggestions">
                            <ul>

                            </ul>
                        </div>
                    </form>

                    <div class="header_icons">
                        <!--                        <a href="-->
                        <?php //echo esc_url( galeon_myaccount_url('register') ); ?><!--" class="header_user header_icon_item">-->
                        <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/')); ?>"
                           class="header_user header_icon_item">
                            <?php else : ?>
                            <a href="#" class="header_user header_icon_item open-modal-btn-profile" data-modal="modal1">
                                <?php endif; ?>


                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30"
                                     fill="none">
                                    <path d="M19.6246 15.875C22.8746 13.375 23.4996 8.625 20.8746 5.375C18.2496 2.125 13.6246 1.5 10.3746 4.125C7.12461 6.75 6.49961 11.375 9.12461 14.625C9.49961 15.125 9.87461 15.5 10.3746 15.875C6.12461 17.625 3.12461 21.5 2.62461 26.125C2.49961 26.875 2.99961 27.375 3.74961 27.5C4.49961 27.625 4.99961 27.125 5.12461 26.375C5.74961 20.875 10.7496 16.875 16.1246 17.5C20.7496 18 24.3746 21.625 24.9996 26.375C25.1246 27 25.6246 27.5 26.2496 27.5H26.3746C26.9996 27.375 27.4996 26.75 27.4996 26.125C26.8746 21.5 23.8746 17.625 19.6246 15.875ZM14.9996 15C12.2496 15 9.99961 12.75 9.99961 10C9.99961 7.25 12.2496 5 14.9996 5C17.7496 5 19.9996 7.25 19.9996 10C19.9996 12.75 17.7496 15 14.9996 15Z"
                                          fill="#131A23"/>
                                </svg>
                            </a>
                            <a href="<?php echo esc_url(get_permalink(get_page_by_path('wishlist'))); ?>"
                               class="header_like header_icon_item">
                                <span class="wishlist-count header_counter"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30"
                                     fill="none">
                                    <path d="M25.2497 5.74999C22.4997 2.99999 18.1247 2.74999 14.9997 4.99999C11.4997 2.37499 6.62465 3.12499 3.99965 6.62499C1.62465 9.74999 1.99965 14.125 4.74965 16.875L13.9997 26.25C14.4997 26.75 15.2497 26.75 15.7497 26.25L24.9997 17C28.2497 13.75 28.2497 8.87499 25.2497 5.74999ZM23.4997 15.125L14.9997 23.5L6.49965 15.125C4.37465 13 4.37465 9.62499 6.49965 7.49999C7.49965 6.49999 8.87465 5.99999 10.2497 5.99999C11.6247 5.99999 12.9997 6.62499 13.9997 7.62499C14.4997 8.12499 15.2497 8.12499 15.7497 7.62499C17.9997 5.74999 21.3747 5.87499 23.2497 8.12499C25.1247 10.125 25.1247 13.125 23.4997 15.125Z"
                                          fill="#131A23"/>
                                </svg>
                            </a>
                            <a href="<?php echo esc_url(galeon_cart_url()); ?>" class="header_basket header_icon_item">
                                <?php
                                $count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
                                ?>
                                <span class="header_counter basket_counter<?php echo $count ? ' active' : ''; ?>">
                              <?php echo (int)$count; ?>
                            </span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30"
                                     fill="none">
                                    <path d="M29.9348 8.52716L26.4457 19.5163C26.1848 20.2989 25.5 20.8206 24.6848 20.8206H11.2174C10.4348 20.8206 9.68478 20.3315 9.42391 19.6141L4.27174 5.82064H1.30435C0.586957 5.82064 0 5.23368 0 4.51629C0 3.7989 0.586957 3.21194 1.30435 3.21194H5.18478C5.73913 3.21194 6.22826 3.57064 6.42391 4.09238L11.7391 18.2119H24.1304L26.9022 9.4076H11.5435C10.8261 9.4076 10.2391 8.82064 10.2391 8.10325C10.2391 7.38586 10.8261 6.7989 11.5435 6.7989H28.6957C29.1196 6.7989 29.5109 7.02716 29.7391 7.35325C30 7.67934 30.0652 8.13586 29.9348 8.52716ZM11.8696 22.5489C11.3152 22.5489 10.7609 22.7772 10.3696 23.1685C9.97826 23.5598 9.75 24.1141 9.75 24.6685C9.75 25.2228 9.97826 25.7772 10.3696 26.1685C10.7609 26.5598 11.3152 26.788 11.8696 26.788C12.4239 26.788 12.9783 26.5598 13.3696 26.1685C13.7609 25.7772 13.9891 25.2228 13.9891 24.6685C13.9891 24.1141 13.7609 23.5598 13.3696 23.1685C12.9783 22.7772 12.4239 22.5489 11.8696 22.5489ZM23.5761 22.5489C23.0217 22.5489 22.4674 22.7772 22.0761 23.1685C21.6848 23.5598 21.4565 24.1141 21.4565 24.6685C21.4565 25.2228 21.6848 25.7772 22.0761 26.1685C22.4674 26.5598 23.0217 26.788 23.5761 26.788C24.1304 26.788 24.6848 26.5598 25.0761 26.1685C25.4674 25.7772 25.6957 25.2228 25.6957 24.6685C25.6957 24.1141 25.4674 23.5598 25.0761 23.1685C24.6848 22.7772 24.1304 22.5489 23.5761 22.5489Z"
                                          fill="#131A23"/>
                                </svg>
                            </a>
                            <div class="open-search-modal-btn header_search_icon">
                                <img src="<?php echo get_template_directory_uri() ?>/assets/images/header_search_icon.svg"
                                     alt="">
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- header navbar menu(hidden) -->
        <div class="header_menu" id="navbar">
            <div class="container">
                <div class="menu_row">
                    <div class="row_item">
                        <div class="menu_title">Разделы</div>
                        <nav>
                            <a class="nav_item" href="<?php echo home_url(); ?>">Главная</a>
                            <a class="nav_item" href="/production">Информация</a>

                            <div class="dropdown catalog_dropdown">
                                <a class="nav_item  dropdown-toggle" href="#">Каталог</a>
                                <div class="dropdown-menu ">
                                    <a href="<?php echo esc_url($shop_url); ?>">Все кейсы</a>
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
                                                    <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat->name); ?></a>

                                                    <ul>
                                                        <?php foreach ($children as $child):
                                                            $child_link = get_term_link($child);
                                                            if (is_wp_error($child_link)) {
                                                                $child_link = '';
                                                            }
                                                            ?>
                                                            <li>
                                                                <a href="<?php echo esc_url($child_link); ?>"><?php echo esc_html($child->name); ?></a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php } else { ?>
                                                    <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat->name); ?></a>
                                                <?php }
                                            }
                                        }

                                    } else {
                                        echo '<!-- WooCommerce off: product_cat mavjud emas -->';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="nav_item active dropdown-toggle" href="#">Производство</a>
                                <div class="dropdown-menu active">
                                    <a href="/production">
                                        Кейсы и контейнеры
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
                                            <a href="<?php echo esc_url($href); ?>">
                                                <?php echo esc_html($title); ?>
                                            </a>
                                        <?php
                                        endwhile;
                                        wp_reset_postdata();
                                    endif;
                                    ?>
                                </div>
                            </div>
                            <a class="nav_item" href="/contact">Контакты</a>
                        </nav>

                        <div class="left">
                            <div class="phone icon_block">
                                <img src="<?php echo get_template_directory_uri() ?>/assets/images/phone_icon_menu.svg"
                                     alt="" class="phone_icon">
                                <a href="tel:<?php echo esc_html($office_address['phone']['phone']) ?>"><?php echo esc_html($office_address['phone']['title']) ?></a>
                            </div>
                            <div class="work_hours">
                                <?php echo esc_html($working_hours) ?>
                            </div>
                            <div class="address icon_block">
                                <img src="<?php echo get_template_directory_uri() ?>/assets/images/location_icon_menu.svg"
                                     alt="" class="phone_icon">
                                <a href="/contact"><?php echo esc_html($office_address['text']) ?></a>
                            </div>
                        </div>
                    </div>

                    <div class="row_item products">

                        <div class="top">
                            <div class="menu_title">Каталог</div>
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
                                        $bg_style = '';
                                        if ($img_url) {
                                            $size = @getimagesize($img_url); // uploads ichida bo'lsa odatda ishlaydi
                                            if ($size && !empty($size[0])) {
                                                $w60 = (int)round($size[0] * 0.6);
                                                $bg_style = ' style="background-image:url(' . esc_url($img_url) . ');background-size:' . $w60 . 'px auto;background-position:right 30px center;background-repeat:no-repeat;"';
                                            }
                                        }
//                                        $bg_style = $img_url ? ' style="background-size: 60% auto; background-image:url(' . esc_url($img_url) . ')"' : '';

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
                                                            <a href="<?php echo esc_url($child_link); ?>"
                                                               class="info_item">
                                                                <?php echo esc_html($child->name); ?>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <a href="<?php echo esc_url($shop_url); ?>" class="link">
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
                </div>
            </div>
        </div>
    </header>

