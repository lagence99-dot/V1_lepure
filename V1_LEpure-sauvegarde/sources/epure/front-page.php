<?php
/**
 * Page d'accueil.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

// Une : dernier article de la rubrique choisie dans les réglages, sinon article épinglé, sinon dernier article.
$epure_hero     = array();
$epure_hero_cat = (int) epure_opt( 'home_hero_cat' );
if ( $epure_hero_cat ) {
	$epure_hero = get_posts( array( 'numberposts' => 1, 'cat' => $epure_hero_cat ) );
}
if ( ! $epure_hero ) {
	$epure_sticky_ids = get_option( 'sticky_posts' );
	if ( $epure_sticky_ids ) {
		$epure_hero = get_posts( array( 'numberposts' => 1, 'post__in' => $epure_sticky_ids ) );
	}
}
if ( ! $epure_hero ) {
	$epure_hero = get_posts( array( 'numberposts' => 1 ) );
}
?>
<main id="main">
<?php if ( ! $epure_hero ) : ?>
	<div class="wrap empty">
		<p>Aucun article publié pour le moment.</p>
		<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
			<p><a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">Écrire un premier article</a> ou <a href="<?php echo esc_url( admin_url( 'themes.php?page=epure-options' ) ); ?>">créer des articles de démonstration</a>.</p>
		<?php endif; ?>
	</div>
<?php else : ?>
	<?php
	$epure_h   = $epure_hero[0]->ID;
	$epure_now = get_posts( array( 'numberposts' => 4, 'post__not_in' => array( $epure_h ) ) );
	?>
	<section class="wrap hero">
		<div class="hero-txt">
			<a href="<?php echo esc_url( get_permalink( $epure_h ) ); ?>">
				<span class="kick">À la une · <?php echo esc_html( epure_kicker( $epure_h ) ); ?></span>
				<h1><?php echo esc_html( get_the_title( $epure_h ) ); ?></h1>
			</a>
			<p class="chapo"><?php echo esc_html( get_the_excerpt( $epure_h ) ); ?></p>
			<p class="meta"><?php echo esc_html( 'Par ' . epure_author_name( $epure_h ) . ' · ' . epure_reading_time( $epure_h ) . ' min de lecture' ); ?></p>
		</div>
		<a class="hero-img zoom" href="<?php echo esc_url( get_permalink( $epure_h ) ); ?>" aria-hidden="true" tabindex="-1"><?php echo epure_media( $epure_h, 'r45', 'epure-portrait' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php if ( $epure_now ) : ?>
		<div class="now">
			<h2>En ce moment</h2>
			<ul>
				<?php foreach ( $epure_now as $epure_p ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $epure_p ) ); ?>"><time datetime="<?php echo esc_attr( get_the_date( 'c', $epure_p ) ); ?>"><?php echo esc_html( get_the_date( 'j M · H \h i', $epure_p ) ); ?></time><h3><?php echo esc_html( get_the_title( $epure_p ) ); ?></h3></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
	</section>

	<?php
	// ——— Une tranche par rubrique principale ———
	$epure_count  = max( 3, (int) epure_opt( 'home_count' ) );
	$epure_cols   = ( 0 === $epure_count % 4 ) ? 'g4' : 'g3';
	$epure_bands  = array();
	foreach ( epure_home_categories() as $epure_c ) {
		$epure_found = get_posts( array( 'numberposts' => $epure_count, 'cat' => $epure_c->term_id ) );
		if ( $epure_found ) {
			$epure_bands[] = array( $epure_c, $epure_found );
		}
	}

	/** Affiche une tranche : titre de la rubrique, lien, cartes. */
	$epure_band = function ( $band, $cols, $ratio, $size ) {
		list( $cat, $posts ) = $band;
		?>
		<section class="band">
			<div class="sec-h">
				<h2><em><?php echo esc_html( $cat->name ); ?></em></h2>
				<a class="more" href="<?php echo esc_url( get_category_link( $cat ) ); ?>">Toute la rubrique</a>
			</div>
			<div class="grid <?php echo esc_attr( $cols ); ?>">
				<?php
				foreach ( $posts as $p ) {
					epure_card( $p->ID, $ratio, $size );
				}
				?>
			</div>
		</section>
		<?php
	};

	// Les deux premières tranches partagent la ligne avec la colonne de droite.
	$epure_first = array_splice( $epure_bands, 0, 2 );
	if ( $epure_first ) :
		?>
	<div class="wrap sec">
		<div class="with-side">
			<div class="bands">
				<?php
				foreach ( $epure_first as $epure_b ) {
					$epure_band( $epure_b, 'g3', 'r45', 'epure-portrait' );
				}
				?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
	<?php endif; ?>

	<?php
	// ——— Mise en avant partenaire ———
	$epure_mea = epure_opt( 'mea' );
	if ( ! empty( $epure_mea['on'] ) && '' !== trim( (string) $epure_mea['title'] ) ) :
		$epure_url = $epure_mea['url'] ? $epure_mea['url'] : '#';
		?>
	<section class="mea-partner" aria-label="Contenu partenaire"><div class="wrap">
		<a href="<?php echo esc_url( $epure_url ); ?>" class="zoom" rel="sponsored">
			<?php if ( $epure_mea['img'] ) : ?>
				<span class="ph has-img r32"><img src="<?php echo esc_url( $epure_mea['img'] ); ?>" alt="" loading="lazy"></span>
			<?php else : ?>
				<span class="ph r32" style="--c1:#e8d9c4;--c2:#8c6a45;--c3:#1d140c;--fx:58%"></span>
			<?php endif; ?>
		</a>
		<div>
			<span class="mea-lbl"><?php echo esc_html( $epure_mea['label'] ? $epure_mea['label'] : 'Contenu partenaire' ); ?></span>
			<h2><?php echo epure_section_title( $epure_mea['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php if ( $epure_mea['text'] ) : ?><p><?php echo esc_html( $epure_mea['text'] ); ?></p><?php endif; ?>
			<?php if ( $epure_mea['url'] && $epure_mea['cta'] ) : ?><a class="cta" href="<?php echo esc_url( $epure_mea['url'] ); ?>" rel="sponsored"><?php echo esc_html( $epure_mea['cta'] ); ?></a><?php endif; ?>
		</div>
	</div></section>
	<?php endif; ?>

	<?php
	// Tranches suivantes en pleine largeur ; le billboard se glisse après la deuxième.
	$epure_mid = epure_get_ad( 'home-mid' );
	foreach ( $epure_bands as $epure_i => $epure_b ) {
		echo '<div class="wrap sec">';
		$epure_band( $epure_b, $epure_cols, 'g4' === $epure_cols ? 'r45' : 'r32', 'g4' === $epure_cols ? 'epure-portrait' : 'epure-landscape' );
		echo '</div>';
		if ( 1 === $epure_i && '' !== $epure_mid ) {
			echo '<div class="wrap slot-mid">' . $epure_mid . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			$epure_mid = '';
		}
	}
	if ( '' !== $epure_mid ) {
		echo '<div class="wrap slot-mid">' . $epure_mid . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>

	<?php
	// ——— Sélection shopping ———
	$epure_shop  = epure_opt( 'shop' );
	$epure_items = array();
	foreach ( (array) $epure_shop['items'] as $epure_it ) {
		if ( ! empty( $epure_it['name'] ) ) {
			$epure_items[] = $epure_it;
		}
	}
	if ( ! empty( $epure_shop['on'] ) && $epure_items ) :
		?>
	<section class="shop" aria-label="Sélection shopping"><div class="wrap">
		<div class="sec-h"><h2><?php echo epure_section_title( $epure_shop['title'] ? $epure_shop['title'] : 'La sélection' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div>
		<div class="grid g4">
			<?php foreach ( $epure_items as $epure_k => $epure_it ) : ?>
				<a class="prod" href="<?php echo esc_url( $epure_it['url'] ? $epure_it['url'] : '#' ); ?>" target="_blank" rel="sponsored noopener">
					<span class="zoom">
						<?php if ( $epure_it['img'] ) : ?>
							<span class="ph has-img r11"><img src="<?php echo esc_url( $epure_it['img'] ); ?>" alt="" loading="lazy"></span>
						<?php else : ?>
							<?php echo epure_plate( $epure_k + 4, 'r11' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php endif; ?>
					</span>
					<b><?php echo esc_html( $epure_it['name'] ); ?></b>
					<?php if ( $epure_it['desc'] ) : ?><span><?php echo esc_html( $epure_it['desc'] ); ?></span><?php endif; ?>
					<?php if ( $epure_it['price'] ) : ?><i><?php echo esc_html( $epure_it['price'] ); ?></i><?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div></section>
	<?php endif; ?>
<?php endif; ?>
</main>
<?php
get_footer();
