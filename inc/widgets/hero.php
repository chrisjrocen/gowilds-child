<?php
/**
 * Page hero for country pages ("Visit Uganda") and experience pages ("Gorilla Trekking"):
 * photo, breadcrumb, large title, a line or paragraph, one button.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Afoyo_Widget_Hero extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-hero';
	}

	public function get_title() {
		return __( 'Page hero', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-image-box';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Hero', 'gowilds-child' ) ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'gowilds-child' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'country'    => __( 'Country page (title and one italic line)', 'gowilds-child' ),
					'experience' => __( 'Experience page (title and a short paragraph)', 'gowilds-child' ),
				),
				'default' => 'country',
			)
		);
		$this->add_control( 'title', array( 'label' => __( 'Title', 'gowilds-child' ), 'description' => __( 'Leave empty to use the page title.', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->add_control( 'text', array( 'label' => __( 'Line or paragraph under the title', 'gowilds-child' ), 'type' => Controls_Manager::TEXTAREA ) );
		$this->add_control( 'image', array( 'label' => __( 'Photo', 'gowilds-child' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Button text', 'gowilds-child' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'button_link', array( 'label' => __( 'Button link', 'gowilds-child' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'crumb_label', array( 'label' => __( 'Breadcrumb: middle item', 'gowilds-child' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'crumb_link', array( 'label' => __( 'Breadcrumb: middle item link', 'gowilds-child' ), 'type' => Controls_Manager::URL ) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$title    = '' !== trim( (string) $settings['title'] ) ? $settings['title'] : get_the_title( get_queried_object_id() );
		$x        = 'experience' === $settings['layout'];
		$class    = $x ? 'xhero' : 'chero';
		$link     = isset( $settings['button_link']['url'] ) ? $settings['button_link']['url'] : '';
		$crumb    = trim( (string) $settings['crumb_label'] );
		$crumb_to = isset( $settings['crumb_link']['url'] ) ? $settings['crumb_link']['url'] : '';
		?>
		<section class="<?php echo esc_attr( $class ); ?>">
			<?php if ( ! empty( $settings['image']['id'] ) ) : ?>
				<div class="media" style="position:absolute;inset:0"><?php echo wp_get_attachment_image( $settings['image']['id'], 'full', false, array( 'fetchpriority' => 'high', 'loading' => 'eager' ) ); ?></div>
			<?php endif; ?>
			<div class="shade" aria-hidden="true"></div>
			<div class="<?php echo esc_attr( $class ); ?>__text">
				<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'gowilds-child' ); ?>">
					<ol class="crumbs">
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'gowilds-child' ); ?></a></li>
						<?php if ( '' !== $crumb ) : ?>
							<li><?php if ( $crumb_to ) : ?><a href="<?php echo esc_url( $crumb_to ); ?>"><?php echo esc_html( $crumb ); ?></a><?php else : ?><span><?php echo esc_html( $crumb ); ?></span><?php endif; ?></li>
						<?php endif; ?>
						<li><span aria-current="page"><?php echo esc_html( get_the_title( get_queried_object_id() ) ); ?></span></li>
					</ol>
				</nav>
				<h1><?php echo esc_html( $title ); ?></h1>
				<?php if ( '' !== trim( (string) $settings['text'] ) ) : ?>
					<p<?php echo $x ? '' : ' class="chero__sub"'; ?>><?php echo esc_html( $settings['text'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( (string) $settings['button_text'] ) && $link ) : ?>
					<a class="btn btn-gold" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $settings['button_text'] ); ?> <i class="las <?php echo 0 === strpos( $link, '#' ) ? 'la-arrow-down' : 'la-arrow-right'; ?>" aria-hidden="true"></i></a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
