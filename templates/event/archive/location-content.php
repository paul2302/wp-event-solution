<?php
defined( 'ABSPATH' ) || die();

$etn_event_location = get_post_meta( get_the_ID(), 'etn_event_location', true );
?>
<div class="etn-event-location">
    <i class="etn-icon etn-location"></i> 
    <?php echo esc_html( $etn_event_location ); ?>
</div>
