<?php
/**
 * WooCommerce Product Archive Template
 * Native Fallback - 100% usable without Elementor Pro
 *
 * @package Deraak_Industrial
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="derak-container" style="padding-top: 20px; padding-bottom: 60px;">

    <!-- Brand Intro & Industrial Positioning -->
    <section style="background-color:#FFFFFF; border:1px solid var(--derak-border); border-radius:12px; padding:16px; margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="width:5px; height:18px; border-radius:4px; background-color:var(--derak-accent);"></span>
            <h1 style="font-size:18px; color:var(--derak-primary); margin:0; font-weight:800;">
                <?php woocommerce_page_title(); ?>
            </h1>
        </div>
        <p style="font-size:13px; color:var(--derak-text-muted); margin:8px 0 0 0; line-height:1.6;">
            مرجع فنی و استعلام بی‌واسطه ملزومات صنعتی تهران و صنایع مادر نفت، گاز، پتروشیمی، نیروگاهی و سیمان
        </p>
    </section>

    <!-- Bento Engineering Categories -->
    <section class="derak-bento-grid">
        <div class="derak-bento-card">
            <div class="derak-bento-header">
                <div class="derak-bento-icon">⚙️</div>
                <span class="derak-bento-count">۱۲۰+ مدل</span>
            </div>
            <h3 class="derak-bento-title">ابزار دقیق</h3>
            <p class="derak-bento-desc">ترانسمیتر، سنسورهای فشار، سطح، جریان و دما</p>
            <div class="derak-bento-tags">HART, Fieldbus, Profibus-PA</div>
        </div>

        <div class="derak-bento-card">
            <div class="derak-bento-header">
                <div class="derak-bento-icon">💻</div>
                <span class="derak-bento-count">۸۵+ مدل</span>
            </div>
            <h3 class="derak-bento-title">اتوماسیون صنعتی</h3>
            <p class="derak-bento-desc">پی‌ال‌سی، کارت‌های ورودی/خروجی، رله و ایزولاتور</p>
            <div class="derak-bento-tags">SIL 2 / SIL 3, IO-Link, Modbus</div>
        </div>

        <div class="derak-bento-card">
            <div class="derak-bento-header">
                <div class="derak-bento-icon">⚡</div>
                <span class="derak-bento-count">۶۰+ مدل</span>
            </div>
            <h3 class="derak-bento-title">برق صنعتی</h3>
            <p class="derak-bento-desc">کلید اتوماتیک، کنتاکتور، درایو و حفاظت قدرت</p>
            <div class="derak-bento-tags">MCCB, VFD Inverters, Overload</div>
        </div>
    </section>

    <!-- Industrial Brands Strip -->
    <section style="margin: 20px 0 10px 0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h3 style="font-size:14px; font-weight:700; color:var(--derak-primary); margin:0;">برندهای تحت پوشش و تأمین</h3>
            <span style="font-size:11px; color:var(--derak-stone-muted);">اورجینال اروپایی و آسیایی</span>
        </div>
        <div class="derak-brands-row" dir="ltr">
            <?php
            $brands = get_terms(array(
                'taxonomy'   => 'product_brand',
                'hide_empty' => false,
            ));
            if (!empty($brands) && !is_wp_error($brands)) :
                foreach ($brands as $brand) :
                    echo '<a href="' . esc_url(get_term_link($brand)) . '" class="derak-brand-pill">' . esc_html($brand->name) . '</a>';
                endforeach;
            else :
                // Default fallback brands
                $demo_brands = array('Endress+Hauser', 'ifm electronic', 'PR electronics', 'Siemens', 'ABB', 'WIKA', 'Yokogawa', 'Schneider');
                foreach ($demo_brands as $db) :
                    echo '<span class="derak-brand-pill">' . esc_html($db) . '</span>';
                endforeach;
            endif;
            ?>
        </div>
    </section>

    <!-- Catalog Header / Sorting -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px; padding-bottom:8px; border-bottom:1px solid var(--derak-border);">
        <span style="font-size:13px; font-weight:700; color:var(--derak-primary);">
            <?php woocommerce_result_count(); ?>
        </span>
        <div>
            <?php woocommerce_catalog_ordering(); ?>
        </div>
    </div>

    <!-- Product Grid -->
    <?php if (woocommerce_product_loop()) : ?>
        <div class="derak-products-grid">
            <?php
            while (have_posts()) :
                the_post();
                wc_get_template_part('content', 'product');
            endwhile;
            ?>
        </div>

        <div style="margin-top:30px; text-align:center;">
            <?php woocommerce_pagination(); ?>
        </div>
    <?php else : ?>
        <div style="background-color:#FFFFFF; border:1px dashed var(--derak-border); border-radius:12px; padding:40px; text-align:center; margin-top:20px;">
            <p style="font-size:15px; color:var(--derak-stone-muted); margin:0;">هنوز محصولی در این رسته فنی تعریف نشده است.</p>
        </div>
    <?php endif; ?>

</div>

<?php
get_footer();
