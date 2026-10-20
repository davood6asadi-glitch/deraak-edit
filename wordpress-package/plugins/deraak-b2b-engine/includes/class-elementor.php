<?php
/**
 * Elementor Free Compatibility & Widget Registration
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_Elementor_Extension {

    public function __construct() {
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_category'));
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
    }

    public function add_elementor_category($elements_manager) {
        $elements_manager->add_category(
            'deraak-elements',
            array(
                'title' => 'المان‌های صنعتی دراک (Deraak B2B)',
                'icon'  => 'fa fa-plug',
            )
        );
    }

    public function register_widgets($widgets_manager) {
        require_once DERAAK_B2B_DIR . 'widgets/widget-search.php';
        require_once DERAAK_B2B_DIR . 'widgets/widget-categories.php';
        require_once DERAAK_B2B_DIR . 'widgets/widget-brands.php';
        require_once DERAAK_B2B_DIR . 'widgets/widget-featured.php';
        require_once DERAAK_B2B_DIR . 'widgets/widget-recently-viewed.php';
        require_once DERAAK_B2B_DIR . 'widgets/widget-pwa-sync.php';

        $widgets_manager->register(new \Deraak_Widget_Search());
        $widgets_manager->register(new \Deraak_Widget_Categories());
        $widgets_manager->register(new \Deraak_Widget_Brands());
        $widgets_manager->register(new \Deraak_Widget_Featured());
        $widgets_manager->register(new \Deraak_Widget_Recently_Viewed());
        $widgets_manager->register(new \Deraak_Widget_PWA_Sync());
    }
}

// Initialize only if Elementor is active
add_action('plugins_loaded', function() {
    if (did_action('elementor/loaded')) {
        new Deraak_Elementor_Extension();
    }
});
