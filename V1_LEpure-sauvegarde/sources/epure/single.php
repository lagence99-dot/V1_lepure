<?php
/**
 * Article.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	$epure_id   = get_the_ID();
	$epure_cats = get_the_category();
	$epure_main = null;
	$epure_sub  = null;
	foreach ( $epure_cats as $epure_c ) {
		if ( $epure_c->parent && ! $epure_sub ) {
			$epure_sub = $epure_c;
		}
	}
	if ( $epure_sub ) {
		$epure_main = get_category( $epure_sub->parent );
	} elseif ( $epure_cats ) {
		$epure_main = $epure_cats[0];
	}
	if ( is_wp_error( $epure_main ) ) {
		$epure_main = null;
	}
	$epure_sponsored = epure_is_sponsored( $epure_id );
	$epure_partner   = get_post_meta( $epure_id, '_epure_partner', true );
	?>
<main id="main">
	<article <?php post_class( 'wrap' ); ?>>
		<p class="crumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a>
			<?php if ( $epure_main ) : ?> / <a href="<?php echo esc_url( get_category_link( $epure_main ) ); ?>"><?php echo esc_html( $epure_main->name ); ?></a><?php endif; ?>
			<?php if ( $epure_sub ) : ?> / <a href="<?php echo esc_url( get_category_link( $epure_sub ) ); ?>"><?php echo esc_html( $epure_sub->name ); ?></a><?php endif; ?>
		</p>
		<header class="art-head">
			<span class="kick"><?php echo esc_html( epure_kicker( $epure_id ) ); ?></span>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="chapo"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<div class="byline">
				<span class="meta">Par <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a></span>
				<span class="meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time> · <?php echo (int) epure_reading_time( $epure_id ); ?> min de lecture</span>
			</div>
			<div class="share"><button type="button" id="copy-link">Copier le lien</button></div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
		<figure class="art-fig">
			<span class="ph has-img r169"><?php the_post_thumbnail( 'epure-wide', array( 'loading' => 'eager' ) ); ?></span>
			<?php if ( get_the_post_thumbnail_caption() ) : ?><figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption><?php endif; ?>
		</figure>
		<?php endif; ?>

		<div class="with-side">
			<div>
				<div class="art-body">
					<?php if ( $epure_sponsored ) : ?>
						<p class="partner-note">Contenu partenaire<?php echo $epure_partner ? esc_html( ', réalisé avec ' . $epure_partner ) : ''; ?>.</p>
					<?php endif; ?>
					<?php
					the_content();
					wp_link_pages( array( 'before' => '<p class="meta">Pages : ', 'after' => '</p>' ) );
					?>
					<?php if ( has_tag() ) : ?>
						<div class="tags"><?php the_tags( '', '', '' ); ?></div>
					<?php endif; ?>
				</div>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</article>

	<?php
	$epure_related = get_posts(
		array(
			'numberposts'  => 4,
			'post__not_in' => array( $epure_id ),
			'category__in' => wp_list_pluck( $epure_cats, 'term_id' ),
		)
	);
	if ( $epure_related ) :
		?>
	<section class="wrap sec">
		<div class="sec-h"><h2>À lire <em>aussi</em></h2></div>
		<div class="grid g4">
			<?php
			foreach ( $epure_related as $epure_p ) {
				epure_card( $epure_p->ID );
			}
			?>
		</div>
	</section>
	<?php endif; ?>
</main>
	<?php
endwhile;
get_footer();
