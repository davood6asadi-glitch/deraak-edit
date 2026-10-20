<?php
/**
 * Single Post Template
 *
 * @package Deraak_Industrial
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="derak-container" style="padding-top: 30px; padding-bottom: 50px;">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 style="color:var(--derak-primary); margin-top:0; font-size:24px;"><?php the_title(); ?></h1>
            <div style="font-size:12px; color:var(--derak-stone-muted); margin-bottom:20px;">
                تاریخ انتشار: <?php echo get_the_date(); ?>
            </div>
            
            <?php if (has_post_thumbnail()) : ?>
                <div style="margin-bottom:24px; border-radius:8px; overflow:hidden;">
                    <?php the_post_thumbnail('large', array('style' => 'width:100%; height:auto;')); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content" style="line-height:1.8; color:var(--derak-stone-charcoal);">
                <?php the_content(); ?>
            </div>
        </article>
        <?php
    endwhile;
    ?>
</div>

<?php
get_footer();
