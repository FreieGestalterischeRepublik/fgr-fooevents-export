<?php
/**
 * Export-Einstellungen: welche Spalten der Export enthält und in welcher Reihenfolge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FGR_FE_Settings {

	const PAGE_SLUG  = 'fgr-fooevents-export-settings';
	const OPTION_KEY = 'fgr_fe_export_columns';

	/**
	 * "Extra"-Spalten: brauchen zusätzliche Meta-/Bestell-Auswertung in
	 * FGR_FE_Data und werden dort nur berechnet, wenn aktiviert.
	 */
	const EXTRA_COLUMN_KEYS = array(
		'checkin_status',
		'ticket_price',
		'order_date',
		'payment_method',
		'coupon_code',
		'order_note',
		'billing_address',
		'attendee_company',
	);

	/**
	 * Alle im Export verfügbaren Spalten. Reihenfolge hier = Standard-Reihenfolge
	 * beim allerersten Aufruf (bevor die Nutzerin etwas umsortiert/gespeichert hat).
	 */
	public static function get_all_columns() {
		return array(
			'date_display'      => array(
				'label'           => __( 'Kurs-Datum', 'fgr-fooevents-export' ),
				'description'     => __( 'Datum des Kurstermins.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'time_display'      => array(
				'label'           => __( 'Uhrzeit', 'fgr-fooevents-export' ),
				'description'     => __( 'Startzeit des Kurstermins.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'course_name'       => array(
				'label'           => __( 'Kursname', 'fgr-fooevents-export' ),
				'description'     => __( 'Titel des Kochkurses.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'sku'                => array(
				'label'           => __( 'SKU', 'fgr-fooevents-export' ),
				'description'     => __( 'Artikelnummer des Kurs-Produkts.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'name'               => array(
				'label'           => __( 'Teilnehmer-Name', 'fgr-fooevents-export' ),
				'description'     => __( 'Name der teilnehmenden Person.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'email'              => array(
				'label'           => __( 'E-Mail', 'fgr-fooevents-export' ),
				'description'     => __( 'E-Mail-Adresse für den Kontakt.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'phone'              => array(
				'label'           => __( 'Telefon', 'fgr-fooevents-export' ),
				'description'     => __( 'Telefonnummer für den Kontakt, falls hinterlegt.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'order_number'       => array(
				'label'           => __( 'Bestellnummer', 'fgr-fooevents-export' ),
				'description'     => __( 'Nummer der zugehörigen WooCommerce-Bestellung.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'payment_label'      => array(
				'label'           => __( 'Zahlungsstatus', 'fgr-fooevents-export' ),
				'description'     => __( 'Status der Bestellung, z. B. Abgeschlossen.', 'fgr-fooevents-export' ),
				'default_enabled' => true,
			),
			'checkin_status'     => array(
				'label'           => __( 'Check-in-Status', 'fgr-fooevents-export' ),
				'description'     => __( 'Ob das Ticket bereits eingecheckt, storniert oder noch offen ist.', 'fgr-fooevents-export' ),
				'default_enabled' => false,
			),
			'ticket_price'       => array(
				'label'           => __( 'Ticket-Preis', 'fgr-fooevents-export' ),
				'description'     => __( 'Preis dieses einzelnen Tickets (aus dem Ticket selbst, nicht der ganzen Bestellung).', 'fgr-fooevents-export' ),
				'default_enabled' => false,
			),
			'order_date'         => array(
				'label'           => __( 'Bestelldatum', 'fgr-fooevents-export' ),
				'description'     => __( 'Datum und Uhrzeit, an dem die Bestellung aufgegeben wurde.', 'fgr-fooevents-export' ),
				'default_enabled' => false,
			),
			'payment_method'     => array(
				'label'           => __( 'Zahlungsmethode', 'fgr-fooevents-export' ),
				'description'     => __( 'z. B. Kreditkarte, PayPal, Überweisung.', 'fgr-fooevents-export' ),
				'default_enabled' => false,
			),
			'coupon_code'        => array(
				'label'           => __( 'Gutschein-Code', 'fgr-fooevents-export' ),
				'description'     => __( 'Bei der Bestellung eingelöste Rabattcodes, falls vorhanden.', 'fgr-fooevents-export' ),
				'default_enabled' => false,
			),
			'order_note'         => array(
				'label'           => __( 'Kundennotiz zur Bestellung', 'fgr-fooevents-export' ),
				'description'     => __( 'Freitext, den die Kundschaft beim Bestellen hinterlassen hat (z. B. Allergien, Wünsche).', 'fgr-fooevents-export' ),
				'default_enabled' => false,
			),
			'billing_address'    => array(
				'label'           => __( 'Rechnungsadresse', 'fgr-fooevents-export' ),
				'description'     => __( 'Straße, PLZ und Ort aus der Bestellung.', 'fgr-fooevents-export' ),
				'default_enabled' => false,
			),
			'attendee_company'   => array(
				'label'           => __( 'Firma', 'fgr-fooevents-export' ),
				'description'     => __( 'Firmenangabe, falls beim Ticket erfasst (bei diesem Shop aktuell nicht genutzt).', 'fgr-fooevents-export' ),
				'default_enabled' => false,
			),
		);
	}

	public static function get_default_layout() {
		$layout = array();
		foreach ( self::get_all_columns() as $key => $definition ) {
			$layout[] = array(
				'key'     => $key,
				'enabled' => $definition['default_enabled'],
			);
		}
		return $layout;
	}

	/**
	 * Gespeicherte Spalten-Reihenfolge + Ein/Aus-Status. Neu hinzugekommene
	 * Spalten (z. B. nach einem Plugin-Update) werden automatisch ans Ende
	 * angehängt, unbekannt gewordene Spalten (z. B. nach einer Umbenennung)
	 * werden stillschweigend übersprungen.
	 */
	public static function get_layout() {
		$saved       = get_option( self::OPTION_KEY, null );
		$all_columns = self::get_all_columns();

		if ( ! is_array( $saved ) ) {
			return self::get_default_layout();
		}

		$layout = array();
		$seen   = array();
		foreach ( $saved as $item ) {
			if ( ! is_array( $item ) || empty( $item['key'] ) || ! isset( $all_columns[ $item['key'] ] ) || isset( $seen[ $item['key'] ] ) ) {
				continue;
			}
			$layout[]              = array(
				'key'     => $item['key'],
				'enabled' => ! empty( $item['enabled'] ),
			);
			$seen[ $item['key'] ] = true;
		}

		foreach ( $all_columns as $key => $definition ) {
			if ( ! isset( $seen[ $key ] ) ) {
				$layout[] = array(
					'key'     => $key,
					'enabled' => $definition['default_enabled'],
				);
			}
		}

		return $layout;
	}

	/**
	 * Aktivierte Spalten in der eingestellten Reihenfolge (Basis für die
	 * Export-Spaltenreihenfolge).
	 *
	 * @return string[]
	 */
	public static function get_enabled_columns() {
		$keys = array();
		foreach ( self::get_layout() as $item ) {
			if ( $item['enabled'] ) {
				$keys[] = $item['key'];
			}
		}
		return $keys;
	}

	/**
	 * Nur die aktivierten "Extra"-Spalten – wird an FGR_FE_Data gereicht,
	 * damit dort nur die tatsächlich benötigten Zusatzauswertungen laufen.
	 *
	 * @return string[]
	 */
	public static function get_enabled_extra_fields() {
		return array_values( array_intersect( self::get_enabled_columns(), self::EXTRA_COLUMN_KEYS ) );
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

	/**
	 * Verarbeitet den POST der Einstellungsseite: übernimmt Checkbox-Status
	 * für alle übermittelten Spalten und führt, falls ein Auf-/Ab-Pfeil
	 * gedrückt wurde, zusätzlich die entsprechende Verschiebung aus.
	 */
	private function save_from_request() {
		$submitted_order   = isset( $_POST['fgr_fe_order'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['fgr_fe_order'] ) ) : array();
		$submitted_enabled = isset( $_POST['fgr_fe_enabled'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['fgr_fe_enabled'] ) ) : array();
		$enabled_set       = array_flip( $submitted_enabled );

		$valid_keys = array_keys( self::get_all_columns() );
		$order      = array_values( array_intersect( $submitted_order, $valid_keys ) );
		foreach ( $valid_keys as $key ) {
			if ( ! in_array( $key, $order, true ) ) {
				$order[] = $key;
			}
		}

		$layout = array();
		foreach ( $order as $key ) {
			$layout[] = array(
				'key'     => $key,
				'enabled' => isset( $enabled_set[ $key ] ),
			);
		}

		if ( ! empty( $_POST['fgr_fe_move'] ) ) {
			$move                    = sanitize_text_field( wp_unslash( $_POST['fgr_fe_move'] ) );
			list( $direction, $key ) = array_pad( explode( ':', $move, 2 ), 2, '' );

			$index = null;
			foreach ( $layout as $i => $item ) {
				if ( $item['key'] === $key ) {
					$index = $i;
					break;
				}
			}

			if ( null !== $index ) {
				$swap_with = ( 'up' === $direction ) ? $index - 1 : $index + 1;
				if ( $swap_with >= 0 && $swap_with < count( $layout ) ) {
					$tmp                = $layout[ $swap_with ];
					$layout[ $swap_with ] = $layout[ $index ];
					$layout[ $index ]     = $tmp;
				}
			}
		}

		update_option( self::OPTION_KEY, $layout );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung für diese Seite.', 'fgr-fooevents-export' ) );
		}

		if ( isset( $_POST['fgr_fe_settings_nonce'] ) && check_admin_referer( 'fgr_fe_save_settings', 'fgr_fe_settings_nonce' ) ) {
			$this->save_from_request();
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Einstellungen gespeichert.', 'fgr-fooevents-export' ) . '</p></div>';
		}

		$columns = self::get_all_columns();
		$layout  = self::get_layout();

		include FGR_FE_PATH . 'includes/views/settings-page.php';
	}
}
