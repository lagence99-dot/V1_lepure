<?php
/**
 * Page introuvable.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="main">
	<div class="wrap">
		<div class="cat-head">
			<span class="kick">Erreur 404</span>
			<h1>Page introuvable</h1>
			<p>Cette page n'existe pas ou a été déplacée. Essayez une recherche, ou revenez à <a href="<?php echo esc_url( home_url( '/' ) ); ?>">l'accueil</a>.</p>
			<?php get_search_form(); ?>
		</div>
		<?php $epure_latest = get_posts( array( 'numberposts' => 4 ) ); ?>
		<?php if ( $epure_latest ) : ?>
			<div class="sec-h"><h2>Derniers <em>articles</em></h2></div>
			<div class="grid g4">
				<?php
				foreach ( $epure_latest as $epure_p ) {
					epure_card( $epure_p->ID );
				}
				?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
