<?php
/**
 * Sidebar template.
 *
 * @package AlmasLand
 */

if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<aside class="sidebar-panel surface-panel" aria-label="<?php esc_attr_e( 'سایدبار', 'almas-land' ); ?>">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</aside>
<?php else : ?>
	<aside class="sidebar-panel surface-panel">
		<h2><?php esc_html_e( 'دسترسی سریع', 'almas-land' ); ?></h2>
		<ul class="sidebar-list">
			<?php wp_list_categories( array( 'title_li' => '' ) ); ?>
		</ul>
	</aside>
<?php endif; ?>
