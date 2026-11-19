<?php
/**
 * Réglages du thème : Apparence > Réglages L'Épure.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function epure_defaults() {
	$ads = array();
	foreach ( array( 'header-top', 'sidebar-top', 'sidebar-sticky', 'home-mid', 'article-inread' ) as $slot ) {
		$ads[ $slot ] = array( 'on' => 1, 'img' => '', 'img_m' => '', 'url' => '', 'code' => '', 'code_m' => '' );
	}
	$ads['mobile-sticky'] = array( 'on' => 0, 'img' => '', 'img_m' => '', 'url' => '', 'code' => '', 'code_m' => '' );

	$item = array( 'name' => '', 'desc' => '', 'price' => '', 'img' => '', 'url' => '' );

	return array(
		'home_hero_cat'   => 0,
		'home_count'      => 3,
		'home_hide'       => array(),
		'top_format'      => 'billboard',
		'inread_after'    => 2,
		'head_code'       => '',
		'ads'             => $ads,
		'skin'            => array( 'on' => 0, 'img' => '', 'url' => '', 'color' => '#ffffff' ),
		'native'          => 1,
		'mea'             => array( 'on' => 1, 'label' => 'Contenu partenaire', 'title' => '', 'text' => '', 'img' => '', 'url' => '', 'cta' => 'Découvrir' ),
		'shop'            => array( 'on' => 1, 'title' => 'La sélection de la rédaction', 'items' => array( $item, $item, $item, $item ) ),
		'nl'              => array( 'on' => 1, 'kicker' => 'La lettre', 'title' => 'L\'essentiel de la mode, chaque vendredi', 'text' => 'Les défilés à retenir, les pièces à suivre et une adresse à garder pour soi.', 'code' => '' ),
	);
}

/** Lit un réglage (premier niveau), valeurs par défaut fusionnées. */
function epure_opt( $key ) {
	static $cache = null;
	if ( null === $cache || doing_action( 'update_option_epure_options' ) ) {
		$saved = get_option( 'epure_options', array() );
		$cache = array_replace_recursive( epure_defaults(), is_array( $saved ) ? $saved : array() );
		if ( is_array( $saved ) && isset( $saved['home_hide'] ) ) {
			$cache['home_hide'] = array_map( 'intval', (array) $saved['home_hide'] );
		}
	}
	return isset( $cache[ $key ] ) ? $cache[ $key ] : null;
}

add_action( 'admin_menu', 'epure_options_menu' );
function epure_options_menu() {
	add_theme_page( 'Réglages L\'Épure', 'Réglages L\'Épure', 'edit_theme_options', 'epure-options', 'epure_options_page' );
}

add_action( 'admin_init', 'epure_options_register' );
function epure_options_register() {
	register_setting( 'epure_options_group', 'epure_options', array( 'sanitize_callback' => 'epure_options_sanitize' ) );
}

add_filter( 'option_page_capability_epure_options_group', 'epure_options_cap' );
function epure_options_cap() {
	return 'edit_theme_options';
}

function epure_clean_code( $code ) {
	$code = (string) $code;
	return current_user_can( 'unfiltered_html' ) ? $code : wp_kses_post( $code );
}

function epure_options_sanitize( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$def = epure_defaults();
	$out = array();

	$out['home_hero_cat'] = isset( $in['home_hero_cat'] ) ? absint( $in['home_hero_cat'] ) : 0;
	$out['home_count']    = ( isset( $in['home_count'] ) && in_array( (int) $in['home_count'], array( 3, 4, 6, 8 ), true ) ) ? (int) $in['home_count'] : 3;
	$out['home_hide']     = isset( $in['home_hide'] ) && is_array( $in['home_hide'] ) ? array_values( array_filter( array_map( 'absint', $in['home_hide'] ) ) ) : array();
	$out['top_format']   = ( isset( $in['top_format'] ) && 'leaderboard' === $in['top_format'] ) ? 'leaderboard' : 'billboard';
	$out['inread_after'] = isset( $in['inread_after'] ) ? max( 1, min( 12, absint( $in['inread_after'] ) ) ) : 2;
	$out['head_code']    = isset( $in['head_code'] ) ? epure_clean_code( $in['head_code'] ) : '';
	$out['native']       = empty( $in['native'] ) ? 0 : 1;

	$out['ads'] = array();
	foreach ( array_keys( $def['ads'] ) as $slot ) {
		$s                   = isset( $in['ads'][ $slot ] ) && is_array( $in['ads'][ $slot ] ) ? $in['ads'][ $slot ] : array();
		$out['ads'][ $slot ] = array(
			'on'     => empty( $s['on'] ) ? 0 : 1,
			'img'    => isset( $s['img'] ) ? esc_url_raw( $s['img'] ) : '',
			'img_m'  => isset( $s['img_m'] ) ? esc_url_raw( $s['img_m'] ) : '',
			'url'    => isset( $s['url'] ) ? esc_url_raw( $s['url'] ) : '',
			'code'   => isset( $s['code'] ) ? epure_clean_code( $s['code'] ) : '',
			'code_m' => isset( $s['code_m'] ) ? epure_clean_code( $s['code_m'] ) : '',
		);
	}

	$s           = isset( $in['skin'] ) && is_array( $in['skin'] ) ? $in['skin'] : array();
	$color       = isset( $s['color'] ) ? sanitize_hex_color( $s['color'] ) : '';
	$out['skin'] = array(
		'on'    => empty( $s['on'] ) ? 0 : 1,
		'img'   => isset( $s['img'] ) ? esc_url_raw( $s['img'] ) : '',
		'url'   => isset( $s['url'] ) ? esc_url_raw( $s['url'] ) : '',
		'color' => $color ? $color : '#ffffff',
	);

	$s          = isset( $in['mea'] ) && is_array( $in['mea'] ) ? $in['mea'] : array();
	$out['mea'] = array(
		'on'    => empty( $s['on'] ) ? 0 : 1,
		'label' => isset( $s['label'] ) ? sanitize_text_field( $s['label'] ) : '',
		'title' => isset( $s['title'] ) ? sanitize_text_field( $s['title'] ) : '',
		'text'  => isset( $s['text'] ) ? sanitize_textarea_field( $s['text'] ) : '',
		'img'   => isset( $s['img'] ) ? esc_url_raw( $s['img'] ) : '',
		'url'   => isset( $s['url'] ) ? esc_url_raw( $s['url'] ) : '',
		'cta'   => isset( $s['cta'] ) ? sanitize_text_field( $s['cta'] ) : '',
	);

	$s           = isset( $in['shop'] ) && is_array( $in['shop'] ) ? $in['shop'] : array();
	$out['shop'] = array(
		'on'    => empty( $s['on'] ) ? 0 : 1,
		'title' => isset( $s['title'] ) ? sanitize_text_field( $s['title'] ) : '',
		'items' => array(),
	);
	for ( $i = 0; $i < 4; $i++ ) {
		$it                     = isset( $s['items'][ $i ] ) && is_array( $s['items'][ $i ] ) ? $s['items'][ $i ] : array();
		$out['shop']['items'][] = array(
			'name'  => isset( $it['name'] ) ? sanitize_text_field( $it['name'] ) : '',
			'desc'  => isset( $it['desc'] ) ? sanitize_text_field( $it['desc'] ) : '',
			'price' => isset( $it['price'] ) ? sanitize_text_field( $it['price'] ) : '',
			'img'   => isset( $it['img'] ) ? esc_url_raw( $it['img'] ) : '',
			'url'   => isset( $it['url'] ) ? esc_url_raw( $it['url'] ) : '',
		);
	}

	$s         = isset( $in['nl'] ) && is_array( $in['nl'] ) ? $in['nl'] : array();
	$out['nl'] = array(
		'on'     => empty( $s['on'] ) ? 0 : 1,
		'kicker' => isset( $s['kicker'] ) ? sanitize_text_field( $s['kicker'] ) : '',
		'title'  => isset( $s['title'] ) ? sanitize_text_field( $s['title'] ) : '',
		'text'   => isset( $s['text'] ) ? sanitize_textarea_field( $s['text'] ) : '',
		'code'   => isset( $s['code'] ) ? epure_clean_code( $s['code'] ) : '',
	);

	delete_transient( 'epure_most_read' );
	return $out;
}

add_action( 'admin_enqueue_scripts', 'epure_options_assets' );
function epure_options_assets( $hook ) {
	if ( 'appearance_page_epure-options' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'epure-admin', get_template_directory_uri() . '/assets/js/admin.js', array( 'jquery' ), EPURE_VERSION, true );
}

/* ——— Champs ——— */

function epure_name( $path ) {
	$n = 'epure_options';
	foreach ( (array) $path as $p ) {
		$n .= '[' . $p . ']';
	}
	return $n;
}

function epure_field_text( $path, $value, $class = 'regular-text', $placeholder = '' ) {
	printf( '<input type="text" class="%s" name="%s" value="%s" placeholder="%s">', esc_attr( $class ), esc_attr( epure_name( $path ) ), esc_attr( $value ), esc_attr( $placeholder ) );
}

function epure_field_check( $path, $value, $label ) {
	printf( '<label><input type="checkbox" name="%s" value="1" %s> %s</label>', esc_attr( epure_name( $path ) ), checked( ! empty( $value ), true, false ), esc_html( $label ) );
}

function epure_field_area( $path, $value, $rows = 4, $code = true ) {
	printf( '<textarea class="large-text %s" rows="%d" name="%s">%s</textarea>', $code ? 'code' : '', (int) $rows, esc_attr( epure_name( $path ) ), esc_textarea( $value ) );
}

function epure_field_image( $path, $value ) {
	printf(
		'<span class="epure-imgfield"><input type="url" class="regular-text epure-img" name="%s" value="%s" placeholder="Adresse de l\'image"> <button type="button" class="button epure-pick">Choisir une image</button> <button type="button" class="button-link epure-clear"%s>Retirer</button><br><img class="epure-preview" src="%s" alt="" style="max-width:320px;max-height:120px;margin-top:6px;border:1px solid #c3c4c7;%s"></span>',
		esc_attr( epure_name( $path ) ),
		esc_attr( $value ),
		$value ? '' : ' style="display:none"',
		esc_url( $value ),
		$value ? '' : 'display:none'
	);
}

function epure_field_cat( $path, $value ) {
	wp_dropdown_categories(
		array(
			'name'             => epure_name( $path ),
			'selected'         => (int) $value,
			'show_option_none' => 'Aucune (section masquée)',
			'option_none_value' => 0,
			'hide_empty'       => 0,
			'hierarchical'     => 1,
		)
	);
}

function epure_options_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$ads   = epure_opt( 'ads' );
	$skin  = epure_opt( 'skin' );
	$mea   = epure_opt( 'mea' );
	$shop  = epure_opt( 'shop' );
	$nl    = epure_opt( 'nl' );
	$slots = epure_ad_slots();
	$done  = get_option( 'epure_setup_done' );
	$demo  = epure_demo_ids();
	?>
	<div class="wrap">
		<h1>Réglages L'Épure</h1>
		<?php if ( isset( $_GET['epure_msg'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( epure_admin_message( sanitize_key( $_GET['epure_msg'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?></p></div>
		<?php endif; ?>
		<?php settings_errors(); ?>

		<h2>Structure du site</h2>
		<p>
			<?php if ( $done ) : ?>
				Structure installée le <?php echo esc_html( date_i18n( 'j F Y à H:i', (int) $done ) ); ?> : rubriques, pages et menus.
			<?php else : ?>
				La structure n'a pas encore été installée.
			<?php endif; ?>
			Relancer l'installation ne crée que ce qui manque et ne modifie rien d'existant.
		</p>
		<p>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=epure_setup' ), 'epure_setup' ) ); ?>">Relancer la structuration</a>
			<?php if ( $demo ) : ?>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=epure_demo_delete' ), 'epure_demo_delete' ) ); ?>">Supprimer les <?php echo count( $demo ); ?> articles de démonstration</a>
			<?php else : ?>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=epure_demo_create' ), 'epure_demo_create' ) ); ?>">Créer des articles de démonstration</a>
			<?php endif; ?>
			<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">Modifier les menus</a>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'epure_options_group' ); ?>

			<hr><h2>Page d'accueil</h2>
			<p>La page d'accueil se construit toute seule : un grand article à la une, puis une tranche par rubrique principale. Un article classé dans une rubrique (ou l'une de ses sous-rubriques) apparaît automatiquement dans la tranche de cette rubrique.</p>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Article à la une</th><td>
					<?php
					wp_dropdown_categories(
						array(
							'name'              => epure_name( 'home_hero_cat' ),
							'selected'          => (int) epure_opt( 'home_hero_cat' ),
							'show_option_none'  => 'Dernier article, toutes rubriques confondues',
							'option_none_value' => 0,
							'hide_empty'        => 0,
							'hierarchical'      => 1,
						)
					);
					?>
					<p class="description">Choisissez une rubrique (par exemple « Actualité ») : son dernier article publié prend la une. Sans rubrique choisie, la une affiche l'article « épinglé » le plus récent, sinon le dernier article du site.</p>
				</td></tr>
				<tr><th scope="row">Articles par tranche</th><td>
					<select name="<?php echo esc_attr( epure_name( 'home_count' ) ); ?>">
						<?php foreach ( array( 3, 4, 6, 8 ) as $epure_n ) : ?>
							<option value="<?php echo (int) $epure_n; ?>" <?php selected( (int) epure_opt( 'home_count' ), $epure_n ); ?>><?php echo (int) $epure_n; ?> articles</option>
						<?php endforeach; ?>
					</select>
					<p class="description">Une tranche affiche les derniers articles de sa rubrique, jusqu'à ce nombre. Une rubrique sans article n'a pas de tranche.</p>
				</td></tr>
				<tr><th scope="row">Rubriques sans tranche</th><td>
					<?php
					$epure_hide = (array) epure_opt( 'home_hide' );
					foreach ( get_categories( array( 'parent' => 0, 'hide_empty' => 0 ) ) as $epure_c ) {
						printf(
							'<label style="display:inline-block;min-width:180px;margin-bottom:4px"><input type="checkbox" name="%s[]" value="%d" %s> %s</label>',
							esc_attr( epure_name( 'home_hide' ) ),
							(int) $epure_c->term_id,
							checked( in_array( (int) $epure_c->term_id, $epure_hide, true ), true, false ),
							esc_html( $epure_c->name )
						);
					}
					?>
					<p class="description">Cochez les rubriques à ne pas afficher sur l'accueil. Les tranches suivent l'ordre du menu principal (Apparence &gt; Menus) ; les rubriques absentes du menu viennent ensuite, par ordre alphabétique.</p>
				</td></tr>
			</table>

			<hr><h2>Publicité</h2>
			<p>Pour chaque emplacement : choisissez une image dans la médiathèque et indiquez le lien de destination. Pour changer de visuel, cliquez de nouveau sur « Choisir une image », puis enregistrez.</p>
			<p>Si vous passez par une régie, dépliez « Ou coller un code de régie » : le code n'est utilisé que lorsqu'aucune image n'est choisie. Un emplacement activé mais vide reste invisible pour les visiteurs ; vous y voyez un gabarit hachuré tant que vous êtes connecté.</p>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Format de la bannière haute</th><td>
					<label><input type="radio" name="<?php echo esc_attr( epure_name( 'top_format' ) ); ?>" value="billboard" <?php checked( epure_opt( 'top_format' ), 'billboard' ); ?>> Billboard 970 × 250</label><br>
					<label><input type="radio" name="<?php echo esc_attr( epure_name( 'top_format' ) ); ?>" value="leaderboard" <?php checked( epure_opt( 'top_format' ), 'leaderboard' ); ?>> Leaderboard 728 × 90</label>
				</td></tr>
				<?php foreach ( $slots as $id => $slot ) : ?>
					<tr><th scope="row"><?php echo esc_html( $slot['label'] ); ?><br><code><?php echo esc_html( $id ); ?></code></th><td>
						<p><?php epure_field_check( array( 'ads', $id, 'on' ), $ads[ $id ]['on'], 'Emplacement activé' ); ?>
						<span class="description">
							<?php
							$f = array();
							if ( $slot['d'] ) {
								$f[] = 'ordinateur ' . $slot['d'][0] . ' × ' . $slot['d'][1];
							}
							if ( $slot['m'] ) {
								$f[] = 'mobile ' . $slot['m'][0] . ' × ' . $slot['m'][1];
							}
							echo esc_html( ' · ' . implode( ', ', $f ) );
							?>
						</span></p>
						<?php $epure_two = $slot['d'] && $slot['m'] !== $slot['d']; ?>
						<?php if ( $slot['d'] ) : ?>
							<p><strong>Image<?php echo $epure_two ? ' ordinateur' : ''; ?></strong> <span class="description"><?php echo esc_html( $slot['d'][0] . ' × ' . $slot['d'][1] . ' px' ); ?></span><br><?php epure_field_image( array( 'ads', $id, 'img' ), $ads[ $id ]['img'] ); ?></p>
						<?php endif; ?>
						<?php if ( $epure_two || ! $slot['d'] ) : ?>
							<p><strong>Image mobile</strong> <span class="description"><?php echo esc_html( $slot['m'][0] . ' × ' . $slot['m'][1] . ' px' . ( $epure_two ? ' · facultative' : '' ) ); ?></span><br><?php epure_field_image( array( 'ads', $id, 'img_m' ), $ads[ $id ]['img_m'] ); ?></p>
						<?php endif; ?>
						<p><strong>Lien au clic</strong><br><?php epure_field_text( array( 'ads', $id, 'url' ), $ads[ $id ]['url'], 'large-text', 'https://' ); ?></p>
						<details<?php echo ( '' !== trim( $ads[ $id ]['code'] . $ads[ $id ]['code_m'] ) ) ? ' open' : ''; ?>>
							<summary>Ou coller un code de régie à la place de l'image</summary>
							<?php if ( $slot['d'] ) : ?>
								<p><?php epure_field_area( array( 'ads', $id, 'code' ), $ads[ $id ]['code'], 3 ); ?></p>
								<p><span class="description">Code mobile (facultatif)</span><?php epure_field_area( array( 'ads', $id, 'code_m' ), $ads[ $id ]['code_m'], 2 ); ?></p>
							<?php else : ?>
								<p><?php epure_field_area( array( 'ads', $id, 'code_m' ), $ads[ $id ]['code_m'], 3 ); ?></p>
							<?php endif; ?>
						</details>
					</td></tr>
				<?php endforeach; ?>
				<tr><th scope="row">Position du pavé dans l'article</th><td>Après le paragraphe n° <input type="number" min="1" max="12" class="small-text" name="<?php echo esc_attr( epure_name( 'inread_after' ) ); ?>" value="<?php echo esc_attr( epure_opt( 'inread_after' ) ); ?>"></td></tr>
				<tr><th scope="row">Code global de la régie</th><td><?php epure_field_area( 'head_code', epure_opt( 'head_code' ), 5 ); ?><p class="description">Inséré dans l'en-tête de toutes les pages (bibliothèque de la régie, déclaration des emplacements).</p></td></tr>
				<tr><th scope="row">Habillage</th><td>
					<p><?php epure_field_check( array( 'skin', 'on' ), $skin['on'], 'Habillage activé (écrans de 1440 px et plus)' ); ?></p>
					<p><?php epure_field_image( array( 'skin', 'img' ), $skin['img'] ); ?></p>
					<p><?php epure_field_text( array( 'skin', 'url' ), $skin['url'], 'regular-text', 'Lien de destination' ); ?>
					<?php epure_field_text( array( 'skin', 'color' ), $skin['color'], 'small-text', '#ffffff' ); ?> <span class="description">couleur de fond</span></p>
					<p class="description">Image centrée en haut de page, 1800 px de large conseillés ; le contenu occupe les 1180 px du centre.</p>
				</td></tr>
			</table>

			<hr><h2>Mise en avant partenaire</h2>
			<p>Bandeau sombre pleine largeur sur la page d'accueil. Il s'affiche dès qu'un titre est renseigné.</p>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Affichage</th><td><?php epure_field_check( array( 'mea', 'on' ), $mea['on'], 'Bandeau activé' ); ?></td></tr>
				<tr><th scope="row">Mention</th><td><?php epure_field_text( array( 'mea', 'label' ), $mea['label'] ); ?></td></tr>
				<tr><th scope="row">Titre</th><td><?php epure_field_text( array( 'mea', 'title' ), $mea['title'], 'large-text' ); ?></td></tr>
				<tr><th scope="row">Texte</th><td><?php epure_field_area( array( 'mea', 'text' ), $mea['text'], 3, false ); ?></td></tr>
				<tr><th scope="row">Image (3:2)</th><td><?php epure_field_image( array( 'mea', 'img' ), $mea['img'] ); ?></td></tr>
				<tr><th scope="row">Lien et bouton</th><td><?php epure_field_text( array( 'mea', 'url' ), $mea['url'], 'regular-text', 'https://' ); ?> <?php epure_field_text( array( 'mea', 'cta' ), $mea['cta'], 'regular-text', 'Texte du bouton' ); ?></td></tr>
				<tr><th scope="row">Carte native</th><td><?php epure_field_check( 'native', epure_opt( 'native' ), 'Insérer le dernier article sponsorisé en 5e position des listes de rubrique' ); ?><p class="description">Un article devient sponsorisé en cochant « Article sponsorisé » dans l'éditeur.</p></td></tr>
			</table>

			<hr><h2>Sélection shopping</h2>
			<p>Quatre vignettes produits sur la page d'accueil. Seules les vignettes ayant un nom s'affichent.</p>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Affichage</th><td><?php epure_field_check( array( 'shop', 'on' ), $shop['on'], 'Section activée' ); ?></td></tr>
				<tr><th scope="row">Titre</th><td><?php epure_field_text( array( 'shop', 'title' ), $shop['title'] ); ?></td></tr>
				<?php for ( $i = 0; $i < 4; $i++ ) : $it = $shop['items'][ $i ]; ?>
					<tr><th scope="row">Produit <?php echo (int) $i + 1; ?></th><td>
						<p><?php epure_field_text( array( 'shop', 'items', $i, 'name' ), $it['name'], 'regular-text', 'Nom' ); ?> <?php epure_field_text( array( 'shop', 'items', $i, 'price' ), $it['price'], 'small-text', 'Prix' ); ?></p>
						<p><?php epure_field_text( array( 'shop', 'items', $i, 'desc' ), $it['desc'], 'large-text', 'Description courte' ); ?></p>
						<p><?php epure_field_image( array( 'shop', 'items', $i, 'img' ), $it['img'] ); ?></p>
						<p><?php epure_field_text( array( 'shop', 'items', $i, 'url' ), $it['url'], 'large-text', 'Lien (affilié ou partenaire)' ); ?></p>
					</td></tr>
				<?php endfor; ?>
			</table>

			<hr><h2>Newsletter</h2>
			<p>Bloc affiché au-dessus du pied de page. Par défaut, il utilise le formulaire intégré : les adresses arrivent dans le menu <a href="<?php echo esc_url( admin_url( 'admin.php?page=epure-newsletter' ) ); ?>">Newsletter</a>. Le même formulaire se place dans n'importe quelle page avec le code <code>[epure_newsletter]</code>.</p>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Affichage</th><td><?php epure_field_check( array( 'nl', 'on' ), $nl['on'], 'Bloc activé' ); ?></td></tr>
				<tr><th scope="row">Surtitre</th><td><?php epure_field_text( array( 'nl', 'kicker' ), $nl['kicker'] ); ?></td></tr>
				<tr><th scope="row">Titre</th><td><?php epure_field_text( array( 'nl', 'title' ), $nl['title'], 'large-text' ); ?></td></tr>
				<tr><th scope="row">Texte</th><td><?php epure_field_area( array( 'nl', 'text' ), $nl['text'], 2, false ); ?></td></tr>
				<tr><th scope="row">Formulaire externe (facultatif)</th><td><?php epure_field_area( array( 'nl', 'code' ), $nl['code'], 4 ); ?><p class="description">À remplir seulement si vous préférez le formulaire de votre outil d'e-mailing : il remplace alors le formulaire intégré dans le pied de page.</p></td></tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
