<?php
/**
 * Safari search card: Destination, Trip type and Duration. Sends the visitor to the trip archive
 * (/safaris/) with WP Travel Engine's own filters applied. The choices come from the trip taxonomies.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Afoyo_Widget_Search extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-search';
	}

	public function get_title() {
		return __( 'Safari search', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Search', 'gowilds-child' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Title (shown on mobile)', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'default' => 'Find your safari' ) );
		$this->add_control( 'button', array( 'label' => __( 'Button text', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'default' => 'Search' ) );
		$this->add_control(
			'durations',
			array(
				'label'       => __( 'Duration choices (days, comma separated)', 'gowilds-child' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '3, 5, 8, 10',
				'label_block' => true,
			)
		);
		$this->end_controls_section();
	}

	private function options( $taxonomy ) {
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			printf( '<option value="%s">%s</option>', esc_attr( $term->slug ), esc_html( $term->name ) );
		}
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$days     = array_filter( array_map( 'absint', explode( ',', (string) $settings['durations'] ) ) );
		?>
		<div class="search-wrap">
			<form class="search" action="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>" method="get" role="search" aria-label="<?php esc_attr_e( 'Find a safari', 'gowilds-child' ); ?>" data-safari-search>
				<span class="search__title"><?php echo esc_html( $settings['title'] ); ?></span>
				<div class="search__field"><i class="las la-map-marker-alt" aria-hidden="true"></i><label><?php esc_html_e( 'Destination', 'gowilds-child' ); ?>
					<select name="destination"><option value=""><?php esc_html_e( 'All countries', 'gowilds-child' ); ?></option><?php $this->options( 'destination' ); ?></select></label></div>
				<div class="search__field"><i class="las la-binoculars" aria-hidden="true"></i><label><?php esc_html_e( 'Trip type', 'gowilds-child' ); ?>
					<select name="trip_types"><option value=""><?php esc_html_e( 'Any type', 'gowilds-child' ); ?></option><?php $this->options( 'trip_types' ); ?></select></label></div>
				<div class="search__field"><i class="las la-clock" aria-hidden="true"></i><label><?php esc_html_e( 'Duration', 'gowilds-child' ); ?>
					<?php // WTE's duration filter counts in hours. ?>
					<select name="maxdur"><option value=""><?php esc_html_e( 'Any length', 'gowilds-child' ); ?></option>
						<?php foreach ( $days as $day ) : ?>
							<option value="<?php echo esc_attr( $day * 24 ); ?>"><?php echo esc_html( sprintf( /* translators: %d: days */ __( 'Up to %d days', 'gowilds-child' ), $day ) ); ?></option>
						<?php endforeach; ?>
					</select></label></div>
				<button type="submit"><i class="las la-search" aria-hidden="true"></i><?php echo esc_html( $settings['button'] ); ?></button>
			</form>
		</div>
		<?php
	}
}
