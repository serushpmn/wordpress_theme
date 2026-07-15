<?php
/**
 * Comments template.
 *
 * @package AlmasLand
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area content-article">
	<?php if ( have_comments() ) : ?>
		<h2>
			<?php
			printf(
				esc_html( _nx( 'یک دیدگاه', '%1$s دیدگاه', get_comments_number(), 'comments title', 'almas-land' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	if ( ! comments_open() && get_comments_number() ) :
		?>
		<p class="no-comments"><?php esc_html_e( 'دیدگاه‌ها بسته شده‌اند.', 'almas-land' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
