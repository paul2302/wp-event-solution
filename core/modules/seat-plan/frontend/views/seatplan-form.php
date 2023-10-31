<?php

namespace Etn\Core\Modules\Seat_Plan\Frontend\Views;

defined( 'ABSPATH' ) || die();

class Seatplan_Form {

	use \Etn\Traits\Singleton;

	/**
	 * Call js/css files
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'etn_after_single_event_details_rsvp_form', array( $this, 'seat_plan_form' ), 10 );
	}

	/**
	 * Enqueue scripts.
	 */
	public function seat_plan_form() {
		$is_seat_plan_enable = get_post_meta( get_the_ID() , 'seat_plan_module_enable' , true );
		if( "yes" !== $is_seat_plan_enable ){
			return false;
		}
		?>
		<form method="POST">
			<?php  wp_nonce_field('ticket_purchase_next_step_two','ticket_purchase_next_step_two'); ?>

			<div class="wrap-seat-plan-form timetics-shortcode-wrapper">
				<div id="etn-seat-plan" data-id="<?php echo intval(get_the_ID()); ?>"></div>
				<input name="event_name" type="hidden" value="<?php echo esc_html(get_the_title(get_the_ID())); ?>"/>
			</div>
		</form>
		<?php
	}

}
