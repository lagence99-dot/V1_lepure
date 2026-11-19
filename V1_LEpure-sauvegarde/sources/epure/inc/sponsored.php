<?php
/**
 * Articles sponsorisés : case à cocher dans l'éditeur + nom du partenaire.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function epure_is_sponsored( $post_id ) {
	return (bool) get_post_meta( $post_id, '_epure_sponsored', true );
}

add_action( 'add_meta_boxes', 'epure_sponsored_box' );
function epure_sponsored_box() {
	add_meta_box( 'epure-sponsored', 'Contenu partenaire', 'epure_sponsored_box_html', 'post', 'side', 'default' );
}

function epure_sponsored_box_html( $post ) {
	wp_nonce_field( 'epure_sponsored', 'epure_sponsored_nonce' );
	$on      = epure_is_sponsored( $post->ID );
	$partner = get_post_meta( $post->ID, '_epure_partner', true );
	?>
	<p><label><input type="checkbox" name="epure_sponsored" value="1" <?php checked( $on ); ?>> Article sponsorisé</label></p>
	<p><label for="epure_partner">Nom du partenaire</label><br>
	<input type="text" class="widefat" id="epure_partner" name="epure_partner" value="<?php echo esc_attr( $partner ); ?>"></p>
	<p class="description">L'article porte la mention « Sponsorisé » et peut être inséré comme carte native dans les listes de rubrique.</p>
	<?php
}

add_action( 'save_post_post', 'epure_sponsored_save' );
function epure_sponsored_save( $post_id ) {
	if ( ! isset( $_POST['epure_sponsored_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['epure_sponsored_nonce'] ), 'epure_sponsored' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! empty( $_POST['epure_sponsored'] ) ) {
		update_post_meta( $post_id, '_epure_sponsored', 1 );
	} else {
		delete_post_meta( $post_id, '_epure_sponsored' );
	}
	$partner = isset( $_POST['epure_partner'] ) ? sanitize_text_field( wp_unslash( $_POST['epure_partner'] ) ) : '';
	if ( '' !== $partner ) {
		update_post_meta( $post_id, '_epure_partner', $partner );
	} else {
		delete_post_meta( $post_id, '_epure_partner' );
	}
}

/** Dernier article sponsorisé, pour la carte native des listes. */
function epure_native_post_id( $exclude = array() ) {
	if ( ! epure_opt( 'native' ) ) {
		return 0;
	}
	$ids = get_posts(
		array(
			'numberposts'  => 1,
			'fields'       => 'ids',
			'meta_key'     => '_epure_sponsored', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'   => 1, // phpcs:ignore WordPress.DB.SlowDBQuery
			'post__not_in' => array_map( 'intval', (array) $exclude ),
		)
	);
	return $ids ? (int) $ids[0] : 0;
}
