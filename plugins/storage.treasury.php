<?php
/**
 * @version  $Revision: 1.13 $
 * @package  liberty
 * @subpackage plugins_storage
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_TREASURY_FILE', 'treasury' );

$pluginParams = array (
	'load_function'    => 'treasury_file_load',
	'expunge_function' => 'treasury_file_expunge',
	'description'      => 'Allow better use of {attachment} plugin with treasury package. It provides file information like what type of file it is and its size when you include it in a page.<br />If you do not use Treasury, there is no point in activating this plugin.',
	'edit_label'       => 'Treasury File',
	'plugin_type'      => STORAGE_PLUGIN,
	'auto_activate'    => TRUE,
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
	if( @BitBase::verifyId( $pRow['foreign_id'] ) ) {
		// fetch the correct content_id we can use to load the treasury item
		$query = "
			SELECT *
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
				INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON (lf.`file_id` = la.`foreign_id`)
				INNER JOIN `".BIT_DB_PREFIX."liberty_attachments_map` lam ON (la.`attachment_id` = lam.`attachment_id`)
				INNER JOIN `".BIT_DB_PREFIX."treasury_item` tri ON( tri.`content_id` = lam.`content_id` )
			WHERE la.`foreign_id` = ? AND la.`attachment_plugin_guid` = ?";
		if( $ret = $gBitSystem->mDb->getRow( $query, array( $pRow['foreign_id'], PLUGIN_GUID_TREASURY_FILE ))) {
			if( $gBitSystem->isPackageActive( 'treasury' )) {
				require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );
				require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'display_bytes' );
				$item = new TreasuryItem( NULL, $ret['content_id'] );
				$item->load();
				$ret = $item->mInfo;
				$ret['prefs'] = $item->mPrefs;
				if( !empty( $ret['file_size'] )) {
					$ret['file_details'] = $ret['title']."<br /><small>(".$ret['mime_type']." ".smarty_modifier_display_bytes( $ret['file_size'] ).")</small>";
				}
			} else {
				$ret['thumbnail_url'] = liberty_fetch_thumbnails( $ret['storage_path'] );
				$ret['filename'] = substr( $ret['storage_path'], strrpos($ret['storage_path'], '/')+1);
				$ret['source_url'] = BIT_ROOT_URL.str_replace( '+', '%20', str_replace( '%2F', '/', urlencode( $ret['storage_path'] ) ) );
				$ret['wiki_plugin_link'] = "{attachment id=".$ret['attachment_id']."}";
			}
		}
	}
	return( $ret );
}

function treasury_file_expunge( $pAttachmentId ) {
	global $gBitSystem;
	$ret = FALSE;

	if( @BitBase::verifyId( $pAttachmentId )) {
		$sql = "SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_attachments_map` WHERE `attachment_id` = ?";
		if( $contentId = $gBitSystem->mDb->getOne( $sql, array( $pAttachmentId ))) {
			$ti = new TreasuryItem( NULL, $contentId );
			if( $ti->load() && $ti->expunge() ) {
				$ret = TRUE;
			}
		}
	}
	return $ret;
}
?>
