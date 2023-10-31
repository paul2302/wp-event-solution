<?php

namespace Etn\Core\Event;

use Etn\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

class Helper {

	use Singleton;

	/**
	 * Return currency symbol
	 */
	public function get_currency() {
		$symbol = '';
		if ( class_exists( 'Wpeventin_Pro' ) && class_exists( '\Etn_Pro\Core\Modules\Sells_Engine\Sells_Engine' ) ) {
			$sells_engine = \Etn_Pro\Core\Modules\Sells_Engine\Sells_Engine::instance()->check_sells_engine();
			if ( 'woocommerce' == $sells_engine ) {
				$symbol = get_woocommerce_currency_symbol();
			} else if ( 'stripe' == $sells_engine ) {
				$get_data = \Etn_Pro\Utils\Helper::retrieve_country_currency();
				$symbol   = ! empty( $get_data ) ? $get_data['currency'] : '';
			}
		} else {
			$symbol = get_woocommerce_currency_symbol();
		}

		return $symbol;
	}


	/**
	 * Return currency symbol with position
	 */
	public function currency_with_position( $price ) {

		$currency_position = 'left';
		if ( class_exists( 'WooCommerce' ) ) {
			$currency_position = get_option( 'woocommerce_currency_pos', 'left' );
		}
		$currency_symbol = $this->get_currency();

		if ( $currency_position === 'left_space' ) {
			return sprintf( '%s %s', esc_html( $currency_symbol ), $price );
		} else if ( $currency_position === 'right_space' ) {
			return sprintf( '%s %s', $price, esc_html( $currency_symbol ) );
		} else if ( $currency_position === 'right' ) {
			return sprintf( '%s%s', $price, esc_html( $currency_symbol ) );
		} else {
			return sprintf( '%s%s', esc_html( $currency_symbol ), $price );
		}
	}

	/**
	 * Add recurring tag
	 */
	public function recurring_tag( $data ) {
		if ( ( is_array( $data ) && count( $data ) > 0 ) ) {
			foreach ( $data as $index => $post ) {
				$post_id             = $post->ID;
				$is_recurring_parent = \Etn\Utils\Helper::get_child_events( $post_id );
				if ( $is_recurring_parent ) {
					$post->etn_recurring = true;
				}
			}
		}

		return $data;
	}

	public function get_event_location( $event_id ) {
		$location      = '';
		$location_type = get_post_meta( $event_id, 'etn_event_location_type', true );
		if ( $location_type == 'existing_location' ) {
			$location = get_post_meta( $event_id, 'etn_event_location', true );
		} else {
			$location_arr = maybe_unserialize( get_post_meta( $event_id, 'etn_event_location_list', true ) );

			if ( ! empty( $location_arr ) && is_array( $location_arr ) ) {
				$location_names = [];

				foreach ( $location_arr as $index => $location_slug ) {
					$location_details = get_term_by( 'slug', $location_slug, 'etn_location' );
					$location_names[] = $location_details->name;
				}

				$location = join( ', ', $location_names );
			}
		}

		return $location;
	}

	/**
	 * Get Attendee for a event
	 */
	public function attendee_by_events( $query ) {
		if ( ( is_admin()
		       && ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "etn-attendee" ) )
		     && ( ! empty( $_GET['event_id'] ) )
		     && $query->is_search ) {
			$meta_query = [
				'relation' => 'AND',
				[
					'key'     => 'etn_event_id',
					'value'   => $_GET['event_id'],
					'compare' => '=',
				],
			];
			$query->set( 'meta_query', $meta_query );
		}

		return $query;
	}

	/**
	 *  Global Date format
	 */
	public function etn_date_format() {
		$settings        = \Etn\Utils\Helper::get_settings();
		$date_options    = \Etn\Utils\Helper::get_date_formats();
		$etn_date_format = ! empty( $settings["date_format"] ) ? $date_options[ $settings["date_format"] ] : get_option( "date_format" );

		return $etn_date_format;
	}

	/**
	 *  Get Tickets by Events
	 */
	public function ticket_by_events( $event_id = null ) {
		$ticket_variations = array();
		$get_tickets       = array( 'tickets' => array(), 'ticket_price' => array() );
		if ( ! is_null( $event_id ) ) {
			$ticket_variations = get_post_meta( $event_id, 'etn_ticket_variations', true );
		}

		if ( ! empty( $ticket_variations ) ) {
			foreach ( $ticket_variations as $key => $value ) {
				$get_tickets['tickets'][ $value['etn_ticket_name'] ] = $value['etn_ticket_name'];
				$get_tickets['ticket_price'][ $key ]                 = $value['etn_ticket_price'];
			}
		}

		return $get_tickets;
	}

	/**
	 * Get Upcoming event only
	 */
	public function get_upcoming_event( $event_id ) {
		$result           = true;
		$event_start_date = get_post_meta( $event_id, 'etn_start_date', true );

		if ( date( 'Y-m-d', strtotime( $event_start_date ) ) < date( 'Y-m-d' ) ) {
			$result = false;
		}

		return $result;
	}

	/**
	 * Get time zone
	 *
	 * @param [type] $timezone
	 * @return void
	 */
	public function get_timezone_numeric_value( $timezone ){
		if ( str_contains( $timezone, 'UTC+' ) || str_contains( $timezone, 'UTC-' ) ) {
			$timezone_offset = str_replace('UTC', '', $timezone);
		} else {
			date_default_timezone_set( $timezone );
			$timezone_offset = date( 'Z' ) / 3600;
		}

		return $timezone_offset;
	}

	/**
	 * Get formatted seat-id
	 *
	 * @return void
	 */
	public function event_seat_id( $etn_booked_seats ) {
		$booked_id = array();
		if ( !empty($etn_booked_seats) ) {
			foreach ($etn_booked_seats as $key => $item) {
				if (!empty($item)) {
					$item =  explode(",",$item);
					foreach ($item as $i => $data) {
						$data = str_replace($key."-","", $data );
						array_push( $booked_id , $data );
					}
				}
			}
		}

		return $booked_id;
	}

	/**
	 * get Order event id
	 */
	public function order_event_id($item) {
		$item_data        = $item->get_data();
		return !is_null( $item_data['product_id'] ) ? $item_data['product_id'] : "";
	}
	
}


