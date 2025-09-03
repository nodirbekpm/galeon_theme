<?php
/**
 * === CPT: Дополнительные аксессуары (slug: tool) ===
 */
add_action('init', function () {
    // Post type
    register_post_type('tool', [
        'labels' => [
            'name'               => 'Аксессуары',
            'singular_name'      => 'Аксессуар',
            'menu_name'          => 'Аксессуары',
            'archives'           => 'Дополнительные аксессуары',
            'add_new'            => 'Добавить новый',
            'add_new_item'       => 'Добавить аксессуар',
            'edit_item'          => 'Редактировать аксессуар',
            'new_item'           => 'Новый аксессуар',
            'view_item'          => 'Просмотр аксессуара',
            'search_items'       => 'Искать аксессуар',
            'not_found'          => 'Не найдено',
            'not_found_in_trash' => 'Не найдено в корзине',
            'all_items'          => 'Все аксессуары',
        ],
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,                 // top-level menyu
        'menu_position'      => 22,                   // Pages(20) va Comments(25) orasida
        'menu_icon'          => 'dashicons-hammer',   // Tools bilan adashmasin :)
        'supports'           => ['title','editor','thumbnail','excerpt','revisions'],
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'tool', 'with_front' => false],
        'publicly_queryable' => true,
        'exclude_from_search'=> false,
        'hierarchical'       => false,
        'show_in_rest'       => true,                 // Gutenberg + REST
        // MUHIM: custom caps yo‘q → admin menyuda ko‘rinadi
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
    ]);

    // Taxonomy: tool_cat (категории аксессуаров)
    register_taxonomy('tool_cat', ['tool'], [
        'labels' => [
            'name'          => 'Категории аксессуаров',
            'singular_name' => 'Категория аксессуаров',
            'menu_name'     => 'Категории',
            'search_items'  => 'Искать категории',
            'all_items'     => 'Все категории',
            'edit_item'     => 'Редактировать категорию',
            'update_item'   => 'Обновить категорию',
            'add_new_item'  => 'Добавить категорию',
            'new_item_name' => 'Новая категория',
            'parent_item'   => 'Родительская категория',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'tool-category', 'with_front' => false],
        'show_in_rest'      => true,
    ]);
}, 10);

// Bir marta permalinklarni yangilash
add_action('admin_init', function(){
    if (!get_option('galeon_tool_cpt_flushed')) {
        flush_rewrite_rules(false);
        update_option('galeon_tool_cpt_flushed', 1);
    }
});

add_filter('manage_tool_posts_columns', function ($columns) {
    $new = [];

    // Avval checkbox ustuni
    if (isset($columns['cb'])) {
        $new['cb'] = $columns['cb'];
        unset($columns['cb']);
    }

    // Keyin bizning thumbnail ustuni
    $new['tool_thumb'] = 'Фото';

    // Qolgan ustunlarni o‘z holicha qo‘shamiz (Title, Date va b.)
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
    }

    return $new;
});

add_action('manage_tool_posts_custom_column', function ($column, $post_id) {
    if ($column === 'tool_thumb') {
        $thumb = get_the_post_thumbnail(
            $post_id,
            [60, 60],
            ['style' => 'width:60px;height:60px;object-fit:cover;border-radius:4px;display:block;margin:0 auto;']
        );

        if ($thumb) {
            // Rasmni edit sahifasiga link qilib beramiz
            $edit_link = get_edit_post_link($post_id);
            echo '<a href="' . esc_url($edit_link) . '">' . $thumb . '</a>';
        } else {
            // Rasm bo‘lmasa — ikonka
            echo '<span class="dashicons dashicons-format-image" style="font-size:20px;opacity:.5;"></span>';
        }
    }
}, 10, 2);

// Ustun kengligi va hizmini ozgina sozlaymiz (faqat tool listing sahifasida)
add_action('admin_head-edit.php', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($screen && $screen->post_type === 'tool') {
        echo '<style>
            .column-tool_thumb{width:80px;text-align:center;}
            .column-tool_thumb img{max-width:60px;height:60px;object-fit:cover;border-radius:4px;}
        </style>';
    }
});