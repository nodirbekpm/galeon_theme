<?php
/**
 * Template Name: Производство
 */
get_header();

// Banner
$banner_hidden = get_field('banner_hidden');
$banner_title = get_field('banner_title'); // Editor
$banner_text = get_field('banner_text');

// Advantages
$advantages_hidden = get_field('advantages_hidden');
$advantages_title = get_field('advantages_title');
$advantages_text = get_field('advantages_text'); // Editor
$advantages = get_field('advantages');

// Examples
$examples_hidden = get_field('examples_hidden');
$examples_title = get_field('examples_title'); // Editor
$examples = get_field('examples');

// Contact
$contact_hidden = get_field('contact_hidden');
?>

<?php if ($banner_hidden !== "Да"): ?>
    <!-- hero -->
    <section class="hero page">
        <div class="container">

            <?php if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<div class="breadcrumb">', '</div>');
            } ?>

            <div class="main_title">
                <?php echo apply_filters('the_content', $banner_title); ?>
            </div>

            <div class="sub_title">
                <?php echo esc_html($banner_text); ?>
            </div>

            <div class="button  open-modal-btn">
                Обсудить проект
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($advantages_hidden !== "Да"): ?>
    <!-- order -->
    <section class="order">
        <div class="container">
            <div class="section_title">
                <?php echo esc_html($advantages_title); ?>
            </div>
            <div class="sub_title">
                <?php echo apply_filters('the_content', $advantages_text); ?>
            </div>

            <div class="order_row">
                <?php foreach ($advantages as $advantage): ?>
                    <!-- advantage item -->
                    <div class="order_item">
                        <img src="<?php echo esc_url($advantage['icon']['url']); ?>" alt="">
                        <div class="title"><?php echo esc_html($advantage['title']); ?></div>
                        <div class="info"><?php echo esc_html($advantage['text']); ?></div>
                    </div>
                <?php endforeach; ?>
                <!-- order item -->
                <div class="order_item">
                    <a class="button open-modal-btn">
                        Оставить заявку
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($examples_hidden !== "Да"): ?>
    <!-- example -->
    <section class="example">
        <div class="container">
            <div class="section_title">
                <?php echo apply_filters('the_content', $examples_title); ?>
            </div>

            <div class="example_row">
                <?php foreach ($examples as $example): ?>
                    <!-- example item -->
                    <div class="example_item">
                        <img src="<?php echo esc_url($example['image']['url']); ?>" alt="">
                        <div class="bottom">
                            <div class="title"><?php echo esc_html($example['title']); ?></div>
                            <div class="info"><?php echo esc_html($example['text']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

    <!-- application -->
    <section class="application page">
        <div class="container">
            <div class="application_row">
                <div class="left">
                </div>
                <div class="right">
                    <div class="section_title">Ваша задача требует больше, чем производство кейсов?</div>
                    <div class="sub_title">
                        Обсудите технические детали с нашими инженерами. Мы предложим решение, основанное на глубоком
                        понимании технологий и материалов.
                    </div>
                    <form action="">
                        <input type="text" placeholder="Ваше Имя*">
                        <div class="input_block">
                            <input type="tel" id="phone" placeholder="+7 999 999 99 99*">
                        </div>
                        <textarea name="" id="" placeholder="Комментарий"></textarea>
                        <button>Оставить заявку</button>

                        <!-- custom confirm -->
                        <div class="confirm">
                            <label class="custom-checkbox">
                                <input type="checkbox" id="confirm">
                                <span class="checkmark"></span>
                            </label>
                            <label for="confirm" class="text">Нажимая на кнопку «Отправить», вы даете согласие на
                                обработку своих <a
                                        href="<?php echo get_template_directory_uri() ?>/assets/documents/Personal_Data_Processing_Extended.pdf"
                                        target="_blank">персональных данных</a></label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

<?php get_footer();
