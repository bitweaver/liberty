<?php
/**
 * @version  $Revision: 1.8 $
 * @package  liberty
 * @subpackage plugins_storage
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_TREASURY_FILE', 'treasury' );

$pluginParams = array (
	'load_function' => 'treasury_file_load',
	'description' => 'Allow better use of {attachment} plugin with treasury package. If you do not use Treasury, there is no point in activating this plugin.',
	'edit_label' => 'Treasury File',
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
		// fetch the correct content_id we can use to load the treasury item
		$query = "
			SELECT *
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
				INNER JOIN `".BIT_DB_PREFIX."treasury_item` tri ON( tri.`content_id` = la.`content_id` )
			WHERE la.`foreign_id` = ?";
		if( $row = $gBitSystem->mDb->getRow( $query, array( $pRow['foreign_id'] ))) {
			require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );
			require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'display_bytes' );
			$item = new TreasuryItem( NULL, $row['content_id'] );
			$item->load();
			$ret = $item->mInfo;
			$ret['prefs'] = $item->mPrefs;
			if( !empty( $ret['file_size'] )) {
				$ret['file_details'] = $ret['title']."<br /><small>(".$ret['mime_type']." ".smarty_modifier_display_bytes( $ret['file_size'] ).")</small>";
			}
		}
	}
	return( $ret );
}
?>
