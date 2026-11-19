<?php
/**
 * L'Épure : fonctions du thème.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EPURE_VERSION', '1.4.0' );

require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/ads.php';
require get_template_directory() . '/inc/sponsored.php';
require get_template_directory() . '/inc/options.php';
require get_template_directory() . '/inc/setup-content.php';
require get_template_directory() . '/inc/newsletter.php';

add_action( 'after_setup_theme', 'epure_theme_setup' );
function epure_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array( 'height' => 220, 'width' => 900, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	add_image_size( 'epure-portrait', 800, 1000, true );   // 4:5, cartes
	add_image_size( 'epure-tall', 800, 1200, true );       // 2:3, défilés
	add_image_size( 'epure-square', 800, 800, true );      // 1:1
	add_image_size( 'epure-landscape', 1200, 800, true );  // 3:2, article principal
	add_image_size( 'epure-wide', 1920, 1080, true );      // 16:9, tête d'article

	register_nav_menus(
		array(
			'primary'  => 'Menu principal (rubriques)',
			'utility'  => 'Barre utilitaire (haut de page)',
			'footer-1' => 'Pied de page, colonne 1',
			'footer-2' => 'Pied de page, colonne 2',
			'footer-3' => 'Pied de page, colonne 3',
			'legal'    => 'Pied de page, liens légaux',
		)
	);
}

add_action( 'widgets_init', 'epure_widgets_init' );
function epure_widgets_init() {
	register_sidebar(
		array(
			'name'          => 'Colonne de droite (complément)',
			'id'            => 'sidebar-1',
			'description'   => 'Widgets affichés entre « Les plus lus » et le grand angle publicitaire.',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}

add_action( 'wp_enqueue_scripts', 'epure_assets' );
function epure_assets() {
	wp_enqueue_style( 'epure-style', get_stylesheet_uri(), array(), EPURE_VERSION );
	wp_enqueue_script( 'epure-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), EPURE_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_filter( 'excerpt_length', 'epure_excerpt_length' );
function epure_excerpt_length() {
	return 32;
}

add_filter( 'excerpt_more', 'epure_excerpt_more' );
function epure_excerpt_more() {
	return '…';
}

// Titres d'archives sans préfixe (« Catégorie : », « Étiquette : »).
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
