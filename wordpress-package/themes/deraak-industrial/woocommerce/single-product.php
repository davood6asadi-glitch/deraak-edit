<?php
/**
 * WooCommerce Single Product Template
 * Native Fallback - 100% usable without Elementor Pro
 *
 * @package Deraak_Industrial
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="derak-container" style="padding-top: 20px; padding-bottom: 60px;">

    <?php
    while (have_posts()) :
        the_post();
        global $product;
        $post_id   = get_the_ID();
        $sku       = $product ? $product->get_sku() : '';
        $tag       = deraak_get_meta($post_id, '_derak_product_tag', 'INDUSTRIAL');
        $lead_time = deraak_get_meta($post_id, '_derak_lead_time', 'تحویل فوری انبار تهران');

        $brands     = get_the_terms($post_id, 'product_brand');
        $brand_name = (!empty($brands) && !is_wp_error($brands)) ? $brands[0]->name : 'Deraak';

        // Tech specs
        $spec_proto = deraak_get_meta($post_id, '_derak_spec_protocol');
        $spec_sup   = deraak_get_meta($post_id, '_derak_spec_supply');
        $spec_acc   = deraak_get_meta($post_id, '_derak_spec_accuracy');
        $spec_cert  = deraak_get_meta($post_id, '_derak_spec_certification');
        $spec_temp  = deraak_get_meta($post_id, '_derak_spec_temp');
        $spec_press = deraak_get_meta($post_id, '_derak_spec_pressure');
        ?>

        <!-- Breadcrumbs -->
        <nav style="font-size:12px; color:var(--derak-stone-muted); margin-bottom:16px;">
            <a href="<?php echo esc_url(home_url('/')); ?>" style="color:var(--derak-stone-muted); text-decoration:none;">خانه</a> / 
            <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop')); ?>" style="color:var(--derak-stone-muted); text-decoration:none;">کاتالوگ</a> / 
            <span style="color:var(--derak-primary); font-weight:700;"><?php the_title(); ?></span>
        </nav>

        <div class="derak-single-layout">
            <!-- Left: Product Image & Badges -->
            <div class="derak-single-gallery">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <span class="derak-card-brand" dir="ltr" style="font-size:14px; font-weight:700;"><?php echo esc_html($brand_name); ?></span>
                    <?php deraak_render_stock_badge($post_id); ?>
                </div>

                <div style="position:relative; background-color:var(--derak-blue-ice); border-radius:12px; padding:20px; border:1px solid var(--derak-blue-periwinkle);">
                    <?php
                    if (has_post_thumbnail()) {
                        the_post_thumbnail('large', array('class' => 'derak-single-img'));
                    } else {
                        echo '<img src="' . esc_url(DERAAK_THEME_URI . '/assets/images/icon.svg') . '" class="derak-single-img" alt="' . esc_attr(get_the_title()) . '" />';
                    }
                    ?>
                    <span class="derak-card-tag" dir="ltr" style="font-size:11px; padding:4px 8px;"><?php echo esc_html($tag); ?></span>
                </div>

                <div style="margin-top:14px; font-size:12px; color:var(--derak-stone-muted); display:flex; align-items:center; justify-content:center; gap:6px;">
                    <span>🚚</span>
                    <span>وضعیت تحویل: <strong><?php echo esc_html($lead_time); ?></strong></span>
                </div>
            </div>

            <!-- Right: Product Engineering Details -->
            <div class="derak-single-info">
                <div>
                    <?php if (!empty($sku)) : ?>
                        <span class="derak-card-model" dir="ltr" style="font-size:16px; padding:4px 12px;"><?php echo esc_html($sku); ?></span>
                    <?php endif; ?>
                    <h1 style="color:var(--derak-primary); font-size:22px; font-weight:800; margin:8px 0;"><?php the_title(); ?></h1>
                </div>

                <!-- Parameters Matrix -->
                <?php deraak_render_param_matrix($post_id); ?>

                <!-- Excerpt / Short Description -->
                <?php if (has_excerpt()) : ?>
                    <div style="font-size:13px; color:var(--derak-stone-charcoal); line-height:1.7; background-color:#FFFFFF; border:1px solid var(--derak-border); border-radius:8px; padding:12px;">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>

                <!-- CTAs: Add to RFQ & External Deraak.com Link -->
                <div style="display:flex; flex-wrap:wrap; gap:10px; margin:16px 0;">
                    <button type="button" 
                            class="derak-btn derak-btn-accent derak-btn-icon-add" 
                            style="width:auto; height:44px; padding:0 24px; font-size:14px;"
                            data-product-id="<?php echo esc_attr($post_id); ?>"
                            data-product-title="<?php echo esc_attr(get_the_title()); ?>"
                            data-product-model="<?php echo esc_attr($sku); ?>">
                        <span>+ افزودن به لیست استعلام قیمت</span>
                    </button>

                    <!-- Explicit External Destination URL (deraak.com) -->
                    <?php deraak_render_external_destination_btn($post_id); ?>
                </div>

                <!-- Structured Technical Specifications Table -->
                <h3 style="color:var(--derak-primary); font-size:15px; font-weight:700; margin:16px 0 6px 0; border-bottom:2px solid var(--derak-accent); padding-bottom:6px; display:inline-block;">
                    ماتریس مشخصات فنی و استانداردهای صنعتی
                </h3>

                <table class="derak-specs-table">
                    <tbody>
                        <?php if (!empty($spec_proto)) : ?>
                            <tr><th>پروتکل خروجی و ارتباطی</th><td dir="ltr"><?php echo esc_html($spec_proto); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($spec_sup)) : ?>
                            <tr><th>ولتاژ تغذیه کاری</th><td dir="ltr"><?php echo esc_html($spec_sup); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($spec_acc)) : ?>
                            <tr><th>کلاس دقت اندازه‌گیری</th><td><?php echo esc_html($spec_acc); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($spec_cert)) : ?>
                            <tr><th>تاییدیه‌ها و استاندارد ایمنی</th><td dir="ltr"><?php echo esc_html($spec_cert); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($spec_temp)) : ?>
                            <tr><th>محدوده دمای فرآیندی</th><td dir="ltr"><?php echo esc_html($spec_temp); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($spec_press)) : ?>
                            <tr><th>تحمل فشار مکانیکی</th><td dir="ltr"><?php echo esc_html($spec_press); ?></td></tr>
                        <?php endif; ?>
                        <tr><th>کد شناسه کاتالوگ</th><td class="derak-mono" dir="ltr">DERAK-<?php echo esc_html($post_id); ?></td></tr>
                    </tbody>
                </table>

                <!-- Full Description -->
                <div style="font-size:13px; line-height:1.8; color:var(--derak-stone-charcoal);">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>

        <!-- Script: Record Recently Viewed in LocalStorage -->
        <script>
        (function() {
            try {
                var currentId = "<?php echo esc_js($post_id); ?>";
                var key = 'derak_recently_viewed';
                var list = JSON.parse(localStorage.getItem(key) || '[]');
                list = list.filter(function(id) { return id !== currentId; });
                list.unshift(currentId);
                if (list.length > 12) list = list.slice(0, 12);
                localStorage.setItem(key, JSON.stringify(list));
            } catch(e) {}
        })();
        </script>

        <?php
    endwhile;
    ?>

</div>

<?php
get_footer();
