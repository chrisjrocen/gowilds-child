<?php
/**
 * Guide article with an "On this page" box. The box is built from the article's Heading 2 lines.
 * An optional row of photos can sit after any section.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Afoyo_Widget_Guide extends \Elementor\Widget_Base {

	public function get_name() {
		return 'afoyo-guide';
	}

	public function get_title() {
		return __( 'Guide article', 'gowilds-child' );
	}

	public function get_icon() {
		return 'eicon-table-of-contents';
	}

	public function get_categories() {
		return array( 'afoyo' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => __( 'Guide', 'gowilds-child' ) ) );

		$this->add_control(
			'content',
			array(
				'label'       => __( 'Article', 'gowilds-child' ),
				'description' => __( 'Use Heading 2 for section titles: each one is listed under "On this page".', 'gowilds-child' ),
				'type'        => Controls_Manager::WYSIWYG,
			)
		);
		$this->add_control( 'numbered', array( 'label' => __( 'Numbered sections (terms and policies)', 'gowilds-child' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'photos', array( 'label' => __( 'Photo row (up to three)', 'gowilds-child' ), 'type' => Controls_Manager::GALLERY ) );
		$this->add_control( 'photos_after', array( 'label' => __( 'Show the photo row after section number', 'gowilds-child' ), 'type' => Controls_Manager::NUMBER, 'default' => 2, 'min' => 1 ) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$html     = wp_kses_post( wpautop( (string) $settings['content'] ) );
		$toc      = array();
		$used     = array();
		$numbered = 'yes' === $settings['numbered'];

		// Give every Heading 2 an ID and collect them for the contents box.
		$html = preg_replace_callback(
			'/<h2([^>]*)>(.*?)<\/h2>/is',
			function ( $m ) use ( &$toc, &$used, $numbered ) {
				$text = trim( wp_strip_all_tags( $m[2] ) );
				$id   = sanitize_title( $text );
				$id   = $id ? $id : 'section';
				$base = $id;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$used[ $id ] = true;
				$toc[]       = array( $id, $text );
				return '<h2 id="' . esc_attr( $id ) . '">' . ( $numbered ? '<span class="num">' . count( $toc ) . '.</span> ' : '' ) . $m[2] . '</h2>';
			},
			$html
		);

		// Photo row after the chosen section.
		$photos = array_slice( (array) $settings['photos'], 0, 3 );
		if ( $photos ) {
			$row = '<div class="photo-row rail">';
			foreach ( $photos as $photo ) {
				$row .= '<div class="media">' . wp_get_attachment_image( $photo['id'], 'medium_large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ) . '</div>';
			}
			$row  .= '</div>';
			$parts = preg_split( '/(?=<h2 )/', $html );
			$after = max( 1, (int) $settings['photos_after'] );
			// $parts[0] is anything before the first heading.
			$index = min( count( $parts ) - 1, $after );
			$parts[ $index ] .= $row;
			$html = implode( '', $parts );
		}
		?>
		<div class="<?php echo $numbered ? 'doc' : 'guide'; ?><?php echo $toc ? '' : ' guide--plain'; ?>">
			<?php if ( $toc ) : ?>
				<details class="toc" open data-toc>
					<summary><span><i class="las la-list" aria-hidden="true"></i><?php echo $numbered ? esc_html__( 'Contents', 'gowilds-child' ) : esc_html__( 'On this page', 'gowilds-child' ); ?></span><i class="las la-angle-down" aria-hidden="true"></i></summary>
					<ol>
						<?php foreach ( $toc as $entry ) : ?>
							<li><a href="#<?php echo esc_attr( $entry[0] ); ?>"><?php if ( $numbered ) : ?><span class="num"><?php echo esc_html( array_search( $entry, $toc, true ) + 1 ); ?>.</span><?php endif; ?><?php echo esc_html( $entry[1] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</details>
			<?php endif; ?>
			<article class="article">
				<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- passed through wp_kses_post above. ?>
			</article>
		</div>
		<?php
	}
}
