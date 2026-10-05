<?php
/**
 * Licence strip: "Licensed and trusted" with one white chip per licence: its logo, or its initials and name
 * until a logo is chosen.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class Afoyo_Widget_Licences extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-licences';
	}

	public function get_title() {
		return __( 'Licence strip', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-logo';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Licences', 'gowilds-child' ) ) );

		$this->add_control(
			'caption',
			array(
				'label'   => __( 'Caption', 'gowilds-child' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Licensed and trusted',
			)
		);

		$this->add_control(
			'theme',
			array(
				'label'   => __( 'Background it sits on', 'gowilds-child' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'light' => __( 'Light', 'gowilds-child' ),
					'dark'  => __( 'Dark green', 'gowilds-child' ),
				),
				'default' => 'light',
			)
		);

		$repeater = new Repeater();
		$repeater->add_control( 'abbr', array( 'label' => __( 'Initials', 'gowilds-child' ), 'type' => Controls_Manager::TEXT ) );
		$repeater->add_control( 'name', array( 'label' => __( 'Name', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$repeater->add_control( 'note', array( 'label' => __( 'Note (optional)', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$repeater->add_control(
			'logo',
			array(
				'label'       => __( 'Logo (optional)', 'gowilds-child' ),
				'description' => __( 'Shown in place of the initials and name.', 'gowilds-child' ),
				'type'        => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Licences', 'gowilds-child' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ abbr }}}',
				'default'     => array(
					array( 'abbr' => 'UTB', 'name' => 'Uganda Tourism Board', 'logo' => array( 'url' => get_stylesheet_directory_uri() . '/assets/img/licences/utb.png', 'id' => '' ) ),
					array( 'abbr' => 'AUTO', 'name' => 'Association of Uganda Tour Operators', 'logo' => array( 'url' => get_stylesheet_directory_uri() . '/assets/img/licences/auto.png', 'id' => '' ) ),
					array( 'abbr' => 'ATTA', 'name' => 'African Travel and Tourism Association' ),
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$class    = 'licences' . ( 'dark' === $settings['theme'] ? ' licences--dark' : '' );
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<?php if ( '' !== $settings['caption'] ) : ?>
				<span class="licences__cap"><?php echo esc_html( $settings['caption'] ); ?></span>
			<?php endif; ?>
			<ul>
				<?php foreach ( $settings['items'] as $item ) : ?>
					<?php $has_logo = ! empty( $item['logo']['url'] ); ?>
					<li<?php echo $has_logo ? '' : ' class="licences__text"'; ?>>
						<?php
						if ( ! empty( $item['logo']['id'] ) ) {
							// Full size: the theme's smaller sizes are hard-cropped, which cuts the ends off wide logos.
							echo wp_get_attachment_image( $item['logo']['id'], 'full', false, array( 'class' => 'licences__logo', 'alt' => $item['name'], 'loading' => 'lazy' ) );
						} elseif ( $has_logo ) {
							printf( '<img class="licences__logo" src="%s" alt="%s" loading="lazy">', esc_url( $item['logo']['url'] ), esc_attr( $item['name'] ) );
						} else {
							printf( '<abbr title="%s">%s</abbr><span>%s</span>', esc_attr( $item['name'] ), esc_html( $item['abbr'] ), esc_html( $item['name'] ) );
						}
						?>
						<?php if ( ! empty( $item['note'] ) ) : ?>
							<small><?php echo esc_html( $item['note'] ); ?></small>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
