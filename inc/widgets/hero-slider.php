<?php
/**
 * Homepage hero slider: photos with a small heading and a headline each, two buttons, arrows and dots.
 * Auto-advances every 6 seconds, pauses on hover and focus, stays still when the visitor prefers reduced motion.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class Afoyo_Widget_Hero_Slider extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-hero-slider';
	}

	public function get_title() {
		return __( 'Hero slider', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-slides';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Slides', 'gowilds-child' ) ) );

		$repeater = new Repeater();
		$repeater->add_control( 'image', array( 'label' => __( 'Photo', 'gowilds-child' ), 'type' => Controls_Manager::MEDIA ) );
		$repeater->add_control( 'eyebrow', array( 'label' => __( 'Small heading', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$repeater->add_control( 'title', array( 'label' => __( 'Headline', 'gowilds-child' ), 'type' => Controls_Manager::TEXTAREA ) );

		$this->add_control(
			'slides',
			array(
				'label'       => __( 'Slides', 'gowilds-child' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ eyebrow }}}',
			)
		);

		$this->add_control( 'button_text', array( 'label' => __( 'First button text', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'default' => 'Plan My Safari' ) );
		$this->add_control( 'button_link', array( 'label' => __( 'First button link', 'gowilds-child' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'button2_text', array( 'label' => __( 'Second button text', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'default' => 'Explore Top Tours' ) );
		$this->add_control( 'button2_link', array( 'label' => __( 'Second button link', 'gowilds-child' ), 'type' => Controls_Manager::URL ) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides   = array_values( array_filter( (array) $settings['slides'], function ( $slide ) {
			return ! empty( $slide['image']['id'] );
		} ) );
		if ( ! $slides ) {
			return;
		}
		$count = count( $slides );
		$url1  = isset( $settings['button_link']['url'] ) ? $settings['button_link']['url'] : '';
		$url2  = isset( $settings['button2_link']['url'] ) ? $settings['button2_link']['url'] : '';
		?>
		<section class="hero" data-hero aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Featured journeys', 'gowilds-child' ); ?>">
			<?php foreach ( $slides as $i => $slide ) : ?>
				<div class="hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide data-eyebrow="<?php echo esc_attr( $slide['eyebrow'] ); ?>" data-title="<?php echo esc_attr( $slide['title'] ); ?>"<?php echo 0 === $i ? '' : ' aria-hidden="true"'; ?>>
					<div class="media" style="position:absolute;inset:0">
						<?php
						// First photo is the page's largest image: load it at once. The others wait.
						echo wp_get_attachment_image( $slide['image']['id'], 'full', false, 0 === $i ? array( 'fetchpriority' => 'high', 'loading' => 'eager' ) : array( 'loading' => 'lazy', 'decoding' => 'async' ) );
						?>
					</div>
				</div>
			<?php endforeach; ?>
			<div class="shade" aria-hidden="true"></div>
			<div class="hero__content">
				<div class="hero__text">
					<span class="eyebrow hero__fade" data-hero-eyebrow><?php echo esc_html( $slides[0]['eyebrow'] ); ?></span>
					<h1 class="hero__fade" data-hero-title aria-live="polite"><?php echo esc_html( $slides[0]['title'] ); ?></h1>
					<div class="hero__ctas">
						<?php if ( $url1 && '' !== trim( (string) $settings['button_text'] ) ) : ?>
							<a class="btn btn-gold" href="<?php echo esc_url( $url1 ); ?>"><?php echo esc_html( $settings['button_text'] ); ?> <i class="las la-arrow-right" aria-hidden="true"></i></a>
						<?php endif; ?>
						<?php if ( $url2 && '' !== trim( (string) $settings['button2_text'] ) ) : ?>
							<a class="btn btn-outline-light" href="<?php echo esc_url( $url2 ); ?>"><?php echo esc_html( $settings['button2_text'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( $count > 1 ) : ?>
					<div class="hero__controls">
						<button class="icon-btn icon-btn--light" type="button" aria-label="<?php esc_attr_e( 'Previous slide', 'gowilds-child' ); ?>" data-prev><i class="las la-arrow-left" aria-hidden="true"></i></button>
						<div class="hero__dots">
							<?php foreach ( $slides as $i => $slide ) : ?>
								<button type="button" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: total */ __( 'Show slide %1$d of %2$d', 'gowilds-child' ), $i + 1, $count ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?> data-go="<?php echo esc_attr( $i ); ?>"></button>
							<?php endforeach; ?>
						</div>
						<button class="icon-btn icon-btn--light" type="button" aria-label="<?php esc_attr_e( 'Next slide', 'gowilds-child' ); ?>" data-next><i class="las la-arrow-right" aria-hidden="true"></i></button>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
