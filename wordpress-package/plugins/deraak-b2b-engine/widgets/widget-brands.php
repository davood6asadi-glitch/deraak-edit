<?php
/**
 * Elementor Widget: Deraak Brand Scroller
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Deraak_Widget_Brands extends Widget_Base {

    public function get_name() {
        return 'derak_brand_scroller';
    }

    public function get_title() {
        return 'نوار افقی برندهای صنعتی';
    }

    public function get_icon() {
        return 'eicon-tags';
    }

    public function get_categories() {
        return array('deraak-elements');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            array('label' => 'تنظیمات برندها')
        );

        $this->add_control(
            'title',
            array(
                'label'   => 'عنوان بخش',
                'type'    => Controls_Manager::TEXT,
                'default' => 'برندهای تحت پوشش و تأمین',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <section class="derak-brands-section" style="margin:16px 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3 style="font-size:14px; font-weight:700; color:var(--derak-primary); margin:0;">
                    <?php echo esc_html($settings['title']); ?>
                </h3>
                <span style="font-size:11px; color:var(--derak-stone-muted);">اورجینال اروپایی و آسیایی</span>
            </div>

            <div class="derak-brands-row" dir="ltr">
                <?php
                $terms = get_terms(array(
                    'taxonomy'   => 'product_brand',
                    'hide_empty' => false,
                ));

                if (!empty($terms) && !is_wp_error($terms)) :
                    foreach ($terms as $term) :
                        echo '<a href="' . esc_url(get_term_link($term)) . '" class="derak-brand-pill">' . esc_html($term->name) . '</a>';
                    endforeach;
                else :
                    $demo = array('Endress+Hauser', 'ifm electronic', 'PR electronics', 'Siemens', 'ABB', 'WIKA', 'Yokogawa', 'Schneider');
                    foreach ($demo as $b) :
                        echo '<span class="derak-brand-pill">' . esc_html($b) . '</span>';
                    endforeach;
                endif;
                ?>
            </div>
        </section>
        <?php
    }
}
