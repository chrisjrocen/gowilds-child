<?php
/**
 * One trip in a WP Travel Engine listing (archive, destination and trip type pages, search results).
 * Shows the design's tour card in both the grid and the list view.
 *
 * @package gowilds-child
 */

defined( 'ABSPATH' ) || exit;

echo '<div class="category-trips-single">';
gowilds_child_tour_card( get_the_ID() );
echo '</div>';
