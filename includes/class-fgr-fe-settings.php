<?php
/**
 * Export-Einstellungen: welche zusätzlichen Spalten der Export enthalten soll.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FGR_FE_Settings {

	const PAGE_SLUG = 'fgr-fooevents-export-settings';
	const OPTION_KEY = 'fgr_fe_export_fields';

	/**
	 * Verfügbare Zusatzfelder für den Export. Reihenfolge = Spaltenreihenfolge
	 * im Export (nach den festen Standardspalten).
	 */
	public static function get_field_definitions() {
		return array(
			'checkin_status'   => array(
				'label'       => __( 'Check-in-Status', 'fgr-fooevents-export' ),
				'description' => __( 'Ob das Ticket bereits eingecheckt, storniert oder noch offen ist.', 'fgr-fooevents-export' ),
			),
			'ticket_price'     => array(
				'label'       => __( 'Ticket-Preis', 'fgr-fooevents-export' ),
				'description' => __( 'Preis dieses einzelnen Tickets (aus dem Ticket selbst, nicht der ganzen Bestellung).', 'fgr-fooevents-export' ),
			),
			'order_date'       => array(
				'label'       => __( 'Bestelldatum', 'fgr-fooevents-export' ),
				'description' => __( 'Datum und Uhrzeit, an dem die Bestellung aufgegeben wurde.', 'fgr-fooevents-export' ),
			),
			'payment_method'   => array(
				'label'       => __( 'Zahlungsmethode', 'fgr-fooevents-export' ),
				'description' => __( 'z. B. Kreditkarte, PayPal, Überweisung.', 'fgr-fooevents-export' ),
			),
			'coupon_code'      => array(
				'label'       => __( 'Gutschein-Code', 'fgr-fooevents-export' ),
				'description' => __( 'Bei der Bestellung eingelöste Rabattcodes, falls vorhanden.', 'fgr-fooevents-export' ),
			),
			'order_note'       => array(
				'label'       => __( 'Kundennotiz zur Bestellung', 'fgr-fooevents-export' ),
				'description' => __( 'Freitext, den die Kundschaft beim Bestellen hinterlassen hat (z. B. Allergien, Wünsche).', 'fgr-fooevents-export' ),
			),
			'billing_address'  => array(
				'label'       => __( 'Rechnungsadresse', 'fgr-fooevents-export' ),
				'description' => __( 'Straße, PLZ und Ort aus der Bestellung.', 'fgr-fooevents-export' ),
			),
			'attendee_company' => array(
				'label'       => __( 'Firma', 'fgr-fooevents-export' ),
				'description' => __( 'Firmenangabe, falls beim Ticket erfasst (bei diesem Shop aktuell nicht genutzt).', 'fgr-fooevents-export' ),
			),
		);
	}

	/**
	 * Aktuell aktivierte Zusatzfelder, in der festgelegten Reihenfolge.
	 *
	 * @return string[]
	 */
	public static function get_enabled_fields() {
		$saved   = get_option( self::OPTION_KEY, array() );
		$defined = array_keys( self::get_field_definitions() );
		return array_values( array_intersect( $defined, (array) $saved ) );
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	public function register_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Export-Einstellungen', 'fgr-fooevents-export' ),
			__( 'Export-Einstellungen', 'fgr-fooevents-export' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung für diese Seite.', 'fgr-fooevents-export' ) );
		}

		if ( isset( $_POST['fgr_fe_settings_nonce'] ) && check_admin_referer( 'fgr_fe_save_settings', 'fgr_fe_settings_nonce' ) ) {
			$selected = isset( $_POST['fgr_fe_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['fgr_fe_fields'] ) ) : array();
			$valid    = array_keys( self::get_field_definitions() );
			update_option( self::OPTION_KEY, array_values( array_intersect( $valid, $selected ) ) );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Einstellungen gespeichert.', 'fgr-fooevents-export' ) . '</p></div>';
		}

		$enabled = self::get_enabled_fields();

		include FGR_FE_PATH . 'includes/views/settings-page.php';
	}
}
