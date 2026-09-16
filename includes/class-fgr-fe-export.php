<?php
/**
 * Export der aktuell gefilterten Kurs-Übersicht als XLSX (Standard) oder CSV.
 *
 * Welche Spalten in welcher Reihenfolge enthalten sind, kommt aus
 * FGR_FE_Settings (Seite "Export-Einstellungen").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FGR_FE_Export {

	private static function get_column_labels( array $columns ) {
		$definitions = FGR_FE_Settings::get_all_columns();
		$labels      = array();
		foreach ( $columns as $key ) {
			if ( isset( $definitions[ $key ] ) ) {
				$labels[] = $definitions[ $key ]['label'];
			}
		}
		return $labels;
	}

	private static function row_to_columns( $row, array $columns ) {
		$values = array();
		foreach ( $columns as $key ) {
			$values[] = isset( $row->$key ) ? $row->$key : '';
		}
		return $values;
	}

	/**
	 * Schutz gegen CSV-/Formel-Injection (OWASP "CSV Injection"): Zellwerte, die
	 * mit =, +, -, @ oder Tab beginnen, werden von Excel/LibreOffice als Formel
	 * interpretiert. Da Name/Telefon/Notiz aus Kundeneingaben beim Checkout
	 * stammen, koennte sonst z. B. ein bewusst als "Name" eingegebenes
	 * "=HYPERLINK(...)" beim Oeffnen der CSV ausgefuehrt werden. Ein
	 * fuehrendes Apostroph neutralisiert das, bleibt aber lesbar. Betrifft nur
	 * den CSV-Export - der XLSX-Export ist als Text-Zelltyp (t="inlineStr")
	 * bereits von sich aus dagegen sicher.
	 */
	private static function csv_safe( $value ) {
		$value = (string) $value;
		if ( '' !== $value && false !== strpos( "=+-@\t", $value[0] ) ) {
			return "'" . $value;
		}
		return $value;
	}

	private static function csv_safe_row( array $values ) {
		return array_map( array( __CLASS__, 'csv_safe' ), $values );
	}

	public function __construct() {
		add_action( 'admin_post_fgr_fe_export', array( $this, 'handle_export' ) );
	}

	public function handle_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung für diesen Export.', 'fgr-fooevents-export' ) );
		}

		check_admin_referer( 'fgr_fe_export', 'fgr_fe_nonce' );

		$filters      = FGR_FE_Admin::get_current_filters();
		$columns      = FGR_FE_Settings::get_enabled_columns();
		$extra_fields = FGR_FE_Settings::get_enabled_extra_fields();
		$rows         = FGR_FE_Data::get_flat_rows( $filters, $extra_fields );
		$format       = isset( $_GET['format'] ) && 'csv' === $_GET['format'] ? 'csv' : 'xlsx';

		if ( 'csv' === $format ) {
			$this->output_csv( $rows, $columns );
		} else {
			$this->output_xlsx( $rows, $columns );
		}
	}

	private function output_xlsx( array $rows, array $columns ) {
		$data = array();
		foreach ( $rows as $row ) {
			$data[] = self::row_to_columns( $row, $columns );
		}

		FGR_FE_Xlsx_Writer::output(
			'kurs-uebersicht-' . gmdate( 'Y-m-d' ) . '.xlsx',
			self::get_column_labels( $columns ),
			$data
		);
	}

	private function output_csv( array $rows, array $columns ) {
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="kurs-uebersicht-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );

		// UTF-8-BOM, damit Excel Umlaute korrekt anzeigt.
		fwrite( $output, "\xEF\xBB\xBF" );

		fputcsv( $output, self::get_column_labels( $columns ), ';' );

		foreach ( $rows as $row ) {
			fputcsv( $output, self::csv_safe_row( self::row_to_columns( $row, $columns ) ), ';' );
		}

		fclose( $output );
		exit;
	}
}
