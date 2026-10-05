<?php
/**
 * Page content (overrides the Gowilds page template part).
 *
 * - Pages built in Elementor run edge to edge with no theme title or breadcrumb: they bring their own banner.
 * - "Safari Blog" and "National Parks" list posts at their own address, with category tabs.
 * - Every other page gets the design's default layout: banner, text column, closing box.
 *
 * @package gowilds-child
 */

$page_id     = get_the_ID();
$is_builder  = 'builder' === get_post_meta( $page_id, '_elementor_edit_mode', true );
$archives    = gowilds_child_archive_pages();
$page_slug   = get_post_field( 'post_name', $page_id );
$info_crumb  = array( __( 'Info & Updates', 'gowilds-child' ), '' );

if ( $is_builder ) :
	?>
	<div class="single-page-template">
		<div class="container-full single-content-inner">
			<div class="row">
				<div class="col-12">
					<?php if ( have_posts() ) : the_post(); ?>
						<div <?php post_class( 'clearfix' ); ?> id="<?php echo esc_attr( $page_id ); ?>">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
	return;
endif;

if ( isset( $archives[ $page_slug ] ) ) :
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters.
	$topic  = isset( $_GET['topic'] ) ? sanitize_title( wp_unslash( $_GET['topic'] ) ) : $archives[ $page_slug ];
	$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$paged  = isset( $_GET['pg'] ) ? max( 1, absint( $_GET['pg'] ) ) : 1;
	// phpcs:enable
	$base   = get_permalink( $page_id );
	if ( $topic !== $archives[ $page_slug ] ) {
		$base = add_query_arg( 'topic', $topic, $base );
	}
	if ( '' !== $search ) {
		$base = add_query_arg( 'q', rawurlencode( $search ), $base );
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 12,
			'paged'               => $paged,
			'category_name'       => $topic,
			's'                   => $search,
			'ignore_sticky_posts' => true,
		)
	);

	gowilds_child_banner(
		array(
			'title'    => 'blogs' === $page_slug ? __( 'Safari Blog', 'gowilds-child' ) : get_the_title( $page_id ),
			'intro'    => '' !== $search ? sprintf( /* translators: %s: search words */ __( 'Articles matching "%s"', 'gowilds-child' ), $search ) : get_post_field( 'post_excerpt', $page_id ),
			'image_id' => (int) get_post_thumbnail_id( $page_id ),
			'crumbs'   => array( $info_crumb ),
		)
	);
	echo '<div id="main">';
	gowilds_child_post_grid( $query, $topic, $base );
	echo '</div>';
	return;
endif;

if ( have_posts() ) :
	the_post();
	$crumbs = array( $info_crumb );
	foreach ( array_reverse( get_post_ancestors( $page_id ) ) as $ancestor ) {
		$crumbs[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
	}
	gowilds_child_banner( array( 'title' => get_the_title(), 'image_id' => gowilds_child_banner_image( $page_id ), 'crumbs' => $crumbs ) );
	?>
	<div id="main">
		<section class="page-body wrap">
			<article <?php post_class( 'article' ); ?> id="<?php echo esc_attr( $page_id ); ?>">
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
				<?php gowilds_child_cta_box(); ?>
			</article>
		</section>
	</div>
	<?php
endif;
