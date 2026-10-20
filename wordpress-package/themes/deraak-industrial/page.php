<?php
/**
 * Page Template for Deraak Industrial Theme
 * Supports Elementor Canvas & Full Width
 *
 * @package Deraak_Industrial
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="derak-container" style="padding-top: 24px; padding-bottom: 40px;">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php if (!is_front_page()) : ?>
                <h1 style="color:var(--derak-primary); margin-top:0;"><?php the_title(); ?></h1>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                the_content();
                ?>
            </div>
        </article>
        <?php
    endwhile;
    ?>
</div>

<?php
get_footer();
