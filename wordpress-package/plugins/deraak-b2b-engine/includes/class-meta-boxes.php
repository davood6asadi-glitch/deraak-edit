<?php
/**
 * Product Engineering Meta Boxes, Datasheet Link & Destination URL Field
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
            'مشخصات مهندسی، دیتاشیت و پیوند مرجع دراک (Deraak B2B)',
            array($this, 'render_meta_box_content'),
            'product',
            'normal',
            'high'
        );
    }

    public function render_meta_box_content($post) {
        wp_nonce_field('derak_save_product_meta', 'derak_product_meta_nonce');

        $sku_model       = get_post_meta($post->ID, '_sku', true) ?: get_post_meta($post->ID, '_derak_sku_model', true);
        $destination_url = get_post_meta($post->ID, '_derak_external_destination_url', true);
        $datasheet_url   = get_post_meta($post->ID, '_derak_datasheet_url', true);
        $stock_badge     = get_post_meta($post->ID, '_derak_stock_badge', true);
        $tag             = get_post_meta($post->ID, '_derak_product_tag', true);
        $lead_time       = get_post_meta($post->ID, '_derak_lead_time', true);
        $is_demo         = get_post_meta($post->ID, '_derak_is_demo', true);

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
            .derak-meta-wrap, .derak-meta-wrap input, .derak-meta-wrap select {
                font-family: 'IBM Plex Sans Arabic', Tahoma, sans-serif;
            }
            .derak-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
            .derak-meta-field label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; }
            .derak-meta-field input[type="text"], .derak-meta-field input[type="url"], .derak-meta-field select { width: 100%; max-width: 100%; height: 36px; }
            .derak-meta-desc { font-size: 11px; color: #475569; margin-top: 4px; }
            .derak-meta-section-title { font-size: 14px; font-weight: bold; color: #002566; border-bottom: 1px solid #CBD5E1; padding-bottom: 4px; margin: 16px 0 10px 0; }
        </style>

        <div class="derak-meta-wrap" dir="rtl">
            <div class="derak-meta-grid" style="background:#EBF5FF; padding:12px; border:1px solid #ADADFF; border-radius:6px; margin-bottom:16px;">
                <div class="derak-meta-field">
                    <label for="_derak_external_destination_url" style="color:#002566;">
                        پیوند صفحه متناظر تجهیز در وب‌سایت مرجع (deraak.com):
                    </label>
                    <input type="url" name="_derak_external_destination_url" id="_derak_external_destination_url" value="<?php echo esc_attr($destination_url); ?>" placeholder="https://deraak.com/product/..." dir="ltr" />
                    <div class="derak-meta-desc">
                        در صورت وارد کردن این آدرس، دکمه مشاهده محصول در deraak.com در صفحه تجهیز نمایش داده می‌شود.
                    </div>
                </div>

                <div class="derak-meta-field">
                    <label for="_derak_datasheet_url" style="color:#002566;">
                        لینک فایل دیتاشیت یا مدرک فنی (PDF):
                    </label>
                    <input type="url" name="_derak_datasheet_url" id="_derak_datasheet_url" value="<?php echo esc_attr($datasheet_url); ?>" placeholder="https://add.co.ir/wp-content/uploads/.../datasheet.pdf" dir="ltr" />
                    <div class="derak-meta-desc">
                        می‌توانید فایل PDF دیتاشیت را در «رسانه» وردپرس بارگذاری کرده و آدرس آن را اینجا قرار دهید.
                    </div>
                </div>
            </div>

            <div class="derak-meta-section-title">شناسه فنی و برچسب‌های کاتالوگ</div>
            <div class="derak-meta-grid">
                <div class="derak-meta-field">
                    <label for="_derak_sku_model">کد مدل فنی / پارت‌نامبر (SKU):</label>
                    <input type="text" name="_derak_sku_model" id="_derak_sku_model" value="<?php echo esc_attr($sku_model); ?>" placeholder="PR 4116 / FMR53" dir="ltr" />
                    <div class="derak-meta-desc">با فیلد SKU ووکامرس همگام می‌شود.</div>
                </div>

                <div class="derak-meta-field">
                    <label for="_derak_product_tag">تگ فنی کارت (مثلاً RADAR، FLOW، ISOLATOR):</label>
                    <input type="text" name="_derak_product_tag" id="_derak_product_tag" value="<?php echo esc_attr($tag); ?>" placeholder="DIN RAIL" dir="ltr" />
                </div>

                <div class="derak-meta-field">
                    <label for="_derak_stock_badge">برچسب وضعیت کاتالوگ (اختیاری):</label>
                    <input type="text" name="_derak_stock_badge" id="_derak_stock_badge" value="<?php echo esc_attr($stock_badge); ?>" placeholder="در صورت خالی بودن نمایش داده نمی‌شود" />
                </div>

                <div class="derak-meta-field">
                    <label for="_derak_lead_time">توضیحات تامین / تحویل (اختیاری):</label>
                    <input type="text" name="_derak_lead_time" id="_derak_lead_time" value="<?php echo esc_attr($lead_time); ?>" placeholder="در صورت خالی بودن نمایش داده نمی‌شود" />
                </div>

                <div class="derak-meta-field" style="grid-column: 1 / -1;">
                    <label>
                        <input type="checkbox" name="_derak_is_demo" value="1" <?php checked($is_demo, '1'); ?> />
                        علامت‌گذاری این رکورد به عنوان <strong>داده نمونه آزمایشی (دمو)</strong>
                    </label>
                </div>
            </div>

            <div class="derak-meta-section-title">پارامترهای خلاصه روی کارت محصول (حداکثر ۲ پارامتر)</div>
            <div class="derak-meta-grid">
                <div class="derak-meta-field">
                    <label for="_derak_param1_label">عنوان پارامتر ۱:</label>
                    <input type="text" name="_derak_param1_label" id="_derak_param1_label" value="<?php echo esc_attr($p1_lbl); ?>" placeholder="ورودی:" />
                </div>
                <div class="derak-meta-field">
                    <label for="_derak_param1_val">مقدار پارامتر ۱:</label>
                    <input type="text" name="_derak_param1_val" id="_derak_param1_val" value="<?php echo esc_attr($p1_val); ?>" placeholder="RTD, TC, mV" dir="ltr" />
                </div>
                <div class="derak-meta-field">
                    <label for="_derak_param2_label">عنوان پارامتر ۲:</label>
                    <input type="text" name="_derak_param2_label" id="_derak_param2_label" value="<?php echo esc_attr($p2_lbl); ?>" placeholder="خروجی:" />
                </div>
                <div class="derak-meta-field">
                    <label for="_derak_param2_val">مقدار پارامتر ۲:</label>
                    <input type="text" name="_derak_param2_val" id="_derak_param2_val" value="<?php echo esc_attr($p2_val); ?>" placeholder="4-20 mA" dir="ltr" />
                </div>
            </div>

            <div class="derak-meta-section-title">جدول مشخصات فنی (فقط ردیف‌های دارای مقدار نمایش داده می‌شوند)</div>
            <div class="derak-meta-grid">
                <div class="derak-meta-field">
                    <label for="_derak_spec_protocol">پروتکل ارتباطی / خروجی:</label>
                    <input type="text" name="_derak_spec_protocol" id="_derak_spec_protocol" value="<?php echo esc_attr($spec_proto); ?>" dir="ltr" />
                </div>
                <div class="derak-meta-field">
                    <label for="_derak_spec_supply">ولتاژ تغذیه:</label>
                    <input type="text" name="_derak_spec_supply" id="_derak_spec_supply" value="<?php echo esc_attr($spec_sup); ?>" dir="ltr" />
                </div>
                <div class="derak-meta-field">
                    <label for="_derak_spec_accuracy">کلاس دقت / مشخصه سنجش:</label>
                    <input type="text" name="_derak_spec_accuracy" id="_derak_spec_accuracy" value="<?php echo esc_attr($spec_acc); ?>" dir="ltr" />
                </div>
                <div class="derak-meta-field">
                    <label for="_derak_spec_certification">استاندارد / درجه حفاظت:</label>
                    <input type="text" name="_derak_spec_certification" id="_derak_spec_certification" value="<?php echo esc_attr($spec_cert); ?>" dir="ltr" />
                </div>
                <div class="derak-meta-field">
                    <label for="_derak_spec_temp">محدوده دمای کاری:</label>
                    <input type="text" name="_derak_spec_temp" id="_derak_spec_temp" value="<?php echo esc_attr($spec_temp); ?>" dir="ltr" />
                </div>
                <div class="derak-meta-field">
                    <label for="_derak_spec_pressure">محدوده فشار کاری:</label>
                    <input type="text" name="_derak_spec_pressure" id="_derak_spec_pressure" value="<?php echo esc_attr($spec_press); ?>" dir="ltr" />
                </div>
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

        if (isset($_POST['_derak_sku_model'])) {
            $sku = sanitize_text_field(wp_unslash($_POST['_derak_sku_model']));
            update_post_meta($post_id, '_derak_sku_model', $sku);
            if (!get_post_meta($post_id, '_sku', true) || !deraak_is_woocommerce_active()) {
                update_post_meta($post_id, '_sku', $sku);
            }
        }

        $fields = array(
            '_derak_external_destination_url' => 'esc_url_raw',
            '_derak_datasheet_url'            => 'esc_url_raw',
            '_derak_stock_badge'              => 'sanitize_text_field',
            '_derak_product_tag'              => 'sanitize_text_field',
            '_derak_lead_time'                => 'sanitize_text_field',
            '_derak_param1_label'             => 'sanitize_text_field',
            '_derak_param1_val'               => 'sanitize_text_field',
            '_derak_param2_label'             => 'sanitize_text_field',
            '_derak_param2_val'               => 'sanitize_text_field',
            '_derak_spec_protocol'            => 'sanitize_text_field',
            '_derak_spec_supply'              => 'sanitize_text_field',
            '_derak_spec_accuracy'            => 'sanitize_text_field',
            '_derak_spec_certification'       => 'sanitize_text_field',
            '_derak_spec_temp'                => 'sanitize_text_field',
            '_derak_spec_pressure'            => 'sanitize_text_field',
        );

        foreach ($fields as $key => $sanitizer) {
            if (isset($_POST[$key])) {
                $val = call_user_func($sanitizer, wp_unslash($_POST[$key]));
                update_post_meta($post_id, $key, $val);
            }
        }

        $is_demo = !empty($_POST['_derak_is_demo']) ? '1' : '0';
        update_post_meta($post_id, '_derak_is_demo', $is_demo);
    }
}

new Deraak_Product_Meta_Boxes();
