<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/filter.attachment.php,v 1.6 2008/11/09 09:08:55 squareing Exp $
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
	'requirement_function'     => 'attachment_filter_reqirements',
	'prestore_function'        => 'attachment_filter',
	'expunge_function'         => 'attachment_filter_expunge',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERATTACHMENT, $pluginParams );

/**
 * To use this plugin you need to create a table in your database such as:
 *
 * CREATE TABLE liberty_attachment_usage (
 *     content_id INT NOT NULL,
 *     attachment_id INT NOT NULL
 * );
 * ALTER TABLE liberty_attachment_usage ADD CONSTRAINT liberty_att_usage_content_ref FOREIGN KEY (content_id) REFERENCES liberty_content(content_id);
 * ALTER TABLE liberty_attachment_usage ADD CONSTRAINT liberty_att_usage_att_ref FOREIGN KEY (attachment_id) REFERENCES liberty_attachments(attachment_id);
 */

/**
 * attachment_filter_reqirements 
 * 
 * @param boolean $pInstall 
 * @access private
 * @return information hash
 */
function attachment_filter_reqirements( $pInstall = FALSE ) {
	global $gLibertySystem;
	$ret = array();
	if( $pInstall ) {
		$ret['schema'] = array(
			'tables' => array(
				'liberty_attachment_usage' => "
					content_id I4 NOTNULL,
					attachment_id I4 NOTNULL
					CONSTRAINT '
						, CONSTRAINT `lib_att_usage_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
						, CONSTRAINT `lib_att_usage_attachment_ref` FOREIGN KEY (`attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments`( `attachment_id` )
					'
				"
			),
			'indexes' => array(
				'lib_att_usage_content_idx' => array( 'table' => 'liberty_attachment_usage', 'cols' => 'content_id', 'opts' => NULL ),
				'lib_att_usage_attachment_idx' => array( 'table' => 'liberty_attachment_usage', 'cols' => 'attachment_id', 'opts' => NULL ),
			),
//			'sequences' => array(
//				'liberty_attachment_usage_id_seq' => array( 'start' => 1 ),
//			),
		);
	}

//	$ret['output']['important'][] = "This plugin will install a new table in your database as soon as you enable it.<br />If you don't want to use this plugin anymore, we recommend that you remove the 'liberty_attachment_usage' table from your database after you have disabled the plugin. You need to do this manually.";
	return $ret;
}

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
