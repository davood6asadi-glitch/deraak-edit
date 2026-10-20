<?php
/**
 * Deraak Industrial Theme Functions
 *
 * @package Deraak_Industrial
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DERAAK_THEME_VERSION', '1.0.0');
define('DERAAK_THEME_DIR', get_template_directory());
define('DERAAK_THEME_URI', get_template_directory_uri());

/**
 * 1. Theme Setup
 */
function deraak_industrial_setup() {
    // Localization
    load_theme_textdomain('deraak-industrial', DERAAK_THEME_DIR . '/languages');

    // Standard Theme Supports
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'gallery', 'caption'));
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // WooCommerce Full Compatibility
    add_theme_support('woocommerce', array(
        'thumbnail_image_width' => 400,
        'single_image_width'    => 800,
        'product_grid'          => array(
            'default_rows'    => 4,
            'min_rows'        => 2,
            'max_rows'        => 8,
            'default_columns' => 4,
            'min_columns'     => 1,
            'max_columns'     => 5,
        ),
    ));
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Register Navigation Menus
    register_nav_menus(array(
        'primary' => __('منوی اصلی هدر', 'deraak-industrial'),
        'mobile_dock' => __('داک ناوبری پایین موبایل', 'deraak-industrial'),
        'footer'  => __('منوی فوتر', 'deraak-industrial'),
    ));
}
add_action('after_setup_theme', 'deraak_industrial_setup');

/**
 * 2. Enqueue Assets (Local & Self-Contained)
 */
function deraak_industrial_scripts() {
    // Main Stylesheet
    wp_enqueue_style('deraak-style', get_stylesheet_uri(), array(), DERAAK_THEME_VERSION);

    // RTL & Local Font Support
    if (is_rtl()) {
        wp_enqueue_style('deraak-rtl', DERAAK_THEME_URI . '/rtl.css', array('deraak-style'), DERAAK_THEME_VERSION);
    }

    // Industrial Theme CSS
    wp_enqueue_style('deraak-theme-css', DERAAK_THEME_URI . '/assets/css/theme.css', array('deraak-style'), DERAAK_THEME_VERSION);

    // Theme JS
    wp_enqueue_script('deraak-theme-js', DERAAK_THEME_URI . '/assets/js/theme.js', array(), DERAAK_THEME_VERSION, true);

    // Localize Script for Search / AJAX
    wp_localize_script('deraak-theme-js', 'deraakData', array(
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'siteUrl'   => home_url('/'),
        'isOffline' => false,
    ));
}
add_action('wp_enqueue_scripts', 'deraak_industrial_scripts');

/**
 * 3. Helper: Safely Retrieve Deraak Meta Field
 */
function deraak_get_meta($post_id, $key, $default = '') {
    $val = get_post_meta($post_id, $key, true);
    return !empty($val) ? $val : $default;
}

/**
 * 4. Helper: Render Stock Badge
 */
function deraak_render_stock_badge($post_id) {
    $badge = deraak_get_meta($post_id, '_derak_stock_badge');
    if (empty($badge)) {
        // Fallback to WooCommerce stock status
        $product = wc_get_product($post_id);
        if ($product) {
            $badge = $product->is_in_stock() ? 'موجود در انبار تهران' : 'سفارشی';
        } else {
            $badge = 'موجود';
        }
    }
    echo '<span class="derak-card-badge">' . esc_html($badge) . '</span>';
}

/**
 * 5. Helper: Render 2-Column Technical Parameter Matrix
 */
function deraak_render_param_matrix($post_id) {
    $p1_lbl = deraak_get_meta($post_id, '_derak_param1_label', 'سنسور:');
    $p1_val = deraak_get_meta($post_id, '_derak_param1_val');
    $p2_lbl = deraak_get_meta($post_id, '_derak_param2_label', 'خروجی:');
    $p2_val = deraak_get_meta($post_id, '_derak_param2_val');

    if (empty($p1_val) && empty($p2_val)) {
        return;
    }

    echo '<div class="derak-param-matrix">';
    if (!empty($p1_val)) {
        echo '<span class="derak-param-label">' . esc_html($p1_lbl) . '</span>';
        echo '<span class="derak-param-val" dir="ltr">' . esc_html($p1_val) . '</span>';
    }
    if (!empty($p2_val)) {
        echo '<span class="derak-param-label">' . esc_html($p2_lbl) . '</span>';
        echo '<span class="derak-param-val" dir="ltr">' . esc_html($p2_val) . '</span>';
    }
    echo '</div>';
}

/**
 * 6. Helper: Render External Product Destination URL (deraak.com)
 * Distinct from canonical SEO URL - only renders if explicitly set!
 */
function deraak_render_external_destination_btn($post_id) {
    $url = deraak_get_meta($post_id, '_derak_external_destination_url');
    if (empty($url)) {
        return;
    }

    echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" class="derak-btn-external" data-requires-net="true">';
    echo '<span>مشاهده مشخصات مرجع در Deraak.com</span>';
    echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';
    echo '</a>';
}

/**
 * 7. Elementor Free Compatibility
 */
function deraak_register_elementor_locations($elementor_theme_manager) {
    $elementor_theme_manager->register_all_core_location();
}
add_action('elementor/theme/register_locations', 'deraak_register_elementor_locations');
