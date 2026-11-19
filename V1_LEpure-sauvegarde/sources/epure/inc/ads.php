<?php
/**
 * Emplacements publicitaires.
 *
 * Chaque emplacement reçoit une image avec lien choisie dans les réglages, ou un code de régie.
 * Emplacement activé mais sans code : les visiteurs ne voient rien, les administrateurs
 * connectés voient un gabarit hachuré aux bonnes dimensions.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function epure_ad_slots() {
	$top = 'leaderboard' === epure_opt( 'top_format' ) ? array( 728, 90, 'Leaderboard' ) : array( 970, 250, 'Billboard' );
	return array(
		'header-top'     => array( 'label' => 'Bannière haute (sous le menu)', 'd' => $top, 'm' => array( 320, 100, 'Bannière mobile' ) ),
		'sidebar-top'    => array( 'label' => 'Colonne de droite, haut', 'd' => array( 300, 250, 'Pavé' ), 'm' => array( 300, 250, 'Pavé' ) ),
		'sidebar-sticky' => array( 'label' => 'Colonne de droite, collant au défilement', 'd' => array( 300, 600, 'Grand angle' ), 'm' => array( 300, 600, 'Grand angle' ) ),
		'home-mid'       => array( 'label' => 'Accueil, milieu de page', 'd' => array( 970, 250, 'Billboard' ), 'm' => array( 300, 250, 'Pavé' ) ),
		'article-inread' => array( 'label' => 'Dans le texte de l\'article', 'd' => array( 300, 250, 'Pavé in-article' ), 'm' => array( 300, 250, 'Pavé in-article' ) ),
		'mobile-sticky'  => array( 'label' => 'Bannière collante mobile (bas d\'écran)', 'd' => null, 'm' => array( 320, 50, 'Bannière collante' ) ),
	);
}

function epure_ad_placeholder( $slot, $format, $class ) {
	return sprintf(
		'<div class="ad %s"><span class="ad-tag">Publicité</span><div class="ad-box" style="--w:%d;--h:%d"><b>%s</b><i>%d × %d</i><u>%s</u></div><span class="ad-admin-note">Gabarit visible des administrateurs uniquement</span></div>',
		esc_attr( $class ),
		$format[0],
		$format[1],
		esc_html( $format[2] ),
		$format[0],
		$format[1],
		esc_html( $slot )
	);
}

/** Renvoie le HTML d'un emplacement, ou une chaîne vide s'il n'y a rien à afficher. */
function epure_get_ad( $slot, $class = '' ) {
	$slots = epure_ad_slots();
	if ( ! isset( $slots[ $slot ] ) ) {
		return '';
	}
	$ads = epure_opt( 'ads' );
	$cfg = isset( $ads[ $slot ] ) ? $ads[ $slot ] : array();
	if ( empty( $cfg['on'] ) ) {
		return '';
	}
	$def = $slots[ $slot ];
	$url = isset( $cfg['url'] ) ? $cfg['url'] : '';

	// Une image choisie dans les réglages passe avant le code de régie.
	$code = '';
	if ( ! empty( $cfg['img'] ) && $def['d'] ) {
		$code = epure_ad_image( $cfg['img'], $url, $def['d'] );
	} elseif ( isset( $cfg['code'] ) && '' !== trim( $cfg['code'] ) ) {
		$code = do_shortcode( trim( $cfg['code'] ) );
	}
	$code_m = '';
	if ( ! empty( $cfg['img_m'] ) && $def['m'] ) {
		$code_m = epure_ad_image( $cfg['img_m'], $url, $def['m'] );
	} elseif ( isset( $cfg['code_m'] ) && '' !== trim( $cfg['code_m'] ) ) {
		$code_m = do_shortcode( trim( $cfg['code_m'] ) );
	}

	if ( '' === $code && '' === $code_m ) {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return '';
		}
		$out = '';
		if ( $def['d'] ) {
			$out .= epure_ad_placeholder( $slot, $def['d'], trim( $class . ( $def['m'] !== $def['d'] ? ' only-d' : '' ) ) );
		}
		if ( $def['m'] && $def['m'] !== $def['d'] ) {
			$out .= epure_ad_placeholder( $slot, $def['m'], trim( $class . ( $def['d'] ? ' only-m' : '' ) ) );
		}
		return $out;
	}

	$wrap = '<div class="ad %s" data-slot="' . esc_attr( $slot ) . '"><span class="ad-tag">Publicité</span><div class="ad-code">%s</div></div>';
	// Les codes sont saisis par un administrateur et filtrés à l'enregistrement.
	if ( '' !== $code_m && '' !== $code ) {
		return sprintf( $wrap, esc_attr( trim( $class . ' only-d' ) ), $code ) . sprintf( $wrap, esc_attr( trim( $class . ' only-m' ) ), $code_m );
	}
	if ( '' !== $code_m ) {
		return sprintf( $wrap, esc_attr( trim( $class . ( $def['d'] ? ' only-m' : '' ) ) ), $code_m );
	}
	return sprintf( $wrap, esc_attr( $class ), $code );
}

/** Bannière image : largeur plafonnée à celle du format, hauteur proportionnelle. */
function epure_ad_image( $img, $url, $format ) {
	$tag = sprintf( '<img src="%s" width="%d" alt="" loading="lazy" style="width:%dpx;max-width:100%%;height:auto">', esc_url( $img ), $format[0], $format[0] );
	if ( $url ) {
		$tag = sprintf( '<a href="%s" target="_blank" rel="sponsored noopener">%s</a>', esc_url( $url ), $tag );
	}
	return $tag;
}

function epure_ad( $slot, $class = '' ) {
	echo epure_get_ad( $slot, $class ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/* ——— Code global de la régie dans <head> ——— */
add_action( 'wp_head', 'epure_head_code', 20 );
function epure_head_code() {
	$code = trim( (string) epure_opt( 'head_code' ) );
	if ( '' !== $code ) {
		echo "\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/* ——— Pavé dans le texte de l'article ——— */
add_filter( 'the_content', 'epure_inread', 20 );
function epure_inread( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$ad = epure_get_ad( 'article-inread' );
	if ( '' === $ad ) {
		return $content;
	}
	$after = max( 1, (int) epure_opt( 'inread_after' ) );
	$parts = explode( '</p>', $content );
	if ( count( $parts ) <= $after + 1 ) {
		return $content;
	}
	$parts[ $after - 1 ] .= '</p><div class="slot-inread">' . $ad . '</div>';
	$out                  = '';
	foreach ( $parts as $i => $part ) {
		$out .= $part;
		if ( $i !== $after - 1 && $i < count( $parts ) - 1 ) {
			$out .= '</p>';
		}
	}
	return $out;
}

/* ——— Habillage ——— */
function epure_skin_active() {
	$skin = epure_opt( 'skin' );
	return ! empty( $skin['on'] ) && ! empty( $skin['img'] );
}

add_filter( 'body_class', 'epure_body_class' );
function epure_body_class( $classes ) {
	if ( epure_skin_active() ) {
		$classes[] = 'skin';
	}
	if ( '' !== epure_get_ad( 'mobile-sticky' ) ) {
		$classes[] = 'has-m-sticky';
	}
	return $classes;
}

add_action( 'wp_head', 'epure_skin_css', 30 );
function epure_skin_css() {
	if ( ! epure_skin_active() ) {
		return;
	}
	$skin  = epure_opt( 'skin' );
	$color = sanitize_hex_color( $skin['color'] );
	printf(
		'<style id="epure-skin">@media (min-width:1440px){body.skin{background:%s url("%s") center top no-repeat fixed}}</style>' . "\n",
		esc_html( $color ? $color : '#ffffff' ),
		esc_url( $skin['img'] )
	);
}

add_action( 'wp_body_open', 'epure_skin_links' );
function epure_skin_links() {
	if ( ! epure_skin_active() ) {
		return;
	}
	$skin = epure_opt( 'skin' );
	if ( empty( $skin['url'] ) ) {
		return;
	}
	foreach ( array( 'l', 'r' ) as $side ) {
		printf( '<a class="skin-side %s" href="%s" target="_blank" rel="sponsored noopener" aria-label="Publicité"></a>', esc_attr( $side ), esc_url( $skin['url'] ) );
	}
}

/* ——— Bannière collante mobile ——— */
add_action( 'wp_footer', 'epure_mobile_sticky', 5 );
function epure_mobile_sticky() {
	$ad = epure_get_ad( 'mobile-sticky' );
	if ( '' === $ad ) {
		return;
	}
	echo '<div class="m-sticky" id="m-sticky"><button type="button" class="m-sticky-x" aria-label="Fermer la publicité">×</button>' . $ad . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
}
