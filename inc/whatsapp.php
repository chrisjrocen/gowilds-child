<?php
/**
 * Floating WhatsApp button on every page (the WhatsApp plugin stays off).
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

function gowilds_child_whatsapp_float() {
	printf(
		'<a class="wa-float" href="%s" target="_blank" rel="noopener" aria-label="%s"><i class="lab la-whatsapp" aria-hidden="true"></i></a>',
		esc_url( AFOYO_WHATSAPP_URL ),
		esc_attr__( 'Chat on WhatsApp', 'gowilds-child' )
	);
}
add_action( 'wp_footer', 'gowilds_child_whatsapp_float', 5 );
