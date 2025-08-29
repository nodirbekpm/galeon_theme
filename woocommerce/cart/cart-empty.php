<div class="top">
    <div class="info">
        <div class="section_title">Корзина</div>
    </div>

</div>
<p style="margin: 20px 0">Корзина пуста. </p>

<?php $shop_url = wc_get_page_permalink('shop'); ?>
<div class="total_row">
    <div class="right">
        <a href="<?php echo esc_url($shop_url); ?>" class="back_link">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/navigation_item_arrow.svg' ); ?>" alt="">
            <span>Вернуться в каталог</span>
        </a>

        <a href="<?php echo esc_url($shop_url); ?>" class="button hidden">
            Вернуться в каталог
        </a>
    </div>
</div>