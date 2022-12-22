<?php
/**
 * The template for displaying all single download - penguin theme required
 *
 */
get_header();
?>
<div id="content-area">
	<div id="primary">
    <main id="main" class="site-main" role="main">
		<?php
		while ( have_posts() ) : the_post(); // Start the loop.	?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<?php
				$tcolor = get_theme_mod( 'link-color', '#006060' );
				$backgd = hexdec(substr($tcolor,1,2)).','.hexdec(substr($tcolor,3,2)).','.hexdec(substr($tcolor,5,2)).',.1';
				echo '<div style="background-color:#eee;background-color:rgba('.$backgd.')">';
				echo meta_icons(); 
				echo '</div>';
				if ( is_single() ) {
					the_title( '<h1 class="entry-title">', '</h1>' );
				} else {
					the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
				}
				?>
			</header><!-- .entry-header -->
			<div class="entry-content">
				<?php
				the_content();
				echo '<br><br>'.do_shortcode('[ddownload id="'.get_the_ID().'" style="singlepost"]');
				?>
            </div><!-- .entry-content -->
			<footer class="entry-footer">
			</footer><!-- .entry-footer -->
          </article>
		  <?php // If comments are open or we have at least one comment, load up the comment template.
		  if ( comments_open() || get_comments_number() ) comments_template();
		  setPostViews(get_the_ID());
		  // End the loop.
        endwhile; ?>
    </main><!-- .site-main -->
	</div><!-- #primary -->
<?php get_sidebar(); ?>
</div><!-- #content-area -->
<?php get_footer(); ?>
