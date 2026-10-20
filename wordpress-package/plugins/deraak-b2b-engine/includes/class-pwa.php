<?php
/**
 * PWA & Offline Engine Backend
 * Root-Scoped Dynamic Service Worker, Web App Manifest, and Paginated Catalog REST Endpoint
 *
 * @package Deraak_B2B_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Deraak_PWA_Service {

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_catalog_rest_route'));
        add_action('init', array($this, 'handle_pwa_endpoints'), 1);
        add_action('wp_head', array($this, 'inject_manifest_link'), 2);
    }

    public function inject_manifest_link() {
        $manifest_url = add_query_arg('deraak_manifest', '1', home_url('/'));
        echo '<link rel="manifest" href="' . esc_url($manifest_url) . '" />' . "\n";
        echo '<link rel="apple-touch-icon" href="' . esc_url(DERAAK_B2B_URI . 'assets/icons/apple-touch-icon.png') . '" />' . "\n";
    }

    /**
     * Serve Web App Manifest and Root-Scoped Service Worker directly from WordPress root
     * so Service Worker naturally controls '/' without requiring custom Apache/Nginx rules.
     */
    public function handle_pwa_endpoints() {
        $request_path = isset($_SERVER['REQUEST_URI'])
            ? (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH)
            : '';

        $manifest_path = (string) wp_parse_url(home_url('/manifest.json'), PHP_URL_PATH);
        $sw_path       = (string) wp_parse_url(home_url('/sw.js'), PHP_URL_PATH);

        $is_manifest = !empty($_GET['deraak_manifest']) || ($request_path !== '' && $request_path === $manifest_path);
        $is_sw       = !empty($_GET['deraak_sw']) || ($request_path !== '' && $request_path === $sw_path);

        if ($is_manifest) {
            $this->serve_manifest_json();
        }

        if ($is_sw) {
            $this->serve_service_worker_js();
        }
    }

    private function serve_manifest_json() {
        if (!headers_sent()) {
            header('Content-Type: application/manifest+json; charset=utf-8');
            header('Cache-Control: public, max-age=3600');
        }

        $home_path = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
        if ($home_path === '') {
            $home_path = '/';
        }

        $manifest = array(
            'id'               => $home_path,
            'name'             => 'دراک — کاتالوگ ابزار دقیق و اتوماسیون صنعتی',
            'short_name'       => 'دراک کاتالوگ',
            'description'      => 'کاتالوگ فنی تجهیزات ابزار دقیق، سیگنال کاندیشنر و اتوماسیون صنعتی با قابلیت مرور آفلاین و ثبت استعلام.',
            'start_url'        => $home_path,
            'scope'            => $home_path,
            'display'          => 'standalone',
            'orientation'      => 'portrait-primary',
            'background_color' => '#00143B',
            'theme_color'      => '#008DFF',
            'dir'              => 'rtl',
            'lang'             => 'fa',
            'categories'       => array('business', 'productivity', 'utilities'),
            'icons'            => array(
                array(
                    'src'     => DERAAK_B2B_URI . 'assets/icons/icon-192.png',
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ),
                array(
                    'src'     => DERAAK_B2B_URI . 'assets/icons/icon-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ),
                array(
                    'src'     => DERAAK_B2B_URI . 'assets/icons/icon-maskable-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'maskable',
                ),
                array(
                    'src'     => DERAAK_B2B_URI . 'assets/icons/icon.svg',
                    'sizes'   => 'any',
                    'type'    => 'image/svg+xml',
                    'purpose' => 'any',
                ),
            ),
        );

        echo wp_json_encode($manifest);
        exit;
    }

    private function serve_service_worker_js() {
        if (!headers_sent()) {
            header('Content-Type: application/javascript; charset=utf-8');
            header('Service-Worker-Allowed: /');
            header('Cache-Control: no-cache, no-store, must-revalidate');
        }

        $version   = preg_replace('/[^A-Za-z0-9._-]/', '', (string) get_option('derak_catalog_version', '1.1.0'));
        $theme_uri = get_template_directory_uri();
        $home_url  = home_url('/');

        $precache = array(
            $home_url,
            $theme_uri . '/style.css',
            $theme_uri . '/rtl.css',
            $theme_uri . '/assets/css/theme.css',
            $theme_uri . '/assets/js/theme.js',
            $theme_uri . '/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Regular.woff2',
            $theme_uri . '/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Medium.woff2',
            $theme_uri . '/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-SemiBold.woff2',
            $theme_uri . '/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Bold.woff2',
            DERAAK_B2B_URI . 'assets/js/indexeddb-catalog.js',
            DERAAK_B2B_URI . 'assets/js/pwa-install.js',
            DERAAK_B2B_URI . 'assets/js/rfq-cart.js',
            DERAAK_B2B_URI . 'assets/icons/icon.svg',
            DERAAK_B2B_URI . 'assets/icons/icon-192.png',
        );

        ?>
/**
 * Deraak Industrial B2B Service Worker (Root-Scoped via WordPress Endpoint)
 * Safe cache namespacing: Never deletes caches belonging to other plugins or apps.
 */
const CACHE_PREFIX = 'derak-pwa-';
const SHELL_CACHE = CACHE_PREFIX + 'shell-v<?php echo esc_js($version); ?>';
const ASSET_CACHE = CACHE_PREFIX + 'assets-v<?php echo esc_js($version); ?>';
const ACTIVE_CACHES = [SHELL_CACHE, ASSET_CACHE];
const PRECACHE_URLS = <?php echo wp_json_encode(array_values($precache)); ?>;
const HOME_URL = <?php echo wp_json_encode($home_url); ?>;

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(SHELL_CACHE).then(function (cache) {
      return Promise.allSettled(
        PRECACHE_URLS.map(function (url) {
          return cache.add(url).catch(function () {});
        })
      );
    }).then(function () {
      return self.skipWaiting();
    })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.map(function (key) {
          // Strictly delete ONLY outdated Deraak caches, leaving all other origin caches untouched
          if (key.indexOf(CACHE_PREFIX) === 0 && ACTIVE_CACHES.indexOf(key) === -1) {
            return caches.delete(key);
          }
          return Promise.resolve();
        })
      );
    }).then(function () {
      return self.clients.claim();
    })
  );
});

self.addEventListener('fetch', function (event) {
  if (event.request.method !== 'GET') return;

  var url;
  try {
    url = new URL(event.request.url);
  } catch (e) {
    return;
  }

  if (url.origin !== self.location.origin) return;

  // Never intercept WordPress admin, login, nonces, REST mutations, or AJAX
  if (
    url.pathname.indexOf('/wp-admin') !== -1 ||
    url.pathname.indexOf('/wp-login') !== -1 ||
    url.pathname.indexOf('/wp-json/') !== -1 ||
    url.search.indexOf('deraak_sw=') !== -1 ||
    url.search.indexOf('wc-ajax=') !== -1 ||
    url.search.indexOf('preview=') !== -1
  ) {
    return;
  }

  // Static assets (Fonts, CSS, JS, Images): Cache-First with background refresh
  if (/\.(woff2|woff|ttf|css|js|svg|png|jpg|jpeg|webp)$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(event.request).then(function (cached) {
        var networkFetch = fetch(event.request).then(function (response) {
          if (response && response.status === 200) {
            var copy = response.clone();
            caches.open(ASSET_CACHE).then(function (cache) {
              cache.put(event.request, copy);
            });
          }
          return response;
        }).catch(function () {
          return cached;
        });
        return cached || networkFetch;
      })
    );
    return;
  }

  // HTML Navigation: Network-First with Cached Page or Homepage Shell Fallback
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).then(function (response) {
        if (response && response.status === 200) {
          var copy = response.clone();
          caches.open(SHELL_CACHE).then(function (cache) {
            cache.put(event.request, copy);
          });
        }
        return response;
      }).catch(function () {
        return caches.match(event.request).then(function (cached) {
          return cached || caches.match(HOME_URL);
        });
      })
    );
  }
});
<?php
        exit;
    }

    public function register_catalog_rest_route() {
        register_rest_route('deraak/v1', '/catalog', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_offline_catalog_data'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'page'     => array(
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ),
                'per_page' => array(
                    'default'           => 100,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }

    /**
     * Paginated Catalog Synchronization Endpoint supporting >100 products
     */
    public function get_offline_catalog_data(WP_REST_Request $request) {
        $version  = (string) get_option('derak_catalog_version', '1.1.0');
        $page     = max(1, (int) $request->get_param('page'));
        $per_page = max(1, min(200, (int) $request->get_param('per_page')));

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'post_status'    => 'publish',
            'has_password'   => false,
            'orderby'        => 'ID',
            'order'          => 'DESC',
        );

        $query    = new WP_Query($args);
        $products = array();
        $fallback_icon = DERAAK_B2B_URI . 'assets/icons/icon.svg';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();

                $sku = '';
                if (function_exists('wc_get_product')) {
                    $wc_product = wc_get_product($id);
                    if ($wc_product) {
                        $sku = (string) $wc_product->get_sku();
                    }
                }
                if ($sku === '') {
                    $sku = (string) get_post_meta($id, '_sku', true) ?: (string) get_post_meta($id, '_derak_sku_model', true);
                }

                $brands     = get_the_terms($id, 'product_brand');
                $brand_name = (!empty($brands) && !is_wp_error($brands)) ? $brands[0]->name : '';

                $cats     = get_the_terms($id, 'product_cat');
                $cat_list = array();
                if (!empty($cats) && !is_wp_error($cats)) {
                    foreach ($cats as $c) {
                        $cat_list[] = array(
                            'slug' => $c->slug,
                            'name' => $c->name,
                        );
                    }
                }

                $demo_svg = (string) get_post_meta($id, '_derak_local_image', true);
                $thumb    = get_the_post_thumbnail_url($id, 'medium');
                if (!$thumb && $demo_svg !== '') {
                    $thumb = DERAAK_B2B_URI . 'assets/products/' . basename($demo_svg);
                }
                if (!$thumb) {
                    $thumb = $fallback_icon;
                }

                $specs = array();
                $spec_map = array(
                    '_derak_spec_protocol'      => 'پروتکل ارتباطی / خروجی',
                    '_derak_spec_supply'        => 'ولتاژ تغذیه',
                    '_derak_spec_accuracy'      => 'مشخصه دقت / سنجش',
                    '_derak_spec_certification' => 'استاندارد / حفاظت',
                    '_derak_spec_temp'          => 'دمای کاری',
                    '_derak_spec_pressure'      => 'فشار کاری',
                );
                foreach ($spec_map as $meta_key => $label) {
                    $val = trim((string) get_post_meta($id, $meta_key, true));
                    if ($val !== '') {
                        $specs[] = array('label' => $label, 'value' => $val);
                    }
                }

                $products[] = array(
                    'id'             => (string) $id,
                    'sku'            => $sku,
                    'title'          => get_the_title(),
                    'excerpt'        => wp_strip_all_tags(get_the_excerpt()),
                    'brand'          => $brand_name,
                    'categories'     => $cat_list,
                    'statusBadge'    => (string) get_post_meta($id, '_derak_stock_badge', true),
                    'tag'            => (string) get_post_meta($id, '_derak_product_tag', true),
                    'leadTime'       => (string) get_post_meta($id, '_derak_lead_time', true),
                    'isDemo'         => get_post_meta($id, '_derak_is_demo', true) === '1',
                    'param1'         => array(
                        'label' => (string) get_post_meta($id, '_derak_param1_label', true),
                        'value' => (string) get_post_meta($id, '_derak_param1_val', true),
                    ),
                    'param2'         => array(
                        'label' => (string) get_post_meta($id, '_derak_param2_label', true),
                        'value' => (string) get_post_meta($id, '_derak_param2_val', true),
                    ),
                    'specs'          => $specs,
                    'datasheetUrl'   => (string) get_post_meta($id, '_derak_datasheet_url', true),
                    'destinationUrl' => (string) get_post_meta($id, '_derak_external_destination_url', true),
                    'permalink'      => get_permalink($id),
                    'image'          => $thumb,
                );
            }
            wp_reset_postdata();
        }

        $total_posts = (int) $query->found_posts;
        $total_pages = (int) $query->max_num_pages;

        $response = new WP_REST_Response(array(
            'version'     => $version,
            'generatedAt' => current_time('mysql'),
            'timestamp'   => time() * 1000,
            'page'        => $page,
            'perPage'     => $per_page,
            'total'       => $total_posts,
            'totalPages'  => max(1, $total_pages),
            'count'       => count($products),
            'items'       => $products,
        ), 200);

        $response->header('X-Catalog-Version', $version);
        $response->header('Cache-Control', 'public, max-age=180');

        return $response;
    }
}

new Deraak_PWA_Service();
