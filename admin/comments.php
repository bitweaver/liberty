<?php
require_once( '../../kernel/includes/setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

$commentSettings = array(
	// when this is enabled, we need to add a spam prevention method
	"comments_auto_show_form" => array(
		'label' => 'Always show comment form',
		'note' => 'Check this if you want to show the comment form automatically instead of the <em>Add Comment</em> button first.',
		'page' => '',
	),
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
	"comments_ajax" => array(
		'label' => 'Use AJAX to Post Comments',
		'note' => 'Comments are posted dynamically with javascript (js) - this requires a one time larger download of a js library, but then comments can be posted without page refresh.',
		'page' => '',
	),
//	"comments_display_expanded" => array(
//		'label' => 'Expand Comments',
//		'note' => 'When users first visit your site, comments can be hidden or displayed by default.',
//		'page' => '',
//	),
);
$gBitSmarty->assign( 'commentSettings', $commentSettings );

if( !empty( $_REQUEST['change_prefs'] ) ) {
	foreach( array_keys( $commentSettings ) as $item ) {
		simple_set_toggle( $item, LIBERTY_PKG_NAME );
	}

	$formValues = array('comments_edit_minutes', 'comments_per_page', 'comments_default_ordering', 'comments_default_display_mode' , 'comments_default_post_lines');
	foreach( $formValues as $item ) {
		simple_set_value( $item, LIBERTY_PKG_NAME );
	}
}

$gBitSystem->display( 'bitpackage:liberty/admin_comments.tpl', tra( 'Comment Settings' ) , array( 'display_mode' => 'admin' ));
?>
