<?php
/**
 * Elementor Widget: Deraak PWA Offline Sync Card
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

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
        <section class="derak-pwa-banner" style="background-color:#031A54; color:#FFFFFF; border-radius:12px; padding:20px; border:1px solid rgba(19, 82, 223, 0.4); margin:24px 0; box-shadow:0 4px 14px rgba(3,26,84,0.2);">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
                <div>
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="font-size:22px;">💾</span>
                        <h3 style="font-size:16px; font-weight:800; color:#FFFFFF; margin:0;">
                            دسترسی آفلاین به کاتالوگ و دیتاشیت‌ها (PWA)
                        </h3>
                    </div>
                    <p style="font-size:12px; color:#C6CDFE; line-height:1.6; margin:0; max-width:650px;">
                        طراحی شده برای محیط‌های صنعتی، پالایشگاه‌ها و سکوهای نفتی پارس جنوبی با پوشش ضعیف اینترنت. اطلاعات کلیدی کدهای سفارش و مشخصات تجهیزات روی حافظه دستگاه در دسترس خواهد بود.
                    </p>
                </div>
            </div>

            <!-- Sync Status Box -->
            <div style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:12px; margin:14px 0;">
                <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:6px;">
                    <span>وضعیت پایگاه داده محلی (IndexedDB):</span>
                    <span id="derak-pwa-sync-label" style="color:#FBDA58; font-weight:bold; font-family:var(--derak-font-mono);">کاتالوگ آماده است</span>
                </div>
                <div style="width:100%; height:8px; background:rgba(255,255,255,0.2); border-radius:4px; overflow:hidden;">
                    <div id="derak-pwa-progress" style="width:100%; height:100%; background:#FBDA58; border-radius:4px; transition:width 0.4s;"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                <button type="button" id="derak-pwa-sync-btn" class="derak-btn" style="background:#FFFFFF; color:#031A54; width:100%;">
                    <span>🔄 همگام‌سازی کاتالوگ آفلاین</span>
                </button>

                <button type="button" id="derak-pwa-install-btn" class="derak-btn derak-btn-accent" style="width:100%;">
                    <span>📲 نصب نسخه پیشرو (PWA)</span>
                </button>
            </div>
        </section>
        <?php
    }
}
