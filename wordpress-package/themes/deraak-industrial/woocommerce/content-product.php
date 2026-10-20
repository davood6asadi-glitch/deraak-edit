<?php
/**
 * The template for displaying product content within loops
 *
 * @package Deraak_Industrial
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$product_id  = $product->get_id();
$sku         = $product->get_sku();
$model_code  = get_post_meta( $product_id, '_deraak_model_code', true );
$lead_time   = get_post_meta( $product_id, '_deraak_lead_time', true );
$specs_raw   = get_post_meta( $product_id, '_deraak_specs_json', true );
$specs       = ! empty( $specs_raw ) ? json_decode( $specs_raw, true ) : array();
if ( ! is_array( $specs ) ) {
	$specs = array();
}

$brands     = wp_get_post_terms( $product_id, 'product_brand', array( 'fields' => 'names' ) );
$brand_name = ( ! is_wp_error( $brands ) && ! empty( $brands ) ) ? $brands[0] : 'Deraak';

$cats     = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'names' ) );
$cat_name = ( ! is_wp_error( $cats ) && ! empty( $cats ) ) ? $cats[0] : 'ابزار دقیق';

$img_url   = function_exists( 'deraak_get_product_image_url' )
	? deraak_get_product_image_url( $product_id, $sku, $model_code )
	: ( get_the_post_thumbnail_url( $product_id, 'medium' ) ?: '' );
$permalink = get_permalink( $product_id );
$excerpt   = wp_trim_words( get_the_excerpt( $product_id ), 18 );

$search_blob = implode(
	' ',
	array(
		get_the_title( $product_id ),
		$sku,
		$model_code,
		$brand_name,
		$cat_name,
		$excerpt,
	)
);
?>
<article
	<?php wc_product_class( 'deraak-product-card', $product ); ?>
	data-category="<?php echo esc_attr( $cat_name ); ?>"
	data-brand="<?php echo esc_attr( $brand_name ); ?>"
	data-search="<?php echo esc_attr( $search_blob ); ?>"
>
	<div>
		<div class="deraak-product-card__header">
			<span class="deraak-product-card__brand" dir="ltr"><?php echo esc_html( $brand_name ); ?></span>
			<span class="deraak-product-card__model font-mono" dir="ltr"><?php echo esc_html( $model_code ? $model_code : ( $sku ? $sku : 'IND-' . $product_id ) ); ?></span>
		</div>

		<a href="<?php echo esc_url( $permalink ); ?>" class="deraak-product-card__image-wrap">
			<?php if ( $img_url ) : ?>
				<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="deraak-product-card__image" loading="lazy" />
			<?php else : ?>
				<div class="deraak-product-card__placeholder font-mono" dir="ltr"><?php echo esc_html( $model_code ? $model_code : 'DERAAK' ); ?></div>
			<?php endif; ?>
		</a>

		<div class="deraak-product-card__body">
			<div class="deraak-product-card__cat-row">
				<span class="deraak-product-card__cat"><?php echo esc_html( $cat_name ); ?></span>
				<?php if ( $lead_time ) : ?>
					<span class="deraak-product-card__lead"><?php echo esc_html( $lead_time ); ?></span>
				<?php endif; ?>
			</div>

			<h3 class="deraak-product-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
			</h3>

			<?php if ( $excerpt ) : ?>
				<p class="deraak-product-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $specs ) ) : ?>
				<div class="deraak-product-card__specs">
					<?php foreach ( array_slice( $specs, 0, 3 ) as $spec ) : ?>
						<?php if ( ! empty( $spec['key'] ) ) : ?>
							<div class="deraak-product-card__spec-item">
								<span class="deraak-product-card__spec-key"><?php echo esc_html( $spec['key'] ); ?>:</span>
								<span class="deraak-product-card__spec-val font-mono" dir="ltr"><?php echo esc_html( isset( $spec['value'] ) ? $spec['value'] : '' ); ?></span>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="deraak-product-card__footer">
		<button
			type="button"
			class="deraak-btn deraak-btn--accent deraak-add-rfq-btn"
			data-id="<?php echo esc_attr( $product_id ); ?>"
			data-title="<?php echo esc_attr( get_the_title() ); ?>"
			data-sku="<?php echo esc_attr( $sku ? $sku : $model_code ); ?>"
			data-model="<?php echo esc_attr( $model_code ? $model_code : $sku ); ?>"
			data-brand="<?php echo esc_attr( $brand_name ); ?>"
			data-category="<?php echo esc_attr( $cat_name ); ?>"
			data-image="<?php echo esc_url( $img_url ); ?>"
			data-url="<?php echo esc_url( $permalink ); ?>"
		>
			+ استعلام قیمت
		</button>
		<button
			type="button"
			class="deraak-btn deraak-btn--outline deraak-quick-view-btn"
			data-id="<?php echo esc_attr( $product_id ); ?>"
			data-title="<?php echo esc_attr( get_the_title() ); ?>"
			data-sku="<?php echo esc_attr( $sku ? $sku : $model_code ); ?>"
			data-model="<?php echo esc_attr( $model_code ? $model_code : $sku ); ?>"
			data-brand="<?php echo esc_attr( $brand_name ); ?>"
			data-category="<?php echo esc_attr( $cat_name ); ?>"
			data-image="<?php echo esc_url( $img_url ); ?>"
			data-excerpt="<?php echo esc_attr( $excerpt ); ?>"
			data-url="<?php echo esc_url( $permalink ); ?>"
			data-specs="<?php echo esc_attr( wp_json_encode( $specs, JSON_UNESCAPED_UNICODE ) ); ?>"
		>
			مشخصات فنی
		</button>
	</div>
</article>
