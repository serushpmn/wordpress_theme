<?php
/**
 * Downloads list.
 *
 * @package AlmasLand
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

$downloads = WC()->customer->get_downloadable_products();
?>
<div class="account-card surface-panel">
	<h2><?php esc_html_e( 'دانلودها', 'almas-land' ); ?></h2>
	<?php if ( $downloads ) : ?>
		<div class="table-wrapper">
			<table class="data-table woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive order_details">
				<thead>
					<tr>
						<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
							<th><?php echo esc_html( $column_name ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $downloads as $download ) : ?>
						<tr>
							<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
								<td data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php
									if ( has_action( 'woocommerce_account_downloads_column_' . $column_id ) ) {
										do_action( 'woocommerce_account_downloads_column_' . $column_id, $download );
									} else {
										switch ( $column_id ) {
											case 'download-product':
												if ( $download['product_url'] ) {
													echo '<a href="' . esc_url( $download['product_url'] ) . '">' . esc_html( $download['product_name'] ) . '</a>';
												} else {
													echo esc_html( $download['product_name'] );
												}
												break;
											case 'download-file':
												echo '<a href="' . esc_url( $download['download_url'] ) . '" class="btn btn--ghost btn--small">' . esc_html( $download['download_name'] ) . '</a>';
												break;
											case 'download-remaining':
												echo is_numeric( $download['downloads_remaining'] ) ? esc_html( almasland_persian_digits( $download['downloads_remaining'] ) ) : esc_html__( 'نامحدود', 'almas-land' );
												break;
											case 'download-expires':
												if ( ! empty( $download['access_expires'] ) ) {
													echo esc_html( almasland_persian_digits( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) ) );
												} else {
													esc_html_e( 'هرگز', 'almas-land' );
												}
												break;
										}
									}
									?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="empty-state">
			<p><?php esc_html_e( 'فایل قابل دانلودی برای حساب شما موجود نیست.', 'almas-land' ); ?></p>
		</div>
	<?php endif; ?>
</div>
