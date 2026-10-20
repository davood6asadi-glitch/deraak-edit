<?php
/**
 * The Template for displaying product archives, including the main shop page
 *
 * @package Deraak_Industrial
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$product_cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
	)
);
$product_brands = get_terms(
	array(
		'taxonomy'   => 'product_brand',
		'hide_empty' => false,
	)
);
?>
<main id="primary" class="site-main deraak-section">
	<div class="deraak-container">
		<!-- مسیر راهنما (Breadcrumb) -->
		<nav class="deraak-breadcrumb" aria-label="مسیر راهنما">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">صفحه اصلی</a>
			<span>/</span>
			<strong><?php woocommerce_page_title(); ?></strong>
		</nav>

		<!-- هدر آرشیو کاتالوگ -->
		<header class="deraak-section__header">
			<div>
				<span class="deraak-section__eyebrow">کاتالوگ مهندسی و استعلام قیمت B2B</span>
				<h1 class="deraak-section__title"><?php woocommerce_page_title(); ?></h1>
				<?php do_action( 'woocommerce_archive_description' ); ?>
			</div>

			<div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
				<button type="button" id="deraak-sync-catalog-btn" class="deraak-btn deraak-btn--outline deraak-sync-catalog-btn">
					<span>↻ بروزرسانی کاتالوگ آفلاین</span>
				</button>
				<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="deraak-btn deraak-btn--primary">
					مشاوره انتخاب پارت‌نامبر
				</a>
			</div>
		</header>

		<!-- نوار ابزار جستجوی زنده و فیلترهای صنعتی -->
		<div class="deraak-catalog-toolbar">
			<div class="deraak-catalog-toolbar__top">
				<div class="deraak-live-search">
					<div class="deraak-live-search__input-wrap">
						<span class="deraak-live-search__icon">🔍</span>
						<input
							type="search"
							id="deraak-catalog-search-input"
							class="deraak-live-search__input"
							placeholder="جستجوی آنی نام تجهیز، کد مدل (مانند FMR53، PR 4116، SI5010) یا کاربرد..."
							value="<?php echo esc_attr( get_search_query() ); ?>"
							autocomplete="off"
						/>
						<span id="deraak-offline-status" class="deraak-live-search__badge">فیلتر آنی</span>
					</div>
				</div>

				<div style="font-size:0.84rem;color:var(--color-muted-fg);font-weight:600;">
					نمایش <strong id="deraak-catalog-visible-count" class="font-mono"><?php echo intval( $GLOBALS['wp_query']->post_count ?: 8 ); ?></strong> تجهیز صنعتی
				</div>
			</div>

			<!-- فیلتر دسته‌بندی‌ها -->
			<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;border-top:1px solid var(--color-border);padding-top:0.85rem;">
				<div class="deraak-filter-pills" role="group" aria-label="فیلتر دسته‌بندی">
					<span style="font-size:0.78rem;color:var(--color-muted-fg);margin-left:0.35rem;">دسته‌بندی:</span>
					<button type="button" class="deraak-filter-pill is-active" data-filter-category="all">همه تجهیزات</button>
					<?php if ( ! empty( $product_cats ) && ! is_wp_error( $product_cats ) ) : ?>
						<?php foreach ( $product_cats as $cat ) : ?>
							<?php if ( 'uncategorized' !== $cat->slug ) : ?>
								<button type="button" class="deraak-filter-pill" data-filter-category="<?php echo esc_attr( $cat->name ); ?>">
									<?php echo esc_html( $cat->name ); ?>
								</button>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php else : ?>
						<button type="button" class="deraak-filter-pill" data-filter-category="تجهیزات سطح‌سنجی">تجهیزات سطح‌سنجی</button>
						<button type="button" class="deraak-filter-pill" data-filter-category="سیگنال کاندیشنر و ایزولاتور">سیگنال کاندیشنر و ایزولاتور</button>
						<button type="button" class="deraak-filter-pill" data-filter-category="جریان، فلو و فشار">جریان، فلو و فشار</button>
					<?php endif; ?>
				</div>

				<!-- فیلتر برندها -->
				<div class="deraak-filter-pills" role="group" aria-label="فیلتر برند سازنده">
					<span style="font-size:0.78rem;color:var(--color-muted-fg);margin-left:0.35rem;">برند:</span>
					<button type="button" class="deraak-filter-pill is-active" data-filter-brand="all">همه برندها</button>
					<?php if ( ! empty( $product_brands ) && ! is_wp_error( $product_brands ) ) : ?>
						<?php foreach ( $product_brands as $brand ) : ?>
							<button type="button" class="deraak-filter-pill" data-filter-brand="<?php echo esc_attr( $brand->name ); ?>" dir="ltr">
								<?php echo esc_html( $brand->name ); ?>
							</button>
						<?php endforeach; ?>
					<?php else : ?>
						<button type="button" class="deraak-filter-pill" data-filter-brand="Endress+Hauser" dir="ltr">Endress+Hauser</button>
						<button type="button" class="deraak-filter-pill" data-filter-brand="PR electronics" dir="ltr">PR electronics</button>
						<button type="button" class="deraak-filter-pill" data-filter-brand="ifm" dir="ltr">ifm electronic</button>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- شبکه محصولات -->
		<?php if ( woocommerce_product_loop() ) : ?>
			<div id="deraak-catalog-grid" class="deraak-products-grid">
				<?php
				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();
						do_action( 'woocommerce_shop_loop' );
						wc_get_template_part( 'content', 'product' );
					}
				}
				?>
			</div>
			<div style="margin-top:2rem;">
				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			</div>
		<?php else : ?>
			<!-- نمایش پیش‌فرض کاتالوگ صنعتی در صورت خالی بودن اولیه دیتابیس ووکامرس -->
			<div id="deraak-catalog-grid" class="deraak-products-grid">
				<?php
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
								data-url="<?php echo esc_url( home_url( '/#catalog' ) ); ?>"
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
								data-url="<?php echo esc_url( home_url( '/#contact' ) ); ?>"
								data-specs="<?php echo esc_attr( wp_json_encode( $item['specs'], JSON_UNESCAPED_UNICODE ) ); ?>"
							>
								مشخصات فنی
							</button>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer( 'shop' );
