<?php
/**
 * Tour card. Expects the query var 'afoyo_trip' (see gowilds_child_tour_card()).
 *
 * @package gowilds-child
 */

$trip = get_query_var( 'afoyo_trip' );
if ( ! $trip ) {
	return;
}
$thumb_id = get_post_thumbnail_id( $trip['id'] );
?>
<div class="tour-card" data-trip data-i="<?php echo esc_attr( $trip['id'] ); ?>" data-days="<?php echo esc_attr( $trip['days'] ); ?>" data-price="<?php echo $trip['price'] > 0 ? esc_attr( $trip['price'] ) : ''; ?>">
	<div class="tour-card__media media">
		<?php
		if ( $thumb_id ) {
			echo wp_get_attachment_image( $thumb_id, 'medium_large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
		}
		?>
		<?php if ( $trip['destinations'] ) : ?>
			<div class="tour-card__tags">
				<?php foreach ( $trip['destinations'] as $destination ) : ?>
					<span><?php echo esc_html( $destination->name ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="tour-card__body">
		<?php if ( $trip['duration'] ) : ?>
			<div class="meta"><i class="las la-clock" aria-hidden="true"></i><span><?php echo esc_html( $trip['duration'] ); ?></span></div>
		<?php endif; ?>
		<h3><a href="<?php echo esc_url( $trip['url'] ); ?>"><?php echo esc_html( $trip['title'] ); ?></a></h3>
		<div class="tour-card__foot">
			<?php if ( $trip['price_text'] ) : ?>
				<div class="price"><small><?php esc_html_e( 'From', 'gowilds-child' ); ?></small><strong><?php echo esc_html( $trip['price_text'] ); ?></strong><small><?php esc_html_e( 'per adult', 'gowilds-child' ); ?></small></div>
			<?php else : ?>
				<div class="price price--request"><small><?php esc_html_e( 'Pricing', 'gowilds-child' ); ?></small><strong><?php esc_html_e( 'Price on request', 'gowilds-child' ); ?></strong></div>
			<?php endif; ?>
			<span class="tour-card__cta" aria-hidden="true"><?php esc_html_e( 'View safari', 'gowilds-child' ); ?> <i class="las la-arrow-right"></i></span>
		</div>
	</div>
</div>
