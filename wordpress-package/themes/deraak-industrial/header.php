<!doctype html>
<html <?php language_attributes(); ?> dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#00143B">
	<script>
	(function() {
		try {
			var saved = localStorage.getItem('deraak-theme-mode');
			if (saved === 'dark') {
				document.documentElement.classList.add('dark');
				document.documentElement.setAttribute('data-theme', 'dark');
			}
		} catch (e) {}
	})();
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- نوار وضعیت صنعتی و ارتباط مستقیم -->
<div class="deraak-topbar">
	<div class="deraak-container deraak-topbar__inner">
		<div class="deraak-topbar__right">
			<span id="deraak-network-badge" class="deraak-network-badge">● کاتالوگ آنلاین | آماده استعلام</span>
			<span>تأمین تخصصی ابزار دقیق Endress+Hauser • PR electronics • ifm</span>
		</div>
		<div class="deraak-topbar__left">
			<button type="button" id="deraak-header-sync-btn" class="deraak-sync-catalog-btn deraak-topbar__sync-link">
				↻ ذخیره‌سازی آفلاین کاتالوگ
			</button>
			<a href="tel:+982188994411" dir="ltr" class="font-mono deraak-topbar__phone">021-88994411</a>
		</div>
	</div>
</div>

<!-- هدر اصلی دراک -->
<header class="deraak-header">
	<div class="deraak-container deraak-header__inner">
		<!-- برند و لوگو -->
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="deraak-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="deraak-brand__badge font-mono">DRK</span>
			<?php endif; ?>
			<div class="deraak-brand__text">
				<span class="deraak-brand__title"><?php bloginfo( 'name' ); ?></span>
				<span class="deraak-brand__subtitle">تأمین تجهیزات ابزار دقیق و اتوماسیون صنعتی</span>
			</div>
		</a>

		<!-- منوی ناوبری دسکتاپ -->
		<nav class="deraak-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'deraak-industrial' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'deraak-nav__list',
						'fallback_cb'    => false,
					)
				);
			} else {
				$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/?post_type=product' );
				?>
				<ul class="deraak-nav__list">
					<li><a href="<?php echo esc_url( home_url( '/#catalog' ) ); ?>">کاتالوگ تجهیزات</a></li>
					<li><a href="<?php echo esc_url( $shop_url ); ?>">آرشیو فنی محصولات</a></li>
					<li><a href="<?php echo esc_url( home_url( '/#brands' ) ); ?>">برندها و فرآیند تأمین</a></li>
					<li><a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">درخواست مشاوره (OTP)</a></li>
				</ul>
				<?php
			}
			?>
		</nav>

		<!-- دکمه‌های عملیاتی (تغییر حالت روشن/تیره، نصب PWA، لیست استعلام RFQ و منوی موبایل) -->
		<div class="deraak-header__actions">
			<button
				type="button"
				id="deraak-theme-toggle"
				class="deraak-theme-toggle"
				aria-label="تغییر حالت نمایش روشن یا تیره"
				title="تغییر حالت روشن / تیره"
			>
				<span id="deraak-theme-toggle-icon">☾</span>
			</button>

			<button type="button" id="deraak-pwa-install-btn" class="deraak-btn deraak-btn--ghost-light deraak-pwa-install-btn" hidden>
				<span>نصب نسخه وب‌اپ (PWA)</span>
			</button>

			<button type="button" id="deraak-open-rfq-drawer" class="deraak-btn deraak-btn--accent" aria-label="مشاهده لیست استعلام قیمت">
				<span>لیست استعلام</span>
				<span id="deraak-rfq-count" class="deraak-badge-count deraak-rfq-badge font-mono">0</span>
			</button>

			<button type="button" id="deraak-mobile-menu-toggle" class="deraak-mobile-toggle" aria-expanded="false" aria-controls="deraak-mobile-nav-drawer" aria-label="باز کردن منوی موبایل">
				☰
			</button>
		</div>
	</div>

	<!-- منوی کشویی موبایل -->
	<div id="deraak-mobile-nav-drawer" class="deraak-mobile-drawer">
		<?php
		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/?post_type=product' );
		?>
		<ul class="deraak-nav__list">
			<li><a href="<?php echo esc_url( home_url( '/#catalog' ) ); ?>">کاتالوگ تجهیزات</a></li>
			<li><a href="<?php echo esc_url( $shop_url ); ?>">آرشیو فنی محصولات</a></li>
			<li><a href="<?php echo esc_url( home_url( '/#brands' ) ); ?>">برندها و فرآیند تأمین</a></li>
			<li><a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">درخواست مشاوره پیامکی (کاوه‌نگار)</a></li>
		</ul>
	</div>
</header>
