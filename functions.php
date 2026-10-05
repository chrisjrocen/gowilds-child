<?php
/**
 * Gowilds Child: custom code for Afoyo African Safaris.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

define( 'AFOYO_WHATSAPP_URL', 'https://wa.me/256755446166' );

/**
 * Load the child stylesheet after the parent theme's styles.
 * The parent already enqueues its own style.css, so it is not loaded again here.
 */
function gowilds_child_scripts() {
	// Fonts are self-hosted (assets/fonts), so the parent's Google Fonts request is dropped.
	wp_dequeue_style( 'gowilds-fonts' );

	wp_enqueue_style(
		'gowilds-child-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'gowilds-child-site',
		get_stylesheet_directory_uri() . '/assets/js/site.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'gowilds_child_scripts', 9999 );

/**
 * Preload the two fonts used above the fold.
 */
function gowilds_child_preload_fonts() {
	foreach ( array( 'kumbh-sans-normal-latin.woff2', 'cormorant-garamond-normal-latin.woff2' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_stylesheet_directory_uri() . '/assets/fonts/' . $font )
		);
	}
}
add_action( 'wp_head', 'gowilds_child_preload_fonts', 1 );

/**
 * SVG favicon from the design. The site icon set in Settings > General stays as the fallback.
 */
function gowilds_child_favicon() {
	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
		esc_url( get_stylesheet_directory_uri() . '/assets/img/favicon.svg' )
	);
}
add_action( 'wp_head', 'gowilds_child_favicon', 100 );

/**
 * "Skip to content" link for keyboard users.
 */
function gowilds_child_skip_link() {
	echo '<a class="skip-link" href="#page-content">' . esc_html__( 'Skip to content', 'gowilds-child' ) . '</a>';
}
add_action( 'wp_body_open', 'gowilds_child_skip_link', 1 );

require get_stylesheet_directory() . '/inc/whatsapp.php';
require get_stylesheet_directory() . '/inc/elementor.php';
require get_stylesheet_directory() . '/inc/redirects.php';
require get_stylesheet_directory() . '/inc/trips.php';
require get_stylesheet_directory() . '/inc/listings.php';
require get_stylesheet_directory() . '/inc/blog.php';
require get_stylesheet_directory() . '/inc/performance.php';
