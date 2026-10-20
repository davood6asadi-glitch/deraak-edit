<?php
/**
 * Elementor Widget: Deraak Technical Search
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Deraak_Widget_Search extends Widget_Base {

    public function get_name() {
        return 'derak_technical_search';
    }

    public function get_title() {
        return 'جستجوی کدهای فنی دراک';
    }

    public function get_icon() {
        return 'eicon-search';
    }

    public function get_categories() {
        return array('deraak-elements');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            array('label' => 'تنظیمات جستجو')
        );

        $this->add_control(
            'placeholder',
            array(
                'label'       => 'متن راهنما',
                'type'        => Controls_Manager::TEXT,
                'default'     => 'جستجوی مدل فنی سازنده (مانند PR 4116، SI5010، FMR53)...',
                'label_block' => true,
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="derak-search-widget" style="width:100%; max-width:650px; margin:0 auto;">
            <form role="search" method="get" class="derak-search-form" action="<?php echo esc_url(home_url('/')); ?>" style="max-width:100%;">
                <input type="hidden" name="post_type" value="product">
                <input type="search" class="derak-search-input" placeholder="<?php echo esc_attr($settings['placeholder']); ?>" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
                <span class="derak-search-icon">🔍</span>
            </form>
        </div>
        <?php
    }
}
