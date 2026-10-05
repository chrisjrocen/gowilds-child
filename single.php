<?php
/**
 * Single blog post: tall banner, article with a sidebar, related articles.
 * Other post types keep the parent theme's template.
 *
 * @package gowilds-child
 */

if ( 'post' !== get_post_type() ) {
	require get_template_directory() . '/single.php';
	return;
}

get_header();

while ( have_posts() ) :
	the_post();
	$post_id    = get_the_ID();
	$categories = get_the_category();
	$category   = $categories ? $categories[0] : null;
	$is_park    = $category && 'national-parks' === $category->slug;
	$crumb      = $is_park ? array( __( 'National Parks', 'gowilds-child' ), home_url( '/national-parks/' ) ) : array( __( 'Safari Blog', 'gowilds-child' ), home_url( '/blogs/' ) );

	gowilds_child_banner(
		array(
			'title'    => get_the_title(),
			'image_id' => gowilds_child_banner_image( $post_id ),
			'size'     => 'tall',
			'crumbs'   => array( $crumb ),
		)
	);
	?>
	<div id="main">
		<section class="post-layout wrap">
			<article <?php post_class( 'article' ); ?>>
				<div class="post-meta">
					<span><i class="las la-calendar" aria-hidden="true"></i><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></span>
					<?php if ( $category ) : ?>
						<span><i class="las la-folder" aria-hidden="true"></i><?php echo esc_html( $category->name ); ?></span>
					<?php endif; ?>
					<span><i class="las la-clock" aria-hidden="true"></i><?php echo esc_html( sprintf( /* translators: %d: minutes */ __( '%d min read', 'gowilds-child' ), gowilds_child_reading_time( $post_id ) ) ); ?></span>
				</div>

				<?php the_content(); ?>

				<div class="share" data-share>
					<span><?php esc_html_e( 'Share', 'gowilds-child' ); ?></span>
					<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" data-share-to="facebook" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Share on Facebook', 'gowilds-child' ); ?>"><i class="lab la-facebook-f" aria-hidden="true"></i></a>
					<a href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode( get_permalink() ); ?>" data-share-to="x" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Share on X', 'gowilds-child' ); ?>"><i class="lab la-twitter" aria-hidden="true"></i></a>
					<a href="https://wa.me/?text=<?php echo rawurlencode( get_the_title() . ' ' . get_permalink() ); ?>" data-share-to="whatsapp" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Share on WhatsApp', 'gowilds-child' ); ?>"><i class="lab la-whatsapp" aria-hidden="true"></i></a>
					<button type="button" aria-label="<?php esc_attr_e( 'Copy link', 'gowilds-child' ); ?>" data-copy-link><i class="las la-link" aria-hidden="true"></i></button>
					<span class="share__msg" role="status" data-copy-msg></span>
				</div>
			</article>

			<?php gowilds_child_post_aside( $post_id ); ?>
		</section>

		<?php
		$related = $category ? new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 3,
				'post__not_in'        => array( $post_id ),
				'cat'                 => $category->term_id,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		) : null;
		?>
		<?php if ( $related && $related->have_posts() ) : ?>
			<section class="sec stack" style="gap:36px;padding-top:110px;padding-bottom:130px">
				<h2 class="h2 wrap" style="font-size:clamp(36px,3.4vw,48px)"><?php esc_html_e( 'Related articles', 'gowilds-child' ); ?></h2>
				<div class="wrap">
					<div class="grid-cards rail">
						<?php
						while ( $related->have_posts() ) {
							$related->the_post();
							get_template_part( 'templates/content/item', 'post-style-1' );
						}
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
		<?php else : ?>
			<div style="padding-bottom:130px"></div>
		<?php endif; ?>
	</div>
	<?php
endwhile;

get_footer();
