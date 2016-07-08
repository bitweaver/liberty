<?php
/**
 * Base class for Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header$
 * @author   spider <spider@steelsun.com>
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See below for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Authors: spider <spider@steelsun.com>
// +----------------------------------------------------------------------+

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'liberty_lib.php' );
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
	function __construct() {
		parent::__construct();
	}

	/**
	 * given a content_type_guid this will return an object of the proper type
	 *
	 * @param the content type to be loaded
	 */
	function getLibertyClass($pContentTypeGuid) {
		// We can abuse getLibertyObject to do the work
		$ret = LibertyBase::getLibertyObject('1', $pContentTypeGuid, FALSE);
		// Make sure we don't have a content_id set though.
		unset($ret->mContentId);
		return $ret;
	}

	/**
	 * Given a content_id, this will return and object of the proper type
	 *
	 * @param integer content_id of the object to be returned
	 * @param string optional content_type_guid of pConId. This will save a select if you happen to have this info. If not, this method will look it up for you.
	 * @param call load on the content. Defaults to true.
	 * @returns object of the appropriate content type class
	 */
	public static function getLibertyObject( $pContentId, $pContentTypeGuid=NULL, $pLoadFromCache = TRUE ) {
		$ret = NULL;
		global $gLibertySystem, $gBitUser, $gBitSystem;

		if( static::verifyId( $pContentId ) ) {
			// remove non integer bits from structure_id and content_id requests
			// can happen with period's at the end of url's that are email'ed around
			$typeClass = NULL;
			$pContentId = preg_replace( '/[\D]/', '', $pContentId );
			if( empty( $pContentTypeGuid ) ) {
				$pContentTypeGuid = $gLibertySystem->mDb->getOne( "SELECT `content_type_guid` FROM `".BIT_DB_PREFIX."liberty_content` WHERE `content_id`=?", array( $pContentId ), NULL, NULL, 3600 );
			}
			if( !empty( $pContentTypeGuid ) && isset( $gLibertySystem->mContentTypes[$pContentTypeGuid] ) ) {
				$typeClass = $gLibertySystem->getContentClassName( $pContentTypeGuid );
			}
			if( $pLoadFromCache && ($ret = static::loadFromCache( $pContentId, $typeClass )) ) {
				$ret->mCacheObject = TRUE;
			} else {
				if( $typeClass ) {
					$creator = new $typeClass();
					$ret = $creator->getNewObject( $typeClass, $pContentId, $pLoadFromCache );
					$ret->setCacheableObject( FALSE );
					$ret->clearFromCache();
				}
			}
		}
		return $ret;
	}

	/**
	 * Simple method to create a given class with a specified primary_id. The purpose of a method is to allow for derived classes to override as necessary.
	 *
	 * @param string class to be created
	 * @param integer id from the secondary table of the object to be returned
	 * @param call load on the content. Defaults to true.
	 * @returns object of the appropriate content type class
	 */
	public static function getNewObjectById( $pClass, $pPrimaryId, $pLoadFromCache=TRUE ) {
		if( $ret = new $pClass( $pPrimaryId ) ) {
			$ret->load();
		}
		return $ret;
	}

	/**
	 * Simple method to create a given class with a specified content_id. The purpose of a method is to allow for derived classes to override as necessary.
	 *
	 * @param string class to be created
	 * @param integer content_id of the object to be returned
	 * @param call load on the content. Defaults to true.
	 * @returns object of the appropriate content type class
	 */
	public static function getNewObject( $pClass, $pContentId, $pLoadFromCache=TRUE ) {
		if( $ret = new $pClass( NULL, $pContentId ) ) {
			$ret->load();
		}
		return $ret;
	}
}

?>
