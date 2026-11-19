<?php
/**
 * Newsletter intégrée : formulaire d'inscription, liste des abonnés dans l'administration,
 * suppression et export CSV. L'envoi des lettres se fait avec un outil d'e-mailing,
 * dans lequel on importe le fichier exporté.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EPURE_NL_DB', '1' );

function epure_nl_table() {
	global $wpdb;
	return $wpdb->prefix . 'epure_subscribers';
}

/** Crée la table des abonnés et branche le formulaire sur les pages prévues (une seule fois). */
add_action( 'after_switch_theme', 'epure_nl_install', 20 );
add_action( 'admin_init', 'epure_nl_install' );
function epure_nl_install() {
	if ( EPURE_NL_DB === get_option( 'epure_nl_db' ) ) {
		return;
	}
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = epure_nl_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta(
		"CREATE TABLE $table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			email varchar(190) NOT NULL,
			source varchar(60) NOT NULL DEFAULT '',
			created datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email)
		) $charset;"
	);

	// Pages « Newsletter » et « S'abonner » encore vides : on y place le formulaire.
	foreach ( array( 'newsletter', 'abonnement' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && '<p>Page à compléter.</p>' === trim( $page->post_content ) ) {
			wp_update_post(
				array(
					'ID'           => $page->ID,
					'post_content' => "<p>Recevez chaque semaine l'essentiel de la mode : les défilés à retenir, les pièces à suivre et nos adresses.</p>\n\n[epure_newsletter]",
				)
			);
		}
	}
	update_option( 'epure_nl_db', EPURE_NL_DB );
}

/* ——— Formulaire ——— */

function epure_nl_message( $status ) {
	$messages = array(
		'ok'      => 'Merci, votre inscription est enregistrée.',
		'invalid' => 'Cette adresse e-mail ne semble pas valide. Vérifiez-la et réessayez.',
		'error'   => 'L\'inscription n\'a pas pu être enregistrée. Réessayez dans un instant.',
	);
	return isset( $messages[ $status ] ) ? $messages[ $status ] : '';
}

function epure_newsletter_form( $source = 'site' ) {
	// Le message ne s'affiche que dans le formulaire qui vient d'être utilisé.
	$status  = isset( $_GET['nl'] ) ? sanitize_key( $_GET['nl'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$from    = isset( $_GET['nls'] ) ? sanitize_key( $_GET['nls'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$message = ( $from === $source ) ? epure_nl_message( $status ) : '';
	$privacy = get_privacy_policy_url();
	$back    = home_url( add_query_arg( array( 'nl' => false, 'nls' => false ) ) );

	ob_start();
	?>
	<form class="nl-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="epure_subscribe">
		<input type="hidden" name="source" value="<?php echo esc_attr( $source ); ?>">
		<input type="hidden" name="back" value="<?php echo esc_url( $back ); ?>">
		<p class="nl-hp" aria-hidden="true"><label>Ne pas remplir ce champ <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>
		<div class="nl-row">
			<input type="email" name="email" placeholder="Votre adresse e-mail" aria-label="Adresse e-mail" autocomplete="email" required>
			<button type="submit">S'inscrire</button>
		</div>
		<?php if ( $message ) : ?>
			<p class="nl-msg <?php echo 'ok' === $status ? 'is-ok' : 'is-err'; ?>" role="status"><?php echo esc_html( $message ); ?></p>
		<?php endif; ?>
		<p class="nl-legal">En vous inscrivant, vous acceptez de recevoir notre lettre par e-mail. Désinscription possible à tout moment<?php if ( $privacy ) : ?>, voir notre <a href="<?php echo esc_url( $privacy ); ?>">politique de confidentialité</a><?php endif; ?>.</p>
	</form>
	<?php
	return ob_get_clean();
}

add_shortcode( 'epure_newsletter', 'epure_newsletter_shortcode' );
function epure_newsletter_shortcode() {
	return '<div class="nl-inline" id="newsletter-form">' . epure_newsletter_form( 'page' ) . '</div>';
}

/* ——— Enregistrement d'une inscription ——— */

add_action( 'admin_post_nopriv_epure_subscribe', 'epure_nl_subscribe' );
add_action( 'admin_post_epure_subscribe', 'epure_nl_subscribe' );
function epure_nl_subscribe() {
	global $wpdb;
	$back = isset( $_POST['back'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['back'] ) ), home_url( '/' ) ) : home_url( '/' ); // phpcs:ignore WordPress.Security.NonceVerification
	$from = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$done = function ( $status ) use ( $back, $from ) {
		$url = add_query_arg( array( 'nl' => $status, 'nls' => $from ), $back );
		wp_safe_redirect( $url . ( 'page' === $from ? '#newsletter-form' : '#newsletter' ) );
		exit;
	};

	// Champ piège : rempli uniquement par les robots, qu'on laisse croire au succès.
	if ( ! empty( $_POST['website'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$done( 'ok' );
	}
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $email || ! is_email( $email ) || strlen( $email ) > 190 ) {
		$done( 'invalid' );
	}
	$email  = strtolower( $email );
	$source = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$table  = epure_nl_table();

	// phpcs:ignore WordPress.DB
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE email = %s", $email ) );
	if ( $exists ) {
		$done( 'ok' ); // Même réponse : on ne révèle pas qu'une adresse est déjà inscrite.
	}
	// phpcs:ignore WordPress.DB
	$ok = $wpdb->insert(
		$table,
		array(
			'email'   => $email,
			'source'  => substr( $source, 0, 60 ),
			'created' => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%s' )
	);
	$done( $ok ? 'ok' : 'error' );
}

/* ——— Administration : liste des abonnés ——— */

add_action( 'admin_menu', 'epure_nl_menu' );
function epure_nl_menu() {
	add_menu_page( 'Abonnés newsletter', 'Newsletter', 'manage_options', 'epure-newsletter', 'epure_nl_page', 'dashicons-email-alt', 26 );
}

function epure_nl_count( $search = '' ) {
	global $wpdb;
	$table = epure_nl_table();
	if ( '' !== $search ) {
		// phpcs:ignore WordPress.DB
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE email LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' ) );
	}
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" ); // phpcs:ignore WordPress.DB
}

function epure_nl_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table  = epure_nl_table();
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
	$per    = 50;
	$total  = epure_nl_count();
	$found  = epure_nl_count( $search );
	$offset = ( $paged - 1 ) * $per;

	if ( '' !== $search ) {
		// phpcs:ignore WordPress.DB
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE email LIKE %s ORDER BY created DESC, id DESC LIMIT %d OFFSET %d", '%' . $wpdb->esc_like( $search ) . '%', $per, $offset ) );
	} else {
		// phpcs:ignore WordPress.DB
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table ORDER BY created DESC, id DESC LIMIT %d OFFSET %d", $per, $offset ) );
	}
	$sources = array( 'pied-de-page' => 'Pied de page', 'page' => 'Page du site', 'admin' => 'Ajout manuel' );
	$notices = array( 'deleted' => 'Abonné supprimé.', 'added' => 'Abonné ajouté.', 'exists' => 'Cette adresse est déjà inscrite.', 'invalid' => 'Adresse e-mail non valide.' );
	$notice  = isset( $_GET['epure_msg'] ) ? sanitize_key( $_GET['epure_msg'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Abonnés newsletter</h1>
		<?php if ( $total ) : ?>
			<a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=epure_nl_export' ), 'epure_nl_export' ) ); ?>">Exporter en CSV</a>
		<?php endif; ?>
		<hr class="wp-header-end">
		<?php if ( isset( $notices[ $notice ] ) ) : ?>
			<div class="notice <?php echo in_array( $notice, array( 'deleted', 'added' ), true ) ? 'notice-success' : 'notice-warning'; ?> is-dismissible"><p><?php echo esc_html( $notices[ $notice ] ); ?></p></div>
		<?php endif; ?>

		<p><strong><?php echo esc_html( number_format_i18n( $total ) ); ?></strong> <?php echo 1 === $total ? 'adresse inscrite' : 'adresses inscrites'; ?>. Les inscriptions viennent du bloc du pied de page et des pages contenant le code <code>[epure_newsletter]</code>. Pour envoyer une lettre, exportez la liste et importez-la dans votre outil d'e-mailing.</p>

		<div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-end;margin:14px 0">
			<form method="get">
				<input type="hidden" name="page" value="epure-newsletter">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Rechercher une adresse">
				<button class="button">Rechercher</button>
				<?php if ( '' !== $search ) : ?><a class="button-link" href="<?php echo esc_url( admin_url( 'admin.php?page=epure-newsletter' ) ); ?>">Tout afficher</a><?php endif; ?>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="epure_nl_add">
				<?php wp_nonce_field( 'epure_nl_add' ); ?>
				<input type="email" name="email" placeholder="Ajouter une adresse" required>
				<button class="button">Ajouter</button>
			</form>
		</div>

		<table class="wp-list-table widefat fixed striped">
			<thead><tr><th>Adresse e-mail</th><th style="width:180px">Inscrit le</th><th style="width:160px">Origine</th><th style="width:110px"></th></tr></thead>
			<tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="4"><?php echo '' !== $search ? 'Aucune adresse ne correspond à cette recherche.' : 'Aucun abonné pour le moment. Les inscriptions apparaîtront ici.'; ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $row->email ); ?></strong></td>
					<td><?php echo esc_html( mysql2date( 'j F Y à H:i', $row->created ) ); ?></td>
					<td><?php echo esc_html( isset( $sources[ $row->source ] ) ? $sources[ $row->source ] : $row->source ); ?></td>
					<td><a style="color:#b32d2e" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=epure_nl_delete&id=' . (int) $row->id ), 'epure_nl_delete_' . (int) $row->id ) ); ?>" onclick="return confirm('Supprimer cette adresse de la liste ?');">Supprimer</a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		$pages = (int) ceil( $found / $per );
		if ( $pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo wp_kses_post(
				paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'format'  => '',
						'current' => $paged,
						'total'   => $pages,
					)
				)
			);
			echo '</div></div>';
		}
		?>
	</div>
	<?php
}

function epure_nl_back( $msg ) {
	wp_safe_redirect( admin_url( 'admin.php?page=epure-newsletter&epure_msg=' . $msg ) );
	exit;
}

add_action( 'admin_post_epure_nl_delete', 'epure_nl_action_delete' );
function epure_nl_action_delete() {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}
	check_admin_referer( 'epure_nl_delete_' . $id );
	global $wpdb;
	$wpdb->delete( epure_nl_table(), array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB
	epure_nl_back( 'deleted' );
}

add_action( 'admin_post_epure_nl_add', 'epure_nl_action_add' );
function epure_nl_action_add() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}
	check_admin_referer( 'epure_nl_add' );
	global $wpdb;
	$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	if ( ! $email || ! is_email( $email ) || strlen( $email ) > 190 ) {
		epure_nl_back( 'invalid' );
	}
	$table = epure_nl_table();
	// phpcs:ignore WordPress.DB
	if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE email = %s", $email ) ) ) {
		epure_nl_back( 'exists' );
	}
	// phpcs:ignore WordPress.DB
	$wpdb->insert( $table, array( 'email' => $email, 'source' => 'admin', 'created' => current_time( 'mysql' ) ), array( '%s', '%s', '%s' ) );
	epure_nl_back( 'added' );
}

add_action( 'admin_post_epure_nl_export', 'epure_nl_action_export' );
function epure_nl_action_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}
	check_admin_referer( 'epure_nl_export' );
	global $wpdb;
	$table = epure_nl_table();
	$rows  = $wpdb->get_results( "SELECT email, created, source FROM $table ORDER BY created DESC, id DESC", ARRAY_A ); // phpcs:ignore WordPress.DB

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="abonnes-newsletter-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // Pour l'ouverture directe dans Excel.
	fputcsv( $out, array( 'email', 'date_inscription', 'origine' ), ';', '"', '\\' );
	foreach ( $rows as $row ) {
		// Neutralise les formules de tableur en début de cellule.
		$email = preg_match( '/^[=+\-@\t\r]/', $row['email'] ) ? "'" . $row['email'] : $row['email'];
		fputcsv( $out, array( $email, $row['created'], $row['source'] ), ';', '"', '\\' );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}

/* ——— Vie privée : effacement des données personnelles (Outils > Effacer des données) ——— */

add_filter( 'wp_privacy_personal_data_erasers', 'epure_nl_register_eraser' );
function epure_nl_register_eraser( $erasers ) {
	$erasers['epure-newsletter'] = array(
		'eraser_friendly_name' => 'Abonnés newsletter',
		'callback'             => 'epure_nl_eraser',
	);
	return $erasers;
}

function epure_nl_eraser( $email ) {
	global $wpdb;
	$removed = $wpdb->delete( epure_nl_table(), array( 'email' => strtolower( $email ) ), array( '%s' ) ); // phpcs:ignore WordPress.DB
	return array(
		'items_removed'  => (bool) $removed,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}
