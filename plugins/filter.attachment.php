<?php
/**
 * @version  $Header$
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERATTACHMENT', 'filterattachment' );

global $gLibertySystem;

$pluginParams = array (
	// plugin title
	'title'                    => 'Attachment Tracker',
	// help page on bitweaver org that explains this plugin
	'help_page'                => 'Attachment Tracker Filter',
	// brief description of the plugin
	'description'              => 'Track attachment usage in content pages.',
	// should this plugin be active or not when loaded for the first time
	'auto_activate'            => FALSE,
	// type of plugin
	'plugin_type'              => FILTER_PLUGIN,
	// url to page with options for this plugin
	//'plugin_settings_url'      => LIBERTY_PKG_URL.'admin/filter_attachment.php',

	// various filter functions and when they are called
	'prestore_function'        => 'attachment_filter',
	'expunge_function'         => 'attachment_filter_expunge',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERATTACHMENT, $pluginParams );

/**
 * attachment_filter will find out what attachments are used where.
 * 
 * @param string $pString 
 * @param array $pFilterHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function attachment_filter( &$pString, &$pFilterHash ) {
	global $gLibertySystem, $gBitSystem;
	if( $gLibertySystem->isPluginActive( 'dataattachment' ) && @BitBase::verifyId( $pFilterHash['content_id'] )) {
		// make sure we have a blank slate in the db since we might have removed some {attachment}s in the content
		attachment_filter_expunge( NULL, $pFilterHash );

		preg_match_all( "#{(attachment|image|file)[^}]*\bid\s?=[\s'\"]*(\d+)#i", $pString, $matches );
		if( $count = count( $matches[0] )) {
			for( $i = 0; $i < $count; $i++ ) {
				// if we included this file using {image} or {file} we get the correct attachment_id of the file
				if( $matches[1][$i] != 'attachment' ) {
					$whereSql = "WHERE `content_id` = ?";
				} else {
					$whereSql = "WHERE `attachment_id` = ?";
				}
				$attachment_id = $gBitSystem->mDb->getOne( "SELECT `attachment_id` FROM `".BIT_DB_PREFIX."liberty_attachments` $whereSql", array( $matches[2][$i] ));

				if( @BitBase::verifyId( $attachment_id )) {
					$store = array(
						'content_id'    => $pFilterHash['content_id'],
						'attachment_id' => $attachment_id,
					);
					$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_attachment_usage", $store );
				}
			}
		}
	}
}

/**
 * attachment_filter_expunge 
 * 
 * @param string $pString 
 * @param array $pFilterHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function attachment_filter_expunge( $pString, &$pFilterHash ) {
	global $gBitSystem;
	if( @BitBase::verifyId( $pFilterHash['content_id'] )) {
		$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_attachment_usage` WHERE `content_id` = ?", array( $pFilterHash['content_id'] ));
	}
}

/**
 * attachment_filter_get_usage this function will return all content that uses a given attachment in their content
 * 
 * @param array $pAttachmentId Attachment ID of the attachment we want usage stats for
 * @access public
 * @return array of content that uses the attachment
 */
function attachment_filter_get_usage( $pAttachmentId ) {
	global $gBitSystem;
	$sql = "
		SELECT lc.`title`,lc.`content_id`
		FROM `".BIT_DB_PREFIX."liberty_attachment_usage` lau
			INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lau.`content_id` = lc.`content_id` )
		WHERE lau.`attachment_id` = ?";
	return $gBitSystem->mDb->getAll( $sql, array( $pAttachmentId ));
}
?>
