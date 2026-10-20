<?php
/**
 * Brand Taxonomy Registration
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_Brand_Taxonomy {

    public function __construct() {
        add_action('init', array($this, 'register_brand_taxonomy'), 0);
    }

    public function register_brand_taxonomy() {
        $labels = array(
            'name'                       => 'برندهای سازنده',
            'singular_name'              => 'برند',
            'menu_name'                  => 'برندهای صنعتی',
            'all_items'                  => 'همه برندها',
            'parent_item'                => 'برند والد',
            'parent_item_colon'          => 'برند والد:',
            'new_item_name'              => 'نام برند جدید',
            'add_new_item'               => 'افزودن برند جدید',
            'edit_item'                  => 'ویرایش برند',
            'update_item'                => 'به‌روزرسانی برند',
            'view_item'                  => 'مشاهده برند',
            'separate_items_with_commas' => 'جداسازی با کاما',
            'add_or_remove_items'        => 'افزودن یا حذف برندها',
            'choose_from_most_used'      => 'انتخاب از پرکاربردترین برندها',
            'popular_items'              => 'برندهای محبوب',
            'search_items'               => 'جستجوی برندها',
            'not_found'                  => 'برندی یافت نشد',
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'show_in_rest'      => true,
            'rewrite'           => array('slug' => 'brand', 'with_front' => false),
        );

        register_taxonomy('product_brand', array('product'), $args);
    }
}

new Deraak_Brand_Taxonomy();
