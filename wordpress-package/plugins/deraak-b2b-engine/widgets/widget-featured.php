<?php
/**
 * Elementor Widget: Deraak Featured Products Carousel
 * Safe with or without WooCommerce active
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Deraak_Widget_Featured extends Widget_Base {

    public function get_name() {
        return 'derak_featured_products';
    }

    public function get_title() {
        return 'کاروسل محصولات منتخب دراک';
    }

    public function get_icon() {
        return 'eicon-star-o';
    }

    public function get_categories() {
        return array('deraak-elements');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            array('label' => 'تنظیمات محصولات منتخب')
        );

        $this->add_control(
            'title',
            array(
                'label'   => 'عنوان بخش',
                'type'    => Controls_Manager::TEXT,
                'default' => 'تجهیزات کاتالوگ مهندسی',
            )
        );

        $this->add_control(
            'posts_per_page',
            array(
                'label'   => 'تعداد محصولات',
                'type'    => Controls_Manager::NUMBER,
                'default' => 8,
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $per_page = absint(isset($settings['posts_per_page']) ? $settings['posts_per_page'] : 8);
        $per_page = max(1, min(24, $per_page));

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $per_page,
            'no_found_rows'  => true,
            'post_status'    => 'publish',
        );

        $query = new WP_Query($args);
        $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : get_post_type_archive_link('product');
        ?>
        <section class="derak-featured-section" style="margin:20px 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h2 style="font-size:17px; font-weight:700; color:var(--derak-primary); margin:0;">
                    <?php echo esc_html($settings['title']); ?>
                </h2>
                <?php if ($shop_url) : ?>
                    <a href="<?php echo esc_url($shop_url); ?>" style="font-size:13px; color:var(--derak-blue-vibrant); font-weight:700; text-decoration:none;">
                        مشاهده همه کاتالوگ ←
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($query->have_posts()) : ?>
                <div class="derak-products-grid">
                    <?php
                    while ($query->have_posts()) :
                        $query->the_post();
                        if (function_exists('wc_get_template_part')) {
                            wc_get_template_part('content', 'product');
                        } else {
                            get_template_part('woocommerce/content', 'product');
                        }
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            <?php else : ?>
                <p style="font-size:13px; color:var(--derak-stone-muted);">هنوز محصولی در کاتالوگ ثبت نشده است.</p>
            <?php endif; ?>
        </section>
        <?php
    }
}
