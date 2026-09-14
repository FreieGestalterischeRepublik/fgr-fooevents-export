<?php
/**
 * View: Export-Einstellungen.
 *
 * Erwartet: $columns (alle Spalten-Definitionen, key => Definition),
 * $layout (aktuelle Reihenfolge + Ein/Aus, Array von ['key','enabled'])
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total = count( $layout );
?>
<div class="wrap fgr-fe-wrap">
	<h1><?php esc_html_e( 'Export-Einstellungen', 'fgr-fooevents-export' ); ?></h1>

	<p>
		<?php esc_html_e( 'Legt fest, welche Spalten der Export (CSV und Excel) enthält und in welcher Reihenfolge. Häkchen setzen/entfernen zum An-/Abschalten, mit den Pfeilen die Reihenfolge ändern. Ohne weitere Änderungen bleibt der Export immer gleich aufgebaut.', 'fgr-fooevents-export' ); ?>
	</p>

	<form method="post">
		<?php wp_nonce_field( 'fgr_fe_save_settings', 'fgr_fe_settings_nonce' ); ?>

		<table class="widefat striped fgr-fe-settings-table">
			<thead>
				<tr>
					<th style="width: 40px;"></th>
					<th style="width: 40px;"><?php esc_html_e( 'Reihenfolge', 'fgr-fooevents-export' ); ?></th>
					<th><?php esc_html_e( 'Spalte', 'fgr-fooevents-export' ); ?></th>
					<th><?php esc_html_e( 'Beschreibung', 'fgr-fooevents-export' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $layout as $position => $item ) : ?>
					<?php
					$key   = $item['key'];
					$field = isset( $columns[ $key ] ) ? $columns[ $key ] : null;
					if ( ! $field ) {
						continue;
					}
					?>
					<tr>
						<td>
							<input type="hidden" name="fgr_fe_order[]" value="<?php echo esc_attr( $key ); ?>">
							<input
								type="checkbox"
								name="fgr_fe_enabled[]"
								id="fgr_fe_field_<?php echo esc_attr( $key ); ?>"
								value="<?php echo esc_attr( $key ); ?>"
								<?php checked( $item['enabled'] ); ?>
							>
						</td>
						<td class="fgr-fe-order-buttons">
							<button
								type="submit"
								name="fgr_fe_move"
								value="up:<?php echo esc_attr( $key ); ?>"
								class="button button-small"
								<?php disabled( 0 === $position ); ?>
								aria-label="<?php esc_attr_e( 'Nach oben verschieben', 'fgr-fooevents-export' ); ?>"
							>&uarr;</button>
							<button
								type="submit"
								name="fgr_fe_move"
								value="down:<?php echo esc_attr( $key ); ?>"
								class="button button-small"
								<?php disabled( $position === $total - 1 ); ?>
								aria-label="<?php esc_attr_e( 'Nach unten verschieben', 'fgr-fooevents-export' ); ?>"
							>&darr;</button>
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
