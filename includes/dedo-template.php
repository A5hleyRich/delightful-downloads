<?php

/**
 * The template for displaying all single download
 */
get_header();
?>

<div id="primary" class="content-area" style="max-width:900px;background-color:white;margin:0 auto;">
    <main id="main" class="site-main" role="main">
        <?php
        // Start the loop.
        while ( have_posts() ) : the_post();
            ?>
            <article id = "post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php
                    if ( is_single() ) :
                        the_title( '<h1 class="entry-title">', '</h1>' );
                    else :
                        the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
                    endif;
                    ?>
                </header><!-- .entry-header -->
				<div class="entry-content">
				<?php
				the_content();
				echo '<br><br>'.do_shortcode('[ddownload id="'.get_the_ID().'" style="singlepost"]');
				?>
                </div><!-- .entry-content -->
                <footer class="entry-footer">
				<?php edit_post_link( __( 'Edit','WPdoodlez' ), '<span class="edit-link">', '</span>' ); ?>
                </footer><!-- .entry-footer -->
            </article>
            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
        // End the loop.
        endwhile;
        ?>

    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>