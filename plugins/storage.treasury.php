<?php
/**
 * @version  $Revision: 1.1 $
 * @package  liberty
 * @subpackage plugins_storage
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_TREASURY_FILE', 'treasury' );

$pluginParams = array (
	'load_function' => 'treasury_file_load',
	'description' => 'Allow better us of {attachment} plugin with treasury package.',
	'plugin_type' => STORAGE_PLUGIN,
	'auto_activate' => TRUE,
);

global $gLibertySystem;
$gLibertySystem->registerPlugin( PLUGIN_GUID_TREASURY_FILE, $pluginParams );

/**
 * loads files that have been uploaded by treasury - this is basically the same as the generic bit_file plugin, but it gets some more data for {attachment} usage
 * 
 * @param array $pRow Row of data from attachment table handed to us by liberty
 * @access public
 * @return TRUE on success, FALSE on failure
 */
function treasury_file_load( $pRow ) {
	global $gBitSystem, $gBitSmarty;
	$ret = NULL;
	if( @BitBase::verifyId( $pRow['content_id'] ) ) {
		require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );
		require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'kbsize' );
		$ti = new TreasuryItem( $pRow['content_id'] );
		$ti->load();
		$ret = $ti->mInfo;
		$ret['file_details'] = $ret['title']."<br /><small>(".$ret['mime_type']." ".smarty_modifier_kbsize( $ret['file_size'] ).")</small>";
	}
	return( $ret );
}
?>
