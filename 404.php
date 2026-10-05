<?php
/**
 * 404 page, laid out as in the design.
 * Heading, text and first button label come from Theme Options > 404 Page.
 *
 * @package gowilds-child
 */

$title     = gowilds_get_option( 'nfpage_title', '' );
$desc      = gowilds_get_option( 'nfpage_desc', '' );
$btn_title = gowilds_get_option( 'nfpage_btn_title', '' );
$btn_link  = gowilds_get_option( 'nfpage_btn_link', '' );

get_header();
?>
<div id="content">
	<section class="notfound wrap">
		<span class="eyebrow"><?php esc_html_e( 'Error 404', 'gowilds-child' ); ?></span>
		<h1><?php echo esc_html( $title ? $title : __( "We couldn't find that trail", 'gowilds-child' ) ); ?></h1>
		<p><?php echo esc_html( $desc ? $desc : __( "The page you were looking for has moved or doesn't exist yet. Try one of these instead.", 'gowilds-child' ) ); ?></p>
		<ul>
			<li><a class="btn btn-gold" href="<?php echo esc_url( $btn_link ? $btn_link : home_url( '/' ) ); ?>"><?php echo esc_html( $btn_title ? $btn_title : __( 'Back to home', 'gowilds-child' ) ); ?></a></li>
			<li><a class="btn btn-outline" href="<?php echo esc_url( home_url( '/safaris/' ) ); ?>"><?php esc_html_e( 'Browse safaris', 'gowilds-child' ); ?></a></li>
			<li><a class="btn btn-outline" href="<?php echo esc_url( home_url( '/plan-your-safari/' ) ); ?>"><?php esc_html_e( 'Plan your safari', 'gowilds-child' ); ?></a></li>
		</ul>
	</section>
</div>
<?php
get_footer();
