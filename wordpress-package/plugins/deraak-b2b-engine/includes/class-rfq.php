<?php
/**
 * B2B RFQ (Request For Quotation) Handler
 * Decoupled from Online Payment Gateway
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_RFQ_Handler {

    public function __construct() {
        add_shortcode('derak_rfq_basket', array($this, 'render_rfq_shortcode'));
    }

    public function render_rfq_shortcode() {
        ob_start();
        ?>
        <div id="derak-rfq-app" class="derak-rfq-container">
            <div style="background:#FFFFFF; border:1px solid #DEDCD6; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #EEF0FF; padding-bottom:12px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:20px;">📋</span>
                        <h2 style="font-size:18px; color:#031A54; margin:0; font-weight:800;">
                            لیست اقلام استعلام قیمت رسمی (پیش‌فاکتور صنعتی)
                        </h2>
                    </div>
                    <button type="button" id="derak-rfq-clear-btn" style="background:none; border:none; color:#BA1A1A; font-size:12px; font-weight:bold; cursor:pointer; text-decoration:underline;">
                        پاکسازی کل لیست
                    </button>
                </div>

                <!-- Empty State -->
                <div id="derak-rfq-empty" style="text-align:center; padding:40px 20px; display:none;">
                    <div style="font-size:40px; margin-bottom:12px;">📦</div>
                    <h3 style="color:#031A54; font-size:16px;">لیست استعلام شما خالی است</h3>
                    <p style="font-size:13px; color:#6B6A67;">برای دریافت پیش‌فاکتور رسمی، تجهیزات موردنظر خود را از کاتالوگ با دکمه (+) اضافه کنید.</p>
                    <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop')); ?>" class="derak-btn derak-btn-primary" style="margin-top:10px;">
                        مشاهده کاتالوگ تجهیزات
                    </a>
                </div>

                <!-- Table Container -->
                <div id="derak-rfq-content">
                    <div id="derak-rfq-items-list" style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;">
                        <!-- Injected via JavaScript -->
                    </div>

                    <!-- Project Site and Company Form -->
                    <div style="background:#EEF0FF; border:1px solid #C6CDFE; border-radius:8px; padding:16px; margin-top:20px;">
                        <h4 style="color:#031A54; margin-top:0; font-size:14px;">اطلاعات پروژه و خریدار</h4>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:bold; margin-bottom:4px;">سایت فرآیندی / تحویل:</label>
                                <select id="derak-rfq-site" style="width:100%; height:38px; border-radius:6px; border:1px solid #C6CDFE;">
                                    <option value="عسلویه - فازهای پارس جنوبی">عسلویه - فازهای پارس جنوبی</option>
                                    <option value="ماهشهر - منطقه ویژه اقتصادی پتروشیمی">ماهشهر - منطقه ویژه اقتصادی پتروشیمی</option>
                                    <option value="پالایشگاه نفت اصفهان / تهران">پالایشگاه نفت اصفهان / تهران</option>
                                    <option value="کنگان - پتروپالایش">کنگان - پتروپالایش</option>
                                    <option value="تحویل درب انبار تهران (لاله‌زار)">تحویل درب انبار تهران (لاله‌زار)</option>
                                </select>
                            </div>

                            <div>
                                <label style="display:block; font-size:12px; font-weight:bold; margin-bottom:4px;">نام شرکت یا پروژه:</label>
                                <input type="text" id="derak-rfq-company" placeholder="شرکت نفت و گاز / پتروشیمی..." style="width:100%; height:38px; border-radius:6px; border:1px solid #C6CDFE; padding:0 10px;" />
                            </div>

                            <div>
                                <label style="display:block; font-size:12px; font-weight:bold; margin-bottom:4px;">شماره تماس پیگیری استعلام:</label>
                                <input type="tel" id="derak-rfq-phone" placeholder="۰۹۱۲..." dir="ltr" style="width:100%; height:38px; border-radius:6px; border:1px solid #C6CDFE; padding:0 10px;" />
                            </div>
                        </div>
                    </div>

                    <!-- Output Actions -->
                    <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:20px;">
                        <button type="button" id="derak-rfq-copy-btn" class="derak-btn derak-btn-primary" style="flex:1;">
                            <span>کپی متن استعلام جهت فکس / ایمیل</span>
                        </button>

                        <button type="button" id="derak-rfq-wa-btn" class="derak-btn derak-btn-accent" style="flex:1;">
                            <span>ارسال لیست به واتس‌اپ مهندسی فروش</span>
                        </button>

                        <button type="button" id="derak-rfq-print-btn" class="derak-btn derak-btn-outline" style="width:auto;">
                            <span>چاپ</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new Deraak_RFQ_Handler();
