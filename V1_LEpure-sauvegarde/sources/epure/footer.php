<?php
/**
 * Pied de page : newsletter, colonnes de menus, liens légaux.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$epure_nl = epure_opt( 'nl' );
$epure_nl_code = trim( (string) $epure_nl['code'] );
if ( ! empty( $epure_nl['on'] ) ) :
	?>
<section class="nl" id="newsletter" aria-label="Newsletter"><div class="wrap">
	<div>
		<?php if ( $epure_nl['kicker'] ) : ?><span class="kick"><?php echo esc_html( $epure_nl['kicker'] ); ?></span><?php endif; ?>
		<h2><?php echo epure_section_title( $epure_nl['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		<?php if ( $epure_nl['text'] ) : ?><p><?php echo esc_html( $epure_nl['text'] ); ?></p><?php endif; ?>
	</div>
	<div class="nl-code">
		<?php
		if ( '' !== $epure_nl_code ) {
			echo do_shortcode( $epure_nl_code ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			echo epure_newsletter_form( 'pied-de-page' ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</div>
</div></section>
<?php endif; ?>

<footer class="foot wrap">
	<div class="foot-g">
		<div>
			<div class="foot-logo"><?php bloginfo( 'name' ); ?></div>
			<?php if ( get_bloginfo( 'description' ) ) : ?><p><?php bloginfo( 'description' ); ?></p><?php endif; ?>
		</div>
		<?php
		epure_footer_menu( 'footer-1' );
		epure_footer_menu( 'footer-2' );
		epure_footer_menu( 'footer-3' );
		?>
	</div>
	<div class="foot-b">
		<span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Tous droits réservés.</span>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'legal',
				'container'      => false,
				'menu_class'     => 'legal-menu',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		?>
	</div>
</footer>
</div><!-- .page -->
<?php wp_footer(); ?>
</body>
</html>
