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
	<div id="posts-container">
		<?php
		 while ( have_posts() ) {  // Start the loop.
				the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<?php
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
				echo '<div style="margin-top:2em">'.do_shortcode('[ddownload id="'.get_the_ID().'" style="singlepost"]').'</div>';
				?>
            </div><!-- .entry-content -->
			<footer class="entry-footer">
			</footer><!-- .entry-footer -->
			<?php
				// meta icons unten
				echo '<div>';
				echo meta_icons(); 
				echo '</div>';
			?>
          </article>
		  <?php
			penguin_post_navigation();
		  // If comments are open or we have at least one comment, load up the comment template.
		  if ( comments_open() || get_comments_number() ) comments_template();
		  setPostViews(get_the_ID());
		 } // End the loop.
		 ?>
	</div><!-- #posts-container -->
	<div class="footer-sidebar">
	<?php dynamic_sidebar( 'sidebar-2' );  ?>
	</div>
    </main><!-- .site-main -->
	</div><!-- #primary -->
<?php get_sidebar(); ?>
</div><!-- #content-area -->
<?php get_footer(); ?>
