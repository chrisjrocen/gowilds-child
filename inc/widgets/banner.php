<?php
/**
 * Page banner: photo, breadcrumb, page title and optional intro line.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Afoyo_Widget_Banner extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-banner';
	}

	public function get_title() {
		return __( 'Page banner', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Banner', 'gowilds-child' ) ) );

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'gowilds-child' ),
				'description' => __( 'Leave empty to use the page title.', 'gowilds-child' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);
		$this->add_control( 'intro', array( 'label' => __( 'Intro line (optional)', 'gowilds-child' ), 'type' => Controls_Manager::TEXTAREA ) );
		$this->add_control( 'image', array( 'label' => __( 'Photo', 'gowilds-child' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_control(
			'size',
			array(
				'label'   => __( 'Height', 'gowilds-child' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'short' => __( 'Short', 'gowilds-child' ),
					''      => __( 'Normal', 'gowilds-child' ),
					'tall'  => __( 'Tall', 'gowilds-child' ),
				),
				'default' => '',
			)
		);
		$this->add_control(
			'crumb_label',
			array(
				'label'       => __( 'Breadcrumb: middle item (optional)', 'gowilds-child' ),
				'description' => __( 'For example "Info & Updates". Parent pages are added automatically.', 'gowilds-child' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);
		$this->add_control( 'crumb_link', array( 'label' => __( 'Breadcrumb: middle item link', 'gowilds-child' ), 'type' => Controls_Manager::URL ) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$post_id  = get_queried_object_id();
		$title    = '' !== trim( (string) $settings['title'] ) ? $settings['title'] : get_the_title( $post_id );
		$class    = 'banner' . ( $settings['size'] ? ' banner--' . $settings['size'] : '' );

		$crumbs = array( array( __( 'Home', 'gowilds-child' ), home_url( '/' ) ) );
		if ( '' !== trim( (string) $settings['crumb_label'] ) ) {
			$crumbs[] = array( $settings['crumb_label'], isset( $settings['crumb_link']['url'] ) ? $settings['crumb_link']['url'] : '' );
		}
		if ( is_tax() && ! is_post_type_archive() ) {
			// A destination or trip type archive shares the /safaris/ banner: name it after the term.
			// (/safaris/?destination=… is the main archive with a filter on, and keeps its own title.)
			$crumbs[] = array( $title, get_post_type_archive_link( 'trip' ) );
			$title    = gowilds_child_term_safaris_title();
		} else {
			foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $ancestor ) {
				$crumbs[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
			}
		}
		?>
		<section class="<?php echo esc_attr( $class ); ?>">
			<?php if ( ! empty( $settings['image']['url'] ) ) : ?>
				<div class="media" style="position:absolute;inset:0">
					<?php
					if ( ! empty( $settings['image']['id'] ) ) {
						echo wp_get_attachment_image( $settings['image']['id'], 'full', false, array( 'fetchpriority' => 'high', 'loading' => 'eager' ) );
					} else {
						printf( '<img src="%s" alt="" fetchpriority="high">', esc_url( $settings['image']['url'] ) );
					}
					?>
				</div>
				<div class="shade" aria-hidden="true"></div>
			<?php endif; ?>
			<div class="banner__inner wrap">
				<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'gowilds-child' ); ?>">
					<ol class="crumbs">
						<?php foreach ( $crumbs as $crumb ) : ?>
							<li>
								<?php if ( $crumb[1] ) : ?>
									<a href="<?php echo esc_url( $crumb[1] ); ?>"><?php echo esc_html( $crumb[0] ); ?></a>
								<?php else : ?>
									<span><?php echo esc_html( $crumb[0] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
						<li><span aria-current="page"><?php echo esc_html( $title ); ?></span></li>
					</ol>
				</nav>
				<h1><?php echo esc_html( $title ); ?></h1>
				<?php if ( '' !== trim( (string) $settings['intro'] ) ) : ?>
					<p><?php echo esc_html( $settings['intro'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
