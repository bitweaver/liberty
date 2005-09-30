<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'bit_p_admin' );

$commentSettings = array(
	"comments_reorganise_page_layout" => array(
		'label' => 'Position Comments at top of page',
		'note' => 'When posting a comment, comments are moved to the top of the page. This can be very disorienting and is only recommended when your site uses comments extensively.',
		'page' => '',
	),
	"comments_display_option_bar" => array(
		'label' => 'Display Comments option bar',
		'note' => 'Display an option bar above comments to specify how they should be sorted and how they should be displayed. Useful if your site uses comments extensively.',
		'page' => '',
	),
	"comments_display_expanded" => array(
		'label' => 'Expand Comments',
		'note' => 'When users first visit your site, comments can be hidden or displayed by default.',
		'page' => '',
	),
);
$gBitSmarty->assign( 'commentSettings', $commentSettings );

if( !empty( $_REQUEST['change_prefs'] ) ) {


	foreach( array_keys( $commentSettings ) as $item ) {
		simple_set_toggle( $item );
	}

	$formValues = array('comments_per_page', 'comments_default_ordering', 'comments_default_display_mode' );
	foreach( $formValues as $item ) {
		simple_set_value( $item );
	}


}

$gBitSystem->display( 'bitpackage:liberty/admin_comments.tpl');
?>
