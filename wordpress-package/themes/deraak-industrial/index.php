<?php
/**
 * The main template file
 *
 * @package Deraak_Industrial
 */

if ( is_front_page() && file_exists( get_template_directory() . '/front-page.php' ) ) {
	include get_template_directory() . '/front-page.php';
	return;
}

get_header();
?>
<main id="primary" class="site-main deraak-section">
	<div class="deraak-container">
		<?php if ( have_posts() ) : ?>
			<div class="deraak-products-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'deraak-product-card' ); ?>>
						<div class="deraak-product-card__body">
							<h2 class="deraak-product-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
							<div class="deraak-product-card__excerpt">
								<?php the_excerpt(); ?>
							</div>
						</div>
						<div class="deraak-product-card__footer">
							<a href="<?php the_permalink(); ?>" class="deraak-btn deraak-btn--primary deraak-btn--full">مشاهده جزئیات</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<div style="margin-top:2rem;">
				<?php the_posts_pagination(); ?>
			</div>
		<?php else : ?>
			<div class="deraak-empty-state" style="background:var(--color-card);padding:2rem;border-radius:var(--radius-xl);border:1px solid var(--color-border);text-align:center;">
				<h2>محتوایی یافت نشد</h2>
				<p>لطفاً از طریق کاتالوگ تجهیزات صنعتی یا جستجوی بالای صفحه، محصول مورد نظر خود را بررسی فرمایید.</p>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
