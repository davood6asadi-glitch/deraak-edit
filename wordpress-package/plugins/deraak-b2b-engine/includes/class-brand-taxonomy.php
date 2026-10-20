<?php
/**
 * Brand Taxonomy, WooCommerce CSV Importer Mapping & Technical Search Enhancement
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_Brand_Taxonomy {

    public function __construct() {
        add_action('init', array($this, 'register_brand_taxonomy'), 8);

        // Native WooCommerce CSV Importer support for 'Brands' column & custom meta
        add_filter('woocommerce_csv_product_import_mapping_options', array($this, 'register_csv_mapping_options'));
        add_filter('woocommerce_csv_product_import_mapping_default_columns', array($this, 'register_csv_default_columns'));
        add_action('woocommerce_product_import_inserted_product_object', array($this, 'process_csv_brand_column'), 10, 2);

        // Enhance product search & filtering by SKU, brand, category, and technical meta
        add_action('pre_get_posts', array($this, 'enhance_catalog_query'));
    }

    public function register_brand_taxonomy() {
        if (taxonomy_exists('product_brand')) {
            return;
        }

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

    /**
     * Add 'product_brand' to WooCommerce CSV Importer dropdown
     */
    public function register_csv_mapping_options($options) {
        $options['deraak_product_brand'] = 'برند سازنده (product_brand)';
        return $options;
    }

    /**
     * Automatically map 'Brands' or 'Brand' CSV header to 'deraak_product_brand'
     */
    public function register_csv_default_columns($columns) {
        $columns['Brands'] = 'deraak_product_brand';
        $columns['Brand']  = 'deraak_product_brand';
        $columns['برند']   = 'deraak_product_brand';
        return $columns;
    }

    /**
     * Assign 'product_brand' taxonomy terms when WooCommerce imports a row
     */
    public function process_csv_brand_column($product, $data) {
        if (empty($data['deraak_product_brand']) || !is_object($product) || !method_exists($product, 'get_id')) {
            return;
        }

        $product_id = $product->get_id();
        $raw_brands = explode(',', (string) $data['deraak_product_brand']);
        $term_ids   = array();

        foreach ($raw_brands as $brand_name) {
            $brand_name = sanitize_text_field(trim($brand_name));
            if ($brand_name === '') {
                continue;
            }
            $term = term_exists($brand_name, 'product_brand');
            if (!$term) {
                $term = wp_insert_term($brand_name, 'product_brand');
            }
            if (!is_wp_error($term) && isset($term['term_id'])) {
                $term_ids[] = (int) $term['term_id'];
            }
        }

        if (!empty($term_ids)) {
            wp_set_object_terms($product_id, $term_ids, 'product_brand', false);
        }
    }

    /**
     * Support combined filtering by product_cat + product_brand and SKU/model search
     */
    public function enhance_catalog_query($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        $post_type = $query->get('post_type');
        $is_product_query = ($post_type === 'product')
            || (function_exists('is_shop') && (is_shop() || is_product_taxonomy()))
            || is_tax('product_brand')
            || is_tax('product_cat');

        if (!$is_product_query) {
            return;
        }

        // Filter by brand query arg (?product_brand=slug)
        if (!empty($_GET['product_brand'])) {
            $brand_slug = sanitize_title(wp_unslash($_GET['product_brand']));
            if ($brand_slug !== '') {
                $tax_query = (array) $query->get('tax_query');
                $tax_query[] = array(
                    'taxonomy' => 'product_brand',
                    'field'    => 'slug',
                    'terms'    => array($brand_slug),
                );
                $query->set('tax_query', $tax_query);
            }
        }

        // Filter by category query arg (?product_cat=slug)
        if (!empty($_GET['product_cat']) && !is_tax('product_cat')) {
            $cat_slug = sanitize_title(wp_unslash($_GET['product_cat']));
            if ($cat_slug !== '') {
                $tax_query = (array) $query->get('tax_query');
                $tax_query[] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => array($cat_slug),
                );
                $query->set('tax_query', $tax_query);
            }
        }
    }
}

new Deraak_Brand_Taxonomy();

/**
 * Extend WordPress product search to include SKU and _derak_* technical meta fields
 */
add_filter('posts_search', 'deraak_extend_product_search_sql', 10, 2);
function deraak_extend_product_search_sql($search, $wp_query) {
    global $wpdb;

    if (is_admin() || empty($search) || !$wp_query->is_search()) {
        return $search;
    }

    $post_type = $wp_query->get('post_type');
    if ($post_type !== 'product' && (!is_array($post_type) || !in_array('product', $post_type, true))) {
        return $search;
    }

    $term = trim((string) $wp_query->get('s'));
    if ($term === '' || strlen($term) > 100) {
        return $search;
    }

    $like = '%' . $wpdb->esc_like($term) . '%';
    $meta_subquery = $wpdb->prepare(
        " OR EXISTS (
            SELECT 1 FROM {$wpdb->postmeta} pm
            WHERE pm.post_id = {$wpdb->posts}.ID
            AND pm.meta_key IN ('_sku', '_deraak_model_code', '_deraak_specs_json', '_deraak_applications', '_derak_sku_model', '_derak_product_tag', '_derak_spec_protocol', '_derak_param1_val', '_derak_param2_val')
            AND pm.meta_value LIKE %s
        )",
        $like
    );

    // Inject into the outer AND ((...)) search clause safely
    $search = preg_replace('/\(\(\(/', '(((', $search, 1);
    $last_paren = strrpos($search, '))');
    if ($last_paren !== false) {
        $search = substr_replace($search, $meta_subquery . '))', $last_paren, 2);
    }

    return $search;
}
