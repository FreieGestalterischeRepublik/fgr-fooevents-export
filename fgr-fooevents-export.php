<?php
/**
 * Plugin Name: FGR FooEventsExport
 * Description: Admin-Übersicht aller FooEvents-Kochkurse, gruppiert nach Kurs-Datum, mit Filtern und CSV-Export.
 * Version: 1.3.1
 * Author: FGR
 * Text Domain: fgr-fooevents-export
 * Requires Plugins: woocommerce
 * Update URI: https://github.com/FreieGestalterischeRepublik/fgr-fooevents-export
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FGR_FE_VERSION', '1.3.1' );
define( 'FGR_FE_PATH', plugin_dir_path( __FILE__ ) );
define( 'FGR_FE_URL', plugin_dir_url( __FILE__ ) );
define( 'FGR_FE_GITHUB_REPO', 'https://github.com/FreieGestalterischeRepublik/fgr-fooevents-export' );

require_once FGR_FE_PATH . 'vendor/plugin-update-checker/plugin-update-checker.php';
require_once FGR_FE_PATH . 'includes/class-fgr-fe-data.php';
require_once FGR_FE_PATH . 'includes/class-fgr-fe-admin.php';
require_once FGR_FE_PATH . 'includes/class-fgr-fe-xlsx-writer.php';
require_once FGR_FE_PATH . 'includes/class-fgr-fe-export.php';

use YahnisElsts\PluginUpdateChecker\v5p7\PucFactory;

add_action(
	'init',
	function () {
		// Prüft neue Git-Tags (z. B. "v1.0.1") im Repo und bietet sie im normalen
		// WordPress-Plugin-Update-Bildschirm an. Kein manuelles Hochladen nötig.
		PucFactory::buildUpdateChecker( FGR_FE_GITHUB_REPO, __FILE__, 'fgr-fooevents-export' );
	}
);

/**
 * Plugin ohne WooCommerce/FooEvents ist wirkungslos statt fatal.
 */
function fgr_fe_dependencies_missing() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		return true;
	}
	if ( ! is_plugin_active( 'fooevents/fooevents.php' ) ) {
		return true;
	}
	return false;
}

function fgr_fe_admin_notice_missing_deps() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'FGR FooEventsExport benötigt WooCommerce und FooEvents. Bitte beide Plugins aktivieren.', 'fgr-fooevents-export' ); ?></p>
	</div>
	<?php
}

add_action(
	'plugins_loaded',
	function () {
		if ( fgr_fe_dependencies_missing() ) {
			add_action( 'admin_notices', 'fgr_fe_admin_notice_missing_deps' );
			return;
		}

		new FGR_FE_Admin();
		new FGR_FE_Export();
	}
);
