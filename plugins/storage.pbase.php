<?php
/**
 * @version  $Revision: 1.1.1.1.2.4 $
 * @package  liberty
 * @subpackage plugins_storage
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_PBASE', 'pbase' );

$pluginParams = array ( 
	'store_function' => 'pbase_store',
	'load_function' => 'pbase_load',
	'verify_function' => 'pbase_verify',
	'expunge_function' => 'pbase_expunge',
	'description' => 'PBase Image ID',
	'plugin_type' => STORAGE_PLUGIN,
	'auto_activate' => FALSE,
	'edit_label' => 'Enter <a href="http://www.pbase.com">PBase</a> Image ID',
	'edit_field' => '<input type="text" name="STORAGE['.PLUGIN_GUID_PBASE.']" size="40" /> 
		<a href="http://www.pbase.com">{biticon ipackage=liberty iname=pbase iexplain=pbase}</a>',
	'edit_help' => 'Enter the ID of the image you would like to include.<br />The ID is the number that is displayed at the end of the URL in the browsers address bar, when viewing the image of choice.'
);

$gLibertySystem->registerPlugin( PLUGIN_GUID_PBASE, $pluginParams );

/*
	the pbase plugin doesn't need to do anything extra besides store the pbase image_id
	in tiki_attachment.foreign_id - which has already happened in LibertyAttachable::store().
	So, we don't need to even define this function
	function pbase_store() {
	}
*/
	function pbase_verify( &$pStoreRow ) {
		if( !empty( $pStoreRow['upload'] ) ) {
			$pStoreRow['foreign_id'] = $pStoreRow['upload'];
		}
		$pStoreRow['plugin_guid'] = PLUGIN_GUID_PBASE;
		return( empty( $pStoreRow['foreign_id'] ) ? FALSE : TRUE );
	}
	
	function pbase_expunge() {
		return TRUE;
	}

	function pbase_store() {
		return TRUE;
	}

	function pbase_load( $pRow ) {
		$ret = array();
		if( $pRow['foreign_id'] ) {
			$ret['source_url'] = 'http://www.pbase.com/image/'.$pRow['foreign_id'];
			$ret['thumbnail_url']['small'] = 'http://www.pbase.com/image/'.$pRow['foreign_id'].'/small.jpg';
			$ret['thumbnail_url']['medium'] = 'http://www.pbase.com/image/'.$pRow['foreign_id'].'/medium.jpg';
			$ret['thumbnail_url']['large'] = 'http://www.pbase.com/image/'.$pRow['foreign_id'].'/large.jpg';
		}
		return $ret;
	}

?>
