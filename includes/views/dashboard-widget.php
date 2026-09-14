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
<style>
	.fgr-fe-dashboard-widget h4 {
		margin: 0 0 8px;
		font-size: 13px;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #646970;
	}
	.fgr-fe-dashboard-widget h4:not(:first-child) {
		margin-top: 16px;
		padding-top: 14px;
		border-top: 1px solid #f0f0f1;
	}
	.fgr-fe-course-row {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 6px 0;
		border-bottom: 1px solid #f6f7f7;
	}
	.fgr-fe-course-row:last-child {
		border-bottom: none;
	}
	.fgr-fe-course-main {
		flex: 1;
		min-width: 0;
	}
	.fgr-fe-course-date {
		font-weight: 600;
		color: #1d2327;
	}
	.fgr-fe-course-name {
		color: #3c434a;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.fgr-fe-course-side {
		flex-shrink: 0;
		text-align: right;
		width: 74px;
	}
	.fgr-fe-course-count {
		font-size: 12px;
		color: #646970;
		display: block;
		margin-bottom: 3px;
	}
	.fgr-fe-bar {
		height: 5px;
		border-radius: 3px;
		background: #f0f0f1;
		overflow: hidden;
	}
	.fgr-fe-bar-fill {
		height: 100%;
		background: #2271b1;
		border-radius: 3px;
	}
	.fgr-fe-bar-fill.is-full {
		background: #d63638;
	}
	.fgr-fe-stats-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px;
	}
	.fgr-fe-stat {
		background: #f6f7f7;
		border-left: 3px solid #2271b1;
		border-radius: 2px;
		padding: 8px 10px;
	}
	.fgr-fe-stat.is-free { border-color: #8c8f94; }
	.fgr-fe-stat.is-revenue { border-color: #00a32a; }
	.fgr-fe-stat.is-potential { border-color: #dba617; }
	.fgr-fe-stat-value {
		display: block;
		font-size: 20px;
		font-weight: 600;
		line-height: 1.25;
		color: #1d2327;
	}
	.fgr-fe-stat-label {
		font-size: 11px;
		color: #646970;
	}
	.fgr-fe-formulas {
		margin-top: 10px;
		font-size: 12px;
		color: #3c434a;
	}
	.fgr-fe-formulas p {
		margin: 3px 0;
	}
	.fgr-fe-formulas strong {
		color: #1d2327;
	}
	.fgr-fe-widget-footer {
		margin: 14px 0 0;
		padding-top: 10px;
		border-top: 1px solid #f0f0f1;
	}
</style>

<div class="fgr-fe-dashboard-widget">
	<h4><?php esc_html_e( 'Nächste Kurse', 'fgr-fooevents-export' ); ?></h4>
	<?php if ( empty( $upcoming_products ) ) : ?>
		<p><?php esc_html_e( 'Keine anstehenden Kurse.', 'fgr-fooevents-export' ); ?></p>
	<?php else : ?>
		<div>
			<?php foreach ( $upcoming_products as $product ) : ?>
				<?php
				$product_stats = isset( $stats[ $product->id ] ) ? $stats[ $product->id ] : (object) array(
					'sold' => 0,
					'free' => 0,
				);
				$capacity      = $product_stats->sold + $product_stats->free;
				$percent_full  = $capacity > 0 ? min( 100, round( $product_stats->sold / $capacity * 100 ) ) : 0;
				?>
				<div class="fgr-fe-course-row">
					<div class="fgr-fe-course-main">
						<div class="fgr-fe-course-date"><?php echo esc_html( $product->date_display . ' · ' . $product->time_display ); ?></div>
						<div class="fgr-fe-course-name"><?php echo esc_html( $product->name ); ?></div>
					</div>
					<div class="fgr-fe-course-side">
						<span class="fgr-fe-course-count">
							<?php echo esc_html( $product_stats->sold . ' / ' . $capacity ); ?>
						</span>
						<div class="fgr-fe-bar">
							<div class="fgr-fe-bar-fill<?php echo 100 === (int) $percent_full ? ' is-full' : ''; ?>" style="width: <?php echo esc_attr( $percent_full ); ?>%;"></div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<h4><?php echo esc_html( sprintf( __( 'Diesen Monat (%s)', 'fgr-fooevents-export' ), $month_label ) ); ?></h4>
	<?php if ( empty( $month_products ) ) : ?>
		<p><?php esc_html_e( 'Keine Kurse in diesem Monat.', 'fgr-fooevents-export' ); ?></p>
	<?php else : ?>
		<div class="fgr-fe-stats-grid">
			<div class="fgr-fe-stat">
				<span class="fgr-fe-stat-value"><?php echo esc_html( number_format_i18n( $month_totals->sold ) ); ?></span>
				<span class="fgr-fe-stat-label"><?php esc_html_e( 'Verkaufte Tickets', 'fgr-fooevents-export' ); ?></span>
			</div>
			<div class="fgr-fe-stat is-free">
				<span class="fgr-fe-stat-value"><?php echo esc_html( number_format_i18n( $month_totals->free ) ); ?></span>
				<span class="fgr-fe-stat-label"><?php esc_html_e( 'Noch freie Plätze', 'fgr-fooevents-export' ); ?></span>
			</div>
			<div class="fgr-fe-stat is-revenue">
				<span class="fgr-fe-stat-value"><?php echo wp_kses_post( wc_price( $month_totals->revenue ) ); ?></span>
				<span class="fgr-fe-stat-label"><?php esc_html_e( 'Nettoumsatz', 'fgr-fooevents-export' ); ?></span>
			</div>
			<div class="fgr-fe-stat is-potential">
				<span class="fgr-fe-stat-value"><?php echo wp_kses_post( wc_price( $month_totals->potential ) ); ?></span>
				<span class="fgr-fe-stat-label"><?php esc_html_e( 'Potenzial (offene Plätze)', 'fgr-fooevents-export' ); ?></span>
			</div>
		</div>

		<div class="fgr-fe-formulas">
			<?php if ( $month_totals->sold > 0 ) : ?>
				<p>
					<?php
					printf(
						/* translators: 1: verkaufte Tickets, 2: Ø-Ticketpreis, 3: Nettoumsatz */
						esc_html__( '%1$s Tickets × Ø %2$s = %3$s Nettoumsatz', 'fgr-fooevents-export' ),
						'<strong>' . esc_html( number_format_i18n( $month_totals->sold ) ) . '</strong>',
						wp_kses_post( wc_price( $month_totals->avg_sold_price ) ),
						'<strong>' . wp_kses_post( wc_price( $month_totals->revenue ) ) . '</strong>'
					);
					?>
				</p>
			<?php endif; ?>
			<?php if ( $month_totals->free > 0 ) : ?>
				<p>
					<?php
					printf(
						/* translators: 1: freie Plätze, 2: Ø-Ticketpreis, 3: Umsatzpotenzial */
						esc_html__( '%1$s freie Plätze × Ø %2$s = %3$s Potenzial', 'fgr-fooevents-export' ),
						'<strong>' . esc_html( number_format_i18n( $month_totals->free ) ) . '</strong>',
						wp_kses_post( wc_price( $month_totals->avg_potential_price ) ),
						'<strong>' . wp_kses_post( wc_price( $month_totals->potential ) ) . '</strong>'
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<p class="fgr-fe-widget-footer">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . FGR_FE_Admin::PAGE_SLUG ) ); ?>">
			<?php esc_html_e( 'Zur vollständigen Kurs-Übersicht', 'fgr-fooevents-export' ); ?>
		</a>
	</p>
</div>
