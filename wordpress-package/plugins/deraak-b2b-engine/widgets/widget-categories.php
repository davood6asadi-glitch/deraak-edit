<?php
/**
 * Elementor Widget: Deraak Bento Categories
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Deraak_Widget_Categories extends Widget_Base {

    public function get_name() {
        return 'derak_bento_categories';
    }

    public function get_title() {
        return 'کاشی‌های دسته‌بندی مهندسی (Bento)';
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return array('deraak-elements');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            array('label' => 'محتوای دسته‌ها')
        );

        $this->add_control(
            'title',
            array(
                'label'   => 'عنوان بخش',
                'type'    => Controls_Manager::TEXT,
                'default' => 'دسته‌بندی‌های تخصصی مهندسی',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
        ?>
        <section class="derak-bento-section">
            <?php if (!empty($settings['title'])) : ?>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:18px;">📑</span>
                        <h2 style="font-size:16px; font-weight:800; color:var(--derak-primary); margin:0;">
                            <?php echo esc_html($settings['title']); ?>
                        </h2>
                    </div>
                    <span style="font-size:11px; color:var(--derak-stone-muted);">انتخاب رسته تجهیزات</span>
                </div>
            <?php endif; ?>

            <div class="derak-bento-grid">
                <a href="<?php echo esc_url(add_query_arg('product_cat', 'instrumentation', $shop_url)); ?>" class="derak-bento-card">
                    <div class="derak-bento-header">
                        <div class="derak-bento-icon">⚙️</div>
                        <span class="derak-bento-count">۱۲۰+ مدل</span>
                    </div>
                    <h3 class="derak-bento-title">ابزار دقیق</h3>
                    <p class="derak-bento-desc">ترانسمیتر، سنسورهای فشار، سطح، جریان و دما</p>
                    <div class="derak-bento-tags">HART, Fieldbus, Profibus-PA</div>
                </a>

                <a href="<?php echo esc_url(add_query_arg('product_cat', 'automation', $shop_url)); ?>" class="derak-bento-card">
                    <div class="derak-bento-header">
                        <div class="derak-bento-icon">💻</div>
                        <span class="derak-bento-count">۸۵+ مدل</span>
                    </div>
                    <h3 class="derak-bento-title">اتوماسیون صنعتی</h3>
                    <p class="derak-bento-desc">پی‌ال‌سی، کارت‌های ورودی/خروجی، رله و ایزولاتور</p>
                    <div class="derak-bento-tags">SIL 2 / SIL 3, IO-Link, Modbus</div>
                </a>

                <a href="<?php echo esc_url(add_query_arg('product_cat', 'electrical', $shop_url)); ?>" class="derak-bento-card">
                    <div class="derak-bento-header">
                        <div class="derak-bento-icon">⚡</div>
                        <span class="derak-bento-count">۶۰+ مدل</span>
                    </div>
                    <h3 class="derak-bento-title">برق صنعتی</h3>
                    <p class="derak-bento-desc">کلید اتوماتیک، کنتاکتور، درایو و حفاظت قدرت</p>
                    <div class="derak-bento-tags">MCCB, VFD Inverters, Overload</div>
                </a>
            </div>
        </section>
        <?php
    }
}
