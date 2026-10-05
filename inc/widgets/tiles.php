<?php
/**
 * Image tiles: a row of photos with a name, an optional line and a link (destinations, parks, experiences).
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class Afoyo_Widget_Tiles extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-tiles';
	}

	public function get_title() {
		return __( 'Image tiles', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Tiles', 'gowilds-child' ) ) );

		$this->add_control(
			'shape',
			array(
				'label'   => __( 'Tile shape', 'gowilds-child' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'3/4' => __( 'Tall (parks)', 'gowilds-child' ),
					'4/3' => __( 'Wide (destinations)', 'gowilds-child' ),
					'3/4L' => __( 'Tall, large name (experiences)', 'gowilds-child' ),
					'1/1' => __( 'Square', 'gowilds-child' ),
				),
				'default' => '3/4',
			)
		);
		$this->add_control( 'link_text', array( 'label' => __( 'Link text on each tile', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'default' => 'Read more' ) );

		$repeater = new Repeater();
		$repeater->add_control( 'image', array( 'label' => __( 'Photo', 'gowilds-child' ), 'type' => Controls_Manager::MEDIA ) );
		$repeater->add_control( 'name', array( 'label' => __( 'Name', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$repeater->add_control( 'line', array( 'label' => __( 'Line under the name (optional)', 'gowilds-child' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$repeater->add_control( 'link', array( 'label' => __( 'Link', 'gowilds-child' ), 'type' => Controls_Manager::URL ) );
		$repeater->add_control( 'count_country', array( 'label' => __( 'Or: count the safaris of this country (slug, e.g. uganda)', 'gowilds-child' ), 'description' => __( 'Shows "10 safaris" as the line and keeps it up to date.', 'gowilds-child' ), 'type' => Controls_Manager::TEXT ) );

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Tiles', 'gowilds-child' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = (array) $settings['items'];
		if ( ! $items ) {
			return;
		}
		$tall  = '3/4' === $settings['shape'];
		$ratio = '3/4L' === $settings['shape'] ? '3/4' : $settings['shape'];
		$style = $tall
			? 'gap:22px;grid-template-columns:repeat(auto-fit,minmax(min(100%,210px),1fr));--rail-w:240px;--rail-h:320px'
			: ( '3/4L' === $settings['shape'] ? 'gap:24px;--rail-w:290px;--rail-h:400px' : 'gap:24px;--rail-w:280px;--rail-h:340px' );
		?>
		<div class="stack" style="gap:28px">
			<div class="grid-cards rail" style="<?php echo esc_attr( $style ); ?>" data-rail>
				<?php foreach ( $items as $item ) : ?>
					<?php
					$url = isset( $item['link']['url'] ) ? $item['link']['url'] : '';
					$tag = $url ? 'a' : 'div';
					if ( ! empty( $item['count_country'] ) ) {
						$term = get_term_by( 'slug', sanitize_title( $item['count_country'] ), 'destination' );
						if ( $term ) {
							/* translators: %d: number of safaris */
							$item['line'] = sprintf( _n( '%d safari', '%d safaris', $term->count, 'gowilds-child' ), $term->count );
						}
					}
					?>
					<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed tag name. ?> class="tile" style="--ratio:<?php echo esc_attr( $ratio ); ?>"<?php echo $url ? ' href="' . esc_url( $url ) . '"' : ''; ?>>
						<div class="media"><?php echo ! empty( $item['image']['id'] ) ? wp_get_attachment_image( $item['image']['id'], 'medium_large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ) : ''; ?></div>
						<div class="shade" aria-hidden="true"></div>
						<div class="tile__text">
							<span class="tile__name"<?php echo $tall ? ' style="font-size:30px"' : ''; ?>><?php echo esc_html( $item['name'] ); ?></span>
							<?php if ( ! empty( $item['line'] ) ) : ?>
								<span class="tile__line"><?php echo esc_html( $item['line'] ); ?></span>
							<?php endif; ?>
							<?php if ( $url && '' !== trim( (string) $settings['link_text'] ) ) : ?>
								<span class="tile__go d-only"<?php echo $tall ? ' style="margin:0;font-size:14px"' : ''; ?>><?php echo esc_html( $settings['link_text'] ); ?> <i class="las la-arrow-right" aria-hidden="true"></i></span>
							<?php endif; ?>
						</div>
					</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php endforeach; ?>
			</div>
			<div class="rail-dots" data-rail-dots></div>
		</div>
		<?php
	}
}
