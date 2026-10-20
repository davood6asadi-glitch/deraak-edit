<?php
/**
 * Deraak B2B Engine Settings Page & Sample CSV Importer
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
        add_action('admin_post_deraak_import_sample_csv', array($this, 'handle_sample_csv_import'));
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
        $settings = array(
            'derak_main_website_url'        => array('sanitize' => 'esc_url_raw', 'default' => 'https://deraak.com'),
            'derak_office_phone'            => array('sanitize' => 'sanitize_text_field', 'default' => '02191693022'),
            'derak_whatsapp_number'         => array('sanitize' => 'sanitize_text_field', 'default' => '989398058002'),
            'derak_inquiry_email'           => array('sanitize' => 'sanitize_email', 'default' => get_option('admin_email')),
            'derak_catalog_version'         => array('sanitize' => 'sanitize_text_field', 'default' => '1.1.0'),
            'derak_kavenegar_api_key'       => array('sanitize' => 'sanitize_text_field', 'default' => ''),
            'derak_kavenegar_otp_template'  => array('sanitize' => 'sanitize_text_field', 'default' => 'derak-verify'),
            'derak_kavenegar_admin_template'=> array('sanitize' => 'sanitize_text_field', 'default' => 'derak-admin-lead'),
            'derak_kavenegar_admin_phone'   => array('sanitize' => 'sanitize_text_field', 'default' => '09398058002'),
        );

        foreach ($settings as $key => $cfg) {
            register_setting('deraak_settings_group', $key, array(
                'type'              => 'string',
                'sanitize_callback' => $cfg['sanitize'],
                'default'           => $cfg['default'],
            ));
        }
    }

    /**
     * One-Click Sample Products Importer (reads bundled sample-deraak-products.csv)
     */
    public function handle_sample_csv_import() {
        if (!current_user_can('manage_options')) {
            wp_die('عدم دسترسی مجاز.');
        }
        check_admin_referer('deraak_import_sample_csv_nonce');

        $csv_path = DERAAK_B2B_DIR . 'assets/sample-deraak-products.csv';
        if (!file_exists($csv_path)) {
            wp_safe_redirect(add_query_arg('derak_import', 'missing', admin_url('admin.php?page=deraak-settings')));
            exit;
        }

        $handle = fopen($csv_path, 'r');
        if (!$handle) {
            wp_safe_redirect(add_query_arg('derak_import', 'error', admin_url('admin.php?page=deraak-settings')));
            exit;
        }

        $headers = fgetcsv($handle);
        if (!is_array($headers)) {
            fclose($handle);
            wp_safe_redirect(add_query_arg('derak_import', 'error', admin_url('admin.php?page=deraak-settings')));
            exit;
        }

        // Strip UTF-8 BOM if present on first header
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }
            $item = array();
            foreach ($headers as $idx => $col_name) {
                $item[trim($col_name)] = isset($row[$idx]) ? trim($row[$idx]) : '';
            }

            $sku  = sanitize_text_field($item['SKU'] ?? '');
            $name = sanitize_text_field($item['Name'] ?? '');
            if ($sku === '' || $name === '') {
                continue;
            }

            // Find existing product by _sku
            $existing = get_posts(array(
                'post_type'      => 'product',
                'posts_per_page' => 1,
                'post_status'    => 'any',
                'meta_key'       => '_sku',
                'meta_value'     => $sku,
                'fields'         => 'ids',
            ));

            $post_data = array(
                'post_type'    => 'product',
                'post_status'  => 'publish',
                'post_title'   => $name,
                'post_excerpt' => wp_kses_post($item['Short description'] ?? ''),
                'post_content' => wp_kses_post($item['Description'] ?? ''),
            );

            if (!empty($existing)) {
                $post_data['ID'] = (int) $existing[0];
                $post_id = wp_update_post($post_data);
            } else {
                $post_id = wp_insert_post($post_data);
            }

            if (is_wp_error($post_id) || !$post_id) {
                continue;
            }

            update_post_meta($post_id, '_sku', $sku);
            update_post_meta($post_id, '_derak_sku_model', $sku);

            // Categories
            if (!empty($item['Categories'])) {
                $cats = array_map('trim', explode('>', $item['Categories']));
                $cat_ids = array();
                foreach ($cats as $c_name) {
                    if ($c_name === '') continue;
                    $term = term_exists($c_name, 'product_cat');
                    if (!$term) {
                        $term = wp_insert_term($c_name, 'product_cat');
                    }
                    if (!is_wp_error($term) && isset($term['term_id'])) {
                        $cat_ids[] = (int) $term['term_id'];
                    }
                }
                if (!empty($cat_ids)) {
                    wp_set_object_terms($post_id, $cat_ids, 'product_cat', false);
                }
            }

            // Brands
            if (!empty($item['Brands'])) {
                $brand_name = sanitize_text_field($item['Brands']);
                $b_term = term_exists($brand_name, 'product_brand');
                if (!$b_term) {
                    $b_term = wp_insert_term($brand_name, 'product_brand');
                }
                if (!is_wp_error($b_term) && isset($b_term['term_id'])) {
                    wp_set_object_terms($post_id, array((int) $b_term['term_id']), 'product_brand', false);
                }
            }

            // Meta columns (Meta: _derak_*)
            foreach ($item as $col => $val) {
                if (strpos($col, 'Meta: ') === 0) {
                    $meta_key = trim(substr($col, 6));
                    if ($meta_key !== '') {
                        if (strpos($meta_key, '_url') !== false) {
                            update_post_meta($post_id, $meta_key, esc_url_raw($val));
                        } else {
                            update_post_meta($post_id, $meta_key, sanitize_text_field($val));
                        }
                    }
                }
            }

            $imported++;
        }

        fclose($handle);
        // Bump catalog version so offline IndexedDB updates
        update_option('derak_catalog_version', '1.1.' . time());

        wp_safe_redirect(add_query_arg(array('derak_import' => 'success', 'count' => $imported), admin_url('admin.php?page=deraak-settings')));
        exit;
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap" dir="rtl" style="font-family:'IBM Plex Sans Arabic', Tahoma, sans-serif;">
            <h1>تنظیمات سامانه کاتالوگ صنعتی دراک (Deraak B2B)</h1>
            <p>پیکربندی اطلاعات تماس، دریافت استعلام‌های فنی، وب‌سرویس پیامکی کاوه‌نگار و همگام‌سازی آفلاین PWA</p>

            <?php if (isset($_GET['derak_import']) && $_GET['derak_import'] === 'success') : ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>درون‌ریزی موفق:</strong> تعداد <?php echo (int) ($_GET['count'] ?? 0); ?> تجهیز نمونه آزمایشی با موفقیت به کاتالوگ اضافه یا به‌روزرسانی شد.</p>
                </div>
            <?php endif; ?>

            <div style="background:#EBF5FF; border:1px solid #ADADFF; border-radius:8px; padding:16px; margin:16px 0;">
                <h2 style="margin-top:0; font-size:15px; color:#002566;">درون‌ریزی سریع تجهیزات نمونه آزمایشی (دمو)</h2>
                <p style="margin:0 0 12px 0; font-size:13px;">
                    علاوه بر امکان استفاده از درون‌ریز استاندارد ووکامرس با فایل <code>sample-deraak-products.csv</code>، می‌توانید با یک کلیک ۸ تجهیز نمونه آزمایشی را به همراه برندها و تصاویر شماتیک محلی وارد کنید:
                </p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="deraak_import_sample_csv" />
                    <?php wp_nonce_field('deraak_import_sample_csv_nonce'); ?>
                    <button type="submit" class="button button-primary">درون‌ریزی محصولات نمونه آزمایشی</button>
                </form>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('deraak_settings_group');
                do_settings_sections('deraak_settings_group');
                ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="derak_main_website_url">آدرس وب‌سایت مرجع دراک:</label></th>
                        <td>
                            <input name="derak_main_website_url" type="url" id="derak_main_website_url" value="<?php echo esc_attr(get_option('derak_main_website_url', 'https://deraak.com')); ?>" class="regular-text" dir="ltr">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_office_phone">تلفن دفتر فروش و مشاوره:</label></th>
                        <td>
                            <input name="derak_office_phone" type="text" id="derak_office_phone" value="<?php echo esc_attr(get_option('derak_office_phone', '02191693022')); ?>" class="regular-text" dir="ltr">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_inquiry_email">ایمیل دریافت استعلام‌های ثبت‌شده:</label></th>
                        <td>
                            <input name="derak_inquiry_email" type="email" id="derak_inquiry_email" value="<?php echo esc_attr(get_option('derak_inquiry_email', get_option('admin_email'))); ?>" class="regular-text" dir="ltr">
                            <p class="description">تمامی استعلام‌ها علاوه بر ذخیره در بخش «استعلام‌های دریافتی» پیشخوان، به این ایمیل نیز ارسال می‌شوند.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_catalog_version">نسخه کاتالوگ آفلاین (PWA Version):</label></th>
                        <td>
                            <input name="derak_catalog_version" type="text" id="derak_catalog_version" value="<?php echo esc_attr(get_option('derak_catalog_version', '1.1.0')); ?>" class="small-text" dir="ltr">
                            <p class="description">با تغییر این نسخه، مرورگر کاربران نسخه جدید کاتالوگ را همگام‌سازی می‌کند.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_kavenegar_api_key">کلید API کاوه‌نگار (اختیاری):</label></th>
                        <td>
                            <input name="derak_kavenegar_api_key" type="password" id="derak_kavenegar_api_key" value="<?php echo esc_attr(get_option('derak_kavenegar_api_key', '')); ?>" class="regular-text" dir="ltr" autocomplete="off">
                            <p class="description">در صورت خالی بودن، فرم مشاوره در حالت آزمایشی (Sandbox) عمل می‌کند.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_kavenegar_otp_template">نام الگوی Verify کد تایید کاوه‌نگار:</label></th>
                        <td>
                            <input name="derak_kavenegar_otp_template" type="text" id="derak_kavenegar_otp_template" value="<?php echo esc_attr(get_option('derak_kavenegar_otp_template', 'derak-verify')); ?>" class="regular-text" dir="ltr">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_kavenegar_admin_template">نام الگوی Verify اطلاع‌رسانی به مدیر:</label></th>
                        <td>
                            <input name="derak_kavenegar_admin_template" type="text" id="derak_kavenegar_admin_template" value="<?php echo esc_attr(get_option('derak_kavenegar_admin_template', 'derak-admin-lead')); ?>" class="regular-text" dir="ltr">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="derak_kavenegar_admin_phone">شماره موبایل مدیر جهت دریافت پیامک:</label></th>
                        <td>
                            <input name="derak_kavenegar_admin_phone" type="text" id="derak_kavenegar_admin_phone" value="<?php echo esc_attr(get_option('derak_kavenegar_admin_phone', '09398058002')); ?>" class="regular-text" dir="ltr">
                        </td>
                    </tr>
                </table>
                <?php submit_button('ذخیره تنظیمات'); ?>
            </form>
        </div>
        <?php
    }
}

new Deraak_Settings();
