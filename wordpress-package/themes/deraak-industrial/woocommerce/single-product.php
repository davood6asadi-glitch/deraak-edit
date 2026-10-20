<?php
/**
 * The Template for displaying all single products (Technical Datasheet B2B Layout)
 *
 * @package Deraak_Industrial
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

while ( have_posts() ) :
	the_post();
	global $product;

	$product_id    = $product->get_id();
	$sku           = $product->get_sku();
	$model_code    = get_post_meta( $product_id, '_deraak_model_code', true );
	$lead_time     = get_post_meta( $product_id, '_deraak_lead_time', true );
	$datasheet_url = get_post_meta( $product_id, '_deraak_datasheet_url', true );
	$apps_raw      = get_post_meta( $product_id, '_deraak_applications', true );
	$specs_raw     = get_post_meta( $product_id, '_deraak_specs_json', true );

	$specs = ! empty( $specs_raw ) ? json_decode( $specs_raw, true ) : array();
	if ( ! is_array( $specs ) ) {
		$specs = array();
	}

	// ادغام ویژگی‌های استاندارد ووکامرس در صورت خالی بودن جدول JSON
	if ( empty( $specs ) ) {
		foreach ( $product->get_attributes() as $attribute ) {
			$name  = wc_attribute_label( $attribute->get_name() );
			$value = $product->get_attribute( $attribute->get_name() );
			if ( $name && $value ) {
				$specs[] = array(
					'key'   => $name,
					'value' => $value,
				);
			}
		}
	}

	$brands        = wp_get_post_terms( $product_id, 'product_brand' );
	$brand_name    = ( ! is_wp_error( $brands ) && ! empty( $brands ) ) ? $brands[0]->name : 'Deraak';
	$brand_country = ( ! is_wp_error( $brands ) && ! empty( $brands ) ) ? get_term_meta( $brands[0]->term_id, '_deraak_brand_country', true ) : '';

	$cats     = wp_get_post_terms( $product_id, 'product_cat' );
	$cat_name = ( ! is_wp_error( $cats ) && ! empty( $cats ) ) ? $cats[0]->name : 'تجهیزات ابزار دقیق';
	$cat_link = ( ! is_wp_error( $cats ) && ! empty( $cats ) ) ? get_term_link( $cats[0] ) : '';

	$img_url = function_exists( 'deraak_get_product_image_url' )
		? deraak_get_product_image_url( $product_id, $sku, $model_code )
		: ( get_the_post_thumbnail_url( $product_id, 'large' ) ?: '' );

	$applications = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $apps_raw ) ) );
	$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/?post_type=product' );
	?>
	<main id="primary" class="site-main deraak-section">
		<div class="deraak-container">
			<!-- مسیر راهنما (Breadcrumb) -->
			<nav class="deraak-breadcrumb" aria-label="مسیر راهنما">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">صفحه اصلی</a>
				<span>/</span>
				<a href="<?php echo esc_url( $shop_url ); ?>">کاتالوگ تجهیزات</a>
				<?php if ( $cat_name && ! is_wp_error( $cat_link ) && $cat_link ) : ?>
					<span>/</span>
					<a href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $cat_name ); ?></a>
				<?php endif; ?>
				<span>/</span>
				<strong><?php the_title(); ?></strong>
			</nav>

			<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'deraak-single-product', $product ); ?>>
				<div class="deraak-single-product__grid">
					<!-- ستون اول: تصویر صنعتی، متادیتای کلیدی، کاربردها و دانلود دیتاشیت -->
					<div class="deraak-single-product__media-card">
						<div class="deraak-single-product__gallery">
							<?php if ( $img_url ) : ?>
								<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" style="max-height:320px;width:auto;object-fit:contain;" />
							<?php else : ?>
								<div class="deraak-product-card__placeholder font-mono" dir="ltr"><?php echo esc_html( $model_code ? $model_code : 'DERAAK' ); ?></div>
							<?php endif; ?>
						</div>

						<div class="deraak-single-product__meta-list">
							<div class="deraak-single-product__meta-box">
								<span class="deraak-single-product__meta-label">برند سازنده</span>
								<span class="deraak-single-product__meta-val" dir="ltr">
									<?php echo esc_html( $brand_name ); ?>
									<?php if ( $brand_country ) : ?>
										<small style="color:var(--color-muted-fg);font-weight:500;">(<?php echo esc_html( $brand_country ); ?>)</small>
									<?php endif; ?>
								</span>
							</div>
							<div class="deraak-single-product__meta-box">
								<span class="deraak-single-product__meta-label">کد مدل فنی (Model)</span>
								<span class="deraak-single-product__meta-val font-mono" dir="ltr"><?php echo esc_html( $model_code ? $model_code : ( $sku ? $sku : 'N/A' ) ); ?></span>
							</div>
							<div class="deraak-single-product__meta-box">
								<span class="deraak-single-product__meta-label">کد کاتالوگ (SKU)</span>
								<span class="deraak-single-product__meta-val font-mono" dir="ltr"><?php echo esc_html( $sku ? $sku : 'DRK-' . $product_id ); ?></span>
							</div>
							<div class="deraak-single-product__meta-box">
								<span class="deraak-single-product__meta-label">وضعیت تأمین و تحویل</span>
								<span class="deraak-single-product__meta-val" style="color:var(--color-success);">
									<?php echo esc_html( $lead_time ? $lead_time : 'آماده استعلام و تأمین' ); ?>
								</span>
							</div>
						</div>

						<?php if ( ! empty( $applications ) ) : ?>
							<div style="margin-bottom:1.25rem;padding:1rem;background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-md);">
								<h4 style="font-size:0.86rem;margin:0 0 0.5rem;">کاربردهای صنعتی پیشنهادی:</h4>
								<ul style="margin:0;padding-right:1.15rem;font-size:0.83rem;color:var(--color-muted-fg);">
									<?php foreach ( $applications as $app_line ) : ?>
										<li><?php echo esc_html( $app_line ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

						<?php if ( $datasheet_url ) : ?>
							<a href="<?php echo esc_url( $datasheet_url ); ?>" target="_blank" rel="noopener noreferrer" class="deraak-btn deraak-btn--outline deraak-btn--full">
								📄 دانلود دیتاشیت فنی و کاتالوگ (PDF)
							</a>
						<?php endif; ?>
					</div>

					<!-- ستون دوم: عنوان، هایلایت‌های فنی، باکس استعلام قیمت B2B و جدول کامل مشخصات -->
					<div class="deraak-single-product__info-card">
						<div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.65rem;">
							<span class="deraak-product-card__brand" dir="ltr"><?php echo esc_html( $brand_name ); ?></span>
							<span class="deraak-product-card__cat"><?php echo esc_html( $cat_name ); ?></span>
						</div>

						<h1 style="font-size:clamp(1.35rem, 2.5vw, 1.85rem);margin-bottom:0.75rem;"><?php the_title(); ?></h1>

						<div style="color:var(--color-muted-fg);font-size:0.95rem;margin-bottom:1.25rem;">
							<?php echo wp_kses_post( wpautop( $product->get_short_description() ? $product->get_short_description() : get_the_excerpt() ) ); ?>
						</div>

						<?php if ( ! empty( $specs ) ) : ?>
							<div class="deraak-highlights-grid">
								<?php foreach ( array_slice( $specs, 0, 3 ) as $hl ) : ?>
									<div class="deraak-highlight-box">
										<span class="deraak-highlight-box__label"><?php echo esc_html( $hl['key'] ); ?></span>
										<span class="deraak-highlight-box__val font-mono" dir="ltr"><?php echo esc_html( $hl['value'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<!-- باکس استعلام رسمی قیمت و موجودی (RFQ) -->
						<div class="deraak-inquiry-box">
							<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
								<div>
									<strong style="font-size:1rem;color:var(--slate-50);display:block;">استعلام قیمت رسمی و بررسی تطابق کد سفارش (Order Code)</strong>
									<span style="font-size:0.8rem;color:var(--slate-300);">تجهیز را به لیست استعلام اضافه کنید تا پیش‌فاکتور رسمی برای شما صادر شود.</span>
								</div>
								<span class="deraak-badge-count" style="background:var(--yellow-950);color:var(--yellow-400);border:1px solid var(--yellow-700);">ضمانت اصالت کالا</span>
							</div>

							<div class="deraak-inquiry-box__controls">
								<label for="deraak-single-qty" style="font-size:0.82rem;">تعداد درخواستی:</label>
								<input type="number" id="deraak-single-qty" class="deraak-qty-input font-mono" value="1" min="1" max="999" dir="ltr" />

								<button
									type="button"
									id="deraak-single-add-rfq"
									class="deraak-btn deraak-btn--accent deraak-add-rfq-btn"
									data-id="<?php echo esc_attr( $product_id ); ?>"
									data-title="<?php echo esc_attr( get_the_title() ); ?>"
									data-sku="<?php echo esc_attr( $sku ? $sku : $model_code ); ?>"
									data-model="<?php echo esc_attr( $model_code ? $model_code : $sku ); ?>"
									data-brand="<?php echo esc_attr( $brand_name ); ?>"
									data-category="<?php echo esc_attr( $cat_name ); ?>"
									data-image="<?php echo esc_url( $img_url ); ?>"
									data-url="<?php echo esc_url( get_permalink() ); ?>"
									data-qty-input="#deraak-single-qty"
								>
									+ افزودن به لیست استعلام قیمت
								</button>

								<a
									href="<?php echo esc_url( 'https://wa.me/989121112233?text=' . rawurlencode( 'سلام، جهت استعلام قیمت و مشخصات فنی تجهیز ' . get_the_title() . ' (' . ( $model_code ? $model_code : $sku ) . ') پیام می‌دهم.' ) ); ?>"
									target="_blank"
									rel="noopener noreferrer"
									class="deraak-btn deraak-btn--ghost-light"
								>
									مشاوره واتساپ مهندسی
								</a>
							</div>
						</div>

						<!-- جدول کامل مشخصات فنی -->
						<?php if ( ! empty( $specs ) ) : ?>
							<div style="margin-top:1.75rem;">
								<h2 style="font-size:1.15rem;margin-bottom:0.75rem;">جدول مشخصات فنی (Technical Specifications)</h2>
								<table class="deraak-spec-table">
									<tbody>
										<?php foreach ( $specs as $spec_row ) : ?>
											<?php if ( ! empty( $spec_row['key'] ) ) : ?>
												<tr>
													<th><?php echo esc_html( $spec_row['key'] ); ?></th>
													<td class="font-mono" dir="ltr"><?php echo esc_html( isset( $spec_row['value'] ) ? $spec_row['value'] : '' ); ?></td>
												</tr>
											<?php endif; ?>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>

						<!-- توضیحات تکمیلی مهندسی -->
						<?php if ( get_the_content() ) : ?>
							<div style="margin-top:1.75rem;padding-top:1.25rem;border-top:1px solid var(--color-border);">
								<h2 style="font-size:1.15rem;margin-bottom:0.75rem;">بررسی فنی و نکات مهندسی</h2>
								<div style="font-size:0.92rem;line-height:1.85;color:var(--color-fg);">
									<?php the_content(); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</main>

	<!-- اسکریپت ثبت محصول در لیست مشاهدات اخیر IndexedDB -->
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		if (window.DeraakRecentlyViewed && typeof window.DeraakRecentlyViewed.add === 'function') {
			window.DeraakRecentlyViewed.add({
				id: <?php echo intval( $product_id ); ?>,
				title: <?php echo wp_json_encode( get_the_title(), JSON_UNESCAPED_UNICODE ); ?>,
				sku: <?php echo wp_json_encode( $sku ? $sku : $model_code, JSON_UNESCAPED_UNICODE ); ?>,
				model: <?php echo wp_json_encode( $model_code ? $model_code : $sku, JSON_UNESCAPED_UNICODE ); ?>,
				brand: <?php echo wp_json_encode( $brand_name, JSON_UNESCAPED_UNICODE ); ?>,
				category: <?php echo wp_json_encode( $cat_name, JSON_UNESCAPED_UNICODE ); ?>,
				url: <?php echo wp_json_encode( get_permalink(), JSON_UNESCAPED_UNICODE ); ?>,
				image: <?php echo wp_json_encode( $img_url, JSON_UNESCAPED_UNICODE ); ?>
			});
		}
	});
	</script>
	<?php
endwhile;

get_footer( 'shop' );
