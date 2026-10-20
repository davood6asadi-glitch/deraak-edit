<?php
/**
 * Generic Archive Template
 *
 * @package Deraak_Industrial
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="derak-container" style="padding-top: 30px; padding-bottom: 50px;">
    <h1 style="color:var(--derak-primary); margin-top:0; font-size:22px;"><?php the_archive_title(); ?></h1>
    <?php the_archive_description('<div style="color:var(--derak-stone-muted); margin-bottom:20px;">', '</div>'); ?>

    <?php if (have_posts()) : ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('derak-product-card'); ?>>
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="derak-card-media">
                            <?php the_post_thumbnail('medium', array('class' => 'derak-card-img')); ?>
                        </div>
                    <?php endif; ?>
                    <h3 class="derak-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p style="font-size:12px; color:var(--derak-stone-muted); line-height:1.5;"><?php echo wp_trim_words(get_the_excerpt(), 18); ?></p>
                    <a href="<?php the_permalink(); ?>" class="derak-btn-card-detail" style="margin-top:auto;">مطالعه بیشتر</a>
                </article>
            <?php endwhile; ?>
        </div>
        <div style="margin-top:30px; text-align:center;">
            <?php the_posts_pagination(); ?>
        </div>
    <?php else : ?>
        <p>موردی یافت نشد.</p>
    <?php endif; ?>
</div>

<?php
get_footer();
