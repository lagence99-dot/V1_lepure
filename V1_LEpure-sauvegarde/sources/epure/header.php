<?php
/**
 * En-tête : barre utilitaire, têtière, menu des rubriques, bannière haute.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="page">
<a class="skip-link screen-reader-text" href="#main">Aller au contenu</a>
<header>
	<div class="util"><div class="wrap">
		<span class="util-date"><?php echo esc_html( ucfirst( date_i18n( 'l j F Y' ) ) ); ?></span>
		<div class="util-r">
			<a class="util-search" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>">Recherche</a>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'utility',
					'container'      => false,
					'menu_class'     => 'util-menu',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</div>
	</div></div>
	<div class="mast wrap">
		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
		<?php endif; ?>
		<?php if ( get_bloginfo( 'description' ) ) : ?>
			<small><?php bloginfo( 'description' ); ?></small>
		<?php endif; ?>
	</div>
</header>
<?php if ( has_nav_menu( 'primary' ) ) : ?>
<nav class="nav" aria-label="Rubriques">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'nav-in wrap',
			'depth'          => 1,
			'fallback_cb'    => false,
		)
	);
	?>
</nav>
<?php endif; ?>
<?php
$epure_top = epure_get_ad( 'header-top' );
if ( '' !== $epure_top ) {
	echo '<div class="ad-band"><div class="wrap">' . $epure_top . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
}
