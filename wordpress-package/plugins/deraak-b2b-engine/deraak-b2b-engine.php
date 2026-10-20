<?php
/**
 * Plugin Name: Deraak B2B Engine
 * Plugin URI: https://add.co.ir
 * Description: موتور تخصصی کاتالوگ صنعتی دراک: مدیریت مشخصات فنی ابزار دقیق، تاکسونومی برندها، پردازش سمت سرور لیست استعلام (RFQ) و فرم مشاوره، ویجت‌های المنتور رایگان، و سامانه PWA آفلاین (IndexedDB) با پیوند به وب‌سایت مرجع deraak.com
 * Version: 1.1.0
 * Author: Deraak Industrial Automation
 * Author URI: https://deraak.com
 * Text Domain: deraak-b2b-engine
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 8.1
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DERAAK_B2B_VERSION', '1.1.0');
define('DERAAK_B2B_DIR', plugin_dir_path(__FILE__));
define('DERAAK_B2B_URI', plugin_dir_url(__FILE__));

/**
 * 1. Activation & Environment Safeguards
 */
register_activation_hook(__FILE__, 'deraak_b2b_activate');
function deraak_b2b_activate() {
    if (version_compare(PHP_VERSION, '8.1', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('افزونه دراک نیازمند PHP نسخه 8.1 یا بالاتر است. لطفاً نسخه PHP هاست خود را ارتقا دهید.');
    }
    if (!get_option('derak_catalog_version')) {
        update_option('derak_catalog_version', '1.1.0');
    }
}

/**
 * Check if WooCommerce is active (Multisite & Single-site safe)
 */
function deraak_is_woocommerce_active() {
    if (class_exists('WooCommerce') || function_exists('wc_get_product')) {
        return true;
    }
    $active_plugins = (array) apply_filters('active_plugins', get_option('active_plugins', array()));
    if (in_array('woocommerce/woocommerce.php', $active_plugins, true)) {
        return true;
    }
    if (is_multisite()) {
        $sitewide = (array) get_site_option('active_sitewide_plugins', array());
        if (isset($sitewide['woocommerce/woocommerce.php'])) {
            return true;
        }
    }
    return false;
}

/**
 * Graceful Fallback: Register 'product' post type and 'product_cat' taxonomy
 * if WooCommerce is temporarily deactivated, so product data and templates never fatally error.
 */
add_action('init', 'deraak_b2b_register_fallback_post_type', 5);
function deraak_b2b_register_fallback_post_type() {
    if (deraak_is_woocommerce_active() || post_type_exists('product')) {
        return;
    }

    register_post_type('product', array(
        'labels' => array(
            'name'          => 'محصولات کاتالوگ',
            'singular_name' => 'محصول',
            'add_new_item'  => 'افزودن تجهیز جدید',
            'edit_item'     => 'ویرایش تجهیز',
        ),
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'supports'     => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
        'rewrite'      => array('slug' => 'product', 'with_front' => false),
        'menu_icon'    => 'dashicons-Index-card',
    ));

    if (!taxonomy_exists('product_cat')) {
        register_taxonomy('product_cat', array('product'), array(
            'labels' => array(
                'name'          => 'دسته‌بندی تجهیزات',
                'singular_name' => 'دسته‌بندی',
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => array('slug' => 'product-category', 'with_front' => false),
        ));
    }
}

/**
 * Disable WooCommerce Cart / Checkout purchasing workflows (Catalog-only mode)
 */
add_filter('woocommerce_is_purchasable', '__return_false', 99);
add_action('init', function () {
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
});

/**
 * Admin notice if WooCommerce is missing
 */
add_action('admin_notices', 'deraak_b2b_woocommerce_notice');
function deraak_b2b_woocommerce_notice() {
    if (!deraak_is_woocommerce_active()) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><strong>راهنمای دراک:</strong> افزونه <strong>WooCommerce</strong> فعال نیست. حالت جایگزین کاتالوگ دراک فعال شده است؛ برای استفاده از درون‌ریز استاندارد ووکامرس و امکانات کامل کاتالوگ، نصب ووکامرس توصیه می‌شود.</p>
        </div>
        <?php
    }
}

/**
 * 2. Load Plugin Modules
 */
require_once DERAAK_B2B_DIR . 'includes/class-brand-taxonomy.php';
require_once DERAAK_B2B_DIR . 'includes/class-meta-boxes.php';
require_once DERAAK_B2B_DIR . 'includes/class-rfq.php';
require_once DERAAK_B2B_DIR . 'includes/class-pwa.php';
require_once DERAAK_B2B_DIR . 'includes/class-elementor.php';
require_once DERAAK_B2B_DIR . 'includes/class-settings.php';

/**
 * 3. Enqueue Frontend Scripts & Localized Configuration
 */
add_action('wp_enqueue_scripts', 'deraak_b2b_enqueue_assets');
function deraak_b2b_enqueue_assets() {
    wp_enqueue_script(
        'deraak-idb-catalog',
        DERAAK_B2B_URI . 'assets/js/indexeddb-catalog.js',
        array(),
        DERAAK_B2B_VERSION,
        true
    );

    wp_enqueue_script(
        'deraak-pwa-install',
        DERAAK_B2B_URI . 'assets/js/pwa-install.js',
        array('deraak-idb-catalog'),
        DERAAK_B2B_VERSION,
        true
    );

    wp_enqueue_script(
        'deraak-rfq-cart',
        DERAAK_B2B_URI . 'assets/js/rfq-cart.js',
        array(),
        DERAAK_B2B_VERSION,
        true
    );

    $home_path = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
    if ($home_path === '') {
        $home_path = '/';
    }

    wp_localize_script('deraak-idb-catalog', 'deraakPWA', array(
        'catalogApi'  => esc_url_raw(rest_url('deraak/v1/catalog')),
        'version'     => sanitize_text_field((string) get_option('derak_catalog_version', '1.1.0')),
        'swUrl'       => esc_url_raw(add_query_arg('deraak_sw', '1', home_url('/'))),
        'scope'       => $home_path,
        'fallbackImg' => esc_url_raw(DERAAK_B2B_URI . 'assets/icons/icon.svg'),
        'staleHours'  => 24,
    ));

    wp_localize_script('deraak-rfq-cart', 'deraakRFQ', array(
        'inquiryApi'   => esc_url_raw(rest_url('deraak/v1/inquiry')),
        'otpSendApi'   => esc_url_raw(rest_url('deraak/v1/send-otp')),
        'otpVerifyApi' => esc_url_raw(rest_url('deraak/v1/verify-otp')),
        'ajaxUrl'      => esc_url_raw(admin_url('admin-ajax.php')),
        'restNonce'    => wp_create_nonce('wp_rest'),
        'inquiryNonce' => wp_create_nonce('deraak_submit_inquiry'),
        'whatsapp'     => preg_replace('/\D/', '', (string) get_option('derak_whatsapp_number', '')),
    ));
}

/**
 * 4. Baseline Security Headers
 */
add_action('send_headers', 'deraak_b2b_security_headers');
function deraak_b2b_security_headers() {
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=63072000');
    }
}
