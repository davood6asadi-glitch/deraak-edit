<?php
/**
 * Deraak Industrial B2B Theme Functions and Definitions
 *
 * @package Deraak_Industrial
 * @version 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DERAAK_THEME_VERSION', '1.2.0' );
define( 'DERAAK_THEME_DIR', get_template_directory() );
define( 'DERAAK_THEME_URI', get_template_directory_uri() );

/**
 * 1. تنظیمات پایه قالب و پشتیبانی از ووکامرس و المنتور
 */
function deraak_theme_setup() {
	load_theme_textdomain( 'deraak-industrial', DERAAK_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// پشتیبانی کامل از ووکامرس
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 440,
			'single_image_width'    => 720,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 3,
				'min_columns'     => 1,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'منوی اصلی هدر', 'deraak-industrial' ),
			'footer'  => __( 'منوی فوتر صنعتی', 'deraak-industrial' ),
		)
	);
}
add_action( 'after_setup_theme', 'deraak_theme_setup' );

/**
 * 2. بهینه‌سازی برای هاستینگ ایران: حذف ایموجی‌ها و درخواست‌های خارجی غیرضروری وردپرس
 */
function deraak_disable_external_bloat() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'deraak_disable_external_bloat' );

/**
 * 3. بارگذاری استایل‌ها، اسکریپت‌ها و فونت محلی IBM Plex Sans Arabic
 */
function deraak_enqueue_assets() {
	wp_enqueue_style(
		'deraak-style',
		get_stylesheet_uri(),
		array(),
		DERAAK_THEME_VERSION
	);

	wp_enqueue_style(
		'deraak-theme-css',
		DERAAK_THEME_URI . '/assets/css/theme.css',
		array( 'deraak-style' ),
		DERAAK_THEME_VERSION
	);

	if ( is_rtl() ) {
		wp_enqueue_style(
			'deraak-rtl',
			DERAAK_THEME_URI . '/rtl.css',
			array( 'deraak-theme-css' ),
			DERAAK_THEME_VERSION
		);
	}

	wp_enqueue_script(
		'deraak-theme-js',
		DERAAK_THEME_URI . '/assets/js/theme.js',
		array(),
		DERAAK_THEME_VERSION,
		true
	);

	wp_localize_script(
		'deraak-theme-js',
		'deraakThemeData',
		array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'restUrl'  => esc_url_raw( rest_url( 'deraak/v1/' ) ),
			'siteUrl'  => home_url( '/' ),
			'shopUrl'  => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/?post_type=product' ),
			'themeUri' => DERAAK_THEME_URI,
			'whatsapp' => 'https://wa.me/989121112233',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'deraak_enqueue_assets' );

/**
 * 4. Preload فونت‌های محلی IBM Plex Sans Arabic برای جلوگیری از پرش فونت (CLS)
 */
function deraak_preload_local_fonts() {
	?>
	<link rel="preload" href="<?php echo esc_url( DERAAK_THEME_URI . '/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Regular.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( DERAAK_THEME_URI . '/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Bold.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<?php
}
add_action( 'wp_head', 'deraak_preload_local_fonts', 1 );

/**
 * 5. دریافت تصویر محصول با پشتیبانی از تصاویر صنعتی محلی قالب (Fallback)
 */
function deraak_get_product_image_url( $product_id, $sku = '', $model = '' ) {
	$thumb_url = get_the_post_thumbnail_url( $product_id, 'medium_large' );
	if ( ! empty( $thumb_url ) ) {
		return $thumb_url;
	}

	if ( empty( $sku ) ) {
		$sku = (string) get_post_meta( $product_id, '_sku', true );
	}
	if ( empty( $model ) ) {
		$model = (string) get_post_meta( $product_id, '_deraak_model_code', true );
	}

	$lookup = strtoupper( trim( $sku . ' ' . $model ) );
	$map    = array(
		'FTW23'  => 'eh-cls13.png',
		'FTL31'  => 'eh-cls13.png',
		'CLS13'  => 'eh-cls13.png',
		'FMR53'  => 'eh-fmr53.png',
		'FMP54'  => 'eh-fmp54.png',
		'FMU90'  => 'eh-fdu95.png',
		'FDU95'  => 'eh-fdu95.png',
		'4116'   => 'pr-4116.png',
		'5116B'  => 'pr-5116b.png',
		'5116'   => 'pr-5116b.png',
		'9106B'  => 'pr-9106b.png',
		'9106'   => 'pr-9106b.png',
		'SI5010' => 'ifm-si5010.png',
	);

	foreach ( $map as $code => $filename ) {
		if ( false !== strpos( $lookup, $code ) ) {
			return DERAAK_THEME_URI . '/assets/images/products/' . $filename;
		}
	}

	return DERAAK_THEME_URI . '/assets/images/products/eh-fmr53.png';
}

/**
 * 6. دیتاست استاندارد ۸ محصول صنعتی دراک (برای نمایش اولیه یا درون‌ریزی تک‌کلیکی در ووکامرس)
 */
function deraak_get_default_products_dataset() {
	return array(
		array(
			'sku'          => 'DRK-EH-FTW23',
			'model'        => 'FTW23',
			'title'        => 'سوئیچ سطح هدایتی Liquipoint FTW23',
			'brand'        => 'Endress+Hauser',
			'brand_slug'   => 'endress-hauser',
			'category'     => 'تجهیزات سطح‌سنجی',
			'cat_slug'     => 'level-measurement',
			'lead_time'    => 'موجود در انبار تهران | تحویل فوری',
			'image'        => DERAAK_THEME_URI . '/assets/images/products/eh-cls13.png',
			'excerpt'      => 'تشخیص نقطه سطح در مایعات رسانا با بدنه استنلس‌استیل 316L؛ مناسب صنایع غذایی، دارویی و فرآیندی با شست‌وشوی CIP/SIP.',
			'applications' => "مخازن ذخیره مایعات رسانا\nخطوط CIP و SIP بهداشتی\nحفاظت سرریز و خشک‌کارکردن پمپ",
			'specs'        => array(
				array( 'key' => 'اصل اندازه‌گیری', 'value' => 'Conductive (هدایتی)' ),
				array( 'key' => 'خروجی سیگنال', 'value' => 'DC PNP / رله دوحالته' ),
				array( 'key' => 'دمای فرآیند', 'value' => '-20 تا +100 °C (تا 135°C در CIP)' ),
				array( 'key' => 'فشار کاری', 'value' => '-1 تا +40 bar' ),
				array( 'key' => 'درجه حفاظت', 'value' => 'IP66 / IP69' ),
			),
		),
		array(
			'sku'          => 'DRK-EH-FMR53',
			'model'        => 'FMR53',
			'title'        => 'ترانسمیتر سطح راداری Micropilot FMR53',
			'brand'        => 'Endress+Hauser',
			'brand_slug'   => 'endress-hauser',
			'category'     => 'تجهیزات سطح‌سنجی',
			'cat_slug'     => 'level-measurement',
			'lead_time'    => 'تأمین مستقیم | ۲ تا ۳ هفته کاری',
			'image'        => DERAAK_THEME_URI . '/assets/images/products/eh-fmr53.png',
			'excerpt'      => 'رادار غیرتماسی با آنتن میله‌ای PTFE مناسب اسیدها، قلیاها و محیط‌های خورنده شیمیایی با بخار و تلاطم شدید.',
			'applications' => "راکتورهای شیمیایی و پتروشیمی\nمخازن اسید و مواد خورنده\nفرآیندهای دارای بخار و گاز متراکم",
			'specs'        => array(
				array( 'key' => 'فرکانس رادار', 'value' => 'K-band (~26 GHz) / 6 GHz' ),
				array( 'key' => 'بازه اندازه‌گیری', 'value' => 'تا 20 متر' ),
				array( 'key' => 'خروجی و پروتکل', 'value' => '4–20 mA HART / PROFIBUS PA' ),
				array( 'key' => 'دمای فرآیند', 'value' => '-40 تا +150 °C' ),
				array( 'key' => 'گواهی ایمنی', 'value' => 'SIL2 / SIL3 (IEC 61508)' ),
			),
		),
		array(
			'sku'          => 'DRK-EH-FMP54',
			'model'        => 'FMP54',
			'title'        => 'رادار موج هدایت‌شده Levelflex FMP54',
			'brand'        => 'Endress+Hauser',
			'brand_slug'   => 'endress-hauser',
			'category'     => 'تجهیزات سطح‌سنجی',
			'cat_slug'     => 'level-measurement',
			'lead_time'    => 'تأمین سفارشی | ۳ تا ۴ هفته کاری',
			'image'        => DERAAK_THEME_URI . '/assets/images/products/eh-fmp54.png',
			'excerpt'      => 'سطح‌سنج رادار موج هدایت‌شده ویژه شرایط بحرانی دما و فشار بالا در بویلرهای بخار، نیروگاه‌ها و واحدهای پالایشگاهی.',
			'applications' => "درام بویلر نیروگاهی\nمخازن فشار قوی هیدروکربنی\nسنجش سطح و اینترفیس مایعات",
			'specs'        => array(
				array( 'key' => 'نوع سنسور', 'value' => 'Guided Radar (موج هدایت‌شده)' ),
				array( 'key' => 'تحمل فشار فرآیند', 'value' => '-1 تا +400 bar' ),
				array( 'key' => 'تحمل دمای فرآیند', 'value' => '-196 تا +450 °C' ),
				array( 'key' => 'خروجی سیگنال', 'value' => '4–20 mA HART / FOUNDATION Fieldbus' ),
				array( 'key' => 'جبران‌سازی فاز گاز', 'value' => 'دارای Gas Phase Compensation' ),
			),
		),
		array(
			'sku'          => 'DRK-EH-FMU90',
			'model'        => 'FMU90 / FDU95',
			'title'        => 'ترانسمیتر و سنسور اولتراسونیک Prosonic FMU90',
			'brand'        => 'Endress+Hauser',
			'brand_slug'   => 'endress-hauser',
			'category'     => 'تجهیزات سطح‌سنجی',
			'cat_slug'     => 'level-measurement',
			'lead_time'    => 'موجود / تحویل سریع',
			'image'        => DERAAK_THEME_URI . '/assets/images/products/eh-fdu95.png',
			'excerpt'      => 'سیستم سطح‌سنجی و فلوسنجی اولتراسونیک غیرتماسی برای سیلوهای مواد جامد فله، سنگ‌شکن‌ها، معادن و کانال‌های روباز.',
			'applications' => "سیلوهای سیمان، گندم و مواد معدنی\nایستگاه‌های پمپاژ آب و فاضلاب\nکانال‌های فلوم و سرریز",
			'specs'        => array(
				array( 'key' => 'بازه اندازه‌گیری', 'value' => 'تا 45 متر با سنسور FDU95' ),
				array( 'key' => 'خروجی و شبکه', 'value' => '4–20 mA HART + تا 6 رله کنترلی' ),
				array( 'key' => 'درجه حفاظت سنسور', 'value' => 'IP68 / NEMA 6P' ),
				array( 'key' => 'دمای محیط کاری', 'value' => '-40 تا +80 °C' ),
			),
		),
		array(
			'sku'          => 'DRK-PR-4116',
			'model'        => 'PR 4116',
			'title'        => 'مبدل یونیورسال سیگنال و دما PR 4116',
			'brand'        => 'PR electronics',
			'brand_slug'   => 'pr-electronics',
			'category'     => 'سیگنال کاندیشنر و ایزولاتور',
			'cat_slug'     => 'signal-conditioning',
			'lead_time'    => 'موجود در انبار تهران | تحویل فوری',
			'image'        => DERAAK_THEME_URI . '/assets/images/products/pr-4116.png',
			'excerpt'      => 'مبدل یونیورسال ورودی RTD، ترموکوپل، جریان و ولتاژ به خروجی آنالوگ ایزوله به همراه ۲ رله کنترلی؛ سازگار با نمایشگر 4501/4511.',
			'applications' => "تابلوهای کنترل PLC و DCS\nایزولاسیون گالوانیکی ۲.۳ کیلوولت\nمانیتورینگ دما و آلارم فرآیندی",
			'specs'        => array(
				array( 'key' => 'ورودی یونیورسال', 'value' => 'RTD, TC, Ohm, Potentiometer, mA, V' ),
				array( 'key' => 'خروجی', 'value' => '4–20 mA / V + 2x Relay Contacts' ),
				array( 'key' => 'تغذیه یونیورسال', 'value' => '21.6–253 VAC / 19.2–300 VDC' ),
				array( 'key' => 'ایزولاسیون گالوانیکی', 'value' => '2.3 kVAC (4-port isolation)' ),
			),
		),
		array(
			'sku'          => 'DRK-PR-5116B',
			'model'        => 'PR 5116B',
			'title'        => 'مبدل یونیورسال ضدانفجار Ex PR 5116B',
			'brand'        => 'PR electronics',
			'brand_slug'   => 'pr-electronics',
			'category'     => 'سیگنال کاندیشنر و ایزولاتور',
			'cat_slug'     => 'signal-conditioning',
			'lead_time'    => 'موجود در انبار تهران | تحویل فوری',
			'image'        => DERAAK_THEME_URI . '/assets/images/products/pr-5116b.png',
			'excerpt'      => 'ایزولاتور و ترانسمیتر برنامه‌پذیر ویژه نواحی مستعد انفجار (Zone 0/1) با دقت بالا و تاییدیه ATEX / IECEx.',
			'applications' => "انتقال سیگنال از Zone 0 به اتاق کنترل\nپالایشگاه نفت، گاز و پتروشیمی\nحلقه‌های اندازه‌گیری ایمن ذاتی (Ex ia)",
			'specs'        => array(
				array( 'key' => 'تاییدیه ضدانفجار', 'value' => 'ATEX / IECEx / FM (Ex ia IIC)' ),
				array( 'key' => 'ورودی', 'value' => 'RTD, TC, mV, Linear Resistance, mA, V' ),
				array( 'key' => 'خروجی', 'value' => 'Current / Voltage + 2 Relays' ),
				array( 'key' => 'ایزولاسیون', 'value' => '2.6 kVAC' ),
			),
		),
		array(
			'sku'          => 'DRK-PR-9106B',
			'model'        => 'PR 9106B',
			'title'        => 'ایزولاتور و بریر HART شفاف PR 9106B',
			'brand'        => 'PR electronics',
			'brand_slug'   => 'pr-electronics',
			'category'     => 'سیگنال کاندیشنر و ایزولاتور',
			'cat_slug'     => 'signal-conditioning',
			'lead_time'    => 'موجود در انبار تهران | تحویل فوری',
			'image'        => DERAAK_THEME_URI . '/assets/images/products/pr-9106b.png',
			'excerpt'      => 'ریپیتر و ایزولاتور پالس/جریان 4-20mA با عبور شفاف پروتکل HART و تاییدیه ایمنی SIL2/SIL3 برای ترانسمیترهای ۲ و ۳ سیمه.',
			'applications' => "تغذیه ترانسمیترهای ۲ سیمه در Zone 0\nحلقه‌های ایمنی ESD و F&G با گواهی SIL2/SIL3\nتکثیر سیگنال آنالوگ برای PLC و سیستم مانیتورینگ",
			'specs'        => array(
				array( 'key' => 'عبور پروتکل', 'value' => 'HART Transparent (4–20 mA)' ),
				array( 'key' => 'تعداد کانال', 'value' => 'تک‌کاناله یا دوکاناله مستقل' ),
				array( 'key' => 'ایمنی عملکردی', 'value' => 'Full SIL2 / SIL3 Assessment' ),
				array( 'key' => 'نصب ریلی', 'value' => 'DIN Rail / Power Rail (عرض 17.5mm)' ),
			),
		),
		array(
			'sku'          => 'DRK-IFM-SI5010',
			'model'        => 'SI5010',
			'title'        => 'فلوسوئیچ حرارتی الکترونیکی ifm SI5010',
			'brand'        => 'ifm electronic',
			'brand_slug'   => 'ifm',
			'category'     => 'جریان، فلو و فشار',
			'cat_slug'     => 'flow-pressure',
			'lead_time'    => 'موجود در انبار تهران | تحویل فوری',
			'image'        => DERAAK_THEME_URI . '/assets/images/products/ifm-si5010.png',
			'excerpt'      => 'سنسور نظارت بر جریان مایعات و گازها بر پایه اصل کالریمتریک با نمایشگر بارگراف LED ده‌تایی و بدنه استیل ضدزنگ.',
			'applications' => "خطوط آب خنک‌کننده و مدارهای روغنکاری\nحفاظت در برابر خشک کار کردن پمپ‌ها\nمانیتورینگ فلو در ماشین‌آلات صنعتی",
			'specs'        => array(
				array( 'key' => 'بازه سنجش در مایعات', 'value' => '3 تا 300 cm/s' ),
				array( 'key' => 'بازه سنجش در گازها', 'value' => '200 تا 3000 cm/s' ),
				array( 'key' => 'تحمل فشار کاری', 'value' => 'تا 300 bar' ),
				array( 'key' => 'خروجی سوئیچینگ', 'value' => 'PNP (NO/NC قابل تنظیم با دکمه فشاری)' ),
				array( 'key' => 'درجه حفاظت', 'value' => 'IP67' ),
			),
		),
	);
}

/**
 * 7. درون‌ریزی تک‌کلیکی محصولات و برندهای پیش‌فرض در ووکامرس (از طریق پنل مدیریت)
 */
function deraak_seed_default_catalog() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return 0;
	}

	$dataset = deraak_get_default_products_dataset();
	$created = 0;

	$brand_meta = array(
		'endress-hauser' => array(
			'country'   => 'سوئیس / آلمان',
			'specialty' => 'سطح، فلو، فشار، دما و آنالیز مایعات فرآیندی',
			'desc'      => 'تأمین تخصصی ترانسمیترهای راداری، اولتراسونیک، هدایتی و دیاپازونی Endress+Hauser با مشاوره انتخاب کد فنی.',
		),
		'pr-electronics' => array(
			'country'   => 'دانمارک',
			'specialty' => 'مبدل سیگنال، ایزولاتور گالوانیکی، بریر Ex و نمایشگر تابلویی',
			'desc'      => 'تأمین مبدل‌های دما، ایزولاتورهای HART و بریرهای ضدانفجار دارای گواهی SIL2/SIL3 از PR electronics دانمارک.',
		),
		'ifm'            => array(
			'country'   => 'آلمان',
			'specialty' => 'فلوسوئیچ، سنسورهای فشار، مجاورتی و سیستم‌های IO-Link',
			'desc'      => 'سنسورهای صنعتی مقاوم با درجه حفاظت بالا برای خطوط تولید، خنک‌کاری و اتوماسیون کارخانه‌ای.',
		),
	);

	foreach ( $dataset as $item ) {
		$existing_id = wc_get_product_id_by_sku( $item['sku'] );
		if ( $existing_id ) {
			$post_id = $existing_id;
		} else {
			$post_id = wp_insert_post(
				array(
					'post_title'   => $item['title'],
					'post_excerpt' => $item['excerpt'],
					'post_content' => $item['excerpt'],
					'post_status'  => 'publish',
					'post_type'    => 'product',
				)
			);
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}
			$created++;
		}

		update_post_meta( $post_id, '_sku', $item['sku'] );
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock' );
		update_post_meta( $post_id, '_deraak_model_code', $item['model'] );
		update_post_meta( $post_id, '_deraak_lead_time', $item['lead_time'] );
		update_post_meta( $post_id, '_deraak_applications', $item['applications'] );
		update_post_meta( $post_id, '_deraak_specs_json', wp_json_encode( $item['specs'], JSON_UNESCAPED_UNICODE ) );

		// ثبت دسته‌بندی ووکامرس
		if ( taxonomy_exists( 'product_cat' ) ) {
			$cat_term = term_exists( $item['cat_slug'], 'product_cat' );
			if ( ! $cat_term ) {
				$cat_term = wp_insert_term(
					$item['category'],
					'product_cat',
					array( 'slug' => $item['cat_slug'] )
				);
			}
			if ( ! is_wp_error( $cat_term ) && isset( $cat_term['term_id'] ) ) {
				wp_set_object_terms( $post_id, array( (int) $cat_term['term_id'] ), 'product_cat' );
			}
		}

		// ثبت برند صنعتی
		if ( taxonomy_exists( 'product_brand' ) ) {
			$b_slug = $item['brand_slug'];
			$b_term = term_exists( $b_slug, 'product_brand' );
			if ( ! $b_term ) {
				$b_term = wp_insert_term(
					$item['brand'],
					'product_brand',
					array(
						'slug'        => $b_slug,
						'description' => isset( $brand_meta[ $b_slug ] ) ? $brand_meta[ $b_slug ]['desc'] : '',
					)
				);
			}
			if ( ! is_wp_error( $b_term ) && isset( $b_term['term_id'] ) ) {
				$t_id = (int) $b_term['term_id'];
				wp_set_object_terms( $post_id, array( $t_id ), 'product_brand' );
				if ( isset( $brand_meta[ $b_slug ] ) ) {
					update_term_meta( $t_id, '_deraak_brand_country', $brand_meta[ $b_slug ]['country'] );
					update_term_meta( $t_id, '_deraak_brand_specialty', $brand_meta[ $b_slug ]['specialty'] );
				}
			}
		}
	}

	if ( class_exists( 'Deraak_PWA' ) && method_exists( 'Deraak_PWA', 'bump_catalog_version' ) ) {
		Deraak_PWA::bump_catalog_version();
	}

	return $created;
}

/**
 * اکشن مدیریتی برای درون‌ریزی تک‌کلیکی محصولات نمونه دراک
 */
function deraak_handle_admin_seed_action() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_GET['deraak_seed_catalog'] ) && '1' === $_GET['deraak_seed_catalog'] ) {
		check_admin_referer( 'deraak_seed_catalog_nonce' );
		$count = deraak_seed_default_catalog();
		wp_safe_redirect( add_query_arg( 'deraak_seeded', $count, admin_url( 'edit.php?post_type=product' ) ) );
		exit;
	}
}
add_action( 'admin_init', 'deraak_handle_admin_seed_action' );

/**
 * نمایش اعلان کمکی در پیشخوان وردپرس برای ساخت سریع کاتالوگ نمونه
 */
function deraak_admin_seed_notice() {
	if ( ! current_user_can( 'manage_options' ) || ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( isset( $_GET['deraak_seeded'] ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>کاتالوگ صنعتی دراک:</strong> محصولات، دسته‌بندی‌ها و برندهای صنعتی با موفقیت در ووکامرس ثبت و بروزرسانی شدند.</p>
		</div>
		<?php
		return;
	}

	$counts = wp_count_posts( 'product' );
	$total  = isset( $counts->publish ) ? (int) $counts->publish : 0;
	if ( 0 === $total ) {
		$seed_url = wp_nonce_url( admin_url( 'index.php?deraak_seed_catalog=1' ), 'deraak_seed_catalog_nonce' );
		?>
		<div class="notice notice-info" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;flex-wrap:wrap;gap:12px;">
			<div>
				<strong>خوش آمدید به پوسته صنعتی دراک (Deraak Industrial B2B)!</strong>
				<p style="margin:4px 0 0;">هنوز محصولی در ووکامرس ثبت نشده است. می‌توانید با یک کلیک ۸ تجهیز شاخص Endress+Hauser، PR electronics و ifm را همراه با جدول مشخصات فنی و برندها ایجاد کنید.</p>
			</div>
			<a href="<?php echo esc_url( $seed_url ); ?>" class="button button-primary">ایجاد خودکار محصولات نمونه دراک</a>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'deraak_admin_seed_notice' );

/**
 * 8. سفارشی‌سازی ووکامرس برای حالت کاتالوگ صنعتی B2B (استعلام قیمت به جای سبد خرید سنتی)
 */
function deraak_woocommerce_b2b_customizations() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
}
add_action( 'init', 'deraak_woocommerce_b2b_customizations' );

function deraak_b2b_price_html( $price, $product ) {
	if ( empty( $price ) ) {
		return '<span class="deraak-price-inquiry">استعلام قیمت روز (B2B)</span>';
	}
	return $price;
}
add_filter( 'woocommerce_get_price_html', 'deraak_b2b_price_html', 10, 2 );

/**
 * 9. تقویت جستجوی استاندارد وردپرس برای جستجو در SKU و کد مدل فنی (_deraak_model_code)
 */
function deraak_extend_product_search( $search, $wp_query ) {
	global $wpdb;

	if ( is_admin() || ! $wp_query->is_search() || empty( $search ) ) {
		return $search;
	}

	$term = $wp_query->get( 's' );
	if ( empty( $term ) ) {
		return $search;
	}

	$like = '%' . $wpdb->esc_like( $term ) . '%';
	$meta_post_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key IN ('_sku', '_deraak_model_code', '_deraak_applications')
			 AND meta_value LIKE %s",
			$like
		)
	);

	if ( ! empty( $meta_post_ids ) ) {
		$ids_sql = implode( ',', array_map( 'intval', $meta_post_ids ) );
		$search  = preg_replace(
			'/^\s*AND\s*\(\(/',
			" AND ((( {$wpdb->posts}.ID IN ({$ids_sql}) ) OR (",
			$search
		);
	}

	return $search;
}
add_filter( 'posts_search', 'deraak_extend_product_search', 10, 2 );
