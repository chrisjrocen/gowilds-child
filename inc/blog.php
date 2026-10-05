<?php
/**
 * Blog and default page templates: shared pieces (banner, post grid with category tabs, sidebar panels).
 * Used by single.php, archive.php, search.php and templates/page/single.php.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

/** Pages that list posts at their own address: page slug => category slug ('' = all posts). */
function gowilds_child_archive_pages() {
	return array(
		'blogs'          => '',
		'national-parks' => 'national-parks',
	);
}

/** Category tabs of the blog: label => [ url, category slug ]. */
function gowilds_child_blog_tabs() {
	$blog = home_url( '/blogs/' );
	$tabs = array(
		__( 'All', 'gowilds-child' )            => array( $blog, '' ),
		__( 'Blogs', 'gowilds-child' )          => array( add_query_arg( 'topic', 'blogs', $blog ), 'blogs' ),
		__( 'National Parks', 'gowilds-child' ) => array( home_url( '/national-parks/' ), 'national-parks' ),
		__( 'Accommodation', 'gowilds-child' )  => array( add_query_arg( 'topic', 'accommodation', $blog ), 'accommodation' ),
	);
	return apply_filters( 'gowilds_child_blog_tabs', $tabs );
}

/**
 * Page banner (same markup as the "Page banner" Elementor widget).
 *
 * @param array $args title, intro, image_id, size ('', 'short', 'tall'), crumbs (list of [ label, url ]).
 */
function gowilds_child_banner( array $args ) {
	$args  = wp_parse_args( $args, array( 'title' => '', 'intro' => '', 'image_id' => 0, 'size' => '', 'crumbs' => array() ) );
	$class = 'banner' . ( $args['size'] ? ' banner--' . $args['size'] : '' );
	?>
	<section class="<?php echo esc_attr( $class ); ?>">
		<?php if ( $args['image_id'] ) : ?>
			<div class="media" style="position:absolute;inset:0"><?php echo wp_get_attachment_image( $args['image_id'], 'full', false, array( 'fetchpriority' => 'high', 'loading' => 'eager' ) ); ?></div>
			<div class="shade" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="banner__inner wrap">
			<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'gowilds-child' ); ?>">
				<ol class="crumbs">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'gowilds-child' ); ?></a></li>
					<?php foreach ( $args['crumbs'] as $crumb ) : ?>
						<li><?php if ( ! empty( $crumb[1] ) ) : ?><a href="<?php echo esc_url( $crumb[1] ); ?>"><?php echo esc_html( $crumb[0] ); ?></a><?php else : ?><span><?php echo esc_html( $crumb[0] ); ?></span><?php endif; ?></li>
					<?php endforeach; ?>
					<li><span aria-current="page"><?php echo esc_html( $args['title'] ); ?></span></li>
				</ol>
			</nav>
			<h1><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( '' !== trim( (string) $args['intro'] ) ) : ?>
				<p><?php echo esc_html( $args['intro'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/** "6 min read" for a post. */
function gowilds_child_reading_time( $post_id ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) round( $words / 200 ) );
}

/**
 * Grid of blog cards with category tabs and pagination.
 *
 * @param WP_Query $query    Posts to show.
 * @param string   $current  Category slug of the active tab ('' = All).
 * @param string   $base_url Address used for pagination links (page-based archives), or '' for normal archives.
 */
function gowilds_child_post_grid( WP_Query $query, $current = '', $base_url = '' ) {
	?>
	<section class="wrap stack afoyo-post-archive" style="padding-top:72px;padding-bottom:130px;gap:36px">
		<nav class="cats" aria-label="<?php esc_attr_e( 'Filter by category', 'gowilds-child' ); ?>">
			<?php foreach ( gowilds_child_blog_tabs() as $label => $tab ) : ?>
				<a href="<?php echo esc_url( $tab[0] ); ?>"<?php echo $tab[1] === $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php if ( $query->have_posts() ) : ?>
			<div class="grid-cards">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					get_template_part( 'templates/content/item', 'post-style-1' );
				}
				wp_reset_postdata();
				?>
			</div>
			<?php
			$links = paginate_links(
				array(
					'base'      => $base_url ? add_query_arg( 'pg', '%#%', $base_url ) : str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
					'format'    => '',
					'current'   => max( 1, (int) $query->get( 'paged' ) ),
					'total'     => (int) $query->max_num_pages,
					'type'      => 'array',
					'prev_next' => false,
					'mid_size'  => 1,
				)
			);
			if ( $links ) :
				?>
				<nav class="pager" aria-label="<?php esc_attr_e( 'Pagination', 'gowilds-child' ); ?>" style="display:flex">
					<?php
					foreach ( $links as $link ) {
						$link = str_replace( array( 'page-numbers current', "aria-current='page'" ), array( 'page-numbers', '' ), $link );
						echo false !== strpos( $link, '<span' ) && false === strpos( $link, 'dots' ) ? str_replace( '<span', '<span aria-current="page"', $link ) : str_replace( 'class="page-numbers dots"', 'class="pager__gap"', $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links output.
					}
					?>
				</nav>
			<?php endif; ?>
		<?php else : ?>
			<div class="empty">
				<span class="empty__icon" aria-hidden="true"><i class="las la-search"></i></span>
				<h3><?php esc_html_e( 'No articles found', 'gowilds-child' ); ?></h3>
				<p><?php esc_html_e( 'Try another word, or browse all articles.', 'gowilds-child' ); ?></p>
				<a class="link-btn" href="<?php echo esc_url( home_url( '/blogs/' ) ); ?>" style="font-weight:700;font-size:15px"><?php esc_html_e( 'Show all articles', 'gowilds-child' ); ?></a>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/** Sidebar of a single post: search, recent posts, "Plan your safari". */
function gowilds_child_post_aside( $exclude_id = 0 ) {
	$recent = get_posts( array( 'numberposts' => 3, 'post__not_in' => array( $exclude_id ), 'post_status' => 'publish' ) );
	?>
	<aside class="post-aside">
		<div class="panel">
			<form class="post-search" action="<?php echo esc_url( home_url( '/blogs/' ) ); ?>" method="get" role="search">
				<label class="sr-only" for="afoyo-q"><?php esc_html_e( 'Search articles', 'gowilds-child' ); ?></label>
				<input id="afoyo-q" name="q" type="search" placeholder="<?php esc_attr_e( 'Search articles', 'gowilds-child' ); ?>">
				<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'gowilds-child' ); ?>"><i class="las la-search" aria-hidden="true"></i></button>
			</form>
		</div>
		<?php if ( $recent ) : ?>
			<div class="panel">
				<span class="panel__title" style="font-size:26px"><?php esc_html_e( 'Recent posts', 'gowilds-child' ); ?></span>
				<ul class="recent">
					<?php foreach ( $recent as $item ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $item ) ); ?>">
							<div class="media"><?php echo get_the_post_thumbnail( $item, 'thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?></div>
							<span><b><?php echo esc_html( get_the_title( $item ) ); ?></b><small><?php echo esc_html( get_the_date( 'j M Y', $item ) ); ?></small></span>
						</a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<div class="panel panel--dark">
			<i class="las la-compass" aria-hidden="true"></i>
			<span class="panel__title"><?php esc_html_e( 'Plan your safari', 'gowilds-child' ); ?></span>
			<p><?php esc_html_e( 'Tell us where you would like to go and we will build the trip around you.', 'gowilds-child' ); ?></p>
			<a class="btn btn-gold" href="<?php echo esc_url( gowilds_child_plan_url( get_permalink() ) ); ?>" style="height:52px"><?php esc_html_e( 'Start planning', 'gowilds-child' ); ?> <i class="las la-arrow-right" aria-hidden="true"></i></a>
		</div>
	</aside>
	<?php
}

/** Closing box of a default page. */
function gowilds_child_cta_box() {
	?>
	<div class="cta-box on-dark">
		<div><strong><?php esc_html_e( 'Ready to start planning?', 'gowilds-child' ); ?></strong><span><?php esc_html_e( "Tell us where you'd like to go and we'll do the rest.", 'gowilds-child' ); ?></span></div>
		<a class="btn btn-gold" href="<?php echo esc_url( gowilds_child_plan_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Plan Your Safari', 'gowilds-child' ); ?> <i class="las la-arrow-right" aria-hidden="true"></i></a>
	</div>
	<?php
}

/** First image inside a post's content, as an attachment ID (banner fallback when there is no featured image). */
function gowilds_child_banner_image( $post_id ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return (int) get_post_thumbnail_id( $post_id );
	}
	if ( preg_match( '/<img[^>]+src="([^"]+)"/', (string) get_post_field( 'post_content', $post_id ), $m ) ) {
		$id = attachment_url_to_postid( preg_replace( '/-\d+x\d+(\.\w+)$/', '$1', $m[1] ) );
		return (int) $id;
	}
	return 0;
}
