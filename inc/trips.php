<?php
/**
 * Trips (WP Travel Engine): data for the tour page and tour card, the pricing note field, enquiry links.
 * Trip content is edited in the normal trip editor. Nothing here stores trip content.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Link to the enquiry form that remembers which page the visitor came from.
 */
function gowilds_child_plan_url( $from_url = '' ) {
	$url = home_url( '/plan-your-safari/' );
	if ( $from_url ) {
		$path = wp_parse_url( $from_url, PHP_URL_PATH );
		// Kept readable (/plan-your-safari/?from=/safaris/<slug>/): the path only holds letters, digits, dashes and slashes.
		$url .= '?from=' . preg_replace( '#[^A-Za-z0-9/_.~-]#', '', (string) $path );
	}
	return $url;
}

/**
 * Everything the tour page and tour card show for one trip.
 */
function gowilds_child_trip( $trip_id ) {
	$settings = (array) get_post_meta( $trip_id, 'wp_travel_engine_setting', true );
	$days     = isset( $settings['trip_duration'] ) ? (int) $settings['trip_duration'] : 0;
	$nights   = isset( $settings['trip_duration_nights'] ) ? (int) $settings['trip_duration_nights'] : 0;
	$price    = (float) get_post_meta( $trip_id, 'wp_travel_engine_setting_trip_price', true );

	$destinations = wp_get_object_terms( $trip_id, 'destination', array( 'orderby' => 'term_id' ) );
	$types        = wp_get_object_terms( $trip_id, 'trip_types', array( 'orderby' => 'term_id' ) );

	$itinerary = array();
	$titles    = isset( $settings['itinerary']['itinerary_title'] ) ? (array) $settings['itinerary']['itinerary_title'] : array();
	foreach ( $titles as $key => $title ) {
		$itinerary[] = array(
			'label'   => isset( $settings['itinerary']['itinerary_days_label'][ $key ] ) ? $settings['itinerary']['itinerary_days_label'][ $key ] : '',
			'title'   => $title,
			'content' => isset( $settings['itinerary']['itinerary_content'][ $key ] ) ? $settings['itinerary']['itinerary_content'][ $key ] : '',
		);
	}

	$faqs = array();
	foreach ( isset( $settings['faq']['faq_title'] ) ? (array) $settings['faq']['faq_title'] : array() as $key => $question ) {
		if ( '' !== trim( (string) $question ) ) {
			$faqs[] = array( 'question' => $question, 'answer' => isset( $settings['faq']['faq_content'][ $key ] ) ? $settings['faq']['faq_content'][ $key ] : '' );
		}
	}

	$lines = function ( $text ) {
		return array_values( array_filter( array_map( 'trim', explode( "\n", (string) $text ) ), 'strlen' ) );
	};

	$photos  = array();
	$gallery = (array) get_post_meta( $trip_id, 'wpte_gallery_id', true );
	unset( $gallery['enable'] );
	$ids = array_unique( array_filter( array_merge( array( (int) get_post_thumbnail_id( $trip_id ) ), array_map( 'intval', $gallery ) ) ) );
	foreach ( $ids as $id ) {
		$full = wp_get_attachment_image_src( $id, 'full' );
		if ( $full ) {
			$alt      = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
			$photos[] = array( 'id' => $id, 'src' => $full[0], 'alt' => '' !== $alt ? $alt : get_the_title( $trip_id ) );
		}
	}

	$map = isset( $settings['map'] ) ? (array) $settings['map'] : array();

	return array(
		'id'           => $trip_id,
		'title'        => get_the_title( $trip_id ),
		'url'          => get_permalink( $trip_id ),
		'days'         => $days,
		'nights'       => $nights,
		'duration'     => $days ? sprintf(
			/* translators: 1: days, 2: nights */
			_n( '%1$d Day', '%1$d Days', $days, 'gowilds-child' ) . ( $nights ? ' / ' . _n( '%2$d Night', '%2$d Nights', $nights, 'gowilds-child' ) : '' ),
			$days,
			$nights
		) : '',
		'price'        => $price,
		'price_text'   => $price > 0 ? 'USD ' . number_format( $price ) : '',
		'note'         => trim( (string) get_post_meta( $trip_id, 'afoyo_pricing_note', true ) ),
		'destinations' => is_wp_error( $destinations ) ? array() : $destinations,
		'types'        => is_wp_error( $types ) ? array() : $types,
		'overview'     => isset( $settings['tab_content']['1_wpeditor'] ) ? $settings['tab_content']['1_wpeditor'] : '',
		'highlights'   => array_values( array_filter( wp_list_pluck( isset( $settings['trip_highlights'] ) ? (array) $settings['trip_highlights'] : array(), 'highlight_text' ), 'strlen' ) ),
		'itinerary'    => $itinerary,
		'includes'     => $lines( isset( $settings['cost']['cost_includes'] ) ? $settings['cost']['cost_includes'] : '' ),
		'excludes'     => $lines( isset( $settings['cost']['cost_excludes'] ) ? $settings['cost']['cost_excludes'] : '' ),
		'map_image'    => ! empty( $map['image_url'] ) ? (int) $map['image_url'] : 0,
		'map_iframe'   => ! empty( $map['iframe'] ) ? $map['iframe'] : '',
		'faqs'         => $faqs,
		'photos'       => $photos,
		'plan_url'     => gowilds_child_plan_url( get_permalink( $trip_id ) ),
	);
}

/**
 * A day's text, with its "Accommodation:" and "Meal plan:" lines pulled out.
 * Editors write the two lines as ordinary paragraphs at the end of the day's text.
 *
 * @return array { html, accommodation, meal }
 */
function gowilds_child_day_parts( $content ) {
	$out = array( 'html' => $content, 'accommodation' => '', 'meal' => '' );
	foreach ( array( 'accommodation' => 'Accommodation', 'meal' => 'Meal\s*plan' ) as $key => $label ) {
		$pattern = '/<p[^>]*>\s*(?:<(?:strong|b)>)?\s*' . $label . '\s*:?\s*(?:<\/(?:strong|b)>)?\s*:?\s*(.*?)<\/p>/is';
		if ( preg_match( $pattern, $out['html'], $m ) ) {
			$out[ $key ] = trim( wp_strip_all_tags( $m[1] ) );
			$out['html'] = str_replace( $m[0], '', $out['html'] );
		}
	}
	$out['html'] = trim( $out['html'] );
	return $out;
}

/**
 * Tour card, as in the design. Used by every listing.
 */
function gowilds_child_tour_card( $trip_id ) {
	$trip = gowilds_child_trip( $trip_id );
	set_query_var( 'afoyo_trip', $trip );
	get_template_part( 'template-parts/tour-card' );
}

/**
 * Body classes: the tour page has a sticky "Book" bar on mobile.
 */
function gowilds_child_trip_body_class( $classes ) {
	if ( is_singular( 'trip' ) ) {
		$classes[] = 'has-bookbar';
		$classes[] = 'afoyo-tour';
	}
	return $classes;
}
add_filter( 'body_class', 'gowilds_child_trip_body_class' );

/**
 * "Pricing note" field on the trip editor (Meta Box plugin).
 */
function gowilds_child_trip_meta_boxes( $meta_boxes ) {
	$meta_boxes[] = array(
		'id'         => 'afoyo_price_box',
		'title'      => __( 'Afoyo: price box', 'gowilds-child' ),
		'post_types' => array( 'trip' ),
		'context'    => 'normal',
		'priority'   => 'high',
		'fields'     => array(
			array(
				'id'   => 'afoyo_pricing_note',
				'name' => __( 'Pricing note', 'gowilds-child' ),
				'desc' => __( 'Shown under the price on the tour page. The price itself is set under Pricing (leave it empty for "Price on request").', 'gowilds-child' ),
				'type' => 'textarea',
				'rows' => 3,
			),
		),
	);
	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'gowilds_child_trip_meta_boxes' );
