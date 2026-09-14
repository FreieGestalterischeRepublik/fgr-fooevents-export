<?php
/**
 * View: Export-Einstellungen.
 *
 * Erwartet: $enabled (Array der aktuell aktivierten Feld-Keys)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap fgr-fe-wrap">
	<h1><?php esc_html_e( 'Export-Einstellungen', 'fgr-fooevents-export' ); ?></h1>

	<p>
		<?php esc_html_e( 'Die folgenden Standardspalten sind im Export immer enthalten: Kurs-Datum, Uhrzeit, Kursname, SKU, Teilnehmer-Name, E-Mail, Telefon, Bestellnummer, Zahlungsstatus.', 'fgr-fooevents-export' ); ?>
	</p>
	<p>
		<?php esc_html_e( 'Zusätzlich lassen sich folgende Spalten dazuschalten. Nur aktivierte Spalten werden exportiert (CSV und Excel).', 'fgr-fooevents-export' ); ?>
	</p>

	<form method="post">
		<?php wp_nonce_field( 'fgr_fe_save_settings', 'fgr_fe_settings_nonce' ); ?>

		<table class="widefat striped fgr-fe-settings-table">
			<thead>
				<tr>
					<th style="width: 40px;"></th>
					<th><?php esc_html_e( 'Spalte', 'fgr-fooevents-export' ); ?></th>
					<th><?php esc_html_e( 'Beschreibung', 'fgr-fooevents-export' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( FGR_FE_Settings::get_field_definitions() as $key => $field ) : ?>
					<tr>
						<td>
							<input
								type="checkbox"
								name="fgr_fe_fields[]"
								id="fgr_fe_field_<?php echo esc_attr( $key ); ?>"
								value="<?php echo esc_attr( $key ); ?>"
								<?php checked( in_array( $key, $enabled, true ) ); ?>
							>
						</td>
						<td>
							<label for="fgr_fe_field_<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $field['label'] ); ?></strong></label>
						</td>
						<td><?php echo esc_html( $field['description'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Einstellungen speichern', 'fgr-fooevents-export' ); ?></button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . FGR_FE_Admin::PAGE_SLUG ) ); ?>" class="button">
				<?php esc_html_e( 'Zurück zur Übersicht', 'fgr-fooevents-export' ); ?>
			</a>
		</p>
	</form>
</div>
