<?php
/**
 * Elementor Widget: Deraak Recently Viewed Products
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Deraak_Widget_Recently_Viewed extends Widget_Base {

    public function get_name() {
        return 'derak_recently_viewed';
    }

    public function get_title() {
        return 'محصولات اخیراً مشاهده شده (Recently Viewed)';
    }

    public function get_icon() {
        return 'eicon-history';
    }

    public function get_categories() {
        return array('deraak-elements');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            array('label' => 'تنظیمات سوابق بازدید')
        );

        $this->add_control(
            'title',
            array(
                'label'   => 'عنوان بخش',
                'type'    => Controls_Manager::TEXT,
                'default' => 'محصولات اخیراً مشاهده شده شما',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <section class="derak-recently-viewed-section" style="margin:24px 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:18px;">🕒</span>
                    <h2 style="font-size:16px; font-weight:800; color:var(--derak-primary); margin:0;">
                        <?php echo esc_html($settings['title']); ?>
                    </h2>
                    <span id="derak-recent-badge" style="font-size:11px; font-family:var(--derak-font-mono); background:var(--derak-blue-ice); color:var(--derak-blue-deep); border:1px solid var(--derak-blue-periwinkle); padding:2px 8px; border-radius:20px; font-weight:bold; display:none;"></span>
                </div>
                <button type="button" id="derak-clear-recent-btn" style="background:none; border:none; color:#BA1A1A; font-size:11px; font-weight:bold; cursor:pointer; text-decoration:underline; display:none;">
                    پاکسازی تاریخچه
                </button>
            </div>

            <!-- Injected or Empty state via JS -->
            <div id="derak-recent-container" style="display:flex; gap:14px; overflow-x:auto; padding-bottom:12px; scrollbar-width:none;" class="derak-brands-row">
                <!-- Populated dynamically from localStorage derak_recently_viewed -->
            </div>

            <div id="derak-recent-empty" style="background:#FFFFFF; border:1px dashed var(--derak-stone-border); border-radius:12px; padding:24px; text-align:center;">
                <div style="font-size:24px; margin-bottom:6px;">🕒</div>
                <h4 style="color:var(--derak-primary); margin:0 0 4px 0; font-size:14px;">هنوز محصولی را مشاهده نکرده‌اید</h4>
                <p style="font-size:12px; color:var(--derak-stone-muted); margin:0;">
                    با باز کردن دیتاشیت هر تجهیز در کاتالوگ، سابقه بازدید شما در این قسمت برای دسترسی سریع ذخیره می‌شود.
                </p>
            </div>
        </section>
        <?php
    }
}
