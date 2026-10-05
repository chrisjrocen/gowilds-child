<?php
/**
 * Safaris grid: the trips of one country and/or one trip type, as tour cards.
 * Two looks: a section (small heading with the count, title, button to the full list)
 * or a listing (bar with the count, "See all destinations" and a sort menu).
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Afoyo_Widget_Trips extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-trips';
	}

	public function get_title() {
		return __( 'Safaris grid', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	private function term_options( $taxonomy ) {
		$options = array( '' => __( 'Any', 'gowilds-child' ) );
		$terms   = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}
		return $options;
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Safaris', 'gowilds-child' ) ) );

		$this->add_control( 'destination', array( 'label' => __( 'Country', 'gowilds-child' ), 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'options' => $this->term_options( 'destination' ), 'label_block' => true ) );
		$this->add_control( 'trip_type', array( 'label' => __( 'Trip type', 'gowilds-child' ), 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'options' => $this->term_options( 'trip_types' ), 'label_block' => true ) );
		$this->add_control( 'featured', array( 'label' => __( 'Featured trips only', 'gowilds-child' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'limit', array( 'label' => __( 'How many (0 = all)', 'gowilds-child' ), 'type' => Controls_Manager::NUMBER, 'default' => 0, 'min' => 0 ) );
		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'gowilds-child' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'section' => __( 'Section with heading', 'gowilds-child' ),
					'listing' => __( 'Listing with count and sort', 'gowilds-child' ),
					'cards'   => __( 'Cards only', 'gowilds-child' ),
				),
				'default' => 'section',
			)
		);
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'condition' => array( 'look' => 'section' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Small heading', 'gowilds-child' ), 'description' => __( 'Leave empty to show the number of trips.', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'look' => 'section' ) ) );
		$this->add_control( 'centered', array( 'label' => __( 'Centre the heading', 'gowilds-child' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'condition' => array( 'look' => 'section' ) ) );
		$this->add_control( 'place', array( 'label' => __( 'Place name for the count', 'gowilds-child' ), 'description' => __( 'For example "Uganda": shows "10 safaris in Uganda".', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'look' => 'listing' ) ) );
		$this->add_control( 'more_text', array( 'label' => __( 'Button text', 'gowilds-child' ), 'description' => __( '%d is replaced by the number of safaris.', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->add_control( 'more_link', array( 'label' => __( 'Button link', 'gowilds-child' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'anchor', array( 'label' => __( 'Anchor ID (optional)', 'gowilds-child' ), 'type' => Controls_Manager::TEXT ) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$tax_query = array();
		foreach ( array( 'destination' => 'destination', 'trip_type' => 'trip_types' ) as $key => $taxonomy ) {
			$slugs = array_filter( (array) $settings[ $key ] );
			if ( $slugs ) {
				$tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $slugs );
			}
		}

		$query = array(
			'post_type'   => 'trip',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
			'orderby'     => 'meta_value_num',
			'meta_key'    => 'wp_travel_engine_setting_trip_duration',
			'order'       => 'ASC',
			'tax_query'   => $tax_query,
		);
		if ( 'yes' === $settings['featured'] ) {
			$query['meta_query'] = array( array( 'key' => 'wp_travel_engine_featured_trip', 'value' => 'yes' ) );
		}
		$ids   = get_posts( $query );
		$total = count( $ids );
		$limit = (int) $settings['limit'];
		$shown = $limit > 0 ? array_slice( $ids, 0, $limit ) : $ids;

		if ( ! $total ) {
			return;
		}

		$more_url  = isset( $settings['more_link']['url'] ) ? $settings['more_link']['url'] : '';
		$more_text = sprintf( str_replace( '%d', '%1$d', (string) $settings['more_text'] ), $total );
		$total_for_button = $total;
		$id_attr   = '' !== trim( (string) $settings['anchor'] ) ? ' id="' . esc_attr( sanitize_title( $settings['anchor'] ) ) . '"' : '';

		if ( 'cards' === $settings['look'] ) {
			echo '<div class="grid-cards">';
			foreach ( $shown as $trip_id ) {
				gowilds_child_tour_card( $trip_id );
			}
			echo '</div>';
			return;
		}

		if ( 'listing' === $settings['look'] ) :
			?>
			<div class="stack"<?php echo $id_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> style="gap:30px" data-sortable data-mobile-limit="3">
				<div class="ct-bar">
					<strong>
						<?php
						/* translators: 1: number of safaris, 2: place */
						echo esc_html( '' !== trim( (string) $settings['place'] ) ? sprintf( _n( '%1$d safari in %2$s', '%1$d safaris in %2$s', $total, 'gowilds-child' ), $total, $settings['place'] ) : sprintf( _n( '%d safari', '%d safaris', $total, 'gowilds-child' ), $total ) );
						?>
					</strong>
					<div class="ct-bar__right">
						<a class="link-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>" style="font-size:15px"><i class="las la-globe-africa" aria-hidden="true" style="font-size:20px"></i><?php esc_html_e( 'See all destinations', 'gowilds-child' ); ?></a>
						<?php if ( $total > 1 ) : ?>
							<label class="sort"><span class="d-only"><?php esc_html_e( 'Sort by', 'gowilds-child' ); ?></span>
								<select class="input" data-sort aria-label="<?php esc_attr_e( 'Sort safaris', 'gowilds-child' ); ?>">
									<option value="featured"><?php esc_html_e( 'Shortest first', 'gowilds-child' ); ?></option>
									<option value="durDesc"><?php esc_html_e( 'Duration: longest first', 'gowilds-child' ); ?></option>
									<option value="priceAsc"><?php esc_html_e( 'Price: low to high', 'gowilds-child' ); ?></option>
									<option value="priceDesc"><?php esc_html_e( 'Price: high to low', 'gowilds-child' ); ?></option>
								</select>
							</label>
						<?php endif; ?>
					</div>
				</div>
				<div class="grid-cards" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))" data-grid>
					<?php
					foreach ( $shown as $trip_id ) {
						gowilds_child_tour_card( $trip_id );
					}
					if ( 1 === $total ) {
						$this->tailor_card();
					}
					?>
				</div>
				<?php if ( $total > 3 ) : ?>
					<button class="btn btn-outline show-more m-only" type="button" data-more style="height:54px"><?php echo esc_html( sprintf( /* translators: %d: number */ __( 'Show all %d safaris', 'gowilds-child' ), $total ) ); ?></button>
				<?php endif; ?>
				<a class="link-arrow m-only" href="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>" style="justify-content:center;font-size:15px"><?php esc_html_e( 'See all destinations', 'gowilds-child' ); ?> <i class="las la-arrow-right" aria-hidden="true"></i></a>
			</div>
			<?php
			return;
		endif;
		?>
		<div class="stack"<?php echo $id_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> style="gap:40px">
			<div class="sec-head<?php echo 'yes' === $settings['centered'] ? ' sec-head--center' : ''; ?>">
				<?php if ( '' !== trim( (string) $settings['eyebrow'] ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></span>
				<?php else : ?>
					<span class="eyebrow d-only"><?php echo esc_html( sprintf( /* translators: %d: number */ _n( '%d trip', '%d trips', $total, 'gowilds-child' ), $total ) ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== trim( (string) $settings['heading'] ) ) : ?>
					<h2 class="h2"><?php echo esc_html( $settings['heading'] ); ?></h2>
				<?php endif; ?>
			</div>
			<div class="stack" style="gap:28px">
				<div class="grid-cards<?php echo 'yes' === $settings['centered'] ? ' rail' : ''; ?>"<?php echo 'yes' === $settings['centered'] ? ' data-rail' : ''; ?>>
					<?php
					foreach ( $shown as $trip_id ) {
						gowilds_child_tour_card( $trip_id );
					}
					if ( 1 === $total ) {
						$this->tailor_card();
					}
					?>
				</div>
				<?php if ( 'yes' === $settings['centered'] ) : ?>
					<div class="rail-dots" data-rail-dots></div>
				<?php endif; ?>
				<?php if ( $more_url && '' !== trim( $more_text ) && $total_for_button > 1 ) : ?>
					<div class="center"><a class="btn btn-outline btn-block-m" href="<?php echo esc_url( $more_url ); ?>"><?php echo esc_html( $more_text ); ?> <i class="las la-arrow-right" aria-hidden="true"></i></a></div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Shown beside the card when a country has a single trip.
	 */
	private function tailor_card() {
		?>
		<div class="tour-card tour-card--tailor">
			<div class="tour-card__body">
				<i class="las la-pencil-ruler" aria-hidden="true"></i>
				<h3><a href="<?php echo esc_url( gowilds_child_plan_url( get_permalink() ) ); ?>"><?php esc_html_e( "We'll tailor a trip for you", 'gowilds-child' ); ?></a></h3>
				<p><?php esc_html_e( 'Tell us your dates, interests and budget and we will build the route around you.', 'gowilds-child' ); ?></p>
				<div class="tour-card__foot"><span class="tour-card__cta" aria-hidden="true"><?php esc_html_e( 'Plan Your Safari', 'gowilds-child' ); ?> <i class="las la-arrow-right"></i></span></div>
			</div>
		</div>
		<?php
	}
}
