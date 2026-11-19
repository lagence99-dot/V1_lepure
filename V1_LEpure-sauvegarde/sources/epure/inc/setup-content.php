<?php
/**
 * Structuration automatique du site à l'activation du thème :
 * rubriques, pages, menus, emplacements de menus, permaliens, réglages d'accueil.
 *
 * Tout est idempotent : on ne crée que ce qui manque, on ne modifie rien d'existant.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Arborescence des rubriques : slug => [ nom, [ sous-rubriques ] ]. */
function epure_structure_categories() {
	return array(
		'mode'         => array( 'Mode', array( 'tendances' => 'Tendances', 'street-style' => 'Street style', 'portraits' => 'Portraits', 'savoir-faire' => 'Savoir-faire', 'vestiaire' => 'Vestiaire' ) ),
		'defiles'      => array( 'Défilés', array( 'pret-a-porter' => 'Prêt-à-porter', 'haute-couture' => 'Haute couture', 'jeunes-createurs' => 'Jeunes créateurs' ) ),
		'beaute'       => array( 'Beauté', array( 'maquillage' => 'Maquillage', 'parfum' => 'Parfum', 'soins' => 'Soins' ) ),
		'joaillerie'   => array( 'Joaillerie', array() ),
		'horlogerie'   => array( 'Horlogerie', array() ),
		'art-de-vivre' => array( 'Art de vivre', array( 'voyage' => 'Voyage', 'design' => 'Design', 'gastronomie' => 'Gastronomie' ) ),
		'culture'      => array( 'Culture', array() ),
		'video'        => array( 'Vidéo', array() ),
	);
}

/** Pages : slug => [ titre, contenu provisoire ]. */
function epure_structure_pages() {
	$todo = '<p>Page à compléter.</p>';
	$nl   = "<p>Recevez chaque semaine l'essentiel de la mode : les défilés à retenir, les pièces à suivre et nos adresses.</p>\n\n[epure_newsletter]";
	return array(
		'la-redaction'          => array( 'La rédaction', $todo ),
		'contact'               => array( 'Contact', $todo ),
		'newsletter'            => array( 'Newsletter', $nl ),
		'abonnement'            => array( 'S\'abonner', $nl ),
		'kit-media'             => array( 'Kit média', $todo ),
		'formats-publicitaires' => array( 'Formats publicitaires', '<p>Billboard 970 × 250, leaderboard 728 × 90, pavé 300 × 250, grand angle 300 × 600, habillage, bannière mobile 320 × 100 et 320 × 50, contenus partenaires.</p>' ),
		'contenus-partenaires'  => array( 'Contenus partenaires', $todo ),
		'mentions-legales'      => array( 'Mentions légales', $todo ),
	);
}

function epure_ensure_term( $name, $slug, $parent = 0 ) {
	$term = term_exists( $slug, 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'category', array( 'slug' => $slug, 'parent' => (int) $parent ) );
	}
	if ( is_wp_error( $term ) || empty( $term['term_id'] ) ) {
		return 0;
	}
	return (int) $term['term_id'];
}

function epure_ensure_page( $slug, $title, $content ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return (int) $page->ID;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
		)
	);
	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Crée un menu (s'il n'existe pas) et l'affecte à un emplacement (s'il est libre).
 *
 * @param array $items Liste de [ type (cat|page|url), id ou url, libellé facultatif, classe facultative ].
 */
function epure_ensure_menu( $name, $location, $items ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
		return false; // Emplacement déjà occupé : on ne touche à rien.
	}
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		$menu_id = (int) $menu->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return false;
		}
		$pos = 0;
		foreach ( $items as $item ) {
			if ( empty( $item[1] ) ) {
				continue;
			}
			$args = array(
				'menu-item-status'   => 'publish',
				'menu-item-position' => ++$pos,
			);
			if ( ! empty( $item[2] ) ) {
				$args['menu-item-title'] = $item[2];
			}
			if ( ! empty( $item[3] ) ) {
				$args['menu-item-classes'] = $item[3];
			}
			if ( 'cat' === $item[0] ) {
				$args['menu-item-type']      = 'taxonomy';
				$args['menu-item-object']    = 'category';
				$args['menu-item-object-id'] = (int) $item[1];
			} elseif ( 'page' === $item[0] ) {
				$args['menu-item-type']      = 'post_type';
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = (int) $item[1];
			} else {
				$args['menu-item-type'] = 'custom';
				$args['menu-item-url']  = $item[1];
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}
	}
	$locations[ $location ] = (int) $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	return true;
}

add_action( 'after_switch_theme', 'epure_run_setup' );
function epure_run_setup() {
	// 1. Rubriques.
	$cat = array();
	foreach ( epure_structure_categories() as $slug => $def ) {
		$cat[ $slug ] = epure_ensure_term( $def[0], $slug );
		if ( ! $cat[ $slug ] ) {
			continue;
		}
		foreach ( $def[1] as $child_slug => $child_name ) {
			epure_ensure_term( $child_name, $child_slug, $cat[ $slug ] );
		}
	}

	// 2. Pages.
	$page = array();
	foreach ( epure_structure_pages() as $slug => $def ) {
		$page[ $slug ] = epure_ensure_page( $slug, $def[0], $def[1] );
	}
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( ! $privacy || ! get_post( $privacy ) ) {
		$privacy = epure_ensure_page( 'politique-de-confidentialite', 'Politique de confidentialité', '<p>Page à compléter.</p>' );
		if ( $privacy ) {
			update_option( 'wp_page_for_privacy_policy', $privacy );
		}
	}

	// 3. Menus et emplacements.
	$main = array();
	foreach ( $cat as $id ) {
		$main[] = array( 'cat', $id );
	}
	epure_ensure_menu( 'Menu principal', 'primary', $main );
	epure_ensure_menu(
		'Barre utilitaire',
		'utility',
		array(
			array( 'page', $page['newsletter'] ),
			array( 'page', $page['contact'] ),
			array( 'page', $page['abonnement'], 'S\'abonner', 'btn-sub' ),
		)
	);
	epure_ensure_menu(
		'Rubriques',
		'footer-1',
		array(
			array( 'cat', $cat['mode'] ),
			array( 'cat', $cat['defiles'] ),
			array( 'cat', $cat['beaute'] ),
			array( 'cat', $cat['joaillerie'] ),
			array( 'cat', $cat['horlogerie'] ),
		)
	);
	epure_ensure_menu(
		'Le magazine',
		'footer-2',
		array(
			array( 'page', $page['la-redaction'] ),
			array( 'page', $page['abonnement'] ),
			array( 'page', $page['newsletter'] ),
			array( 'page', $page['contact'] ),
		)
	);
	epure_ensure_menu(
		'Annonceurs',
		'footer-3',
		array(
			array( 'page', $page['kit-media'] ),
			array( 'page', $page['formats-publicitaires'] ),
			array( 'page', $page['contenus-partenaires'] ),
		)
	);
	epure_ensure_menu(
		'Liens légaux',
		'legal',
		array(
			array( 'page', $page['mentions-legales'] ),
			array( 'page', $privacy, 'Confidentialité' ),
		)
	);

	// 5. Accueil = derniers articles (le gabarit front-page.php fait le reste).
	if ( 'page' === get_option( 'show_on_front' ) && ! get_option( 'page_on_front' ) ) {
		update_option( 'show_on_front', 'posts' );
	}

	// 6. Slogan par défaut de WordPress remplacé.
	$tagline = get_option( 'blogdescription' );
	if ( in_array( $tagline, array( '', 'Un site utilisant WordPress', 'Just another WordPress site', 'Un autre site WordPress' ), true ) ) {
		update_option( 'blogdescription', 'Mode · Luxe · Art de vivre' );
	}

	// 7. Permaliens lisibles si le site est encore en réglage « simple ».
	if ( '' === (string) get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%category%/%postname%/' );
		// Les règles d'adresses sont reconstruites à la page suivante : les rubriques
		// ont été déclarées avant le changement, un recalcul immédiat serait incomplet.
		update_option( 'epure_flush_rewrite', 1 );
	}

	// 8. Première installation : les widgets posés d'office par WordPress (recherche,
	// articles récents, archives…) sont rangés dans « Widgets inactifs », rien n'est supprimé.
	if ( ! get_option( 'epure_setup_done' ) ) {
		$widgets = get_option( 'sidebars_widgets', array() );
		$default = array( 'block-2', 'block-3', 'block-4', 'block-5', 'block-6' );
		if ( isset( $widgets['sidebar-1'] ) && is_array( $widgets['sidebar-1'] ) && $widgets['sidebar-1'] && ! array_diff( $widgets['sidebar-1'], $default ) ) {
			$inactive                        = isset( $widgets['wp_inactive_widgets'] ) && is_array( $widgets['wp_inactive_widgets'] ) ? $widgets['wp_inactive_widgets'] : array();
			$widgets['wp_inactive_widgets'] = array_merge( $inactive, $widgets['sidebar-1'] );
			$widgets['sidebar-1']           = array();
			update_option( 'sidebars_widgets', $widgets );
		}
	}

	update_option( 'epure_setup_done', time() );
	set_transient( 'epure_setup_notice', 1, 5 * MINUTE_IN_SECONDS );
}

// Recalcul différé des règles d'adresses : lu avant la structuration (priorité 1),
// donc toujours exécuté à la requête qui suit celle où le drapeau a été posé.
add_action( 'init', 'epure_maybe_flush_rewrite', 1 );
function epure_maybe_flush_rewrite() {
	if ( get_option( 'epure_flush_rewrite' ) ) {
		delete_option( 'epure_flush_rewrite' );
		add_action( 'wp_loaded', 'flush_rewrite_rules' );
	}
}

add_action( 'admin_notices', 'epure_setup_notice' );
function epure_setup_notice() {
	if ( ! get_transient( 'epure_setup_notice' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	delete_transient( 'epure_setup_notice' );
	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>L\'Épure est installé.</strong> Rubriques, pages et menus ont été créés. <a href="%s">Régler la publicité et les mises en avant</a> · <a href="%s">Voir les menus</a></p></div>',
		esc_url( admin_url( 'themes.php?page=epure-options' ) ),
		esc_url( admin_url( 'nav-menus.php' ) )
	);
}

function epure_admin_message( $key ) {
	$messages = array(
		'setup'        => 'Structuration relancée : les éléments manquants ont été créés.',
		'demo_created' => 'Articles de démonstration créés.',
		'demo_deleted' => 'Articles de démonstration supprimés.',
	);
	return isset( $messages[ $key ] ) ? $messages[ $key ] : '';
}

function epure_admin_redirect( $msg ) {
	wp_safe_redirect( admin_url( 'themes.php?page=epure-options&epure_msg=' . $msg ) );
	exit;
}

add_action( 'admin_post_epure_setup', 'epure_action_setup' );
function epure_action_setup() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}
	check_admin_referer( 'epure_setup' );
	epure_run_setup();
	delete_transient( 'epure_setup_notice' );
	epure_admin_redirect( 'setup' );
}

/* ——— Articles de démonstration ——— */

function epure_demo_ids() {
	return get_posts(
		array(
			'numberposts' => -1,
			'fields'      => 'ids',
			'post_status' => 'any',
			'meta_key'    => '_epure_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

add_action( 'admin_post_epure_demo_create', 'epure_action_demo_create' );
function epure_action_demo_create() {
	if ( ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}
	check_admin_referer( 'epure_demo_create' );

	$body = '<p>Il y a des vêtements qui racontent une époque mieux que les éditoriaux. Cet hiver, c\'est un manteau. Long, très long, il frôle le sol, gomme la taille et allonge le pas.</p>'
		. '<p>Le phénomène n\'a rien d\'un caprice. Depuis trois saisons, les ventes de pièces dites « d\'investissement » progressent quand le reste du vestiaire marque le pas.</p>'
		. '<h2>Une épaule, une ligne</h2>'
		. '<p>Dans les ateliers, tout commence par l\'épaule. « C\'est elle qui porte le vêtement, pas le corps », explique une première d\'atelier installée à Paris depuis vingt-deux ans.</p>'
		. '<blockquote><p>« Un bon manteau ne se remarque pas. Il se reconnaît, de dos, à trente mètres. »</p></blockquote>'
		. '<p>La palette suit la même discipline : anthracite, tabac, un bleu marine presque noir. Les couleurs vives existent, mais en doublure, pour soi.</p>'
		. '<p>Reste la question du prix. Le marché de la seconde main l\'a bien compris : les modèles des années 1990 s\'y arrachent, souvent retaillés par des ateliers de retouche.</p>';

	$demo = array(
		array( 'tendances', 'Le manteau long reprend le pouvoir' ),
		array( 'savoir-faire', 'Pourquoi les grandes maisons rouvrent toutes leurs archives' ),
		array( 'street-style', 'Paris, sept heures du matin : ce que portent celles qui ouvrent les ateliers' ),
		array( 'portraits', 'À 29 ans, elle redessine la maille depuis un ancien moulin des Cévennes' ),
		array( 'tendances', 'Le chocolat détrône le noir : huit façons de le porter cet hiver' ),
		array( 'savoir-faire', 'La dentelle de Calais retrouve ses métiers centenaires' ),
		array( 'vestiaire', 'Le mocassin épais, ou comment une chaussure d\'écolier a conquis les bureaux' ),
		array( 'vestiaire', 'Seconde main de luxe : les prix flambent, les contrefaçons aussi' ),
		array( 'pret-a-porter', 'Paris : le tailleur, version fluide' ),
		array( 'pret-a-porter', 'Milan : cuir souple et épaules nues' ),
		array( 'haute-couture', 'Haute couture : trente-deux robes, une seule couleur' ),
		array( 'jeunes-createurs', 'Londres : la relève mise sur le tartan' ),
		array( 'maquillage', 'Le rouge mat revient, plus sombre et plus net' ),
		array( 'parfum', 'Iris, cuir, fumée : les sillages de l\'hiver' ),
		array( 'joaillerie', 'Place Vendôme, l\'émeraude reprend la première place' ),
		array( 'joaillerie', 'Le retour discret de la broche' ),
		array( 'horlogerie', '36 millimètres : la petite montre séduit de nouveau les hommes' ),
		array( 'culture', 'Une exposition de printemps consacrée au tailleur' ),
	);

	$now = time();
	foreach ( array_reverse( $demo ) as $i => $d ) {
		$term = get_term_by( 'slug', $d[0], 'category' );
		$cats = array();
		if ( $term ) {
			$cats[] = (int) $term->term_id;
			if ( $term->parent ) {
				$cats[] = (int) $term->parent;
			}
		}
		$id = wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_title'    => $d[1],
				'post_content'  => $body,
				'post_excerpt'  => 'Texte de démonstration : remplacez ce chapô par deux phrases qui donnent envie de lire l\'article.',
				'post_category' => $cats,
				'post_date'     => gmdate( 'Y-m-d H:i:s', $now - ( count( $demo ) - 1 - $i ) * 3 * HOUR_IN_SECONDS + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_epure_demo', 1 );
		}
	}
	delete_transient( 'epure_most_read' );
	epure_admin_redirect( 'demo_created' );
}

add_action( 'admin_post_epure_demo_delete', 'epure_action_demo_delete' );
function epure_action_demo_delete() {
	if ( ! current_user_can( 'delete_others_posts' ) || ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}
	check_admin_referer( 'epure_demo_delete' );
	foreach ( epure_demo_ids() as $id ) {
		wp_delete_post( $id, true );
	}
	delete_transient( 'epure_most_read' );
	epure_admin_redirect( 'demo_deleted' );
}
