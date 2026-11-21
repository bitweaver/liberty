<?php
/**
 * PDF mime plugin settings form, included by liberty/admin/plugins.php?settings=mimepdfx
 * (the plugin declares it as 'plugin_settings_inc'). The dispatcher has
 * checked p_admin, and the ticket when 'plugin_settings' is posted.
 *
 * @package liberty
 * @subpackage plugins
 */
global $gBitSystem, $gBitSmarty, $gLibertySystem;

$feedback = array();

$pdfSettings = array(
	'mwconvert_path' => array(
		'label'   => 'Path to ImageMagick convert',
		'note'    => 'Path to the ImageMagick convert executable.',
		'type'    => 'text',
	),
	'pdf_thumbnails' => array(
		'label'   => 'PDF Upload Thumbnails',
		'note'    => 'Create thumbnails for PDF uploads.',
		'type'    => 'checkbox',
		'default' => 'y',
	),
);

if( function_exists( 'shell_exec' )) {
	$pdfSettings['mwconvert_path']['default'] =  shell_exec( 'which convert' );
} else {
	$feedback['error'] = "You can not execute binaries on your server. You can not make use of this plugin.";
}

$gBitSmarty->assign( 'pdfSettings', $pdfSettings );

if( !empty( $_REQUEST['plugin_settings'] )) {
	foreach( $pdfSettings as $item => $data ) {
		if( $data['type'] == 'checkbox' ) {
			simple_set_toggle( $item, LIBERTY_PKG_NAME );
		} elseif( $data['type'] == 'numeric' ) {
			simple_set_int( $item, LIBERTY_PKG_NAME );
		} else {
			simple_set_value( $item, LIBERTY_PKG_NAME );
		}
	}

	$feedback['success'] = tra( 'The plugin was successfully updated' );
}

$gBitSmarty->assign( 'feedback', $feedback );
$pluginSettingsTemplate = 'bitpackage:liberty/mime/pdf/admin.tpl';
$pluginSettingsTitle = tra( 'PDF Plugin Settings' );
