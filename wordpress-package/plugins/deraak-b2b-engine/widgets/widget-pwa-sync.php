<?php
/**
 * Elementor Widget: Deraak PWA Offline Sync & Local Catalog Browser Card
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;

class Deraak_Widget_PWA_Sync extends Widget_Base {

    public function get_name() {
        return 'derak_pwa_sync_card';
    }

    public function get_title() {
        return 'کارت دسترسی آفلاین و نصب PWA دراک';
    }

    public function get_icon() {
        return 'eicon-download-bold';
    }

    public function get_categories() {
        return array('deraak-elements');
    }

    protected function render() {
        ?>
        <section class="derak-pwa-banner" style="background-color:var(--blue-950); color:var(--slate-50); border-radius:12px; padding:20px; border:1px solid var(--blue-800); margin:24px 0;">
            <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-start; gap:16px;">
                <div>
                    <h3 style="font-size:17px; font-weight:700; color:var(--slate-50); margin:0 0 6px 0;">
                        دسترسی آفلاین به کاتالوگ مهندسی (PWA)
                    </h3>
                    <p style="font-size:13px; color:var(--blue-100); line-height:1.7; margin:0; max-width:650px;">
                        با همگام‌سازی کاتالوگ، مشخصات فنی تجهیزات در پایگاه داده محلی مرورگر (IndexedDB) ذخیره شده و در زمان اختلال یا قطع اینترنت بین‌الملل قابل جستجو و مرور خواهد بود.
                    </p>
                </div>
            </div>

            <!-- Sync Status & Last Sync Timestamp -->
            <div style="background:var(--blue-900); border:1px solid var(--blue-800); border-radius:8px; padding:12px 14px; margin:14px 0;">
                <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:8px; font-size:12px; margin-bottom:8px;">
                    <span>وضعیت حافظه آفلاین (IndexedDB): <strong id="derak-pwa-sync-label" style="color:var(--yellow-400);">بررسی وضعیت...</strong></span>
                    <span id="derak-pwa-last-sync" style="color:var(--blue-300);">آخرین همگام‌سازی: ثبت نشده</span>
                </div>
                <div id="derak-pwa-stale-warning" style="display:none; color:var(--yellow-400); font-size:11px; margin-bottom:8px;">
                    توجه: بیش از ۲۴ ساعت از آخرین همگام‌سازی گذشته یا در حالت آفلاین هستید؛ اطلاعات نمایش داده شده ممکن است قدیمی باشد.
                </div>
                <div style="width:100%; height:6px; background:var(--blue-800); border-radius:4px; overflow:hidden;">
                    <div id="derak-pwa-progress" style="width:100%; height:100%; background:var(--yellow-500); border-radius:4px;"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display:flex; flex-wrap:wrap; gap:10px;">
                <button type="button" id="derak-pwa-sync-btn" class="derak-btn" style="background:var(--blue-500); color:var(--slate-50);">
                    <span>همگام‌سازی کاتالوگ آفلاین</span>
                </button>

                <button type="button" id="derak-pwa-browse-btn" class="derak-btn derak-btn-outline" style="background:var(--blue-900); color:var(--slate-50); border-color:var(--blue-700);">
                    <span>مرور و جستجوی کاتالوگ آفلاین</span>
                </button>

                <button type="button" id="derak-pwa-install-btn" class="derak-btn derak-btn-accent" style="background:var(--yellow-500); color:var(--yellow-950);">
                    <span>نصب وب‌اپلیکیشن (PWA)</span>
                </button>
            </div>

            <!-- Offline Interactive Catalog Browser Container -->
            <div id="derak-offline-catalog-panel" style="display:none; margin-top:18px; padding-top:16px; border-top:1px solid var(--blue-800);">
                <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
                    <input type="search" id="derak-offline-search-input" placeholder="جستجو در کاتالوگ آفلاین (نام تجهیز، کد مدل، برند یا مشخصه فنی)..." style="flex:2; min-width:220px; height:40px; border-radius:6px; border:1px solid var(--blue-700); background:var(--blue-900); color:var(--slate-50); padding:0 12px; font-size:13px;" />
                    <select id="derak-offline-brand-filter" style="flex:1; min-width:150px; height:40px; border-radius:6px; border:1px solid var(--blue-700); background:var(--blue-900); color:var(--slate-50); padding:0 10px; font-size:13px;">
                        <option value="">همه برندها</option>
                    </select>
                    <select id="derak-offline-cat-filter" style="flex:1; min-width:150px; height:40px; border-radius:6px; border:1px solid var(--blue-700); background:var(--blue-900); color:var(--slate-50); padding:0 10px; font-size:13px;">
                        <option value="">همه دسته‌ها</option>
                    </select>
                </div>
                <div id="derak-offline-catalog-results" class="derak-products-grid"></div>
            </div>
        </section>
        <?php
    }
}
