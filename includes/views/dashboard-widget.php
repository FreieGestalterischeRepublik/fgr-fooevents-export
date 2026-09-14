<?php
/**
 * View: Dashboard-Widget.
 *
 * Erwartet folgende Variablen aus FGR_FE_Dashboard_Widget::render():
 * $upcoming_products, $month_products, $stats, $month_totals, $month_label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="fgr-fe-dashboard-widget">
	<h4><?php esc_html_e( 'Nächste Kurse', 'fgr-fooevents-export' ); ?></h4>
	<?php if ( empty( $upcoming_products ) ) : ?>
		<p><?php esc_html_e( 'Keine anstehenden Kurse.', 'fgr-fooevents-export' ); ?></p>
	<?php else : ?>
		<ul style="margin: 0 0 1em;">
			<?php foreach ( $upcoming_products as $product ) : ?>
				<?php
				$product_stats = isset( $stats[ $product->id ] ) ? $stats[ $product->id ] : (object) array(
					'sold' => 0,
					'free' => 0,
				);
				$capacity      = $product_stats->sold + $product_stats->free;
				?>
				<li>
					<strong><?php echo esc_html( $product->date_display . ' ' . $product->time_display ); ?></strong>
					– <?php echo esc_html( $product->name ); ?>
					<?php
					printf(
						/* translators: 1: verkaufte Tickets, 2: Gesamtkapazität */
						esc_html__( '(%1$d von %2$d Plätzen belegt)', 'fgr-fooevents-export' ),
						(int) $product_stats->sold,
						(int) $capacity
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<h4><?php echo esc_html( sprintf( __( 'Diesen Monat (%s)', 'fgr-fooevents-export' ), $month_label ) ); ?></h4>
	<?php if ( empty( $month_products ) ) : ?>
		<p><?php esc_html_e( 'Keine Kurse in diesem Monat.', 'fgr-fooevents-export' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table" style="width: auto;">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Verkaufte Tickets', 'fgr-fooevents-export' ); ?></td>
					<td><strong><?php echo esc_html( $month_totals->sold ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Noch freie Plätze', 'fgr-fooevents-export' ); ?></td>
					<td><strong><?php echo esc_html( $month_totals->free ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Nettoumsatz', 'fgr-fooevents-export' ); ?></td>
					<td><strong><?php echo wp_kses_post( wc_price( $month_totals->revenue ) ); ?></strong></td>
				</tr>
			</tbody>
		</table>
	<?php endif; ?>

	<p style="margin-top: 1em;">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . FGR_FE_Admin::PAGE_SLUG ) ); ?>">
			<?php esc_html_e( 'Zur vollständigen Kurs-Übersicht', 'fgr-fooevents-export' ); ?>
		</a>
	</p>
</div>
