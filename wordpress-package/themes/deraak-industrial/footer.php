<footer class="deraak-footer">
	<div class="deraak-container">
		<div class="deraak-footer__grid">
			<!-- معرفی دراک -->
			<div class="deraak-footer__col">
				<div class="deraak-brand" style="margin-bottom:0.85rem;">
					<span class="deraak-brand__badge font-mono">DRK</span>
					<div class="deraak-brand__text">
						<span class="deraak-brand__title"><?php bloginfo( 'name' ); ?></span>
						<span class="deraak-brand__subtitle">تأمین ابزار دقیق و اتوماسیون صنعتی</span>
					</div>
				</div>
				<p>
					مرجع تخصصی بررسی مشخصات فنی، انتخاب پارت‌نامبر و استعلام قیمت تجهیزات ابزار دقیق از برندهای معتبر اروپایی Endress+Hauser، PR electronics و ifm با پشتیبانی فنی مهندسی.
				</p>
			</div>

			<!-- لینک‌های سریع و دسته‌بندی‌ها -->
			<div class="deraak-footer__col">
				<h4>دسترسی سریع کاتالوگ</h4>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'deraak-footer__links',
						)
					);
				} else {
					$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/?post_type=product' );
					?>
					<ul>
						<li><a href="<?php echo esc_url( $shop_url ); ?>">کاتالوگ کامل تجهیزات صنعتی</a></li>
						<li><a href="<?php echo esc_url( home_url( '/#catalog' ) ); ?>">تجهیزات سطح‌سنجی راداری و اولتراسونیک</a></li>
						<li><a href="<?php echo esc_url( home_url( '/#catalog' ) ); ?>">مبدل سیگنال و ایزولاتور گالوانیکی</a></li>
						<li><a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">فرم درخواست مشاوره مهندسی (OTP)</a></li>
					</ul>
					<?php
				}
				?>
			</div>

			<!-- اطلاعات تماس مهندسی فروش -->
			<div class="deraak-footer__col">
				<h4>واحد مهندسی فروش و استعلام</h4>
				<ul>
					<li>تهران، خیابان سهروردی شمالی، واحد مهندسی فروش دراک</li>
					<li>تلفن مستقیم: <a href="tel:+982188994411" dir="ltr" class="font-mono">021-88994411</a></li>
					<li>واتساپ فنی: <a href="https://wa.me/989121112233" target="_blank" rel="noopener noreferrer" dir="ltr" class="font-mono">+98 912 111 2233</a></li>
					<li>ایمیل استعلام: <a href="mailto:sales@add.co.ir" dir="ltr" class="font-mono">sales@add.co.ir</a></li>
				</ul>
			</div>
		</div>

		<div class="deraak-footer__bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — تمامی حقوق محفوظ است.</span>
			<span>نسخه کاتالوگ B2B مجهز به پایگاه داده آفلاین IndexedDB و تایپوگرافی محلی IBM Plex Sans Arabic</span>
		</div>
	</div>
</footer>

<!-- مودال مشاهده سریع مشخصات فنی محصول (Quick View) -->
<div id="deraak-quick-view-modal" class="deraak-quick-modal" role="dialog" aria-modal="true" aria-labelledby="deraak-quick-view-title" hidden>
	<div id="deraak-quick-view-backdrop" class="deraak-quick-modal__backdrop"></div>
	<div class="deraak-quick-modal__dialog">
		<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;border-bottom:1px solid var(--color-border);padding-bottom:1rem;margin-bottom:1.25rem;">
			<div>
				<span id="deraak-quick-view-brand" class="deraak-product-card__brand"></span>
				<span id="deraak-quick-view-model" class="deraak-product-card__model font-mono" dir="ltr" style="margin-right:0.4rem;"></span>
				<h3 id="deraak-quick-view-title" style="margin:0.5rem 0 0;font-size:1.2rem;"></h3>
			</div>
			<button type="button" id="deraak-quick-view-close" class="deraak-rfq-drawer__close" style="background:var(--color-muted);color:var(--color-fg);" aria-label="بستن">&times;</button>
		</div>

		<div style="display:grid;grid-template-columns:200px 1fr;gap:1.25rem;align-items:start;" class="deraak-quick-view-body-grid">
			<div style="background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:1rem;display:flex;align-items:center;justify-content:center;">
				<img id="deraak-quick-view-img" src="" alt="" style="max-height:160px;object-fit:contain;" />
			</div>
			<div>
				<p id="deraak-quick-view-excerpt" style="font-size:0.88rem;color:var(--color-muted-fg);margin-top:0;"></p>
				<div id="deraak-quick-view-specs-wrap">
					<h4 style="font-size:0.85rem;margin-bottom:0.5rem;">جدول مشخصات فنی کلیدی:</h4>
					<table class="deraak-spec-table" id="deraak-quick-view-specs"></table>
				</div>
			</div>
		</div>

		<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--color-border);">
			<a id="deraak-quick-view-link" href="#" class="deraak-btn deraak-btn--outline">مشاهده صفحه کامل محصول و دیتاشیت</a>
			<button type="button" id="deraak-quick-view-rfq-btn" class="deraak-btn deraak-btn--accent deraak-add-rfq-btn">+ افزودن به لیست استعلام قیمت</button>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
