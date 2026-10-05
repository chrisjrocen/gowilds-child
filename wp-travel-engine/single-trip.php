<?php
/**
 * Tour page (single WP Travel Engine trip), laid out as in the design.
 *
 * Overrides the Gowilds / WP Travel Engine single-trip template because the design's markup
 * (photo mosaic, tabs, day accordion, enquiry-only price box, mobile Book bar) differs throughout.
 * All content comes from the trip editor; see inc/trips.php.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$trip        = gowilds_child_trip( get_the_ID() );
	$destination = $trip['destinations'] ? $trip['destinations'][0] : null;
	$photo_count = count( $trip['photos'] );
	$has_map     = $trip['map_image'] || $trip['map_iframe'];

	$tabs = array();
	if ( '' !== trim( wp_strip_all_tags( $trip['overview'] ) ) || $trip['highlights'] ) {
		$tabs['overview'] = __( 'Overview', 'gowilds-child' );
	}
	if ( $trip['itinerary'] ) {
		$tabs['itinerary'] = __( 'Itinerary', 'gowilds-child' );
	}
	if ( $trip['includes'] || $trip['excludes'] ) {
		$tabs['includes'] = __( 'Includes / Excludes', 'gowilds-child' );
	}
	if ( $has_map ) {
		$tabs['map'] = __( 'Map', 'gowilds-child' );
	}
	if ( $trip['faqs'] ) {
		$tabs['faq'] = __( 'FAQ', 'gowilds-child' );
	}
	// As in the design, the itinerary is the tab that is open first.
	$active = isset( $tabs['itinerary'] ) ? 'itinerary' : key( $tabs );

	$crumbs = function () use ( $destination ) {
		?>
		<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'gowilds-child' ); ?>">
			<ol class="crumbs crumbs--dark">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'gowilds-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>"><?php esc_html_e( 'Safaris', 'gowilds-child' ); ?></a></li>
				<li><span aria-current="page"><?php echo esc_html( $destination ? $destination->name : get_the_title() ); ?></span></li>
			</ol>
		</nav>
		<?php
	};

	$panel_attrs = function ( $id ) use ( $active ) {
		printf(
			'class="tabpanel" role="tabpanel" id="panel-%1$s" aria-labelledby="tab-%1$s" tabindex="0"%2$s',
			esc_attr( $id ),
			$id === $active ? '' : ' hidden'
		);
	};
	?>

	<div id="main" class="tour">

		<section class="tour-top wrap" style="padding-top:28px;display:flex;flex-direction:column;gap:20px">
			<div class="d-only"><?php $crumbs(); ?></div>

			<?php if ( $trip['photos'] ) : ?>
				<?php
				$lightbox_photos = array_map(
					function ( $photo ) {
						return array( 'src' => $photo['src'], 'alt' => $photo['alt'] );
					},
					$trip['photos']
				);
				?>
				<div class="gallery-wrap" data-gallery data-photos="<?php echo esc_attr( wp_json_encode( $lightbox_photos ) ); ?>">
					<div class="gallery gallery--<?php echo esc_attr( min( 5, $photo_count ) ); ?>" data-gallery-track>
						<?php foreach ( $trip['photos'] as $i => $photo ) : ?>
							<button class="gallery__item" type="button" data-lb="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: photo number, 2: total, 3: description */ __( 'Open photo %1$d of %2$d: %3$s', 'gowilds-child' ), $i + 1, $photo_count, $photo['alt'] ) ); ?>">
								<?php
								echo wp_get_attachment_image(
									$photo['id'],
									0 === $i ? 'large' : 'medium_large',
									false,
									0 === $i ? array( 'fetchpriority' => 'high', 'loading' => 'eager', 'alt' => $photo['alt'] ) : array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => $photo['alt'] )
								);
								?>
							</button>
						<?php endforeach; ?>
					</div>
					<?php if ( $photo_count > 1 ) : ?>
						<button class="gallery__all" type="button" data-lb="0"><i class="las la-images" aria-hidden="true"></i><?php echo esc_html( sprintf( /* translators: %d: number of photos */ __( 'View all photos (%d)', 'gowilds-child' ), $photo_count ) ); ?></button>
						<span class="gallery__count" aria-hidden="true"><i class="las la-images"></i><span data-gallery-count>1/<?php echo esc_html( $photo_count ); ?></span></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</section>

		<section class="tour-layout wrap">

			<div class="tour-head">
				<div class="m-only"><?php $crumbs(); ?></div>
				<h1><?php the_title(); ?></h1>
				<div class="tour-facts">
					<?php if ( $trip['duration'] ) : ?>
						<span><i class="las la-clock" aria-hidden="true"></i><?php echo esc_html( $trip['duration'] ); ?></span>
					<?php endif; ?>
					<?php if ( $trip['destinations'] ) : ?>
						<span><i class="las la-map-marker-alt" aria-hidden="true"></i><?php echo esc_html( implode( ', ', wp_list_pluck( $trip['destinations'], 'name' ) ) ); ?></span>
					<?php endif; ?>
					<?php if ( $trip['types'] ) : ?>
						<span><i class="las la-binoculars" aria-hidden="true"></i><?php foreach ( $trip['types'] as $type ) : ?><span class="pill"><?php echo esc_html( $type->name ); ?></span><?php endforeach; ?></span>
					<?php endif; ?>
					<?php if ( $trip['price_text'] ) : ?>
						<span class="fact-price"><i class="las la-tag" aria-hidden="true"></i><?php esc_html_e( 'Price from', 'gowilds-child' ); ?>&nbsp;<strong><?php echo esc_html( $trip['price_text'] ); ?></strong>&nbsp;<?php esc_html_e( 'per adult', 'gowilds-child' ); ?></span>
					<?php else : ?>
						<span class="fact-price"><i class="las la-tag" aria-hidden="true"></i><strong><?php esc_html_e( 'Price on request', 'gowilds-child' ); ?></strong></span>
					<?php endif; ?>
				</div>
			</div>

			<aside class="book" aria-label="<?php esc_attr_e( 'Book this safari', 'gowilds-child' ); ?>">
				<div class="book__card">
					<div class="book__from">
						<?php if ( $trip['price_text'] ) : ?>
							<small><?php esc_html_e( 'From', 'gowilds-child' ); ?></small>
							<span><strong><?php echo esc_html( $trip['price_text'] ); ?></strong><em><?php esc_html_e( 'per adult', 'gowilds-child' ); ?></em></span>
						<?php else : ?>
							<small><?php esc_html_e( 'Pricing', 'gowilds-child' ); ?></small>
							<span><strong class="book__request"><?php esc_html_e( 'Price on request', 'gowilds-child' ); ?></strong></span>
						<?php endif; ?>
					</div>
					<?php if ( $trip['note'] ) : ?>
						<p class="book__note"><?php echo esc_html( $trip['note'] ); ?></p>
					<?php endif; ?>
					<a class="btn btn-gold" href="<?php echo esc_url( $trip['plan_url'] ); ?>"><?php esc_html_e( 'Book This Expedition', 'gowilds-child' ); ?> <i class="las la-arrow-right" aria-hidden="true"></i></a>
					<a class="btn btn-green" href="<?php echo esc_url( AFOYO_WHATSAPP_URL ); ?>" target="_blank" rel="noopener"><i class="lab la-whatsapp" aria-hidden="true" style="color:var(--gold)"></i><?php esc_html_e( 'Chat on WhatsApp', 'gowilds-child' ); ?></a>
					<ul class="book__trust">
						<li><i class="las la-shield-alt" aria-hidden="true"></i><?php esc_html_e( 'No payment online', 'gowilds-child' ); ?></li>
						<li><i class="las la-reply" aria-hidden="true"></i><?php esc_html_e( 'Reply within 24 hours', 'gowilds-child' ); ?></li>
						<li><i class="las la-calendar-check" aria-hidden="true"></i><?php esc_html_e( 'Tailored to your dates', 'gowilds-child' ); ?></li>
					</ul>
				</div>
			</aside>

			<?php if ( $tabs ) : ?>
				<div class="tour-tabs" data-tabs>
					<div class="tablist" role="tablist" aria-label="<?php esc_attr_e( 'Safari details', 'gowilds-child' ); ?>">
						<?php foreach ( $tabs as $id => $label ) : ?>
							<button type="button" role="tab" id="tab-<?php echo esc_attr( $id ); ?>" aria-controls="panel-<?php echo esc_attr( $id ); ?>" aria-selected="<?php echo $id === $active ? 'true' : 'false'; ?>" tabindex="<?php echo $id === $active ? '0' : '-1'; ?>"><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>

					<?php if ( isset( $tabs['overview'] ) ) : ?>
						<div <?php $panel_attrs( 'overview' ); ?>>
							<h2><?php esc_html_e( 'Overview', 'gowilds-child' ); ?></h2>
							<?php echo wp_kses_post( wpautop( $trip['overview'] ) ); ?>
							<?php if ( $trip['highlights'] ) : ?>
								<h3><?php esc_html_e( 'Highlights', 'gowilds-child' ); ?></h3>
								<ul class="check-list highlights">
									<?php foreach ( $trip['highlights'] as $highlight ) : ?>
										<li><i class="las la-check-circle" aria-hidden="true"></i><?php echo esc_html( $highlight ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( isset( $tabs['itinerary'] ) ) : ?>
						<div <?php $panel_attrs( 'itinerary' ); ?>>
							<div class="itin-head">
								<h2><?php esc_html_e( 'Itinerary', 'gowilds-child' ); ?></h2>
								<?php if ( count( $trip['itinerary'] ) > 1 ) : ?>
									<button type="button" data-expand-all="days" data-label-expand="<?php esc_attr_e( 'Expand all', 'gowilds-child' ); ?>" data-label-collapse="<?php esc_attr_e( 'Collapse all', 'gowilds-child' ); ?>"><i class="las la-expand-arrows-alt" aria-hidden="true"></i><span><?php esc_html_e( 'Expand all', 'gowilds-child' ); ?></span></button>
								<?php endif; ?>
							</div>
							<div class="days" id="days" data-acc>
								<?php foreach ( $trip['itinerary'] as $n => $day ) : ?>
									<?php
									$open  = 0 === $n;
									$parts = gowilds_child_day_parts( $day['content'] );
									// "Day 3" => small "Day", big "3"; "Days 5 & 6" => "Days", "5–6".
									$label = '' !== trim( $day['label'] ) ? trim( $day['label'] ) : sprintf( 'Day %d', $n + 1 );
									$word  = trim( preg_replace( '/[\d\s&,\-–]+.*$/u', '', $label ) );
									$num   = trim( preg_replace( '/\s*(?:&|and|to|-)\s*/i', '–', trim( substr( $label, strlen( $word ) ) ) ) );
									?>
									<div class="acc day<?php echo $open ? ' is-open' : ''; ?>">
										<h3>
											<button class="acc__btn" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="day-<?php echo esc_attr( $n + 1 ); ?>">
												<span class="day__badge" aria-hidden="true"><small><?php echo esc_html( $word ); ?></small><b><?php echo esc_html( $num ); ?></b></span>
												<span class="day__title"><?php echo esc_html( $label . ': ' . $day['title'] ); ?></span>
												<i class="las la-plus acc__icon" aria-hidden="true"></i>
											</button>
										</h3>
										<div class="acc__panel" id="day-<?php echo esc_attr( $n + 1 ); ?>"<?php echo $open ? '' : ' hidden'; ?>>
											<?php echo wp_kses_post( wpautop( $parts['html'] ) ); ?>
											<?php if ( $parts['accommodation'] || $parts['meal'] ) : ?>
												<div class="day__meta">
													<?php if ( $parts['accommodation'] ) : ?>
														<span><i class="las la-bed" aria-hidden="true"></i><strong><?php esc_html_e( 'Accommodation:', 'gowilds-child' ); ?></strong> <?php echo esc_html( $parts['accommodation'] ); ?></span>
													<?php endif; ?>
													<?php if ( $parts['meal'] ) : ?>
														<span><i class="las la-utensils" aria-hidden="true"></i><strong><?php esc_html_e( 'Meal plan:', 'gowilds-child' ); ?></strong> <?php echo esc_html( $parts['meal'] ); ?></span>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( isset( $tabs['includes'] ) ) : ?>
						<div <?php $panel_attrs( 'includes' ); ?>>
							<h2 class="sr-only"><?php esc_html_e( 'Includes and excludes', 'gowilds-child' ); ?></h2>
							<div class="inc-grid">
								<?php if ( $trip['includes'] ) : ?>
									<div class="inc">
										<h3><?php esc_html_e( 'Includes', 'gowilds-child' ); ?></h3>
										<ul>
											<?php foreach ( $trip['includes'] as $line ) : ?>
												<li><i class="las la-check" aria-hidden="true"></i><?php echo esc_html( $line ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>
								<?php if ( $trip['excludes'] ) : ?>
									<div class="inc inc--ex">
										<h3><?php esc_html_e( 'Excludes', 'gowilds-child' ); ?></h3>
										<ul>
											<?php foreach ( $trip['excludes'] as $line ) : ?>
												<li><i class="las la-times" aria-hidden="true"></i><?php echo esc_html( $line ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( isset( $tabs['map'] ) ) : ?>
						<div <?php $panel_attrs( 'map' ); ?>>
							<h2><?php esc_html_e( 'Route map', 'gowilds-child' ); ?></h2>
							<figure class="route-map">
								<?php
								if ( $trip['map_image'] ) {
									echo wp_get_attachment_image( $trip['map_image'], 'large', false, array( 'loading' => 'lazy' ) );
								} else {
									echo wp_kses(
										$trip['map_iframe'],
										array( 'iframe' => array( 'src' => true, 'width' => true, 'height' => true, 'style' => true, 'loading' => true, 'allowfullscreen' => true, 'referrerpolicy' => true, 'title' => true ) )
									);
								}
								?>
								<figcaption><?php esc_html_e( 'Final routing is confirmed with you.', 'gowilds-child' ); ?></figcaption>
							</figure>
						</div>
					<?php endif; ?>

					<?php if ( isset( $tabs['faq'] ) ) : ?>
						<div <?php $panel_attrs( 'faq' ); ?>>
							<h2><?php esc_html_e( 'Questions about this safari', 'gowilds-child' ); ?></h2>
							<div class="faq-list" data-acc data-single>
								<?php foreach ( $trip['faqs'] as $n => $faq ) : ?>
									<div class="acc<?php echo 0 === $n ? ' is-open' : ''; ?>">
										<h3><button class="acc__btn" type="button" aria-expanded="<?php echo 0 === $n ? 'true' : 'false'; ?>" aria-controls="tfaq-<?php echo esc_attr( $n ); ?>"><span><?php echo esc_html( $faq['question'] ); ?></span><i class="las la-plus acc__icon" aria-hidden="true"></i></button></h3>
										<div class="acc__panel" id="tfaq-<?php echo esc_attr( $n ); ?>"<?php echo 0 === $n ? '' : ' hidden'; ?>><?php echo wp_kses_post( wpautop( $faq['answer'] ) ); ?></div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</section>

		<?php
		// More safaris in the same country (up to three).
		$related = $destination ? get_posts(
			array(
				'post_type'    => 'trip',
				'numberposts'  => 3,
				'post__not_in' => array( get_the_ID() ),
				'orderby'      => 'meta_value_num',
				'meta_key'     => 'wp_travel_engine_setting_trip_duration',
				'order'        => 'ASC',
				'fields'       => 'ids',
				'tax_query'    => array( array( 'taxonomy' => 'destination', 'field' => 'term_id', 'terms' => $destination->term_id ) ),
			)
		) : array();
		$listing = $destination ? get_page_by_path( $destination->slug . '-tours' ) : null;
		?>
		<?php if ( $related ) : ?>
			<section class="sec stack" style="gap:40px;padding-bottom:130px">
				<div class="sec-head sec-head--split wrap">
					<div>
						<span class="eyebrow d-only"><?php esc_html_e( 'Keep exploring', 'gowilds-child' ); ?></span>
						<h2 class="h2"><?php echo esc_html( sprintf( /* translators: %s: country */ __( 'More safaris in %s', 'gowilds-child' ), $destination->name ) ); ?></h2>
					</div>
					<?php if ( $listing && 'publish' === $listing->post_status ) : ?>
						<a class="link-arrow d-only" href="<?php echo esc_url( get_permalink( $listing ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s: country */ __( 'All %s safaris', 'gowilds-child' ), $destination->name ) ); ?> <i class="las la-arrow-right" aria-hidden="true"></i></a>
					<?php endif; ?>
				</div>
				<div class="wrap">
					<div class="grid-cards rail">
						<?php
						foreach ( $related as $related_id ) {
							gowilds_child_tour_card( $related_id );
						}
						?>
					</div>
				</div>
			</section>
		<?php else : ?>
			<div style="padding-bottom:130px"></div>
		<?php endif; ?>

	</div>

	<?php
	// CTA band: the saved Elementor template "Afoyo: CTA band" (edit it under Templates > Saved Templates).
	$cta = get_posts( array( 'post_type' => 'elementor_library', 'meta_key' => '_afoyo_build_key', 'meta_value' => 'tpl-cta-band', 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $cta && class_exists( '\Elementor\Plugin' ) ) {
		echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $cta[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
	}
	?>

	<div class="bookbar" aria-label="<?php esc_attr_e( 'Book this safari', 'gowilds-child' ); ?>">
		<div>
			<?php if ( $trip['price_text'] ) : ?>
				<small><?php esc_html_e( 'From', 'gowilds-child' ); ?></small><strong><?php echo esc_html( $trip['price_text'] ); ?></strong>
			<?php else : ?>
				<small><?php esc_html_e( 'Pricing', 'gowilds-child' ); ?></small><strong><?php esc_html_e( 'On request', 'gowilds-child' ); ?></strong>
			<?php endif; ?>
		</div>
		<a class="btn btn-gold" href="<?php echo esc_url( $trip['plan_url'] ); ?>"><?php esc_html_e( 'Book This Expedition', 'gowilds-child' ); ?></a>
	</div>

	<?php if ( $trip['photos'] ) : ?>
		<div class="lightbox" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Photo gallery', 'gowilds-child' ); ?>" data-lightbox hidden>
			<div class="lightbox__top"><span class="lightbox__cap" data-lb-cap aria-live="polite"></span><button class="icon-btn" type="button" aria-label="<?php esc_attr_e( 'Close gallery', 'gowilds-child' ); ?>" data-lb-close><i class="las la-times" aria-hidden="true"></i></button></div>
			<div class="lightbox__stage">
				<button class="icon-btn" type="button" aria-label="<?php esc_attr_e( 'Previous photo', 'gowilds-child' ); ?>" data-lb-prev><i class="las la-arrow-left" aria-hidden="true"></i></button>
				<div class="lightbox__img"><img src="" alt="" data-lb-img></div>
				<button class="icon-btn" type="button" aria-label="<?php esc_attr_e( 'Next photo', 'gowilds-child' ); ?>" data-lb-next><i class="las la-arrow-right" aria-hidden="true"></i></button>
			</div>
		</div>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
