<?php
/**
 * Trip listings: the /safaris/ archive (WP Travel Engine's own filters and sort, restyled in style.css).
 * This file only adds what the plugin's settings cannot: the banner and closing sections around the
 * archive, the design's sort labels, result wording and empty state.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print a saved Elementor template by the key the build scripts gave it.
 */
function gowilds_child_saved_template( $key ) {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}
	$ids = get_posts(
		array(
			'post_type'   => 'elementor_library',
			'meta_key'    => '_afoyo_build_key',
			'meta_value'  => $key,
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $ids ) {
		echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $ids[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
	}
}

function gowilds_child_is_trip_listing() {
	return is_post_type_archive( 'trip' ) || is_tax( array( 'destination', 'trip_types', 'activities', 'difficulty', 'trip_tag' ) );
}

/**
 * Heading for a destination or trip type archive: "Uganda Safaris", "Gorilla Trekking Safaris".
 */
function gowilds_child_term_safaris_title() {
	$term = single_term_title( '', false );
	/* translators: %s: destination or trip type */
	return preg_match( '/safaris?$/i', $term ) ? $term : sprintf( __( '%s Safaris', 'gowilds-child' ), $term );
}

/**
 * Browser tab title: "Safaris" for the archive (the post type is called "Trips"), and the same
 * heading as the banner on destination and trip type archives.
 */
function gowilds_child_listing_title( $parts ) {
	if ( is_post_type_archive( 'trip' ) ) {
		$parts['title'] = __( 'Safaris', 'gowilds-child' );
	} elseif ( gowilds_child_is_trip_listing() ) {
		$parts['title'] = gowilds_child_term_safaris_title();
	}
	return $parts;
}
add_filter( 'document_title_parts', 'gowilds_child_listing_title' );

/**
 * Above the archive: the banner template instead of the theme's breadcrumb bar and WTE's title.
 */
function gowilds_child_archive_top() {
	if ( ! gowilds_child_is_trip_listing() ) {
		return;
	}
	remove_all_actions( 'wp_travel_engine_breadcrumb_holder' );
	echo '<div class="afoyo-archive-top">';
	gowilds_child_saved_template( 'tpl-safaris-banner' );
	echo '</div>';
}
add_action( 'wp_travel_engine_trip_archive_outer_wrapper', 'gowilds_child_archive_top', 1 );
add_filter( 'wp_travel_engine_archive_header_block_display', '__return_false' );

/**
 * Below the archive: "Why travel with us" and the CTA band.
 */
function gowilds_child_archive_bottom() {
	if ( ! gowilds_child_is_trip_listing() ) {
		return;
	}
	echo '<div class="afoyo-archive-bottom">';
	gowilds_child_saved_template( 'tpl-why-row' );
	gowilds_child_saved_template( 'tpl-cta-band' );
	echo '</div>';
}
add_action( 'wp_travel_engine_trip_archive_outer_wrapper_close', 'gowilds_child_archive_bottom', 99 );

/**
 * Sort options as in the design. "Featured" is WTE's default order with featured trips first.
 */
function gowilds_child_sort_options() {
	return array(
		'latest'     => __( 'Featured', 'gowilds-child' ),
		'price'      => __( 'Price: low to high', 'gowilds-child' ),
		'price-desc' => __( 'Price: high to low', 'gowilds-child' ),
		'days'       => __( 'Duration: shortest first', 'gowilds-child' ),
		'days-desc'  => __( 'Duration: longest first', 'gowilds-child' ),
	);
}
add_filter( 'wp_travel_engine_archive_header_sorting_options', 'gowilds_child_sort_options' );

/**
 * Empty state when no trip matches the filters.
 */
function gowilds_child_no_results() {
	return '<div class="empty">'
		. '<span class="empty__icon" aria-hidden="true"><i class="las la-binoculars"></i></span>'
		. '<h3>' . esc_html__( 'No safaris match', 'gowilds-child' ) . '</h3>'
		. '<p>' . esc_html__( "Tell us what you want and we'll build it for you.", 'gowilds-child' ) . '</p>'
		. '<div class="empty__actions"><a class="btn btn-gold" href="' . esc_url( gowilds_child_plan_url( get_post_type_archive_link( 'trip' ) ) ) . '">' . esc_html__( 'Plan Your Safari', 'gowilds-child' ) . ' <i class="las la-arrow-right" aria-hidden="true"></i></a>'
		. '<a class="link-btn" href="' . esc_url( get_post_type_archive_link( 'trip' ) ) . '">' . esc_html__( 'Clear filters', 'gowilds-child' ) . '</a></div>'
		. '</div>';
}
add_filter( 'no_result_found_message', 'gowilds_child_no_results' );

/**
 * WTE's wording, in the site's words: "Trips" are safaris here.
 */
function gowilds_child_wte_plural_words( $translation, $single, $plural, $number, $context, $domain ) {
	// WTE writes this string two ways: "%s Trip Found" (search) and "%1$s Trip Found" (archive and filters).
	if ( 'wp-travel-engine' === $domain && in_array( $single, array( '%s Trip Found', '%1$s Trip Found' ), true ) ) {
		return 1 === (int) $number ? '%1$s safari' : '%1$s safaris';
	}
	return $translation;
}
add_filter( 'ngettext_with_context', 'gowilds_child_wte_plural_words', 10, 6 );

function gowilds_child_wte_words( $translation, $text, $domain ) {
	if ( 'wp-travel-engine' !== $domain ) {
		return $translation;
	}
	$words = array(
		'Filter By'     => 'Filter',
		'Trip Types'    => 'Trip type',
		'Apply Filters' => 'Filter',
		'Sort'          => 'Sort by',
		'Duration'      => 'Duration',
		'Price'         => 'Price (USD)',
	);
	return isset( $words[ $text ] ) ? $words[ $text ] : $translation;
}
add_filter( 'gettext', 'gowilds_child_wte_words', 10, 3 );

/**
 * The listing is styled from scratch in the child stylesheet (section 3.9), so WTE's own archive
 * stylesheet and the parent theme's WTE stylesheet are not loaded: they fight every rule of the design.
 */
function gowilds_child_listing_styles() {
	wp_dequeue_style( 'gowilds-booking-wte' );
	if ( gowilds_child_is_trip_listing() ) {
		wp_dequeue_style( 'wpte-trip-archive' );
	}
}
add_action( 'wp_enqueue_scripts', 'gowilds_child_listing_styles', 10000 );

/**
 * WTE enqueues its archive stylesheet while the page is already rendering, after the dequeue above:
 * drop the tag itself.
 */
function gowilds_child_drop_archive_style( $tag, $handle ) {
	return ( 'wpte-trip-archive' === $handle && gowilds_child_is_trip_listing() ) ? '' : $tag;
}
add_filter( 'style_loader_tag', 'gowilds_child_drop_archive_style', 10, 2 );

/**
 * Total number of trips, for the result count before any filter is used.
 */
function gowilds_child_listing_total() {
	if ( gowilds_child_is_trip_listing() ) {
		global $wp_query;
		printf( '<span hidden data-afoyo-total="%d"></span>', (int) $wp_query->found_posts );
	}
}
add_action( 'wp_travel_engine_trip_archive_outer_wrapper_close', 'gowilds_child_listing_total', 1 );

/**
 * "Featured first" only applies to the default order. With any other sort chosen, WTE would still
 * pin the featured trips to the top, which makes "Price: low to high" look wrong.
 */
function gowilds_child_featured_only_by_default( $settings ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
	$orderby = isset( $_REQUEST['wte_orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['wte_orderby'] ) ) : ( isset( $_REQUEST['sort'] ) ? sanitize_key( wp_unslash( $_REQUEST['sort'] ) ) : '' );
	if ( is_array( $settings ) && '' !== $orderby && 'latest' !== $orderby ) {
		$settings['show_featured_trips_on_top'] = 'no';
	}
	return $settings;
}
add_filter( 'option_wp_travel_engine_settings', 'gowilds_child_featured_only_by_default' );
