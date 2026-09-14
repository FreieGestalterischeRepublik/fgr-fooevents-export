<?php
/**
 * Export der aktuell gefilterten Kurs-Übersicht als XLSX (Standard) oder CSV.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FGR_FE_Export {

	private static function get_columns() {
		return array(
			__( 'Kurs-Datum', 'fgr-fooevents-export' ),
			__( 'Uhrzeit', 'fgr-fooevents-export' ),
			__( 'Kursname', 'fgr-fooevents-export' ),
			__( 'SKU', 'fgr-fooevents-export' ),
			__( 'Teilnehmer-Name', 'fgr-fooevents-export' ),
			__( 'E-Mail', 'fgr-fooevents-export' ),
			__( 'Telefon', 'fgr-fooevents-export' ),
			__( 'Bestellnummer', 'fgr-fooevents-export' ),
			__( 'Zahlungsstatus', 'fgr-fooevents-export' ),
		);
	}

	private static function row_to_columns( $row ) {
		return array(
			$row->date_display,
			$row->time_display,
			$row->course_name,
			$row->sku,
			$row->name,
			$row->email,
			$row->phone,
			$row->order_number,
			$row->payment_label,
		);
	}

	public function __construct() {
		add_action( 'admin_post_fgr_fe_export', array( $this, 'handle_export' ) );
	}

	public function handle_export() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung für diesen Export.', 'fgr-fooevents-export' ) );
		}

		check_admin_referer( 'fgr_fe_export', 'fgr_fe_nonce' );

		$filters = FGR_FE_Admin::get_current_filters();
		$rows    = FGR_FE_Data::get_flat_rows( $filters );
		$format  = isset( $_GET['format'] ) && 'csv' === $_GET['format'] ? 'csv' : 'xlsx';

		if ( 'csv' === $format ) {
			$this->output_csv( $rows );
		} else {
			$this->output_xlsx( $rows );
		}
	}

	private function output_xlsx( array $rows ) {
		$data = array();
		foreach ( $rows as $row ) {
			$data[] = self::row_to_columns( $row );
		}

		FGR_FE_Xlsx_Writer::output(
			'kurs-uebersicht-' . gmdate( 'Y-m-d' ) . '.xlsx',
			self::get_columns(),
			$data
		);
	}

	private function output_csv( array $rows ) {
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="kurs-uebersicht-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );

		// UTF-8-BOM, damit Excel Umlaute korrekt anzeigt.
		fwrite( $output, "\xEF\xBB\xBF" );

		fputcsv( $output, self::get_columns(), ';' );

		foreach ( $rows as $row ) {
			fputcsv( $output, self::row_to_columns( $row ), ';' );
		}

		fclose( $output );
		exit;
	}
}
