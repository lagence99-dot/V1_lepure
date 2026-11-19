<?php
/**
 * Commentaires.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments">
	<?php if ( have_comments() ) : ?>
		<h2><?php echo esc_html( sprintf( _n( '%s commentaire', '%s commentaires', get_comments_number(), 'epure' ), number_format_i18n( get_comments_number() ) ) ); ?></h2>
		<ol class="comment-list">
			<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true, 'avatar_size' => 0 ) ); ?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>
	<?php comment_form(); ?>
</section>
