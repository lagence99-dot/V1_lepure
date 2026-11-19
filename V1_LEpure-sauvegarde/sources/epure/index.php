<?php
/**
 * Listes d'articles : rubriques, mots-clés, auteurs, dates, recherche, blog.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$epure_kick  = 'Articles';
$epure_title = 'Tous les articles';
$epure_desc  = '';
$epure_chips = array();

if ( is_search() ) {
	$epure_kick  = 'Recherche';
	$epure_title = get_search_query() ? get_search_query() : 'Rechercher';
} elseif ( is_category() ) {
	$epure_term  = get_queried_object();
	$epure_kick  = 'Rubrique';
	$epure_title = single_cat_title( '', false );
	$epure_desc  = category_description();
	// Sous-rubriques : celles de la rubrique courante, ou ses sœurs si elle est elle-même une sous-rubrique.
	$epure_root = $epure_term->parent ? get_category( $epure_term->parent ) : $epure_term;
	if ( $epure_root && ! is_wp_error( $epure_root ) ) {
		$epure_children = get_categories( array( 'parent' => $epure_root->term_id, 'hide_empty' => 1 ) );
		if ( $epure_children ) {
			$epure_chips[] = array( 'Tout', get_category_link( $epure_root ), $epure_root->term_id === $epure_term->term_id );
			foreach ( $epure_children as $epure_c ) {
				$epure_chips[] = array( $epure_c->name, get_category_link( $epure_c ), $epure_c->term_id === $epure_term->term_id );
			}
		}
	}
} elseif ( is_tag() ) {
	$epure_kick  = 'Mot-clé';
	$epure_title = single_tag_title( '', false );
	$epure_desc  = tag_description();
} elseif ( is_author() ) {
	$epure_kick  = 'Signature';
	$epure_title = get_the_author_meta( 'display_name', get_queried_object_id() );
	$epure_desc  = get_the_author_meta( 'description', get_queried_object_id() );
} elseif ( is_archive() ) {
	$epure_kick  = 'Archives';
	$epure_title = get_the_archive_title();
}

$epure_ids = array();
while ( have_posts() ) {
	the_post();
	$epure_ids[] = get_the_ID();
}
wp_reset_postdata();
?>
<main id="main">
	<div class="wrap">
		<div class="cat-head">
			<span class="kick"><?php echo esc_html( $epure_kick ); ?></span>
			<h1><?php echo esc_html( wp_strip_all_tags( $epure_title ) ); ?></h1>
			<?php if ( $epure_desc ) : ?><p><?php echo esc_html( wp_strip_all_tags( $epure_desc ) ); ?></p><?php endif; ?>
			<?php if ( is_search() ) : ?><?php get_search_form(); ?><?php endif; ?>
			<?php if ( $epure_chips ) : ?>
				<div class="chips">
					<?php foreach ( $epure_chips as $epure_chip ) : ?>
						<a href="<?php echo esc_url( $epure_chip[1] ); ?>" class="<?php echo $epure_chip[2] ? 'is-on' : ''; ?>"<?php echo $epure_chip[2] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $epure_chip[0] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! $epure_ids ) : ?>
			<p class="empty"><?php echo is_search() ? 'Aucun article ne correspond à cette recherche.' : 'Aucun article dans cette rubrique pour le moment.'; ?></p>
		<?php else : ?>
		<div class="with-side">
			<div>
				<?php
				if ( ! is_paged() && count( $epure_ids ) > 3 ) {
					epure_lead( array_shift( $epure_ids ) );
				}
				$epure_native = ( is_category() || is_home() ) ? epure_native_post_id( $epure_ids ) : 0;
				?>
				<div class="grid g3">
					<?php
					foreach ( $epure_ids as $epure_i => $epure_id ) {
						if ( 4 === $epure_i && $epure_native ) {
							epure_card( $epure_native );
						}
						epure_card( $epure_id );
					}
					?>
				</div>
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => 'Précédent',
						'next_text' => 'Suivant',
					)
				);
				?>
			</div>
			<?php get_sidebar(); ?>
		</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
