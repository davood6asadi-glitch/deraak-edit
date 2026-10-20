<?php
/**
 * Elementor Widget: Deraak Featured Products Carousel
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
                'default' => 'محصولات منتخب مهندسی',
            )
        );

        $this->add_control(
            'posts_per_page',
            array(
                'label'   => 'تعداد محصولات',
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $per_page = absint(isset($settings['posts_per_page']) ? $settings['posts_per_page'] : 6);
        $per_page = max(1, min(24, $per_page));

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $per_page,
            'no_found_rows'  => true,
            'post_status'    => 'publish',
        );

        $query = new WP_Query($args);
        $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
        ?>
        <section class="derak-featured-section" style="margin:20px 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:18px;">⭐</span>
                    <h2 style="font-size:16px; font-weight:800; color:var(--derak-primary); margin:0;">
                        <?php echo esc_html($settings['title']); ?>
                    </h2>
                </div>
                <a href="<?php echo esc_url($shop_url); ?>" style="font-size:12px; color:var(--derak-blue-vibrant); font-weight:700; text-decoration:none;">
                    مشاهده همه کاتالوگ ←
                </a>
            </div>

            <?php if ($query->have_posts()) : ?>
                <div style="display:flex; gap:14px; overflow-x:auto; padding-bottom:12px; scrollbar-width:none;" class="derak-brands-row">
                    <?php
                    while ($query->have_posts()) :
                        $query->the_post();
                        ?>
                        <div style="min-width:275px; max-width:285px; flex-shrink:0;">
                            <?php wc_get_template_part('content', 'product'); ?>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            <?php else : ?>
                <p style="font-size:13px; color:var(--derak-stone-muted);">محصولی یافت نشد.</p>
            <?php endif; ?>
        </section>
        <?php
    }
}
