<?php
/**
 * B2B RFQ (Request For Quotation) & Consultation Server-Side Handler
 * Decoupled from WooCommerce Cart/Checkout — Persistent storage in WordPress + Kavenegar OTP/SMS support
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_RFQ_Handler {

    public function __construct() {
        add_action('init', array($this, 'register_inquiry_cpt'));
        add_action('add_meta_boxes', array($this, 'add_inquiry_detail_meta_box'));
        add_filter('manage_derak_inquiry_posts_columns', array($this, 'set_inquiry_columns'));
        add_action('manage_derak_inquiry_posts_custom_column', array($this, 'render_inquiry_column'), 10, 2);

        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('wp_ajax_deraak_submit_inquiry', array($this, 'handle_ajax_inquiry'));
        add_action('wp_ajax_nopriv_deraak_submit_inquiry', array($this, 'handle_ajax_inquiry'));

        add_shortcode('derak_rfq_basket', array($this, 'render_rfq_shortcode'));
        add_shortcode('derak_consultation_form', array($this, 'render_consultation_shortcode'));
    }

    /**
     * Register custom post type to store inquiries persistently in WordPress DB
     */
    public function register_inquiry_cpt() {
        register_post_type('derak_inquiry', array(
            'labels' => array(
                'name'               => 'استعلام‌های دریافتی',
                'singular_name'      => 'استعلام',
                'menu_name'          => 'استعلام‌های دریافتی',
                'all_items'          => 'استعلام‌های دریافتی',
                'edit_item'          => 'مشاهده جزئیات استعلام',
                'search_items'       => 'جستجوی استعلام‌ها',
                'not_found'          => 'استعلامی یافت نشد',
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'deraak-settings',
            'capability_type'     => 'post',
            'capabilities'        => array(
                'create_posts' => 'do_not_allow',
            ),
            'map_meta_cap'        => true,
            'supports'            => array('title'),
        ));
    }

    public function set_inquiry_columns($columns) {
        return array(
            'cb'       => '<input type="checkbox" />',
            'title'    => 'عنوان / کد رهگیری',
            'sender'   => 'درخواست‌دهنده و شرکت',
            'phone'    => 'شماره تماس',
            'count'    => 'تعداد اقلام',
            'date'     => 'تاریخ ثبت',
        );
    }

    public function render_inquiry_column($column, $post_id) {
        switch ($column) {
            case 'sender':
                $name = get_post_meta($post_id, '_derak_inq_name', true);
                $comp = get_post_meta($post_id, '_derak_inq_company', true);
                echo esc_html($name . ($comp ? ' (' . $comp . ')' : ''));
                break;
            case 'phone':
                echo '<span dir="ltr">' . esc_html(get_post_meta($post_id, '_derak_inq_phone', true)) . '</span>';
                break;
            case 'count':
                $items = get_post_meta($post_id, '_derak_inq_items', true);
                echo is_array($items) ? count($items) . ' ردیف' : 'مشاوره فنی';
                break;
        }
    }

    public function add_inquiry_detail_meta_box() {
        add_meta_box(
            'derak_inquiry_details_box',
            'جزئیات کامل درخواست استعلام / مشاوره',
            array($this, 'render_inquiry_meta_box'),
            'derak_inquiry',
            'normal',
            'high'
        );
    }

    public function render_inquiry_meta_box($post) {
        $tracking = get_post_meta($post->ID, '_derak_inq_tracking', true);
        $name     = get_post_meta($post->ID, '_derak_inq_name', true);
        $company  = get_post_meta($post->ID, '_derak_inq_company', true);
        $phone    = get_post_meta($post->ID, '_derak_inq_phone', true);
        $site     = get_post_meta($post->ID, '_derak_inq_site', true);
        $part     = get_post_meta($post->ID, '_derak_inq_part', true);
        $message  = get_post_meta($post->ID, '_derak_inq_message', true);
        $items    = get_post_meta($post->ID, '_derak_inq_items', true);
        ?>
        <div dir="rtl" style="font-family:'IBM Plex Sans Arabic', Tahoma, sans-serif; line-height:1.8;">
            <p><strong>کد رهگیری:</strong> <span dir="ltr"><?php echo esc_html($tracking); ?></span></p>
            <p><strong>نام و نام خانوادگی:</strong> <?php echo esc_html($name); ?></p>
            <p><strong>نام شرکت / پروژه:</strong> <?php echo esc_html($company ?: '—'); ?></p>
            <p><strong>شماره تماس:</strong> <span dir="ltr"><?php echo esc_html($phone); ?></span></p>
            <?php if ($site) : ?>
                <p><strong>محل تحویل / سایت:</strong> <?php echo esc_html($site); ?></p>
            <?php endif; ?>
            <?php if ($part) : ?>
                <p><strong>پارت‌نامبر مورد نظر:</strong> <span dir="ltr"><?php echo esc_html($part); ?></span></p>
            <?php endif; ?>
            <?php if ($message) : ?>
                <p><strong>شرح نیاز فنی / توضیحات:</strong><br><?php echo nl2br(esc_html($message)); ?></p>
            <?php endif; ?>

            <?php if (is_array($items) && !empty($items)) : ?>
                <h4>لیست اقلام استعلام شده:</h4>
                <table class="widefat striped" dir="rtl">
                    <thead>
                        <tr>
                            <th>ردیف</th>
                            <th>مدل فنی (SKU)</th>
                            <th>عنوان تجهیز</th>
                            <th>تعداد</th>
                            <th>وضعیت</th>
                            <th>یادداشت فنی</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $item) : ?>
                            <tr>
                                <td><?php echo (int) ($idx + 1); ?></td>
                                <td dir="ltr"><?php echo esc_html($item['model'] ?? ''); ?></td>
                                <td><?php echo esc_html($item['title'] ?? ''); ?></td>
                                <td><?php echo (int) ($item['quantity'] ?? 1); ?></td>
                                <td><?php echo !empty($item['urgent']) ? 'فوری' : 'عادی'; ?></td>
                                <td><?php echo esc_html($item['notes'] ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Register REST endpoints for Inquiry submission & Kavenegar OTP verification
     */
    public function register_rest_routes() {
        register_rest_route('deraak/v1', '/inquiry', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_rest_inquiry'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('deraak/v1', '/send-otp', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_rest_send_otp'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('deraak/v1', '/verify-otp', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_rest_verify_otp'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Normalize Persian/Arabic digits to English and validate Iranian phone number
     */
    public static function normalize_phone($raw) {
        $persian = array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩');
        $english = array('0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9');
        $clean   = str_replace($persian, $english, (string) $raw);
        $digits  = preg_replace('/[\s\-()]/', '', $clean);

        if (preg_match('/^09\d{9}$/', $digits)) {
            return $digits;
        }
        if (preg_match('/^\+989\d{9}$/', $digits)) {
            return '0' . substr($digits, 3);
        }
        if (preg_match('/^00989\d{9}$/', $digits)) {
            return '0' . substr($digits, 4);
        }
        if (preg_match('/^989\d{9}$/', $digits)) {
            return '0' . substr($digits, 2);
        }
        if (preg_match('/^0\d{10}$/', $digits)) {
            return $digits;
        }
        return '';
    }

    private function check_rate_limit($action_prefix = 'inq') {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '0.0.0.0';
        $key = 'derak_rl_' . $action_prefix . '_' . md5($ip);
        $count = (int) get_transient($key);
        if ($count >= 10) {
            return false;
        }
        set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);
        return true;
    }

    private function verify_request_nonce($request_or_data) {
        $nonce = '';
        if ($request_or_data instanceof WP_REST_Request) {
            $nonce = (string) $request_or_data->get_header('X-WP-Nonce');
            if ($nonce === '') {
                $params = (array) $request_or_data->get_json_params();
                $nonce = isset($params['inquiryNonce']) ? (string) $params['inquiryNonce'] : '';
            }
            if ($nonce !== '' && (wp_verify_nonce($nonce, 'wp_rest') || wp_verify_nonce($nonce, 'deraak_submit_inquiry'))) {
                return true;
            }
        }
        if (is_array($request_or_data) && !empty($request_or_data['inquiryNonce'])) {
            $n = sanitize_text_field((string) $request_or_data['inquiryNonce']);
            if (wp_verify_nonce($n, 'deraak_submit_inquiry') || wp_verify_nonce($n, 'wp_rest')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Send SMS via Kavenegar Verify Lookup API
     */
    private function call_kavenegar_verify($receptor, $template, $token, $token2 = '', $token3 = '') {
        $api_key = trim((string) get_option('derak_kavenegar_api_key', ''));
        if ($api_key === '') {
            return array(
                'ok'      => true,
                'sandbox' => true,
                'message' => 'ارسال آزمایشی (کلید کاوه‌نگار در تنظیمات دراک وارد نشده است)',
            );
        }

        $url = 'https://api.kavenegar.com/v1/' . rawurlencode($api_key) . '/verify/lookup.json';
        $body = array(
            'receptor' => $receptor,
            'template' => $template,
            'token'    => preg_replace('/\s+/', '_', trim((string) $token)),
        );
        if ($token2 !== '') {
            $body['token2'] = preg_replace('/\s+/', '_', mb_substr(trim((string) $token2), 0, 30));
        }
        if ($token3 !== '') {
            $body['token3'] = preg_replace('/\s+/', '_', mb_substr(trim((string) $token3), 0, 30));
        }

        $res = wp_remote_post($url, array(
            'timeout' => 10,
            'body'    => $body,
        ));

        if (is_wp_error($res)) {
            return array(
                'ok'      => false,
                'sandbox' => false,
                'message' => 'خطا در ارتباط با وب‌سرویس کاوه‌نگار',
            );
        }

        $code = wp_remote_retrieve_response_code($res);
        $json = json_decode(wp_remote_retrieve_body($res), true);
        $status = isset($json['return']['status']) ? (int) $json['return']['status'] : $code;

        if ($status === 200) {
            return array(
                'ok'      => true,
                'sandbox' => false,
                'message' => 'پیامک با موفقیت ارسال شد',
            );
        }

        return array(
            'ok'      => false,
            'sandbox' => false,
            'message' => isset($json['return']['message']) ? sanitize_text_field($json['return']['message']) : 'خطای کاوه‌نگار کد ' . $status,
        );
    }

    /**
     * Send 5-digit OTP for Consultation Form
     */
    public function handle_rest_send_otp(WP_REST_Request $request) {
        if (!$this->check_rate_limit('otp')) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'تعداد درخواست‌ها بیش از حد مجاز است؛ چند دقیقه دیگر تلاش کنید.'), 429);
        }

        $body = (array) $request->get_json_params();
        if (!empty($body['website_hp'])) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'درخواست نامعتبر است.'), 400);
        }

        $name    = sanitize_text_field(mb_substr(trim((string) ($body['name'] ?? '')), 0, 80));
        $phone   = self::normalize_phone($body['phone'] ?? '');
        $message = sanitize_textarea_field(mb_substr(trim((string) ($body['message'] ?? '')), 0, 600));

        if (mb_strlen($name) < 2) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'نام و نام خانوادگی را وارد کنید'), 400);
        }
        if ($phone === '' || strpos($phone, '09') !== 0) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'شماره موبایل معتبر وارد کنید (مثال: 09123456789)'), 400);
        }
        if (mb_strlen($message) < 5) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'شرح نیاز فنی را وارد کنید (حداقل ۵ کاراکتر)'), 400);
        }

        $otp_code = (string) wp_rand(10000, 99999);
        $exp      = time() + 120;
        $secret   = wp_salt('auth');
        $sig      = hash_hmac('sha256', $phone . ':' . $otp_code . ':' . $exp, $secret);
        $token    = base64_encode(wp_json_encode(array('phone' => $phone, 'exp' => $exp, 'sig' => $sig)));

        $template = get_option('derak_kavenegar_otp_template', 'derak-verify') ?: 'derak-verify';
        $sms = $this->call_kavenegar_verify($phone, $template, $otp_code);

        if (!$sms['ok']) {
            return new WP_REST_Response(array('ok' => false, 'error' => $sms['message']), 502);
        }

        return new WP_REST_Response(array(
            'ok'          => true,
            'phone'       => $phone,
            'otpToken'    => $token,
            'expiresIn'   => 120,
            'sandbox'     => $sms['sandbox'],
            'sandboxCode' => $sms['sandbox'] ? $otp_code : null,
        ), 200);
    }

    /**
     * Verify OTP & Store Consultation Inquiry in WordPress + Notify Admin via Kavenegar
     */
    public function handle_rest_verify_otp(WP_REST_Request $request) {
        $body      = (array) $request->get_json_params();
        $phone     = self::normalize_phone($body['phone'] ?? '');
        $otp_code  = preg_replace('/\D/', '', self::normalize_phone($body['otpCode'] ?? ''));
        $raw_tok   = (string) ($body['otpToken'] ?? '');

        $decoded = json_decode((string) base64_decode($raw_tok, true), true);
        if (!is_array($decoded) || empty($decoded['phone']) || empty($decoded['exp']) || empty($decoded['sig'])) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'توکن اعتبارسنجی نامعتبر است؛ مجدداً کد دریافت کنید.'), 400);
        }

        if (time() > (int) $decoded['exp']) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'مهلت زمانی کد تایید به پایان رسیده است.'), 400);
        }

        $expected_sig = hash_hmac('sha256', $phone . ':' . $otp_code . ':' . (int) $decoded['exp'], wp_salt('auth'));
        if (!hash_equals($expected_sig, (string) $decoded['sig'])) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'کد تایید وارد شده اشتباه است.'), 400);
        }

        return $this->process_and_store_inquiry($body, true);
    }

    public function handle_rest_inquiry(WP_REST_Request $request) {
        $raw_body = $request->get_body();
        if (strlen($raw_body) > 32768) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'حجم درخواست بیش از حد مجاز است.'), 413);
        }

        $data = (array) $request->get_json_params();
        if (!$this->verify_request_nonce($request) && !$this->verify_request_nonce($data)) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'نشست امنیتی منقضی شده است؛ صفحه را بازخوانی کنید.'), 403);
        }

        return $this->process_and_store_inquiry($data, false);
    }

    public function handle_ajax_inquiry() {
        $raw = isset($_POST['payload']) ? wp_unslash($_POST['payload']) : '';
        if (strlen($raw) > 32768) {
            wp_send_json(array('ok' => false, 'error' => 'حجم درخواست بیش از حد مجاز است.'), 413);
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !$this->verify_request_nonce($data)) {
            wp_send_json(array('ok' => false, 'error' => 'توکن امنیتی معتبر نیست.'), 403);
        }
        $res = $this->process_and_store_inquiry($data, false);
        wp_send_json($res->get_data(), $res->get_status());
    }

    private function process_and_store_inquiry(array $data, $otp_verified = false) {
        if (!$this->check_rate_limit('inq')) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'تعداد درخواست‌ها بیش از حد مجاز است؛ لطفاً چند دقیقه دیگر تلاش کنید.'), 429);
        }

        if (!empty($data['website_hp'])) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'درخواست نامعتبر تشخیص داده شد.'), 400);
        }

        $name    = sanitize_text_field(mb_substr(trim((string) ($data['name'] ?? '')), 0, 80));
        $company = sanitize_text_field(mb_substr(trim((string) ($data['company'] ?? '')), 0, 120));
        $phone   = self::normalize_phone($data['phone'] ?? '');
        $site    = sanitize_text_field(mb_substr(trim((string) ($data['site'] ?? '')), 0, 120));
        $part    = sanitize_text_field(mb_substr(trim((string) ($data['part'] ?? '')), 0, 60));
        $message = sanitize_textarea_field(mb_substr(trim((string) ($data['message'] ?? '')), 0, 800));

        if ($name === '' && $company !== '') {
            $name = $company;
        }

        if (mb_strlen($name) < 2) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'لطفاً نام درخواست‌دهنده یا نام شرکت را وارد نمایید.'), 400);
        }
        if ($phone === '') {
            return new WP_REST_Response(array('ok' => false, 'error' => 'لطفاً شماره تماس معتبر وارد نمایید (مثال: 09123456789).'), 400);
        }

        $raw_items = isset($data['items']) && is_array($data['items']) ? array_slice($data['items'], 0, 50) : array();
        $clean_items = array();
        foreach ($raw_items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $clean_items[] = array(
                'id'       => preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($item['id'] ?? '')),
                'model'    => sanitize_text_field(mb_substr((string) ($item['model'] ?? ''), 0, 80)),
                'title'    => sanitize_text_field(mb_substr((string) ($item['title'] ?? ''), 0, 200)),
                'quantity' => max(1, min(9999, (int) ($item['quantity'] ?? 1))),
                'notes'    => sanitize_text_field(mb_substr((string) ($item['notes'] ?? ''), 0, 300)),
                'urgent'   => !empty($item['urgent']),
            );
        }

        if (empty($clean_items) && mb_strlen($message) < 5) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'لیست اقلام یا شرح نیاز فنی را وارد نمایید.'), 400);
        }

        $tracking_code = 'DRK-' . strtoupper(substr(md5(uniqid((string) wp_rand(), true)), 0, 6));
        $post_title    = sprintf('%s — %s (%s)', $tracking_code, $name, $phone);

        $post_id = wp_insert_post(array(
            'post_type'   => 'derak_inquiry',
            'post_status' => 'publish',
            'post_title'  => $post_title,
        ), true);

        if (is_wp_error($post_id) || !$post_id) {
            return new WP_REST_Response(array('ok' => false, 'error' => 'خطا در ذخیره‌سازی استعلام در پایگاه داده وردپرس.'), 500);
        }

        update_post_meta($post_id, '_derak_inq_tracking', $tracking_code);
        update_post_meta($post_id, '_derak_inq_name', $name);
        update_post_meta($post_id, '_derak_inq_company', $company);
        update_post_meta($post_id, '_derak_inq_phone', $phone);
        update_post_meta($post_id, '_derak_inq_site', $site);
        update_post_meta($post_id, '_derak_inq_part', $part);
        update_post_meta($post_id, '_derak_inq_message', $message);
        update_post_meta($post_id, '_derak_inq_items', $clean_items);
        update_post_meta($post_id, '_derak_inq_otp_verified', $otp_verified ? '1' : '0');

        // Send Admin Notification Email via wp_mail
        $admin_email = sanitize_email((string) get_option('derak_inquiry_email', get_option('admin_email')));
        $mail_sent   = false;
        if (is_email($admin_email)) {
            $subject = sprintf('[دراک] درخواست استعلام جدید %s از %s', $tracking_code, $name);
            $lines   = array(
                'کد رهگیری: ' . $tracking_code,
                'نام درخواست‌دهنده: ' . $name,
                'شرکت / پروژه: ' . ($company ?: '-'),
                'شماره تماس: ' . $phone . ($otp_verified ? ' (تایید شده با پیامک)' : ''),
                'سایت تحویل: ' . ($site ?: '-'),
                'پارت‌نامبر: ' . ($part ?: '-'),
                '',
            );
            if ($message !== '') {
                $lines[] = 'شرح نیاز فنی:';
                $lines[] = $message;
                $lines[] = '';
            }
            if (!empty($clean_items)) {
                $lines[] = 'اقلام استعلام شده:';
                foreach ($clean_items as $i => $it) {
                    $lines[] = sprintf(
                        '%d. %s | %s × %d %s %s',
                        $i + 1,
                        $it['model'] ?: '-',
                        $it['title'],
                        $it['quantity'],
                        $it['urgent'] ? '[فوری]' : '',
                        $it['notes'] ? '(' . $it['notes'] . ')' : ''
                    );
                }
            }
            $mail_sent = wp_mail($admin_email, $subject, implode("\n", $lines));
        }

        // Send Kavenegar Admin Notification if configured
        $admin_phone    = self::normalize_phone(get_option('derak_kavenegar_admin_phone', ''));
        $admin_template = get_option('derak_kavenegar_admin_template', 'derak-admin-lead') ?: 'derak-admin-lead';
        if ($admin_phone !== '') {
            $summary_tok = $part ?: (!empty($clean_items) ? count($clean_items) . '_قلم' : $tracking_code);
            $this->call_kavenegar_verify($admin_phone, $admin_template, $phone, $name, $summary_tok);
        }

        return new WP_REST_Response(array(
            'ok'           => true,
            'trackingCode' => $tracking_code,
            'inquiryId'    => (int) $post_id,
            'mailSent'     => (bool) $mail_sent,
            'message'      => 'درخواست شما با موفقیت در سامانه ثبت شد.',
        ), 200);
    }

    /**
     * Shortcode: [derak_rfq_basket]
     */
    public function render_rfq_shortcode() {
        ob_start();
        ?>
        <div id="derak-rfq-app" class="derak-rfq-container" dir="rtl">
            <div class="derak-rfq-card">
                <div class="derak-rfq-header">
                    <div>
                        <h2 class="derak-rfq-heading">لیست اقلام استعلام قیمت و مشخصات فنی</h2>
                        <p class="derak-rfq-subheading">اقلام انتخابی شما در مرورگر نگهداری شده و پس از تایید، در پایگاه داده سرور ثبت می‌شوند.</p>
                    </div>
                    <button type="button" id="derak-rfq-clear-btn" class="derak-btn-text-danger">
                        پاکسازی کل لیست
                    </button>
                </div>

                <!-- Offline Notice -->
                <div id="derak-rfq-offline-note" class="derak-rfq-alert-warning" style="display:none;" role="status">
                    شما در حالت آفلاین هستید. می‌توانید اقلام و توضیحات را ویرایش، چاپ یا کپی کنید؛ اما ثبت نهایی در سرور نیازمند اتصال اینترنت است.
                </div>

                <!-- Empty State -->
                <div id="derak-rfq-empty" style="text-align:center; padding:40px 20px; display:none;">
                    <h3 style="color:var(--derak-primary); font-size:16px; margin:0 0 8px 0;">لیست استعلام شما خالی است</h3>
                    <p style="font-size:13px; color:var(--derak-text-muted); margin:0 0 16px 0;">برای ثبت درخواست استعلام، تجهیزات مورد نظر خود را از کاتالوگ با دکمه (+) اضافه کنید.</p>
                    <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/?post_type=product')); ?>" class="derak-btn derak-btn-primary">
                        مشاهده کاتالوگ تجهیزات
                    </a>
                </div>

                <!-- Success Confirmation State -->
                <div id="derak-rfq-success" class="derak-rfq-success-box" style="display:none;" role="status" aria-live="polite">
                    <h3 style="margin:0 0 6px 0; color:var(--derak-primary); font-size:16px;">درخواست استعلام شما با موفقیت در سرور ثبت شد</h3>
                    <p style="margin:0 0 12px 0; font-size:13px; color:var(--derak-text-muted);">
                        کد رهگیری ثبت شده در سامانه: <strong id="derak-rfq-tracking-code" dir="ltr" style="color:var(--derak-primary); font-size:15px;"></strong>
                    </p>
                    <button type="button" id="derak-rfq-new-btn" class="derak-btn derak-btn-primary">
                        ثبت لیست استعلام جدید
                    </button>
                </div>

                <!-- Active Basket Content -->
                <div id="derak-rfq-content">
                    <div id="derak-rfq-items-list" style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;"></div>

                    <form id="derak-rfq-submit-form" class="derak-rfq-buyer-box" novalidate>
                        <h4 style="color:var(--derak-primary); margin:0 0 12px 0; font-size:14px;">اطلاعات درخواست‌دهنده و پروژه</h4>
                        <input type="text" id="derak-rfq-hp" name="website_hp" tabindex="-1" autocomplete="off" style="display:none;" aria-hidden="true" />
                        <div class="derak-rfq-form-grid">
                            <div>
                                <label for="derak-rfq-name" class="derak-field-label">نام و نام خانوادگی (الزامی):</label>
                                <input type="text" id="derak-rfq-name" maxlength="80" required placeholder="مهندس..." class="derak-input" />
                            </div>
                            <div>
                                <label for="derak-rfq-company" class="derak-field-label">نام شرکت یا پروژه:</label>
                                <input type="text" id="derak-rfq-company" maxlength="120" placeholder="پتروشیمی / پالایشگاه / کارخانه..." class="derak-input" />
                            </div>
                            <div>
                                <label for="derak-rfq-phone" class="derak-field-label">شماره تماس پیگیری (الزامی):</label>
                                <input type="tel" id="derak-rfq-phone" maxlength="20" required placeholder="0912..." dir="ltr" class="derak-input" />
                            </div>
                            <div>
                                <label for="derak-rfq-site" class="derak-field-label">محل تحویل / سایت فرآیندی:</label>
                                <select id="derak-rfq-site" class="derak-input">
                                    <option value="تهران">تهران</option>
                                    <option value="عسلویه - پارس جنوبی">عسلویه - پارس جنوبی</option>
                                    <option value="ماهشهر - منطقه ویژه پتروشیمی">ماهشهر - منطقه ویژه پتروشیمی</option>
                                    <option value="اصفهان / مرکزی">اصفهان / مرکزی</option>
                                    <option value="سایر مراکز صنعتی">سایر مراکز صنعتی</option>
                                </select>
                            </div>
                        </div>

                        <p id="derak-rfq-feedback" role="alert" style="display:none; margin:12px 0 0 0; font-size:13px; font-weight:700; color:var(--color-danger);"></p>

                        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:16px;">
                            <button type="submit" id="derak-rfq-submit-btn" class="derak-btn derak-btn-primary" style="flex:2; min-width:220px;">
                                <span>ثبت نهایی استعلام در سامانه</span>
                            </button>

                            <button type="button" id="derak-rfq-copy-btn" class="derak-btn derak-btn-outline" style="flex:1;">
                                <span>کپی متن لیست</span>
                            </button>

                            <button type="button" id="derak-rfq-print-btn" class="derak-btn derak-btn-outline">
                                <span>چاپ برگ استعلام</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [derak_consultation_form]
     * Consultation form with Kavenegar OTP verification (Digits-style inline OTP step)
     */
    public function render_consultation_shortcode() {
        ob_start();
        ?>
        <div id="derak-consult-widget" class="derak-consult-card" dir="rtl">
            <form id="derak-consult-step-details" class="derak-consult-form" novalidate>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:17px; font-weight:700;">فرم درخواست مشاوره فنی</h3>
                    <span style="font-size:11px; color:var(--derak-accent);">اعتبارسنجی پیامکی کاوه‌نگار</span>
                </div>
                <input type="text" id="derak-consult-hp" name="website_hp" tabindex="-1" autocomplete="off" style="display:none;" aria-hidden="true" />
                <div class="derak-rfq-form-grid">
                    <div>
                        <label for="derak-consult-name" class="derak-field-label-dark">نام و نام خانوادگی</label>
                        <input type="text" id="derak-consult-name" maxlength="80" placeholder="نام و نام خانوادگی" class="derak-input-dark" />
                    </div>
                    <div>
                        <label for="derak-consult-company" class="derak-field-label-dark">نام شرکت</label>
                        <input type="text" id="derak-consult-company" maxlength="120" placeholder="نام شرکت یا واحد صنعتی" class="derak-input-dark" />
                    </div>
                    <div>
                        <label for="derak-consult-phone" class="derak-field-label-dark">شماره موبایل</label>
                        <input type="tel" id="derak-consult-phone" maxlength="20" placeholder="09xx xxx xxxx" dir="ltr" class="derak-input-dark" />
                    </div>
                    <div>
                        <label for="derak-consult-part" class="derak-field-label-dark">پارت‌نامبر / مدل تجهیز</label>
                        <input type="text" id="derak-consult-part" maxlength="60" placeholder="Part number (e.g. FMR53)" dir="ltr" class="derak-input-dark" />
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <label for="derak-consult-msg" class="derak-field-label-dark">شرح نیاز فنی</label>
                    <textarea id="derak-consult-msg" rows="4" maxlength="600" placeholder="سیال، دما، فشار، اتصال فرآیندی، استاندارد مورد نیاز..." class="derak-textarea-dark"></textarea>
                </div>
                <p id="derak-consult-err" role="alert" style="display:none; color:var(--derak-accent); font-size:13px; margin:8px 0 0 0;"></p>
                <button type="submit" id="derak-consult-send-otp-btn" class="derak-btn derak-btn-accent" style="width:100%; height:44px; margin-top:14px;">
                    دریافت کد تایید و ثبت درخواست مشاوره
                </button>
            </form>

            <form id="derak-consult-step-otp" style="display:none;" novalidate>
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.12); padding-bottom:10px; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:16px; font-weight:700;">تایید شماره موبایل</h3>
                    <button type="button" id="derak-consult-edit-phone-btn" style="background:none; border:none; color:var(--derak-accent); font-size:12px; cursor:pointer; text-decoration:underline;">
                        ویرایش شماره یا اطلاعات
                    </button>
                </div>
                <p style="font-size:13px; margin:0 0 10px 0;">
                    کد تایید ۵ رقمی به شماره <strong id="derak-consult-target-phone" dir="ltr" style="color:var(--derak-accent);"></strong> ارسال شد.
                </p>
                <div id="derak-consult-sandbox-hint" style="display:none; background:rgba(251,218,88,0.15); border:1px solid var(--derak-accent); border-radius:6px; padding:8px 12px; font-size:12px; margin-bottom:12px;"></div>
                <div style="margin:12px 0;">
                    <label for="derak-consult-otp-input" class="derak-field-label-dark">کد تایید ۵ رقمی:</label>
                    <input type="text" id="derak-consult-otp-input" inputmode="numeric" maxlength="5" dir="ltr" placeholder="-----" class="derak-input-dark" style="text-align:center; font-size:20px; letter-spacing:8px; font-weight:700; height:48px;" />
                </div>
                <p id="derak-consult-otp-err" role="alert" style="display:none; color:var(--derak-accent); font-size:13px; margin:8px 0;"></p>
                <button type="submit" id="derak-consult-verify-btn" class="derak-btn derak-btn-accent" style="width:100%; height:44px; margin-top:8px;">
                    تایید کد و ارسال نهایی به مدیر سایت
                </button>
            </form>

            <div id="derak-consult-step-success" style="display:none; padding:12px 0;">
                <h3 style="margin:0 0 8px 0; color:var(--derak-accent); font-size:17px;">درخواست مشاوره ثبت و پیامک شد</h3>
                <p style="font-size:13px; line-height:1.7; margin:0 0 12px 0;">
                    شماره همراه شما تایید شد و درخواست مشاوره با کد رهگیری <strong id="derak-consult-tracking" dir="ltr" style="color:var(--derak-accent);"></strong> در سامانه ثبت گردید.
                </p>
                <button type="button" id="derak-consult-reset-btn" class="derak-btn derak-btn-accent">
                    ثبت درخواست جدید
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new Deraak_RFQ_Handler();
