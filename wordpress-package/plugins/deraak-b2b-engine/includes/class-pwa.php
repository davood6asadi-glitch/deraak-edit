<?php
/**
 * PWA & Offline Engine Backend
 * Dynamic Manifest and Versioned Catalog Endpoint
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_PWA_Service {

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_catalog_rest_route'));
        add_action('init', array($this, 'handle_manifest_rewrite'));
        add_action('wp_head', array($this, 'inject_manifest_link'), 2);
    }

    public function inject_manifest_link() {
        echo '<link rel="manifest" href="' . esc_url(home_url('/manifest.json')) . '" />' . "\n";
        echo '<link rel="apple-touch-icon" href="' . esc_url(DERAAK_B2B_URI . 'assets/icons/icon.svg') . '" />' . "\n";
    }

    public function handle_manifest_rewrite() {
        $request_path = isset($_SERVER['REQUEST_URI'])
            ? (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH)
            : '';
        $manifest_path = (string) wp_parse_url(home_url('/manifest.json'), PHP_URL_PATH);

        if ($request_path !== '' && $request_path === $manifest_path) {
            header('Content-Type: application/manifest+json; charset=utf-8');
            header('Cache-Control: public, max-age=3600');

            $manifest = array(
                'id'               => '/',
                'name'             => 'دراک - سامانه ابزار دقیق و اتوماسیون صنعتی',
                'short_name'       => 'دراک PWA',
                'description'      => 'سامانه تخصصی تأمین و استعلام قیمت تجهیزات ابزار دقیق، اتوماسیون صنعتی و برق بازار لاله‌زار تهران',
                'start_url'        => '/',
                'scope'            => '/',
                'display'          => 'standalone',
                'orientation'      => 'portrait-primary',
                'background_color' => '#EEF0FF',
                'theme_color'      => '#031A54',
                'dir'              => 'rtl',
                'lang'             => 'fa',
                'categories'       => array('business', 'productivity', 'utilities'),
                'icons'            => array(
                    array(
                        'src'     => DERAAK_B2B_URI . 'assets/icons/icon.svg',
                        'sizes'   => '192x192 512x512',
                        'type'    => 'image/svg+xml',
                        'purpose' => 'any maskable',
                    ),
                ),
            );

            echo wp_json_encode($manifest);
            exit;
        }
    }

    public function register_catalog_rest_route() {
        register_rest_route('deraak/v1', '/catalog', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_offline_catalog_data'),
            'permission_callback' => '__return_true',
        ));
    }

    public function get_offline_catalog_data($request) {
        $version = get_option('derak_catalog_version', '1.0.0');

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'has_password'   => false,
            'no_found_rows'  => true,
        );

        $query = new WP_Query($args);
        $products = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();
                $wc_product = wc_get_product($id);

                $brands = get_the_terms($id, 'product_brand');
                $brand_name = (!empty($brands) && !is_wp_error($brands)) ? $brands[0]->name : 'Deraak';

                $products[] = array(
                    'id'              => (string) $id,
                    'sku'             => $wc_product ? $wc_product->get_sku() : '',
                    'title'           => get_the_title(),
                    'brand'           => $brand_name,
                    'statusBadge'     => get_post_meta($id, '_derak_stock_badge', true) ?: 'موجود',
                    'tag'             => get_post_meta($id, '_derak_product_tag', true) ?: 'INDUSTRIAL',
                    'leadTime'        => get_post_meta($id, '_derak_lead_time', true) ?: 'تحویل فوری تهران',
                    'param1'          => array(
                        'label' => get_post_meta($id, '_derak_param1_label', true) ?: 'سنسور:',
                        'value' => get_post_meta($id, '_derak_param1_val', true) ?: '',
                    ),
                    'param2'          => array(
                        'label' => get_post_meta($id, '_derak_param2_label', true) ?: 'خروجی:',
                        'value' => get_post_meta($id, '_derak_param2_val', true) ?: '',
                    ),
                    'destinationUrl'  => get_post_meta($id, '_derak_external_destination_url', true) ?: '',
                    'permalink'       => get_permalink($id),
                    'image'           => get_the_post_thumbnail_url($id, 'medium') ?: DERAAK_THEME_URI . '/assets/images/icon.svg',
                );
            }
            wp_reset_postdata();
        }

        $response = new WP_REST_Response(array(
            'version'  => $version,
            'count'    => count($products),
            'items'    => $products,
        ));

        $response->header('X-Catalog-Version', $version);
        $response->header('Cache-Control', 'public, max-age=300');

        return $response;
    }
}

new Deraak_PWA_Service();
