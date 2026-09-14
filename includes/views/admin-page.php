<?php
/**
 * View: Kurs-Übersicht.
 *
 * Erwartet folgende Variablen aus FGR_FE_Admin::render_page():
 * $products, $months, $courses, $filters, $groups, $page_groups, $paged, $total_pages, $per_page, $export_url
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap fgr-fe-wrap">
	<h1><?php esc_html_e( 'Kurs-Übersicht', 'fgr-fooevents-export' ); ?></h1>

	<form method="get" class="fgr-fe-filters">
		<input type="hidden" name="page" value="<?php echo esc_attr( FGR_FE_Admin::PAGE_SLUG ); ?>">

		<select name="fgr_fe_month">
			<option value=""><?php esc_html_e( 'Alle Monate', 'fgr-fooevents-export' ); ?></option>
			<?php foreach ( $months as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filters['month'], $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<select name="fgr_fe_course">
			<option value=""><?php esc_html_e( 'Alle Kurse', 'fgr-fooevents-export' ); ?></option>
			<?php foreach ( $courses as $product_id => $label ) : ?>
				<option value="<?php echo esc_attr( $product_id ); ?>" <?php selected( $filters['course'], $product_id ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<select name="fgr_fe_per_page">
			<?php foreach ( FGR_FE_Admin::PER_PAGE_OPTIONS as $option ) : ?>
				<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $per_page, $option ); ?>>
					<?php
					echo 0 === $option
						? esc_html__( 'Alle anzeigen', 'fgr-fooevents-export' )
						/* translators: %d: Anzahl der Kurse pro Seite */
						: esc_html( sprintf( __( '%d pro Seite', 'fgr-fooevents-export' ), $option ) );
					?>
				</option>
			<?php endforeach; ?>
		</select>

		<button type="submit" class="button"><?php esc_html_e( 'Filtern', 'fgr-fooevents-export' ); ?></button>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . FGR_FE_Admin::PAGE_SLUG ) ); ?>" class="button">
			<?php esc_html_e( 'Zurücksetzen', 'fgr-fooevents-export' ); ?>
		</a>
		<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary">
			<?php esc_html_e( 'Als CSV exportieren', 'fgr-fooevents-export' ); ?>
		</a>
	</form>

	<?php if ( empty( $groups ) ) : ?>

		<p><?php esc_html_e( 'Keine Kurse mit den gewählten Filtern gefunden.', 'fgr-fooevents-export' ); ?></p>

	<?php else : ?>

		<p class="description">
			<?php
			printf(
				/* translators: %d: Anzahl der gefundenen Kurse */
				esc_html__( '%d Kurse gefunden.', 'fgr-fooevents-export' ),
				count( $groups )
			);
			?>
		</p>

		<div class="fgr-fe-groups">
			<?php foreach ( $page_groups as $group ) : ?>
				<details class="fgr-fe-group">
					<summary>
						<strong><?php echo esc_html( $group->product->date_display ); ?></strong>
						– <?php echo esc_html( $group->product->name ); ?>
						– <?php echo esc_html( $group->count ); ?> <?php esc_html_e( 'Teilnehmende', 'fgr-fooevents-export' ); ?>
					</summary>

					<table class="widefat striped fgr-fe-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Teilnehmer-Name', 'fgr-fooevents-export' ); ?></th>
								<th><?php esc_html_e( 'Kontakt', 'fgr-fooevents-export' ); ?></th>
								<th><?php esc_html_e( 'Bestellnummer', 'fgr-fooevents-export' ); ?></th>
								<th><?php esc_html_e( 'Zahlungsstatus', 'fgr-fooevents-export' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $group->participants ) ) : ?>
								<tr>
									<td colspan="4"><?php esc_html_e( 'Keine Buchungen.', 'fgr-fooevents-export' ); ?></td>
								</tr>
							<?php else : ?>
								<?php foreach ( $group->participants as $participant ) : ?>
									<tr>
										<td><?php echo esc_html( $participant->name ); ?></td>
										<td>
											<?php echo esc_html( $participant->email ); ?>
											<?php if ( $participant->phone ) : ?>
												<br><?php echo esc_html( $participant->phone ); ?>
											<?php endif; ?>
										</td>
										<td><?php echo esc_html( $participant->order_number ); ?></td>
										<td><?php echo esc_html( $participant->payment_label ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</details>
			<?php endforeach; ?>
		</div>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'base'    => add_query_arg( 'paged', '%#%' ),
								'format'  => '',
								'current' => $paged,
								'total'   => $total_pages,
							)
						)
					);
					?>
				</div>
			</div>
		<?php endif; ?>

	<?php endif; ?>
</div>
