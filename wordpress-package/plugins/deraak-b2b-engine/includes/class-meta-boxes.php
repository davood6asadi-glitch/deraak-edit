<?php
/**
 * Product Engineering Meta Boxes & Destination URL Field
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_Product_Meta_Boxes {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_engineering_meta_boxes'));
        add_action('save_post_product', array($this, 'save_engineering_meta_boxes'), 10, 2);
    }

    public function add_engineering_meta_boxes() {
        add_meta_box(
            'derak_engineering_specs',
            'مشخصات مهندسی، انبار و پیوند مرجع دراک (Deraak B2B)',
            array($this, 'render_meta_box_content'),
            'product',
            'normal',
            'high'
        );
    }

    public function render_meta_box_content($post) {
        wp_nonce_field('derak_save_product_meta', 'derak_product_meta_nonce');

        $destination_url = get_post_meta($post->ID, '_derak_external_destination_url', true);
        $stock_badge     = get_post_meta($post->ID, '_derak_stock_badge', true);
        $tag             = get_post_meta($post->ID, '_derak_product_tag', true);
        $lead_time       = get_post_meta($post->ID, '_derak_lead_time', true);

        $p1_lbl = get_post_meta($post->ID, '_derak_param1_label', true);
        $p1_val = get_post_meta($post->ID, '_derak_param1_val', true);
        $p2_lbl = get_post_meta($post->ID, '_derak_param2_label', true);
        $p2_val = get_post_meta($post->ID, '_derak_param2_val', true);

        $spec_proto = get_post_meta($post->ID, '_derak_spec_protocol', true);
        $spec_sup   = get_post_meta($post->ID, '_derak_spec_supply', true);
        $spec_acc   = get_post_meta($post->ID, '_derak_spec_accuracy', true);
        $spec_cert  = get_post_meta($post->ID, '_derak_spec_certification', true);
        $spec_temp  = get_post_meta($post->ID, '_derak_spec_temp', true);
        $spec_press = get_post_meta($post->ID, '_derak_spec_pressure', true);
        ?>
        <style>
            .derak-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
            .derak-meta-field label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; }
            .derak-meta-field input, .derak-meta-field select { width: 100%; max-width: 100%; height: 36px; }
            .derak-meta-desc { font-size: 11px; color: #666; margin-top: 4px; }
            .derak-meta-section-title { font-size: 14px; font-weight: bold; color: #031A54; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin: 16px 0 10px 0; }
        </style>

        <div class="derak-meta-field" style="background:#EEF0FF; padding:12px; border:1px solid #C6CDFE; border-radius:6px; margin-bottom:16px;">
            <label for="_derak_external_destination_url" style="color:#031A54;">
                🔗 پیوند مستقیم صفحه محصول در وب‌سایت اصلی (Deraak.com):
            </label>
            <input type="url" name="_derak_external_destination_url" id="_derak_external_destination_url" value="<?php echo esc_attr($destination_url); ?>" placeholder="https://deraak.com/product/exact-part-number" dir="ltr" />
            <div class="derak-meta-desc">
                این فیلد صرفاً جهت هدایت مستقیم خریدار به صفحه مشخصات ارزی در سایت اصلی دراک است و تأثیری بر تگ سئو کانونی این سایت ندارد. در صورت خالی بودن، دکمه ارجاع مخفی خواهد ماند.
            </div>
        </div>

        <div class="derak-meta-section-title">وضعیت انبار و شناسه فنی</div>
        <div class="derak-meta-grid">
            <div class="derak-meta-field">
                <label for="_derak_stock_badge">برچسب وضعیت موجودی:</label>
                <select name="_derak_stock_badge" id="_derak_stock_badge">
                    <option value="موجود در انبار تهران" <?php selected($stock_badge, 'موجود در انبار تهران'); ?>>موجود در انبار تهران</option>
                    <option value="موجود" <?php selected($stock_badge, 'موجود'); ?>>موجود</option>
                    <option value="تحویل ۴۸h" <?php selected($stock_badge, 'تحویل ۴۸h'); ?>>تحویل ۴۸h</option>
                    <option value="تحویل فوری" <?php selected($stock_badge, 'تحویل فوری'); ?>>تحویل فوری</option>
                    <option value="سفارشی" <?php selected($stock_badge, 'سفارشی'); ?>>سفارشی (تأمین از خارج)</option>
                </select>
            </div>

            <div class="derak-meta-field">
                <label for="_derak_product_tag">تگ فنی کارت (مثلاً RADAR، FLOW، SIL 2):</label>
                <input type="text" name="_derak_product_tag" id="_derak_product_tag" value="<?php echo esc_attr($tag); ?>" placeholder="DIN RAIL" dir="ltr" />
            </div>

            <div class="derak-meta-field" style="grid-column: 1 / -1;">
                <label for="_derak_lead_time">زمان و شرایط تحویل کالا:</label>
                <input type="text" name="_derak_lead_time" id="_derak_lead_time" value="<?php echo esc_attr($lead_time); ?>" placeholder="موجود در انبار تهران - تحویل ۲۴ ساعته" />
            </div>
        </div>

        <div class="derak-meta-section-title">پارامترهای سریع کارت محصول (2-Param Matrix)</div>
        <div class="derak-meta-grid">
            <div class="derak-meta-field">
                <label>عنوان پارامتر ۱:</label>
                <input type="text" name="_derak_param1_label" value="<?php echo esc_attr($p1_lbl ? $p1_lbl : 'سنسور:'); ?>" />
            </div>
            <div class="derak-meta-field">
                <label>مقدار پارامتر ۱:</label>
                <input type="text" name="_derak_param1_val" value="<?php echo esc_attr($p1_val); ?>" placeholder="RTD, TC, mV" dir="ltr" />
            </div>
            <div class="derak-meta-field">
                <label>عنوان پارامتر ۲:</label>
                <input type="text" name="_derak_param2_label" value="<?php echo esc_attr($p2_lbl ? $p2_lbl : 'خروجی:'); ?>" />
            </div>
            <div class="derak-meta-field">
                <label>مقدار پارامتر ۲:</label>
                <input type="text" name="_derak_param2_val" value="<?php echo esc_attr($p2_val); ?>" placeholder="4-20 mA" dir="ltr" />
            </div>
        </div>

        <div class="derak-meta-section-title">ماتریس مشخصات تفصیلی مهندسی</div>
        <div class="derak-meta-grid">
            <div class="derak-meta-field">
                <label>پروتکل ارتباطی / خروجی:</label>
                <input type="text" name="_derak_spec_protocol" value="<?php echo esc_attr($spec_proto); ?>" placeholder="4-20 mA HART, Modbus RTU" dir="ltr" />
            </div>
            <div class="derak-meta-field">
                <label>ولتاژ تغذیه:</label>
                <input type="text" name="_derak_spec_supply" value="<?php echo esc_attr($spec_sup); ?>" placeholder="24 VDC (Loop-powered)" dir="ltr" />
            </div>
            <div class="derak-meta-field">
                <label>کلاس دقت:</label>
                <input type="text" name="_derak_spec_accuracy" value="<?php echo esc_attr($spec_acc); ?>" placeholder="±0.05% FS" dir="ltr" />
            </div>
            <div class="derak-meta-field">
                <label>تاییدیه‌ها و استاندارد ایمنی:</label>
                <input type="text" name="_derak_spec_certification" value="<?php echo esc_attr($spec_cert); ?>" placeholder="ATEX Ex ia, SIL 2, CE" dir="ltr" />
            </div>
            <div class="derak-meta-field">
                <label>محدوده دمای کاری:</label>
                <input type="text" name="_derak_spec_temp" value="<?php echo esc_attr($spec_temp); ?>" placeholder="-40°C تا +85°C" dir="ltr" />
            </div>
            <div class="derak-meta-field">
                <label>تحمل فشار فرآیندی:</label>
                <input type="text" name="_derak_spec_pressure" value="<?php echo esc_attr($spec_press); ?>" placeholder="تا 40 bar" dir="ltr" />
            </div>
        </div>
        <?php
    }

    public function save_engineering_meta_boxes($post_id, $post) {
        if (!isset($_POST['derak_product_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['derak_product_meta_nonce'])), 'derak_save_product_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitization and Saving
        $fields = array(
            '_derak_external_destination_url' => 'esc_url_raw',
            '_derak_stock_badge'             => 'sanitize_text_field',
            '_derak_product_tag'             => 'sanitize_text_field',
            '_derak_lead_time'               => 'sanitize_text_field',
            '_derak_param1_label'            => 'sanitize_text_field',
            '_derak_param1_val'              => 'sanitize_text_field',
            '_derak_param2_label'            => 'sanitize_text_field',
            '_derak_param2_val'              => 'sanitize_text_field',
            '_derak_spec_protocol'           => 'sanitize_text_field',
            '_derak_spec_supply'             => 'sanitize_text_field',
            '_derak_spec_accuracy'           => 'sanitize_text_field',
            '_derak_spec_certification'      => 'sanitize_text_field',
            '_derak_spec_temp'               => 'sanitize_text_field',
            '_derak_spec_pressure'           => 'sanitize_text_field',
        );

        foreach ($fields as $key => $sanitizer) {
            if (isset($_POST[$key])) {
                $val = call_user_func($sanitizer, wp_unslash($_POST[$key]));
                update_post_meta($post_id, $key, $val);
            }
        }
    }
}

new Deraak_Product_Meta_Boxes();
