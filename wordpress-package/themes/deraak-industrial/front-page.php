<?php
/**
 * Front Page Template — کاتالوگ صنعتی و تجهیزات ابزار دقیق دراک (B2B)
 * اگر صفحه با المنتور طراحی شده باشد محتوای المنتور را نمایش می‌دهد؛
 * در غیر این صورت چیدمان کامل صنعتی دراک (Hero، برندها، فرآیند تأمین، کاتالوگ تعاملی ووکامرس، PWA آفلاین و فرم مشاوره پیامکی کاوه‌نگار) را رندر می‌کند.
 *
 * @package Deraak_Industrial
 */

get_header();

$is_elementor_page = false;
if ( is_singular() && class_exists( '\Elementor\Plugin' ) ) {
	$doc = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
	if ( $doc && $doc->is_built_with_elementor() ) {
		$is_elementor_page = true;
	}
}

if ( $is_elementor_page ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
else :
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/?post_type=product' );
	?>
	<main id="primary" class="site-main">
		<!-- ۱. بخش Hero صنعتی دراک -->
		<section id="top" class="deraak-hero bg-grid">
			<div class="deraak-hero__glow"></div>
			<div class="deraak-container deraak-hero__grid">
				<div>
					<span class="deraak-hero__eyebrow">
						<span>●</span>
						<span>تأمین تخصصی ابزار دقیق و اتوماسیون فرآیندی B2B</span>
					</span>
					<h1 class="deraak-hero__title">
						مرجع مهندسی انتخاب، تطبیق پارت‌نامبر و تأمین تجهیزات ابزار دقیق صنعتی
					</h1>
					<p class="deraak-hero__lead">
						دسترسی مستقیم به مشخصات فنی، دیتاشیت‌ها و استعلام قیمت تجهیزات سطح‌سنجی، فلوسنجی، فشار، دما و سیگنال کاندیشنر از برندهای معتبر اروپایی Endress+Hauser، PR electronics و ifm.
					</p>

					<!-- جستجوی زنده در کاتالوگ آفلاین/آنلاین -->
					<div class="deraak-live-search" style="max-width:580px;margin-bottom:1.5rem;">
						<div class="deraak-live-search__input-wrap">
							<span class="deraak-live-search__icon">🔍</span>
							<input
								type="search"
								id="deraak-live-search-input"
								class="deraak-live-search__input"
								placeholder="جستجوی نام تجهیز، کد مدل (FMR53, PR 4116, SI5010) یا برند..."
								autocomplete="off"
							/>
							<span id="deraak-offline-status" class="deraak-live-search__badge">جستجوی آنی</span>
						</div>
						<div id="deraak-live-search-results" class="deraak-live-search__results" hidden></div>
					</div>

					<div class="deraak-hero__actions">
						<a href="#catalog" class="deraak-btn deraak-btn--accent">مشاهده کاتالوگ تجهیزات</a>
						<a href="#contact" class="deraak-btn deraak-btn--ghost-light">درخواست مشاوره فنی (پیامکی OTP)</a>
					</div>

					<div class="deraak-hero__stats">
						<div>
							<span class="deraak-hero__stat-label">برندهای تخصصی</span>
							<strong class="deraak-hero__stat-value" dir="ltr">E+H • PR • ifm</strong>
						</div>
						<div>
							<span class="deraak-hero__stat-label">ضمانت اصالت و رهگیری</span>
							<strong class="deraak-hero__stat-value">۱۰۰٪ اورجینال اروپایی</strong>
						</div>
						<div>
							<span class="deraak-hero__stat-label">کاتالوگ آفلاین PWA</span>
							<strong class="deraak-hero__stat-value">دسترسی در سایت‌های صنعتی</strong>
						</div>
					</div>
				</div>

				<!-- کارت تصویر صنعتی و هایلایت فنی -->
				<div class="deraak-hero__card">
					<img
						src="<?php echo esc_url( DERAAK_THEME_URI . '/assets/images/hero-plant.jpg' ); ?>"
						alt="واحد فرآیندی و تجهیزات ابزار دقیق دراک"
						style="width:100%;height:260px;object-fit:cover;border-radius:var(--radius-lg);margin-bottom:1.15rem;border:1px solid rgba(255,255,255,0.15);"
					/>
					<div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:0.65rem;">
						<span style="font-size:0.78rem;color:var(--color-accent);font-weight:700;">پوشش صنایع نفت، گاز، پتروشیمی، معدن و آب</span>
						<span class="font-mono" dir="ltr" style="font-size:0.75rem;color:rgba(238,241,248,0.7);">SIL2 / SIL3 • ATEX</span>
					</div>
					<p style="font-size:0.84rem;color:rgba(238,241,248,0.8);margin:0;">
						مهندسان فروش دراک در انتخاب دقیق Order Code، جایگزینی مدل‌های منسوخ‌شده (Obsolete) و ارائه پیش‌فاکتور رسمی همراه شما هستند.
					</p>
				</div>
			</div>
		</section>

		<!-- ۲. بخش کاتالوگ تعاملی تجهیزات صنعتی -->
		<section id="catalog" class="deraak-section">
			<div class="deraak-container">
				<div class="deraak-section__header">
					<div>
						<span class="deraak-section__eyebrow">کاتالوگ فنی و استعلام قیمت B2B</span>
						<h2 class="deraak-section__title">تجهیزات شاخص ابزار دقیق و اتوماسیون</h2>
					</div>
					<div style="display:flex;gap:0.65rem;flex-wrap:wrap;">
						<button type="button" class="deraak-btn deraak-btn--outline deraak-sync-catalog-btn">
							↻ ذخیره‌سازی آفلاین کاتالوگ
						</button>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="deraak-btn deraak-btn--primary">
							مشاهده آرشیو کامل ووکامرس &larr;
						</a>
					</div>
				</div>

				<!-- نوار ابزار فیلتر آنی دسته‌بندی، برند و جستجو -->
				<div class="deraak-catalog-toolbar">
					<div class="deraak-catalog-toolbar__top">
						<div class="deraak-live-search">
							<div class="deraak-live-search__input-wrap">
								<span class="deraak-live-search__icon">🔍</span>
								<input
									type="search"
									id="deraak-catalog-search-input"
									class="deraak-live-search__input"
									placeholder="فیلتر فوری بر اساس نام تجهیز، کد مدل (FTW23, FMR53, FMP54, PR 4116, SI5010) یا کاربرد..."
									autocomplete="off"
								/>
							</div>
						</div>
						<div style="font-size:0.84rem;color:var(--color-muted-fg);font-weight:600;">
							تجهیزات قابل نمایش: <strong id="deraak-catalog-visible-count" class="font-mono">8</strong>
						</div>
					</div>

					<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;border-top:1px solid var(--color-border);padding-top:0.85rem;">
						<div class="deraak-filter-pills">
							<span style="font-size:0.78rem;color:var(--color-muted-fg);margin-left:0.3rem;">دسته‌بندی:</span>
							<button type="button" class="deraak-filter-pill is-active" data-filter-category="all">همه دسته‌ها</button>
							<button type="button" class="deraak-filter-pill" data-filter-category="سطح">تجهیزات سطح‌سنجی</button>
							<button type="button" class="deraak-filter-pill" data-filter-category="سیگنال">سیگنال کاندیشنر و ایزولاتور</button>
							<button type="button" class="deraak-filter-pill" data-filter-category="فلو">جریان، فلو و فشار</button>
						</div>

						<div class="deraak-filter-pills">
							<span style="font-size:0.78rem;color:var(--color-muted-fg);margin-left:0.3rem;">برند:</span>
							<button type="button" class="deraak-filter-pill is-active" data-filter-brand="all">همه برندها</button>
							<button type="button" class="deraak-filter-pill" data-filter-brand="Endress+Hauser" dir="ltr">Endress+Hauser</button>
							<button type="button" class="deraak-filter-pill" data-filter-brand="PR electronics" dir="ltr">PR electronics</button>
							<button type="button" class="deraak-filter-pill" data-filter-brand="ifm" dir="ltr">ifm electronic</button>
						</div>
					</div>
				</div>

				<!-- شبکه کارت‌های محصول (از دیتابیس ووکامرس یا دیتاست پیش‌فرض دراک) -->
				<div id="deraak-catalog-grid" class="deraak-products-grid">
					<?php
					$wc_query = null;
					if ( class_exists( 'WooCommerce' ) ) {
						$wc_query = new WP_Query(
							array(
								'post_type'      => 'product',
								'post_status'    => 'publish',
								'posts_per_page' => 12,
							)
						);
					}

					if ( $wc_query && $wc_query->have_posts() ) :
						while ( $wc_query->have_posts() ) :
							$wc_query->the_post();
							wc_get_template_part( 'content', 'product' );
						endwhile;
						wp_reset_postdata();
					else :
						$defaults = function_exists( 'deraak_get_default_products_dataset' ) ? deraak_get_default_products_dataset() : array();
						foreach ( $defaults as $idx => $item ) :
							$item_id     = 9000 + $idx;
							$search_blob = $item['title'] . ' ' . $item['sku'] . ' ' . $item['model'] . ' ' . $item['brand'] . ' ' . $item['category'] . ' ' . $item['excerpt'];
							?>
							<article
								class="deraak-product-card"
								data-category="<?php echo esc_attr( $item['category'] ); ?>"
								data-brand="<?php echo esc_attr( $item['brand'] ); ?>"
								data-search="<?php echo esc_attr( $search_blob ); ?>"
							>
								<div>
									<div class="deraak-product-card__header">
										<span class="deraak-product-card__brand" dir="ltr"><?php echo esc_html( $item['brand'] ); ?></span>
										<span class="deraak-product-card__model font-mono" dir="ltr"><?php echo esc_html( $item['model'] ); ?></span>
									</div>
									<div class="deraak-product-card__image-wrap">
										<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" class="deraak-product-card__image" loading="lazy" />
									</div>
									<div class="deraak-product-card__body">
										<div class="deraak-product-card__cat-row">
											<span class="deraak-product-card__cat"><?php echo esc_html( $item['category'] ); ?></span>
											<span class="deraak-product-card__lead"><?php echo esc_html( $item['lead_time'] ); ?></span>
										</div>
										<h3 class="deraak-product-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
										<p class="deraak-product-card__excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
										<div class="deraak-product-card__specs">
											<?php foreach ( array_slice( $item['specs'], 0, 3 ) as $spec ) : ?>
												<div class="deraak-product-card__spec-item">
													<span class="deraak-product-card__spec-key"><?php echo esc_html( $spec['key'] ); ?>:</span>
													<span class="deraak-product-card__spec-val font-mono" dir="ltr"><?php echo esc_html( $spec['value'] ); ?></span>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
								<div class="deraak-product-card__footer">
									<button
										type="button"
										class="deraak-btn deraak-btn--accent deraak-add-rfq-btn"
										data-id="<?php echo esc_attr( $item_id ); ?>"
										data-title="<?php echo esc_attr( $item['title'] ); ?>"
										data-sku="<?php echo esc_attr( $item['sku'] ); ?>"
										data-model="<?php echo esc_attr( $item['model'] ); ?>"
										data-brand="<?php echo esc_attr( $item['brand'] ); ?>"
										data-category="<?php echo esc_attr( $item['category'] ); ?>"
										data-image="<?php echo esc_url( $item['image'] ); ?>"
										data-url="<?php echo esc_url( $shop_url ); ?>"
									>
										+ استعلام قیمت
									</button>
									<button
										type="button"
										class="deraak-btn deraak-btn--outline deraak-quick-view-btn"
										data-id="<?php echo esc_attr( $item_id ); ?>"
										data-title="<?php echo esc_attr( $item['title'] ); ?>"
										data-sku="<?php echo esc_attr( $item['sku'] ); ?>"
										data-model="<?php echo esc_attr( $item['model'] ); ?>"
										data-brand="<?php echo esc_attr( $item['brand'] ); ?>"
										data-category="<?php echo esc_attr( $item['category'] ); ?>"
										data-image="<?php echo esc_url( $item['image'] ); ?>"
										data-excerpt="<?php echo esc_attr( $item['excerpt'] ); ?>"
										data-url="<?php echo esc_url( $shop_url ); ?>"
										data-specs="<?php echo esc_attr( wp_json_encode( $item['specs'], JSON_UNESCAPED_UNICODE ) ); ?>"
									>
										مشخصات فنی
									</button>
								</div>
							</article>
							<?php
						endforeach;
					endif;
					?>
				</div>
			</div>
		</section>

		<!-- ۳. بخش برندهای تخصصی و فرآیند ۴ مرحله‌ای تأمین B2B -->
		<section id="brands" class="deraak-section deraak-section--muted">
			<div class="deraak-container">
				<div class="deraak-section__header">
					<div>
						<span class="deraak-section__eyebrow">همکاران تجاری و استانداردهای مهندسی</span>
						<h2 class="deraak-section__title">برندهای تحت پوشش و فرآیند تأمین تجهیزات</h2>
					</div>
				</div>

				<div class="deraak-brands-grid" style="margin-bottom:2.5rem;">
					<div class="deraak-brand-card">
						<div class="deraak-brand-card__top">
							<span class="deraak-brand-card__abbr font-mono" dir="ltr">E+H</span>
							<span class="deraak-brand-card__country">سوئیس / آلمان</span>
						</div>
						<h3 dir="ltr" style="margin:0 0 0.4rem;">Endress+Hauser</h3>
						<p style="font-size:0.84rem;color:var(--color-muted-fg);margin:0;">
							تأمین تخصصی ترانسمیترهای سطح راداری Micropilot، رادار موج هدایت‌شده Levelflex، اولتراسونیک Prosonic و سوئیچ‌های سطح Liquipoint / Liquiphant.
						</p>
					</div>

					<div class="deraak-brand-card">
						<div class="deraak-brand-card__top">
							<span class="deraak-brand-card__abbr font-mono" dir="ltr">PR</span>
							<span class="deraak-brand-card__country">دانمارک</span>
						</div>
						<h3 dir="ltr" style="margin:0 0 0.4rem;">PR electronics</h3>
						<p style="font-size:0.84rem;color:var(--color-muted-fg);margin:0;">
							پیشرو در تولید مبدل‌های یونیورسال سیگنال و دما (PR 4116 / 5116B)، ایزولاتورهای گالوانیکی و بریرهای HART شفاف (PR 9106B) با گواهی SIL2/SIL3.
						</p>
					</div>

					<div class="deraak-brand-card">
						<div class="deraak-brand-card__top">
							<span class="deraak-brand-card__abbr font-mono" dir="ltr">ifm</span>
							<span class="deraak-brand-card__country">آلمان</span>
						</div>
						<h3 dir="ltr" style="margin:0 0 0.4rem;">ifm electronic</h3>
						<p style="font-size:0.84rem;color:var(--color-muted-fg);margin:0;">
							سنسورهای نظارت بر جریان کالریمتریک (سری SI5010)، پرشر ترانسمیترها و سنسورهای صنعتی مقاوم با ارتباط IO-Link ویژه خطوط تولید و خنک‌کاری.
						</p>
					</div>
				</div>

				<!-- نوار همگام‌سازی کاتالوگ آفلاین PWA -->
				<div class="deraak-pwa-sync-box">
					<div>
						<span style="color:var(--yellow-400);font-size:0.76rem;font-weight:700;display:block;">کاتالوگ آفلاین مهندسی (IndexedDB + PWA)</span>
						<h3 style="color:var(--slate-50);margin:0.25rem 0 0.4rem;font-size:1.15rem;">دسترسی به مشخصات فنی تجهیزات حتی در سایت‌های صنعتی بدون اینترنت</h3>
						<p id="deraak-pwa-sync-status" style="margin:0;font-size:0.84rem;color:var(--slate-300);">
							با یک کلیک، اطلاعات فنی تجهیزات را روی مرورگر موبایل یا لپ‌تاپ خود ذخیره کنید.
						</p>
					</div>
					<div class="deraak-pwa-sync-box__actions">
						<button type="button" class="deraak-btn deraak-btn--accent deraak-sync-catalog-btn">
							↻ همگام‌سازی و ذخیره آفلاین کاتالوگ
						</button>
						<button type="button" class="deraak-btn deraak-btn--ghost-light deraak-pwa-install-btn" hidden>
							📲 نصب وب‌اپلیکیشن دراک
						</button>
					</div>
				</div>
			</div>
		</section>

		<!-- ۴. بخش تماس با ما و فرم درخواست مشاوره با احراز هویت پیامکی کاوه‌نگار (OTP) -->
		<?php
		if ( shortcode_exists( 'deraak_contact_form' ) ) {
			echo do_shortcode( '[deraak_contact_form]' );
		}
		?>
	</main>
	<?php
endif;

get_footer();
