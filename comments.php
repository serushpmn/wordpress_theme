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
<section id="comments" class="blog-comments">
	<?php if ( have_comments() ) : ?>
		<header class="blog-comments__header">
			<h2>
				<?php
				printf(
					/* translators: %s: comment count */
					esc_html( _nx( 'یک دیدگاه', '%s دیدگاه', get_comments_number(), 'comments title', 'almas-land' ) ),
					esc_html( almasland_persian_digits( number_format_i18n( get_comments_number() ) ) )
				);
				?>
			</h2>
		</header>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'دیدگاه‌ها بسته شده‌اند.', 'almas-land' ); ?></p>
	<?php endif; ?>

	<div class="blog-comments__form">
		<?php
		comment_form(
			array(
				'title_reply'          => __( 'دیدگاه خود را بنویسید', 'almas-land' ),
				'title_reply_to'       => __( 'پاسخ به %s', 'almas-land' ),
				'cancel_reply_link'    => __( 'لغو پاسخ', 'almas-land' ),
				'label_submit'         => __( 'ارسال دیدگاه', 'almas-land' ),
				'comment_notes_before' => '',
				'class_submit'         => 'btn btn--primary',
			)
		);
		?>
	</div>
</section>
