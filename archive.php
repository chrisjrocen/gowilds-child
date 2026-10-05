<?php
/**
 * Post archives (category, tag, date, author, search): banner, category tabs, card grid.
 * Other archives keep the parent theme's template.
 *
 * @package gowilds-child
 */

global $wp_query;

if ( ! is_search() && ! is_category() && ! is_tag() && ! is_date() && ! is_author() && ! is_home() ) {
	require get_template_directory() . '/archive.php';
	return;
}

get_header();

$current = is_category() ? get_queried_object()->slug : '';
$title   = is_search()
	? sprintf( /* translators: %s: search words */ __( 'Search: %s', 'gowilds-child' ), get_search_query() )
	: wp_strip_all_tags( get_the_archive_title() );
if ( is_category() || is_tag() ) {
	$title = single_term_title( '', false );
}

gowilds_child_banner(
	array(
		'title'  => $title,
		'intro'  => is_category() ? wp_strip_all_tags( category_description() ) : '',
		'size'   => 'short',
		'crumbs' => array( array( __( 'Safari Blog', 'gowilds-child' ), home_url( '/blogs/' ) ) ),
	)
);
?>
<div id="main">
	<?php gowilds_child_post_grid( $wp_query, $current ); ?>
</div>
<?php
get_footer();
