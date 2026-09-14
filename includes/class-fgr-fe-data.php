<?php
/**
 * Datenzugriff: Kurs-Produkte, Tickets und Bestellungen einlesen und gruppieren.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FGR_FE_Data {

	/**
	 * Alle WooCommerce-Produkte mit gesetztem FooEvents-Kursdatum, aufsteigend sortiert.
	 *
	 * @return array product_id => stdClass
	 */
	public static function get_event_products() {
		$query = new WP_Query(
			array(
				'post_type'              => 'product',
				'post_status'            => array( 'publish', 'private' ),
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'orderby'                => 'meta_value',
				'meta_key'               => 'WooCommerceEventsDateMySQLFormat',
				'order'                  => 'ASC',
				'meta_query'             => array(
					array(
						'key'     => 'WooCommerceEventsDateMySQLFormat',
						'value'   => array( '', '1970-01-01 00:00:00' ),
						'compare' => 'NOT IN',
					),
				),
			)
		);

		$products = array();

		foreach ( $query->posts as $product_id ) {
			$date_mysql = get_post_meta( $product_id, 'WooCommerceEventsDateMySQLFormat', true );
			$timestamp  = $date_mysql ? strtotime( $date_mysql ) : false;

			if ( ! $timestamp ) {
				continue;
			}

			$products[ $product_id ] = (object) array(
				'id'           => $product_id,
				'name'         => get_the_title( $product_id ),
				'sku'          => get_post_meta( $product_id, '_sku', true ),
				'timestamp'    => $timestamp,
				'date_display' => date_i18n( 'd.m.Y', $timestamp ),
				'time_display' => date_i18n( 'H:i', $timestamp ),
				'month_key'    => date_i18n( 'Y-m', $timestamp ),
				'month_label'  => date_i18n( 'F Y', $timestamp ),
			);
		}

		return $products;
	}

	/**
	 * Monats-Dropdown-Optionen aus einer Produktliste ableiten.
	 */
	public static function get_month_options( array $products ) {
		$months = array();
		foreach ( $products as $product ) {
			$months[ $product->month_key ] = $product->month_label;
		}
		ksort( $months );
		return $months;
	}

	/**
	 * Kurs-Dropdown-Optionen aus einer Produktliste ableiten.
	 */
	public static function get_course_options( array $products ) {
		$courses = array();
		foreach ( $products as $product ) {
			$label = $product->name;
			if ( $product->sku ) {
				$label .= ' (' . $product->sku . ')';
			}
			$courses[ $product->id ] = $product->date_display . ' – ' . $label;
		}
		return $courses;
	}

	private static function filter_products( array $products, array $filters ) {
		return array_filter(
			$products,
			function ( $product ) use ( $filters ) {
				if ( ! empty( $filters['month'] ) && $product->month_key !== $filters['month'] ) {
					return false;
				}
				if ( ! empty( $filters['course'] ) && (int) $product->id !== (int) $filters['course'] ) {
					return false;
				}
				return true;
			}
		);
	}

	/**
	 * Tickets + zugehörige Bestellungen gebündelt laden (kein N+1: eine Ticket-Query,
	 * ein Meta-Cache-Priming, eine Bestell-Query für alle betroffenen Order-IDs).
	 *
	 * @return array product_id => array von Zeilen-Objekten
	 */
	private static function get_rows_by_product( array $product_ids ) {
		if ( empty( $product_ids ) ) {
			return array();
		}

		$ticket_query = new WP_Query(
			array(
				'post_type'      => 'event_magic_tickets',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => 'WooCommerceEventsProductID',
						'value'   => $product_ids,
						'compare' => 'IN',
					),
				),
			)
		);

		$ticket_ids = $ticket_query->posts;
		if ( empty( $ticket_ids ) ) {
			return array();
		}

		// Primt den Meta-Cache für alle Tickets in einer Query statt pro Ticket einzeln.
		update_meta_cache( 'post', $ticket_ids );

		$tickets_by_order = array();
		$order_ids        = array();

		foreach ( $ticket_ids as $ticket_id ) {
			$order_id   = (int) get_post_meta( $ticket_id, 'WooCommerceEventsOrderID', true );
			$product_id = (int) get_post_meta( $ticket_id, 'WooCommerceEventsProductID', true );

			// Attendee-Felder sind in diesem Shop bislang ungenutzt (Erfassung deaktiviert),
			// deshalb Fallback auf die Käufer-Daten, die FooEvents immer speichert.
			$name = trim(
				get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeName', true ) . ' ' .
				get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeLastName', true )
			);
			if ( '' === $name ) {
				$name = trim(
					get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserFirstName', true ) . ' ' .
					get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserLastName', true )
				);
			}

			$email = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeEmail', true );
			if ( '' === $email ) {
				$email = get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserEmail', true );
			}

			$phone = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeTelephone', true );
			if ( '' === $phone ) {
				$phone = get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserPhone', true );
			}

			$row = (object) array(
				'ticket_id'  => $ticket_id,
				'product_id' => $product_id,
				'order_id'   => $order_id,
				'name'       => $name,
				'email'      => $email,
				'phone'      => $phone,
			);

			$tickets_by_order[ $order_id ][] = $row;
			if ( $order_id ) {
				$order_ids[ $order_id ] = $order_id;
			}
		}

		$orders = array();
		if ( ! empty( $order_ids ) ) {
			// HPOS-sicher: Bestellungen ausschließlich über wc_get_orders() laden,
			// gebündelt für alle betroffenen IDs statt einzeln pro Ticket.
			$order_objects = wc_get_orders(
				array(
					'id'    => array_values( $order_ids ),
					'limit' => -1,
				)
			);
			foreach ( $order_objects as $order ) {
				$orders[ $order->get_id() ] = $order;
			}
		}

		$rows_by_product = array();

		foreach ( $tickets_by_order as $order_id => $rows ) {
			$order = isset( $orders[ $order_id ] ) ? $orders[ $order_id ] : null;

			foreach ( $rows as $row ) {
				if ( $order ) {
					$row->order_number  = $order->get_order_number();
					$row->payment_label = wc_get_order_status_name( $order->get_status() );
				} else {
					$row->order_number  = '';
					$row->payment_label = __( 'Bestellung nicht gefunden', 'fgr-fooevents-export' );
				}

				$rows_by_product[ $row->product_id ][] = $row;
			}
		}

		return $rows_by_product;
	}

	/**
	 * Gruppierte Buchungen für die Admin-Übersicht, aufsteigend nach Kurs-Datum.
	 */
	public static function get_grouped_bookings( array $filters = array() ) {
		$products = self::filter_products( self::get_event_products(), $filters );
		if ( empty( $products ) ) {
			return array();
		}

		$rows_by_product = self::get_rows_by_product( array_keys( $products ) );

		$groups = array();
		foreach ( $products as $product_id => $product ) {
			$rows       = isset( $rows_by_product[ $product_id ] ) ? $rows_by_product[ $product_id ] : array();
			$groups[]   = (object) array(
				'product'      => $product,
				'participants' => $rows,
				'count'        => count( $rows ),
			);
		}

		usort(
			$groups,
			function ( $a, $b ) {
				return $a->product->timestamp <=> $b->product->timestamp;
			}
		);

		return $groups;
	}

	/**
	 * Flache, exportierbare Zeilenliste (eine Zeile pro Teilnehmer), nach Datum sortiert.
	 */
	public static function get_flat_rows( array $filters = array() ) {
		$rows = array();

		foreach ( self::get_grouped_bookings( $filters ) as $group ) {
			foreach ( $group->participants as $participant ) {
				$rows[] = (object) array(
					'date_display'  => $group->product->date_display,
					'time_display'  => $group->product->time_display,
					'course_name'   => $group->product->name,
					'sku'           => $group->product->sku,
					'name'          => $participant->name,
					'email'         => $participant->email,
					'phone'         => $participant->phone,
					'order_number'  => $participant->order_number,
					'payment_label' => $participant->payment_label,
				);
			}
		}

		return $rows;
	}
}
