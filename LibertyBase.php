<?php
/**
 * Base class for Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyBase.php,v 1.1.1.1.2.11 2005/08/29 21:51:48 spiderr Exp $
 * @author   spider <spider@steelsun.com>
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

/**
 * required setup
 */
require_once( KERNEL_PKG_PATH.'BitBase.php' );

/**
 * Virtual base class (as much as one can have such things in PHP) for all
 * derived bitweaver classes that manage content.
 *
 * @package liberty
 */
class LibertyBase extends BitBase {

	/**
	 * Constructor building on BitBase object
	 *
	 * Object need to init the database connection early
	 * Database will be linked via a previously activated BitDb object
	 * which will provide the mDb pointer to that database
	 */
	function LibertyBase () {
		BitBase::BitBase();
	}

	/**
	 * Given a content_id, this will return and object of the proper type
	 *
	 * @param integer content_id of the object to be returned
	 * @param string optional content_type_guid of pConId. This will save a select if you happen to have this info. If not, this method will look it up for you.
	 * @returns object of the appropriate content type class
	 */
	function getLibertyObject( $pContentId, $pContentGuid=NULL ) {
		$ret = NULL;
		global $gLibertySystem;

		if( BitBase::verifyId( $pContentId ) ) {
			if( empty( $pContentGuid ) ) {
				$pContentGuid = $gLibertySystem->mDb->getOne( "SELECT `content_type_guid` FROM `".BIT_DB_PREFIX."tiki_content` WHERE `content_id`=?", array( $pContentId ) );
			}
			if( !empty( $pContentGuid) && isset( $gLibertySystem->mContentTypes[$pContentGuid] ) ) {
				$type = $gLibertySystem->mContentTypes[$pContentGuid];
				require_once( constant( strtoupper( $type['handler_package'] ).'_PKG_PATH' ).$type['handler_file'] );
				$ret = new $type['handler_class']( NULL, $pContentId );
				$ret->load();
			}
		}
		return $ret;
	}
}

?>
