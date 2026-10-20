<?php
/**
 * Elementor Widget: Deraak Bento Categories
 * Queries real WooCommerce / WordPress product_cat taxonomy terms
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
        $terms = taxonomy_exists('product_cat') ? get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'parent'     => 0,
        )) : array();
        ?>
        <section class="derak-bento-section">
            <?php if (!empty($settings['title'])) : ?>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h2 style="font-size:17px; font-weight:700; color:var(--derak-primary); margin:0;">
                        <?php echo esc_html($settings['title']); ?>
                    </h2>
                    <span style="font-size:12px; color:var(--derak-stone-muted);">انتخاب رسته تجهیزات</span>
                </div>
            <?php endif; ?>

            <div class="derak-bento-grid">
                <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
                    <?php foreach ($terms as $term) :
                        if ($term->slug === 'uncategorized') {
                            continue;
                        }
                        $term_link = get_term_link($term);
                        if (is_wp_error($term_link)) {
                            continue;
                        }
                        ?>
                        <a href="<?php echo esc_url($term_link); ?>" class="derak-bento-card">
                            <div class="derak-bento-header">
                                <span class="derak-bento-title" style="margin:0;"><?php echo esc_html($term->name); ?></span>
                                <span class="derak-bento-count"><?php echo (int) $term->count; ?> تجهیز</span>
                            </div>
                            <?php if (!empty($term->description)) : ?>
                                <p class="derak-bento-desc" style="margin-top:8px;"><?php echo esc_html($term->description); ?></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="derak-bento-card">
                        <h3 class="derak-bento-title">هنوز دسته‌بندی محصولی ثبت نشده است</h3>
                        <p class="derak-bento-desc">از بخش «محصولات &gt; دسته‌بندی‌ها» یا درون‌ریز نمونه در «تنظیمات دراک» دسته‌بندی‌ها را اضافه کنید.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
