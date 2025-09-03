<?php
/*
 * Template name: Дополнительные аксессуары
 * */

get_header();

//$title = get_field('title'); // Editor
//$text = get_field('text');

?>

    <!-- hero -->
    <section class="hero page tool">
        <div class="container">

            <?php if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<div class="breadcrumb">', '</div>');
            } ?>

            <div class="main_title">
                Возможности <br>
                нашего производства
            </div>

            <div class="sub_title">
                Наши мощности также позволяют изготавливать: ложементы, интерфейсные панели, приборных панели и другие
            </div>

            <div class="button open-modal-btn">
                Оставить заявку
            </div>
        </div>
    </section>

    <!-- tool -->
    <section class="tool">
        <div class="container">
            <div class="tool_row">
                <?php
                $tools = new WP_Query([
                    'post_type'      => 'tool',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'date',
                    'order'          => 'ASC',
                ]);

                if ( $tools->have_posts() ) :
                    while ( $tools->have_posts() ) : $tools->the_post();

                        $desc       = get_field('description') ?: '';
                        $img_url    = get_the_post_thumbnail_url(get_the_ID(), 'large');
                        $facilities = get_field('facilities'); // repeater: title, text
                        ?>
                        <!-- tool item -->
                        <div class="tool_item" id="tool<?php echo esc_attr(get_the_ID()); ?>">
                            <div class="top">
                                <div class="left">
                                    <div class="title"><?php the_title(); ?></div>

                                    <?php if ($desc): ?>
                                        <div class="description"><?php echo wp_kses_post($desc); ?></div>
                                    <?php endif; ?>

                                    <div class="button_container">
                                        <div class="button open-modal-btn">Сделать заказ</div>
                                    </div>
                                </div>

                                <div class="right">
                                    <?php if ($img_url): ?>
                                        <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($facilities) && is_array($facilities)): ?>
                                <div class="bottom">
                                    <?php foreach ($facilities as $row):
                                        $lt   = isset($row['title']) ? $row['title'] : '';
                                        $info = isset($row['text'])  ? $row['text']  : '';
                                        ?>
                                        <div class="info_item">
                                            <?php if ($lt):   ?><div class="little_title"><?php echo esc_html($lt);   ?></div><?php endif; ?>
                                            <?php if ($info): ?><div class="info"><?php echo wp_kses_post($info); ?></div><?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p>Ничего не найдено.</p>';
                endif;
                ?>
            </div>
        </div>
    </section>


<?php
get_footer();
