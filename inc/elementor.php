<?php
/**
 * Elementor widgets for sections of the design that the Gowilds and WP Travel Engine widgets cannot match.
 * They appear in the Elementor panel under "Afoyo". Each file in inc/widgets/ is one widget.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

function gowilds_child_widget_category( $elements_manager ) {
	$elements_manager->add_category( 'afoyo', array( 'title' => __( 'Afoyo', 'gowilds-child' ) ) );
}
add_action( 'elementor/elements/categories_registered', 'gowilds_child_widget_category' );

function gowilds_child_register_widgets( $widgets_manager ) {
	$widgets = array(
		'licences'     => 'Afoyo_Widget_Licences',
		'testimonials' => 'Afoyo_Widget_Testimonials',
		'banner'       => 'Afoyo_Widget_Banner',
		'hero'         => 'Afoyo_Widget_Hero',
		'tiles'        => 'Afoyo_Widget_Tiles',
		'trips'        => 'Afoyo_Widget_Trips',
		'guide'        => 'Afoyo_Widget_Guide',
		'hero-slider'  => 'Afoyo_Widget_Hero_Slider',
		'search'       => 'Afoyo_Widget_Search',
	);
	foreach ( $widgets as $file => $class ) {
		require_once get_stylesheet_directory() . '/inc/widgets/' . $file . '.php';
		$widgets_manager->register( new $class() );
	}
}
add_action( 'elementor/widgets/register', 'gowilds_child_register_widgets' );

/**
 * Social Icons widget: Elementor builds the screen-reader label from the icon class ("La-facebook-f").
 * Replace it with the network's name.
 */
function gowilds_child_social_icon_labels( $content, $widget ) {
	if ( 'social-icons' !== $widget->get_name() ) {
		return $content;
	}
	$names = array(
		'La-facebook-f'  => 'Facebook',
		'La-instagram'   => 'Instagram',
		'La-youtube'     => 'YouTube',
		'La-tripadvisor' => 'TripAdvisor',
		'La-whatsapp'    => 'WhatsApp',
		'La-twitter'     => 'X (Twitter)',
		'La-tiktok'      => 'TikTok',
		'La-linkedin-in' => 'LinkedIn',
	);
	foreach ( $names as $class => $name ) {
		$content = str_replace( '<span class="elementor-screen-only">' . $class . '</span>', '<span class="elementor-screen-only">' . $name . '</span>', $content );
	}
	return $content;
}
add_filter( 'elementor/widget/render_content', 'gowilds_child_social_icon_labels', 10, 2 );
