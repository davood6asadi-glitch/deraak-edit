<?php
/**
 * Deraak B2B Engine Settings Page
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_Settings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'تنظیمات سامانه دراک',
            'تنظیمات دراک',
            'manage_options',
            'deraak-settings',
            array($this, 'render_settings_page'),
            'dashicons-admin-generic',
            58
        );
    }

    public function register_settings() {
        register_setting('deraak_settings_group', 'derak_main_website_url', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => 'https://deraak.com',
        ));

        register_setting('deraak_settings_group', 'derak_whatsapp_number', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '989120000000',
        ));

        register_setting('deraak_settings_group', 'derak_office_phone', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '02133900000',
        ));

        register_setting('deraak_settings_group', 'derak_catalog_version', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '1.0.0',
        ));
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap" dir="rtl">
            <h1>تنظیمات سامانه ابزار دقیق دراک (Deraak B2B)</h1>
            <p>پیکربندی آدرس‌های مرجع، اطلاعات تماس لاله‌زار و نسخه کاتالوگ آفلاین PWA</p>

            <form method="post" action="options.php">
                <?php
                settings_fields('deraak_settings_group');
                do_settings_sections('deraak_settings_group');
                ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="derak_main_website_url">آدرس وب‌سایت اصلی دراک:</label></th>
                        <td>
                            <input name="derak_main_website_url" type="url" id="derak_main_website_url" value="<?php echo esc_attr(get_option('derak_main_website_url', 'https://deraak.com')); ?>" class="regular-text" dir="ltr">
                            <p class="description">آدرس پیش‌فرض سایت مرجع Deraak بین‌الملل.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_whatsapp_number">شماره واتس‌اپ مهندسی فروش:</label></th>
                        <td>
                            <input name="derak_whatsapp_number" type="text" id="derak_whatsapp_number" value="<?php echo esc_attr(get_option('derak_whatsapp_number', '989120000000')); ?>" class="regular-text" dir="ltr">
                            <p class="description">شماره با پیش‌شماره کشور (مثلاً 989120000000) جهت ارسال پیش‌فاکتورهای استعلام.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_office_phone">تلفن دفتر لاله‌زار تهران:</label></th>
                        <td>
                            <input name="derak_office_phone" type="text" id="derak_office_phone" value="<?php echo esc_attr(get_option('derak_office_phone', '02133900000')); ?>" class="regular-text" dir="ltr">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_catalog_version">نسخه کاتالوگ آفلاین (PWA Version):</label></th>
                        <td>
                            <input name="derak_catalog_version" type="text" id="derak_catalog_version" value="<?php echo esc_attr(get_option('derak_catalog_version', '1.0.0')); ?>" class="small-text" dir="ltr">
                            <p class="description">با تغییر این نسخه، مرورگر کاربران نسخه جدید کاتالوگ را در حافظه محلی دانلود و ذخیره می‌کند.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('ذخیره تغییرات'); ?>
            </form>
        </div>
        <?php
    }
}

new Deraak_Settings();
