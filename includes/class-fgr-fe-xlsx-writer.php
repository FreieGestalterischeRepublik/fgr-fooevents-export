<?php
/**
 * Minimaler XLSX-Schreiber (eine Tabelle, ein Arbeitsblatt) ohne Fremdbibliothek.
 *
 * Nutzt ausschließlich PHPs eingebautes ZipArchive. Alle Zellen werden als
 * Text geschrieben (t="inlineStr"), damit Excel z. B. Telefonnummern mit
 * führender Null oder Bestellnummern nicht versehentlich in Zahlen umwandelt.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FGR_FE_Xlsx_Writer {

	/**
	 * Erstellt die XLSX-Datei und sendet sie direkt als Download.
	 *
	 * @param string $filename Dateiname für den Download (inkl. .xlsx).
	 * @param array  $header   Spaltenüberschriften.
	 * @param array  $rows     Array von Arrays mit den Zeilenwerten.
	 */
	public static function output( $filename, array $header, array $rows ) {
		// wp_tempnam() legt die Datei bereits (leer) an; ZipArchive erwartet für
		// CREATE aber einen noch nicht existierenden Pfad, sonst gibt PHP 8 eine
		// Deprecation-Notice aus ("Using empty file as ZipArchive").
		$tmp_file = wp_tempnam( 'fgr-fe-export.xlsx' );
		wp_delete_file( $tmp_file );

		$zip = new ZipArchive();
		$zip->open( $tmp_file, ZipArchive::CREATE );

		$zip->addFromString( '[Content_Types].xml', self::content_types_xml() );
		$zip->addFromString( '_rels/.rels', self::root_rels_xml() );
		$zip->addFromString( 'xl/workbook.xml', self::workbook_xml() );
		$zip->addFromString( 'xl/_rels/workbook.xml.rels', self::workbook_rels_xml() );
		$zip->addFromString( 'xl/styles.xml', self::styles_xml() );
		$zip->addFromString( 'xl/worksheets/sheet1.xml', self::sheet_xml( $header, $rows ) );

		$zip->close();

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . filesize( $tmp_file ) );

		readfile( $tmp_file );
		wp_delete_file( $tmp_file );
		exit;
	}

	/**
	 * Entfernt für XML ungültige Steuerzeichen und escaped Sonderzeichen.
	 */
	private static function esc( $value ) {
		$value = (string) $value;
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value );
		return htmlspecialchars( $value, ENT_QUOTES | ENT_XML1, 'UTF-8' );
	}

	private static function content_types_xml() {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
			. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
			. '<Default Extension="xml" ContentType="application/xml"/>'
			. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
			. '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
			. '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
			. '</Types>';
	}

	private static function root_rels_xml() {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
			. '</Relationships>';
	}

	private static function workbook_xml() {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
			. '<sheets><sheet name="Kurs-Uebersicht" sheetId="1" r:id="rId1"/></sheets>'
			. '</workbook>';
	}

	private static function workbook_rels_xml() {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
			. '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
			. '</Relationships>';
	}

	private static function styles_xml() {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
			. '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
			. '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
			. '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
			. '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
			. '<cellXfs count="2">'
			. '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
			. '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
			. '</cellXfs>'
			. '<cellStyles count="1"><cellStyle name="Standard" xfId="0" builtinId="0"/></cellStyles>'
			. '</styleSheet>';
	}

	/**
	 * Wandelt einen 0-basierten Spaltenindex in einen Excel-Spaltenbuchstaben um (0 -> A, 25 -> Z, 26 -> AA, ...).
	 */
	private static function column_letter( $index ) {
		$letter = '';
		$index++;
		while ( $index > 0 ) {
			$mod    = ( $index - 1 ) % 26;
			$letter = chr( 65 + $mod ) . $letter;
			$index  = (int) ( ( $index - $mod ) / 26 );
		}
		return $letter;
	}

	private static function sheet_xml( array $header, array $rows ) {
		$xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

		$xml .= '<cols>';
		$widths = array( 12, 8, 30, 14, 22, 28, 16, 14, 18 );
		foreach ( $widths as $i => $width ) {
			$col  = $i + 1;
			$xml .= '<col min="' . $col . '" max="' . $col . '" width="' . $width . '" customWidth="1"/>';
		}
		$xml .= '</cols>';

		$xml .= '<sheetData>';

		$xml .= '<row r="1">';
		foreach ( $header as $i => $value ) {
			$cell_ref = self::column_letter( $i ) . '1';
			$xml     .= '<c r="' . $cell_ref . '" t="inlineStr" s="1"><is><t xml:space="preserve">' . self::esc( $value ) . '</t></is></c>';
		}
		$xml .= '</row>';

		$row_num = 2;
		foreach ( $rows as $row ) {
			$xml .= '<row r="' . $row_num . '">';
			foreach ( $row as $i => $value ) {
				$cell_ref = self::column_letter( $i ) . $row_num;
				$xml     .= '<c r="' . $cell_ref . '" t="inlineStr"><is><t xml:space="preserve">' . self::esc( $value ) . '</t></is></c>';
			}
			$xml .= '</row>';
			++$row_num;
		}

		$xml .= '</sheetData>';
		$xml .= '</worksheet>';

		return $xml;
	}
}
