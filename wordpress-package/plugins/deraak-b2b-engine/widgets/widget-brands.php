<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Deraak_Widget_Brands extends \Elementor\Widget_Base {

	public function get_name() {
		return 'deraak_brands';
	}

	public function get_title() {
		return 'برندهای صنعتی (Deraak)';
	}

	public function get_icon() {
		return 'eicon-logo';
	}

	public function get_categories() {
		return array( 'deraak-b2b' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => 'تنظیمات برندها',
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'hide_empty',
			array(
				'label'        => 'مخفی کردن برندهای بدون محصول',
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => 'بله',
				'label_off'    => 'خیر',
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$hide_empty = ( 'yes' === $settings['hide_empty'] );

		$brands = get_terms(
			array(
				'taxonomy'   => 'product_brand',
				'hide_empty' => $hide_empty,
			)
		);

		if ( empty( $brands ) || is_wp_error( $brands ) ) {
			echo '<p class="deraak-empty-notice">برند صنعتی ثبت نشده است.</p>';
			return;
		}
		?>
		<div class="deraak-brands-grid">
			<?php
			foreach ( $brands as $brand ) :
				$country    = get_term_meta( $brand->term_id, '_deraak_brand_country', true );
				$logo       = get_term_meta( $brand->term_id, '_deraak_brand_logo', true );
				$specialty  = get_term_meta( $brand->term_id, '_deraak_brand_specialty', true );
				$brand_link = get_term_link( $brand );
				if ( is_wp_error( $brand_link ) ) {
					$brand_link = '#';
				}
				?>
				<a href="<?php echo esc_url( $brand_link ); ?>" class="deraak-brand-card">
					<div class="deraak-brand-card__top">
						<?php if ( $logo ) : ?>
							<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $brand->name ); ?>" class="deraak-brand-card__logo" loading="lazy" />
						<?php else : ?>
							<span class="deraak-brand-card__abbr" dir="ltr"><?php echo esc_html( mb_substr( $brand->name, 0, 3 ) ); ?></span>
						<?php endif; ?>
						<?php if ( $country ) : ?>
							<span class="deraak-brand-card__country"><?php echo esc_html( $country ); ?></span>
						<?php endif; ?>
					</div>
					<h3 class="deraak-brand-card__name" dir="ltr"><?php echo esc_html( $brand->name ); ?></h3>
					<?php if ( $specialty ) : ?>
						<p class="deraak-brand-card__specialty"><?php echo esc_html( $specialty ); ?></p>
					<?php endif; ?>
					<?php if ( $brand->description ) : ?>
						<p class="deraak-brand-card__desc"><?php echo esc_html( wp_trim_words( $brand->description, 22 ) ); ?></p>
					<?php endif; ?>
					<span class="deraak-brand-card__link">مشاهده تجهیزات برند (<?php echo intval( $brand->count ); ?>) &larr;</span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
