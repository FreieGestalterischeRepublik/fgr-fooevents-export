<?php
/**
 * WP-Dashboard-Widget: nächste Kurse + Kennzahlen für den aktuellen Kalendermonat.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FGR_FE_Dashboard_Widget {

	const UPCOMING_LIMIT = 5;

	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'register' ) );
	}

	public function register() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		wp_add_dashboard_widget(
			'fgr_fe_dashboard_widget',
			__( 'Kochkurse – Übersicht', 'fgr-fooevents-export' ),
			array( $this, 'render' )
		);
	}

	public function render() {
		$now       = current_time( 'timestamp' );
		$month_key = date_i18n( 'Y-m', $now );

		$products = FGR_FE_Data::get_event_products();

		$upcoming_products = array_values(
			array_filter(
				$products,
				function ( $product ) use ( $now ) {
					return $product->timestamp >= $now;
				}
			)
		);
		$upcoming_products = array_slice( $upcoming_products, 0, self::UPCOMING_LIMIT );

		$month_products = array_values(
			array_filter(
				$products,
				function ( $product ) use ( $month_key ) {
					return $product->month_key === $month_key;
				}
			)
		);

		$stats_product_ids = array_unique(
			array_merge(
				wp_list_pluck( $upcoming_products, 'id' ),
				wp_list_pluck( $month_products, 'id' )
			)
		);

		$stats = FGR_FE_Data::get_product_stats( $stats_product_ids );

		$month_totals = (object) array(
			'sold'    => 0,
			'free'    => 0,
			'revenue' => 0.0,
		);
		foreach ( $month_products as $product ) {
			if ( ! isset( $stats[ $product->id ] ) ) {
				continue;
			}
			$month_totals->sold    += $stats[ $product->id ]->sold;
			$month_totals->free    += $stats[ $product->id ]->free;
			$month_totals->revenue += $stats[ $product->id ]->revenue_net;
		}

		$month_label = date_i18n( 'F Y', $now );

		include FGR_FE_PATH . 'includes/views/dashboard-widget.php';
	}
}
