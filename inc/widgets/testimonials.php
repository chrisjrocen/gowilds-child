<?php
/**
 * Testimonials: heading with previous/next buttons and a row of quote cards.
 * Desktop: the buttons rotate the cards. Mobile: the cards become a swipe rail with dots.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class Afoyo_Widget_Testimonials extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-testimonials';
	}

	public function get_title() {
		return __( 'Testimonials', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-testimonial-carousel';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Testimonials', 'gowilds-child' ) ) );

		$this->add_control( 'eyebrow', array( 'label' => __( 'Small heading', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'default' => 'Traveller stories' ) );
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'default' => 'In their words', 'label_block' => true ) );

		$this->add_control(
			'show_nav',
			array(
				'label'        => __( 'Previous / next buttons', 'gowilds-child' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$repeater = new Repeater();
		$repeater->add_control( 'quote', array( 'label' => __( 'Quote', 'gowilds-child' ), 'type' => Controls_Manager::TEXTAREA ) );
		$repeater->add_control( 'name', array( 'label' => __( 'Name', 'gowilds-child' ), 'type' => Controls_Manager::TEXT ) );
		$repeater->add_control( 'place', array( 'label' => __( 'Country or trip', 'gowilds-child' ), 'type' => Controls_Manager::TEXT ) );

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Quotes', 'gowilds-child' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = array_filter( (array) $settings['items'], function ( $item ) {
			return '' !== trim( (string) $item['quote'] );
		} );
		if ( ! $items ) {
			return;
		}
		$rotates  = count( $items ) > 1 && 'yes' === $settings['show_nav'];
		$has_head = '' !== trim( $settings['eyebrow'] . $settings['heading'] );
		?>
		<div class="stack quotes" style="gap:<?php echo $rotates ? '48' : '40'; ?>px" data-quotes>
			<?php if ( $has_head ) : ?>
				<div class="sec-head sec-head--split">
					<div>
						<?php if ( '' !== $settings['eyebrow'] ) : ?>
							<span class="eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $settings['heading'] ) : ?>
							<h2 class="h2"><?php echo esc_html( $settings['heading'] ); ?></h2>
						<?php endif; ?>
					</div>
					<?php if ( $rotates ) : ?>
						<div class="carousel-nav">
							<button class="icon-btn" type="button" aria-label="<?php esc_attr_e( 'Previous review', 'gowilds-child' ); ?>" data-qprev><i class="las la-arrow-left" aria-hidden="true"></i></button>
							<button class="icon-btn icon-btn--solid" type="button" aria-label="<?php esc_attr_e( 'Next review', 'gowilds-child' ); ?>" data-qnext><i class="las la-arrow-right" aria-hidden="true"></i></button>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="stack" style="gap:28px">
				<div class="<?php echo count( $items ) > 1 ? 'grid-cards rail' : 'quotes__single'; ?>" style="gap:24px;--rail-w:320px" data-rail data-quote-list>
					<?php foreach ( $items as $item ) : ?>
						<figure class="quote-card" data-quote>
							<div class="quote-card__top">
								<div class="stars" role="img" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'gowilds-child' ); ?>"><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i></div>
								<i class="las la-quote-right" aria-hidden="true"></i>
							</div>
							<blockquote><?php echo esc_html( $item['quote'] ); ?></blockquote>
							<figcaption>
								<strong><?php echo esc_html( $item['name'] ); ?></strong>
								<?php if ( ! empty( $item['place'] ) ) : ?>
									<span><?php echo esc_html( $item['place'] ); ?></span>
								<?php endif; ?>
							</figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
				<?php if ( count( $items ) > 1 ) : ?>
					<div class="rail-dots" data-rail-dots></div>
				<?php endif; ?>
				<?php if ( $rotates ) : ?>
					<div class="dots" aria-hidden="true" data-quote-dots>
						<?php foreach ( array_values( $items ) as $i => $item ) : ?>
							<span<?php echo 0 === $i ? ' class="is-on"' : ''; ?>></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
