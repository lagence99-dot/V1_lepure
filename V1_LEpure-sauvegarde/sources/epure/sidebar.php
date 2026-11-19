<?php
/**
 * Colonne de droite : pavé, les plus lus, widgets, grand angle collant.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$epure_top_ids = epure_most_read( 5 );
?>
<aside class="side">
	<?php epure_ad( 'sidebar-top' ); ?>
	<?php if ( $epure_top_ids ) : ?>
	<div class="top">
		<h3>Les plus lus</h3>
		<ol>
			<?php foreach ( $epure_top_ids as $epure_i => $epure_id ) : ?>
				<li><span><?php echo (int) $epure_i + 1; ?></span><a href="<?php echo esc_url( get_permalink( $epure_id ) ); ?>"><span class="kick"><?php echo esc_html( epure_kicker( $epure_id ) ); ?></span><h4><?php echo esc_html( get_the_title( $epure_id ) ); ?></h4></a></li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php endif; ?>
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	<?php endif; ?>
	<?php
	$epure_sticky = epure_get_ad( 'sidebar-sticky', 'sticky' );
	if ( '' !== $epure_sticky ) {
		echo '<div style="flex:1">' . $epure_sticky . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>
</aside>
