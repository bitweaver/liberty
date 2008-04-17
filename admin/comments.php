<?php
require_once( '../../bit_setup_inc.php' );
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

$commentModerationSettings = array();

if ( $gBitSystem->isPackageActive('moderation') ){
	$commentModerationSettings = array(
		"comments_allow_moderation" => array(
			'label' => 'Allow admins to moderate comments',
			'note' => 'Checking this allows users with the permission to edit comments the ability to force moderation on comment posts. When comments are moderated they are automatically hidden until approved by a moderator. This is opt in, meaning you can limit the moderation requirement on a content by content basis.',
			'page' => '',
		),
		"comments_allow_owner_moderation" => array(
			'label' => 'Allow content creators to moderate comments on their content',
			'note' => 'This is similar to allowing admins to moderate comments, but this lets the creator of a content item to require and moderate the comments on the things they create. Administrators will also be able to admin those comments.',
			'page' => '',
		),
		"comments_moderate_all" => array(
			'label' => 'Require moderation of all comments',
			'note' => 'This forces all comments to be held for moderation before being published. We recommend this only if you are have extensive problems with spam or malicious comments; on high traffic sites this features requires a lot of hands on work to read all comments',
			'page' => '',
		),
	);

	$gBitSmarty->assign( 'commentModerationSettings', $commentModerationSettings );
}

if( !empty( $_REQUEST['change_prefs'] ) ) {
	$commentOptions = array_merge( $commentSettings, $commentModerationSettings );
	foreach( array_keys( $commentOptions ) as $item ) {
		simple_set_toggle( $item, LIBERTY_PKG_NAME );
	}

	$formValues = array('comments_per_page', 'comments_default_ordering', 'comments_default_display_mode' , 'comments_default_post_lines');
	foreach( $formValues as $item ) {
		simple_set_value( $item, LIBERTY_PKG_NAME );
	}
}

$gBitSystem->display( 'bitpackage:liberty/admin_comments.tpl', tra( 'Comment Settings' ) );
?>
