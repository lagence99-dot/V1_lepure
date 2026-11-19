<?php
/**
 * Fonctions d'affichage partagées par les gabarits.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Identifiants déjà affichés sur la page, pour éviter les doublons entre sections. */
function epure_seen( $add = null ) {
	static $ids = array();
	if ( null !== $add ) {
		foreach ( (array) $add as $id ) {
			$ids[] = (int) $id;
		}
	}
	return $ids;
}

/** Raccourci get_posts qui exclut les articles déjà affichés et les enregistre. */
function epure_posts( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'numberposts' => 4,
			'post_type'   => 'post',
			'post_status' => 'publish',
		)
	);
	$args['post__not_in'] = array_merge( epure_seen(), isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array() );
	$posts = get_posts( $args );
	epure_seen( wp_list_pluck( $posts, 'ID' ) );
	return $posts;
}

/** Visuel d'un article : image à la une, sinon plaque de couleur de substitution. */
function epure_media( $post_id, $ratio = 'r45', $size = 'epure-portrait' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return '<span class="ph has-img ' . esc_attr( $ratio ) . '">' . get_the_post_thumbnail( $post_id, $size, array( 'loading' => 'lazy' ) ) . '</span>';
	}
	return epure_plate( $post_id, $ratio );
}

/** Plaque de couleur de substitution, choisie d'après un numéro quelconque. */
function epure_plate( $seed, $ratio = 'r45' ) {
	$palettes = array(
		array( '#e3d8cb', '#8d7361', '#241d19' ),
		array( '#cdd6da', '#70858f', '#1c262b' ),
		array( '#ead6d2', '#b0646c', '#3b1821' ),
		array( '#dcd9c3', '#8d8a57', '#27271a' ),
		array( '#eee5d8', '#c3a67b', '#463620' ),
		array( '#d3cce0', '#766a92', '#211c2f' ),
		array( '#dfe7df', '#7f9b87', '#1a2820' ),
		array( '#b9b2b6', '#3b363c', '#0c0b0d' ),
		array( '#f3e6df', '#d5a79c', '#74463d' ),
		array( '#c6cedb', '#3f5c82', '#0f1929' ),
	);
	$p  = $palettes[ absint( $seed ) % count( $palettes ) ];
	$fx = array( 40, 30, 52, 36, 46 );
	return sprintf(
		'<span class="ph %s" style="--c1:%s;--c2:%s;--c3:%s;--fx:%d%%"></span>',
		esc_attr( $ratio ),
		esc_attr( $p[0] ),
		esc_attr( $p[1] ),
		esc_attr( $p[2] ),
		$fx[ absint( $seed ) % 5 ]
	);
}

/** Surtitre : rubrique principale, ou mention sponsorisée. */
function epure_kicker( $post_id ) {
	if ( epure_is_sponsored( $post_id ) ) {
		$partner = get_post_meta( $post_id, '_epure_partner', true );
		return 'Sponsorisé' . ( $partner ? ' · ' . $partner : '' );
	}
	$cats = get_the_category( $post_id );
	if ( empty( $cats ) ) {
		return '';
	}
	// Préfère la sous-rubrique quand il y en a une.
	foreach ( $cats as $c ) {
		if ( $c->parent ) {
			return $c->name;
		}
	}
	return $cats[0]->name;
}

function epure_author_name( $post_id ) {
	$post = get_post( $post_id );
	return $post ? get_the_author_meta( 'display_name', $post->post_author ) : '';
}

function epure_reading_time( $post_id ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) ceil( $words / 220 ) );
}

function epure_meta_line( $post_id, $with_time = false ) {
	$out = 'Par ' . epure_author_name( $post_id ) . ' · ' . get_the_date( 'j M', $post_id );
	if ( $with_time ) {
		$out .= ' · ' . epure_reading_time( $post_id ) . ' min';
	}
	return $out;
}

/** Carte d'article standard. */
function epure_card( $post_id, $ratio = 'r45', $size = 'epure-portrait', $class = '' ) {
	$sponsored = epure_is_sponsored( $post_id );
	printf(
		'<a class="card %s" href="%s"><span class="zoom">%s</span><div><span class="kick">%s</span><h3>%s</h3><p class="meta">%s</p></div></a>',
		esc_attr( trim( $class . ( $sponsored ? ' native-card' : '' ) ) ),
		esc_url( get_permalink( $post_id ) ),
		epure_media( $post_id, $ratio, $size ), // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html( epure_kicker( $post_id ) ),
		esc_html( get_the_title( $post_id ) ),
		esc_html( $sponsored ? 'Contenu partenaire' : epure_meta_line( $post_id ) )
	);
}

/** Article principal d'une section (visuel paysage + texte). */
function epure_lead( $post_id ) {
	printf(
		'<a class="lead" href="%s"><span class="zoom">%s</span><div><span class="kick">%s</span><h3>%s</h3><p class="dek">%s</p><p class="meta">%s</p></div></a>',
		esc_url( get_permalink( $post_id ) ),
		epure_media( $post_id, 'r32', 'epure-landscape' ), // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html( epure_kicker( $post_id ) ),
		esc_html( get_the_title( $post_id ) ),
		esc_html( get_the_excerpt( $post_id ) ),
		esc_html( epure_meta_line( $post_id, true ) )
	);
}

/** Titre de section : le dernier mot passe en italique, comme sur la maquette. */
function epure_section_title( $title ) {
	$title = trim( $title );
	$pos   = strrpos( $title, ' ' );
	if ( false === $pos ) {
		return '<em>' . esc_html( $title ) . '</em>';
	}
	return esc_html( substr( $title, 0, $pos ) ) . ' <em>' . esc_html( substr( $title, $pos + 1 ) ) . '</em>';
}

/* ——— Compteur de lectures pour « Les plus lus » ——— */

add_action( 'wp_head', 'epure_count_view' );
function epure_count_view() {
	if ( ! is_singular( 'post' ) || is_user_logged_in() || is_preview() ) {
		return;
	}
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
	if ( '' === $ua || preg_match( '/bot|crawl|spider|slurp|preview/', $ua ) ) {
		return;
	}
	$id = get_queried_object_id();
	update_post_meta( $id, 'epure_views', (int) get_post_meta( $id, 'epure_views', true ) + 1 );
}

function epure_most_read( $count = 5 ) {
	$ids = get_transient( 'epure_most_read' );
	if ( false === $ids ) {
		$ids = get_posts(
			array(
				'numberposts' => $count,
				'fields'      => 'ids',
				'meta_key'    => 'epure_views', // phpcs:ignore WordPress.DB.SlowDBQuery
				'orderby'     => 'meta_value_num',
				'order'       => 'DESC',
				'date_query'  => array( array( 'after' => '60 days ago' ) ),
			)
		);
		if ( count( $ids ) < $count ) {
			$more = get_posts(
				array(
					'numberposts'  => $count - count( $ids ),
					'fields'       => 'ids',
					'post__not_in' => $ids,
				)
			);
			$ids  = array_merge( $ids, $more );
		}
		set_transient( 'epure_most_read', $ids, 15 * MINUTE_IN_SECONDS );
	}
	return array_map( 'intval', (array) $ids );
}

/** Menu de pied de page avec le nom du menu comme titre de colonne. */
function epure_footer_menu( $location ) {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}
	$locations = get_nav_menu_locations();
	$menu      = wp_get_nav_menu_object( $locations[ $location ] );
	echo '<div>';
	if ( $menu ) {
		echo '<h4>' . esc_html( $menu->name ) . '</h4>';
	}
	wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => 'foot-menu',
			'depth'          => 1,
			'fallback_cb'    => false,
		)
	);
	echo '</div>';
}

/**
 * Rubriques principales à afficher en tranches sur l'accueil :
 * d'abord dans l'ordre du menu principal, puis les autres par ordre alphabétique.
 */
function epure_home_categories() {
	$hide = array_map( 'intval', (array) epure_opt( 'home_hide' ) );
	$all  = array();
	foreach ( get_categories( array( 'parent' => 0, 'hide_empty' => 1 ) ) as $c ) {
		if ( ! in_array( (int) $c->term_id, $hide, true ) ) {
			$all[ (int) $c->term_id ] = $c;
		}
	}
	$ordered   = array();
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		$items = wp_get_nav_menu_items( $locations['primary'] );
		foreach ( (array) $items as $item ) {
			$id = (int) $item->object_id;
			if ( 'category' === $item->object && isset( $all[ $id ] ) ) {
				$ordered[ $id ] = $all[ $id ];
			}
		}
	}
	return array_values( $ordered + $all );
}
