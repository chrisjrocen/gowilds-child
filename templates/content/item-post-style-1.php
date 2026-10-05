<?php
/**
 * Blog card (overrides the Gowilds "Item Post Style I" card used by the Posts widgets and blog archives).
 * Markup follows the design's .blog-card.
 *
 * @package gowilds-child
 */

$thumbnail  = ( isset( $thumbnail_size ) && $thumbnail_size ) ? $thumbnail_size : 'medium_large';
$categories = get_the_category();
?>
<article <?php post_class( 'blog-card' ); ?>>
	<div class="blog-card__media media">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( $thumbnail, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
		}
		?>
	</div>
	<div class="blog-card__body">
		<div class="blog-card__meta">
			<span><i class="las la-calendar" aria-hidden="true"></i><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
			<?php if ( $categories ) : ?>
				<span><i class="las la-folder" aria-hidden="true"></i><?php echo esc_html( $categories[0]->name ); ?></span>
			<?php endif; ?>
		</div>
		<h3><a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
		<span class="blog-card__more" aria-hidden="true"><?php esc_html_e( 'Read more', 'gowilds-child' ); ?> <i class="las la-arrow-right"></i></span>
	</div>
</article>
