<?php
/**
 * Page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="main">
<?php
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'wrap' ); ?>>
		<header class="art-head">
			<h1><?php the_title(); ?></h1>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="art-fig"><span class="ph has-img r169"><?php the_post_thumbnail( 'epure-wide' ); ?></span></figure>
		<?php endif; ?>
		<div class="art-body page-body">
			<?php the_content(); ?>
		</div>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</article>
	<?php
endwhile;
?>
</main>
<?php
get_footer();
