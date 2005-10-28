<?php
/**
 * @version  $Revision: 1.1.1.1.2.8 $
 * @package  liberty
 * @subpackage plugins_storage
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_LULU', 'lulu' );

$pluginParams = array ( 
	'store_function' => 'lulu_store',
	'load_function' => 'lulu_load',
	'expunge_function' => 'lulu_expunge',
	'verify_function' => 'lulu_verify',
	'description' => 'Lulu Content ID',
	'plugin_type' => STORAGE_PLUGIN,
	'auto_activate' => FALSE,
	'edit_label' => 'Enter <a href="http://www.lulu.com">Lulu</a> Content ID',
	'edit_field' => '<input type="text" name="STORAGE['.PLUGIN_GUID_LULU.']" size="40" /> 
		<a href="http://www.lulu.com">{biticon ipackage=liberty iname=lulu iexplain=lulu}</a>',
	'edit_help' => 'Enter the ID of the Lulu item you would like to include.<br />The ID is the number that is displayed at the end of the URL in the browsers address bar, when viewing the image of choice.'
);

$gLibertySystem->registerPlugin( PLUGIN_GUID_LULU, $pluginParams );

/*
	the lulu plugin doesn't need to do anything extra besides store the lulu image_id
	in tiki_attachment.foreign_id - which has already happened in LibertyAttachable::store().
	So, we don't need to even define this function
	function lulu_store() {
	}
*/
	function lulu_verify( &$pStoreRow ) {
		if( !empty( $pStoreRow['upload'] ) ) {
			$pStoreRow['foreign_id'] = $pStoreRow['upload'];
		}
		return( !empty( $pStoreRow['foreign_id'] ) );
	}
	
	function lulu_expunge($pAttachmentId) {
		global $gBitSystem;
		
		$sql = "DELETE FROM `".BIT_DB_PREFIX."tiki_attachments` WHERE `attachment_id` = ?";
		$gBitSystem->mDb->query($sql, array($pAttachmentId));
		
		return TRUE;
	}

	function lulu_store() {
	}

	function lulu_load( $pRow ) {
		$ret = array();
		if( $pRow['foreign_id'] ) {
			$cidGroup = ((int)($pRow['foreign_id'] / 1000)) * 1000;
			$ret['source_url'] = 'http://www.lulu.com/content/'.$pRow['foreign_id'];
			$ret['thumbnail_url']['small']  = 'http://www.lulu.com/author/display_thumbnail.php?fSize=promo_&fCID='.$pRow['foreign_id'];
			$ret['thumbnail_url']['medium'] = 'http://www.lulu.com/author/display_thumbnail.php?fSize=detail_&fCID='.$pRow['foreign_id'];
			$ret['thumbnail_url']['large']  = 'http://www.lulu.com/author/display_thumbnail.php?fSize=320_&fCID='.$pRow['foreign_id'];
			$ret['attachment_id'] = $pRow['attachment_id'];
			$ret['wiki_plugin_link'] = "{attachment id=".$ret['attachment_id']."}";
		}
		return $ret;
	}
?>
