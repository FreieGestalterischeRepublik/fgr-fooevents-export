<?php
/**
 * Admin-Seite: Kurs-Übersicht mit Filtern und aufklappbaren Buchungsgruppen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FGR_FE_Admin {

	const PAGE_SLUG = 'fgr-fooevents-export';

	/**
	 * Erlaubte Werte für "Kurse pro Seite". 0 bedeutet "Alle".
	 */
	const PER_PAGE_OPTIONS = array( 20, 50, 100, 200, 0 );
	const DEFAULT_PER_PAGE = 20;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Kurs-Übersicht', 'fgr-fooevents-export' ),
			__( 'Kurs-Übersicht', 'fgr-fooevents-export' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	public function enqueue_assets( $hook_suffix ) {
		if ( strpos( $hook_suffix, self::PAGE_SLUG ) === false ) {
			return;
		}
		wp_enqueue_style( 'fgr-fe-admin', FGR_FE_URL . 'assets/admin.css', array(), FGR_FE_VERSION );
	}

	/**
	 * Liest und säubert die aktuellen Filter aus der Query-String.
	 */
	public static function get_current_filters() {
		return array(
			'month'  => isset( $_GET['fgr_fe_month'] ) ? sanitize_text_field( wp_unslash( $_GET['fgr_fe_month'] ) ) : '',
			'course' => isset( $_GET['fgr_fe_course'] ) ? absint( $_GET['fgr_fe_course'] ) : 0,
		);
	}

	/**
	 * Liest die gewählte Seitengröße; fällt auf DEFAULT_PER_PAGE zurück, falls
	 * der Wert nicht zu den erlaubten Abstufungen gehört.
	 */
	public static function get_current_per_page() {
		if ( ! isset( $_GET['fgr_fe_per_page'] ) ) {
			return self::DEFAULT_PER_PAGE;
		}
		$value = absint( $_GET['fgr_fe_per_page'] );
		return in_array( $value, self::PER_PAGE_OPTIONS, true ) ? $value : self::DEFAULT_PER_PAGE;
	}

	private static function build_export_url( array $filters, $format ) {
		return wp_nonce_url(
			add_query_arg(
				array_merge(
					array(
						'action' => 'fgr_fe_export',
						'format' => $format,
					),
					array_filter( $filters )
				),
				admin_url( 'admin-post.php' )
			),
			'fgr_fe_export',
			'fgr_fe_nonce'
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung für diese Seite.', 'fgr-fooevents-export' ) );
		}

		$products = FGR_FE_Data::get_event_products();
		$months   = FGR_FE_Data::get_month_options( $products );
		$courses  = FGR_FE_Data::get_course_options( $products );
		$filters  = self::get_current_filters();

		$groups   = FGR_FE_Data::get_grouped_bookings( $filters );
		$per_page = self::get_current_per_page();

		if ( 0 === $per_page ) {
			// "Alle" gewählt: keine Aufteilung in Seiten.
			$total_pages = 1;
			$paged       = 1;
			$page_groups = $groups;
		} else {
			$total_pages = max( 1, (int) ceil( count( $groups ) / $per_page ) );
			$paged       = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
			$paged       = min( $paged, $total_pages );
			$page_groups = array_slice( $groups, ( $paged - 1 ) * $per_page, $per_page );
		}

		$export_url_xlsx = self::build_export_url( $filters, 'xlsx' );
		$export_url_csv  = self::build_export_url( $filters, 'csv' );

		include FGR_FE_PATH . 'includes/views/admin-page.php';
	}
}
