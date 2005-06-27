<?php
/**
 * Management of Liberty Content
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.1 $
 * @package  Liberty
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Authors: spider <spider@steelsun.com>
// +----------------------------------------------------------------------+
//
// $Id: LibertyBase.php,v 1.1.1.1.2.1 2005/06/27 10:08:41 lsces Exp $

require_once( KERNEL_PKG_PATH.'BitBase.php' );

/**
 * Virtual base class (as much as one can have such things in PHP) for all
 * derived bitweaver classes that manage content.
 *
 * @abstract
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.1 $
 * @package  Liberty
 * @subpackage LibertyBase
 */
class LibertyBase extends BitBase {

	function LibertyBase () {
		// we need to init our mDb early
		BitBase::BitBase();
	}

	/**
	* Given a content_id, this will return and object of the proper type
	* @param pConId content_id of the object to be returned
	* @param pGuid optional content_type_guid of pConId. This will save a select if you happen to have this info. If not, this method will look it up for you.
	* @returns an object of the appropriate content type class
	*/
	function getLibertyObject( $pContentId, $pContentGuid=NULL ) {
		$ret = NULL;
		global $gLibertySystem;

		if( BitBase::verifyId( $pContentId ) ) {
			if( empty( $pContentGuid ) ) {
				$pContentGuid = $gLibertySystem->GetOne( "SELECT `content_type_guid` FROM `".BIT_DB_PREFIX."tiki_content` WHERE `content_id`=?", array( $pContentId ) );
			}
			if( !empty( $pContentGuid) && isset( $gLibertySystem->mContentTypes[$pContentGuid] ) ) {
				$type = $gLibertySystem->mContentTypes[$pContentGuid];
				require_once( constant( strtoupper( $type['handler_package'] ).'_PKG_PATH' ).$type['handler_file'] );
				$ret = new $type['handler_class']( NULL, $pContentId );
				$ret->load();
			} else {
				print "UNHANDLED ERROR. UNKNOWN CONTENT: $pContentId -> $pContentGuid";
			}
		}
		return $ret;
	}
}

?>
