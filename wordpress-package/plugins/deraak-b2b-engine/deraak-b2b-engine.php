<?php
/**
 * Plugin Name: Deraak B2B Engine
 * Plugin URI: https://add.co.ir
 * Description: موتور تخصصی B2B، مدیریت مشخصات فنی ابزار دقیق، برندها، سبد استعلام استعلام (RFQ)، ویجت‌های المنتور رایگان، قابلیت PWA آفلاین و پیوند به وب‌سایت مرجع deraak.com
 * Version: 1.0.0
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

define('DERAAK_B2B_VERSION', '1.0.0');
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
}

/**
 * Check if WooCommerce is active
 */
function deraak_is_woocommerce_active() {
    return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
        || (is_multisite() && array_key_exists('woocommerce/woocommerce.php', get_site_option('active_sitewide_plugins')));
}

/**
 * Admin notice if WooCommerce is missing
 */
add_action('admin_notices', 'deraak_b2b_woocommerce_notice');
function deraak_b2b_woocommerce_notice() {
    if (!deraak_is_woocommerce_active()) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>هشدار دراک:</strong> برای کارکرد کاتالوگ صنعتی، فیلدهای مشخصات فنی و استعلام، لطفاً افزونه <strong>ووکامرس (WooCommerce)</strong> را نصب و فعال کنید.</p>
        </div>
        <?php
    }
}

/**
 * 2. Autoload Components
 */
require_once DERAAK_B2B_DIR . 'includes/class-brand-taxonomy.php';
require_once DERAAK_B2B_DIR . 'includes/class-meta-boxes.php';
require_once DERAAK_B2B_DIR . 'includes/class-rfq.php';
require_once DERAAK_B2B_DIR . 'includes/class-pwa.php';
require_once DERAAK_B2B_DIR . 'includes/class-elementor.php';
require_once DERAAK_B2B_DIR . 'includes/class-settings.php';

/**
 * 3. Enqueue Frontend Scripts & Styles for Plugin
 */
add_action('wp_enqueue_scripts', 'deraak_b2b_enqueue_assets');
function deraak_b2b_enqueue_assets() {
    // PWA & IndexedDB Catalog JS
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
        array(),
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

    // Localize data for PWA & REST
    wp_localize_script('deraak-pwa-install', 'deraakPWA', array(
        'catalogApi' => esc_url_raw(rest_url('deraak/v1/catalog')),
        'version'    => get_option('derak_catalog_version', '1.0.0'),
        'swUrl'      => esc_url_raw(DERAAK_B2B_URI . 'assets/sw.js'),
        'scope'      => '/',
    ));

    wp_localize_script('deraak-rfq-cart', 'deraakRFQ', array(
        'whatsapp' => preg_replace('/\D/', '', (string) get_option('derak_whatsapp_number', '')),
    ));
}

/**
 * 4. Baseline security headers (defense-in-depth)
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
