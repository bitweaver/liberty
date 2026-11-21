<?php
/**
 * Simple HTML Purifier filter settings form, included by liberty/admin/plugins.php?settings=filtersimplepure
 * (the plugin declares it as 'plugin_settings_inc'). The dispatcher has
 * checked p_admin, and the ticket when 'plugin_settings' is posted.
 *
 * @package liberty
 * @subpackage plugins
 */
global $gBitSystem, $gBitSmarty, $gLibertySystem;

/* We need DEFAULT_ACCEPTABLE_TAGS from here. */
include_once( LIBERTY_PKG_PATH.'plugins/filter.simplepurifier.php');

if( !empty( $_REQUEST['plugin_settings'] )) {
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

$pluginSettingsTemplate = 'bitpackage:liberty/plugins/filter_simplepurifier_admin.tpl';
$pluginSettingsTitle = 'Simple HTML Purifier';
