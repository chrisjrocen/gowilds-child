<?php
/**
 * Leave out scripts and styles the pages do not use.
 *
 * WP Travel Engine and its Elementor add-on load their booking, date picker, upload and carousel files on
 * every page. This site has no booking, and its trip pages and cards are drawn by this theme, so those
 * files are only needed on the trip listing (/safaris/ and the destination and trip type archives),
 * where WTE's own filters run.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles to drop: 'everywhere' or 'not_on_listings'.
 */
function gowilds_child_unused_assets() {
	return array(
		'styles'  => array(
			// Font Awesome: the site's icons are all Line Awesome. The rest is WTE's booking, date picker, upload and carousel styling.
			'everywhere'      => array( 'fontawesome', 'font-awesome-5-all', 'font-awesome-4-shim', 'mcustomscrollbar', 'wte-dropzone', 'owl-carousel', 'wptravelengine-coupon-banner', 'wte-blocks-index', 'wte-elementor-swiper-styles', 'wte-elementor-widget-styles', 'wte-fpickr', 'single-trip', 'style-trip-booking-modal' ),
			'not_on_listings' => array( 'wp-travel-engine' ),
		),
		'scripts' => array(
			'everywhere'      => array( 'ajax-form', 'font-awesome-4-shim', 'wte-dropzone', 'owl-carousel', 'wte-fpickr-lib', 'wte-fpickr', 'wte-offcanvas', 'wpte-animation', 'wte-redux', 'single-trip', 'trip-booking-modal' ),
			'not_on_listings' => array( 'wp-travel-engine', 'wptravelengine-trip-search-widgets-dropdown', 'wptravelengine-trip-search-widgets-slider' ),
		),
	);
}

function gowilds_child_is_unused_asset( $handle, $type ) {
	if ( is_admin() || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) ) {
		return false;
	}
	$assets = gowilds_child_unused_assets();
	if ( in_array( $handle, $assets[ $type ]['everywhere'], true ) ) {
		return true;
	}
	return in_array( $handle, $assets[ $type ]['not_on_listings'], true ) && ! gowilds_child_is_trip_listing();
}

// Some of these files are enqueued while the page is already rendering, so the tags are dropped, not just dequeued.
function gowilds_child_drop_style_tag( $tag, $handle ) {
	return gowilds_child_is_unused_asset( $handle, 'styles' ) ? '' : $tag;
}
add_filter( 'style_loader_tag', 'gowilds_child_drop_style_tag', 20, 2 );

function gowilds_child_drop_script_tag( $tag, $handle ) {
	return gowilds_child_is_unused_asset( $handle, 'scripts' ) ? '' : $tag;
}
add_filter( 'script_loader_tag', 'gowilds_child_drop_script_tag', 20, 2 );

/**
 * Dequeue as well, late, so the libraries these files pull in (React and friends for the booking modal)
 * are not loaded either.
 */
function gowilds_child_dequeue_unused() {
	global $wp_scripts, $wp_styles;
	foreach ( array( 'scripts' => $wp_scripts, 'styles' => $wp_styles ) as $type => $registry ) {
		if ( ! $registry ) {
			continue;
		}
		foreach ( (array) $registry->queue as $handle ) {
			if ( gowilds_child_is_unused_asset( $handle, $type ) ) {
				'scripts' === $type ? wp_dequeue_script( $handle ) : wp_dequeue_style( $handle );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'gowilds_child_dequeue_unused', 100000 );
add_action( 'wp_footer', 'gowilds_child_dequeue_unused', 1 );

/**
 * Dashicons are an admin icon font: visitors do not need them.
 */
function gowilds_child_no_dashicons() {
	if ( ! is_user_logged_in() ) {
		wp_dequeue_style( 'dashicons' );
	}
}
add_action( 'wp_enqueue_scripts', 'gowilds_child_no_dashicons', 10000 );

// The emoji script converts emoji to images for very old browsers.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
