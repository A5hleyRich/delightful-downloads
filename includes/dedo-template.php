<?php
/** Unified singular template for dedo_download. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div id="content-area" class="dedo-singular-wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header"><?php the_title( '<h1 class="entry-title">', '</h1>' ); ?></header>
                    <div class="entry-content">
                        <?php the_content(); ?>
                        <div class="dedo-singular-download"><?php echo do_shortcode( '[ddownload id="' . get_the_ID() . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </div>
                    <footer class="entry-footer"><?php edit_post_link( __( 'Edit', 'delightful-downloads' ), '<span class="edit-link">', '</span>' ); ?></footer>
                </article>
                <?php if ( function_exists( 'penguin_post_navigation' ) ) penguin_post_navigation(); else the_post_navigation(); ?>
                <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
                <?php if ( function_exists( 'setPostViews' ) ) setPostViews( get_the_ID() ); ?>
            <?php endwhile; ?>
        </main>
    </div>
    <?php if ( function_exists( 'get_sidebar' ) ) get_sidebar(); ?>
</div>
<?php get_footer();
