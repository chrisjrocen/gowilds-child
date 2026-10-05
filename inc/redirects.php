<?php
/**
 * 301 redirects for URLs that moved in the rebuild.
 * Tours moved from /<slug>/ (posts) to /safaris/<slug>/ (WP Travel Engine trips).
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Old path => new path. Both without the domain, with leading and trailing slashes.
 */
function gowilds_child_redirect_map() {
	$tours = array(
		'3-day-bwindi-gorilla-trekking-safari',
		'4-days-uganda-primate-and-wildlife-safari',
		'3-day-rwanda-gorilla-trekking-safari',
		'12-days-budget-bwindi-gorilla-trekking',
		'2-days-gorilla-trekking-in-rwanda',
		'5-days-gorilla-wildlife-safari',
		'8-day-wildlife-gorilla-safari',
		'5-days-congo-lowland-gorilla-safari',
		'7-days-rwanda-primates-and-wildlife-safari',
		'5-day-uganda-rwanda-safari',
		'14-days-uganda-rwanda-safari',
		'5-day-affordable-flying-safari-in-uganda',
		'1-day-mubaku-community-tour',
		'10-day-blue-nile-trails-cultural-wildlife-adventure',
		'4-days-best-of-amboseli-wildlife',
		'7-days-kenya-wildlife-safari',
		'10-days-classic-kenya-wildlife-safari',
		'7-days-classic-tanzania-wildlife-safari',
		'3-days-victoria-falls-experience',
		'4-day-lower-zambezi-canoe-safari-experience',
		'10-days-best-of-zambia-safari',
	);

	$map = array();
	foreach ( $tours as $slug ) {
		$map[ '/' . $slug . '/' ] = '/safaris/' . $slug . '/';
	}

	// One all-safaris listing: the old Expeditions page now points to the trip archive.
	$map['/expeditions/'] = '/safaris/';
	// Old links inside posts point to /news/, which never existed as a page: send them to the blog.
	$map['/news/'] = '/blogs/';

	return apply_filters( 'gowilds_child_redirect_map', $map );
}

/**
 * Redirect before WordPress looks the URL up, so each old URL answers with a single 301.
 */
function gowilds_child_redirects() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'WP_CLI' ) && WP_CLI ) || empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- compared against a fixed list only.
	$path = trailingslashit( strtolower( (string) $path ) );
	$map  = gowilds_child_redirect_map();

	if ( isset( $map[ $path ] ) ) {
		$query = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_QUERY ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		wp_safe_redirect( home_url( $map[ $path ] ) . ( $query ? '?' . $query : '' ), 301, 'Afoyo' );
		exit;
	}
}
add_action( 'init', 'gowilds_child_redirects', 1 );
