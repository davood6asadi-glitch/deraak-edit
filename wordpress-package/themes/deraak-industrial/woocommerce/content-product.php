<?php
/**
 * WooCommerce Product Card Content Template
 *
 * @package Deraak_Industrial
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

$post_id   = $product->get_id();
$sku       = $product->get_sku();
$tag       = deraak_get_meta($post_id, '_derak_product_tag', 'INDUSTRIAL');
$lead_time = deraak_get_meta($post_id, '_derak_lead_time', 'تحویل فوری تهران');

// Brand taxonomy or fallback
$brands     = get_the_terms($post_id, 'product_brand');
$brand_name = (!empty($brands) && !is_wp_error($brands)) ? $brands[0]->name : 'Deraak';
?>

<article <?php wc_product_class('derak-product-card', $product); ?>>
    <div class="derak-card-top">
        <span class="derak-card-brand" dir="ltr"><?php echo esc_html($brand_name); ?></span>
        <?php deraak_render_stock_badge($post_id); ?>
    </div>

    <!-- Media / Image -->
    <a href="<?php the_permalink(); ?>" class="derak-card-media">
        <?php
        if (has_post_thumbnail()) {
            the_post_thumbnail('medium', array('class' => 'derak-card-img'));
        } else {
            echo '<img src="' . esc_url(DERAAK_THEME_URI . '/assets/images/icon.svg') . '" class="derak-card-img" alt="' . esc_attr(get_the_title()) . '" />';
        }
        ?>
        <span class="derak-card-tag" dir="ltr"><?php echo esc_html($tag); ?></span>
    </a>

    <!-- Title & Model -->
    <div>
        <a href="<?php the_permalink(); ?>" class="derak-card-title"><?php the_title(); ?></a>
        <?php if (!empty($sku)) : ?>
            <div><span class="derak-card-model" dir="ltr"><?php echo esc_html($sku); ?></span></div>
        <?php endif; ?>
    </div>

    <!-- Technical Parameters Matrix -->
    <?php deraak_render_param_matrix($post_id); ?>

    <!-- Delivery Lead Time -->
    <div class="derak-card-leadtime">
        <span>🚚</span>
        <span><?php echo esc_html($lead_time); ?></span>
    </div>

    <!-- Actions -->
    <div class="derak-card-actions">
        <button type="button" 
                class="derak-btn-icon-add" 
                data-product-id="<?php echo esc_attr($post_id); ?>"
                data-product-title="<?php echo esc_attr(get_the_title()); ?>"
                data-product-model="<?php echo esc_attr($sku); ?>"
                title="افزودن به سبد استعلام" 
                aria-label="افزودن به استعلام">
            +
        </button>

        <a href="<?php the_permalink(); ?>" class="derak-btn-card-detail">
            <span>استعلام قیمت و دیتاشیت</span>
            <span style="font-size:14px;">←</span>
        </a>
    </div>
</article>
