<?php
require_once( '../../../kernel/includes/setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );
/* We need DEFAULT_ACCEPTABLE_TAGS from here. */
include_once( LIBERTY_PKG_PATH.'plugins/filter.simplepurifier.php');
$gBitSystem->verifyPermission( 'p_admin' );

if( !empty( $_REQUEST['apply'] )) {
	$errors = array();
	if( $_REQUEST['approved_html_tags'] != DEFAULT_ACCEPTABLE_TAGS ) {
		$tags = preg_replace( '/\s/', '', $_REQUEST['approved_html_tags'] );
		$lastAngle = strrpos( $tags, '>' ) + 1;
		if( strlen( $tags ) > 250 || ($lastAngle < strlen( $tags ))) {
			$tags = substr( $tags, 0, 250 );
			$tags = substr( $tags, 0, $lastAngle );
			$errors['warning'] = 'The approved tags list has been shortened. You can only have 250 characters for approved tags.';
		}
		$gBitSystem->storeConfig('approved_html_tags', $tags , LIBERTY_PKG_NAME );
	}
	$gBitSmarty->assignByRef( 'errors', $errors );

	if( !empty($_REQUEST['approved_html_tags'] )) {
		$tags = preg_replace( '/\s/', '', $_REQUEST['approved_html_tags'] );
		if( strlen( $tags ) > 250 ) {
			$tags = substr( $tags, 0, 250 );
			$errors['blacklist'] = 'The approved tags list has been shortened. You can only have 250 characters for approved tags.';
		}
		$gBitSystem->storeConfig('approved_html_tags', $tags , LIBERTY_PKG_NAME );
	}
}

$tags = $gBitSystem->getConfig( 'approved_html_tags', DEFAULT_ACCEPTABLE_TAGS );

$gBitSmarty->assign( 'approved_html_tags', $tags );

$gBitSystem->display( 'bitpackage:liberty/plugins/filter_simplepurifier_admin.tpl', 'Simple HTML Purifier' , array( 'display_mode' => 'admin' ));
?>
