<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/liberty_rss.php,v 1.8 2006/05/04 18:43:21 squareing Exp $
 * @package wiki
 * @subpackage functions
 */

/**
 * Initialization
 */
require_once( "../bit_setup_inc.php" );
require_once( RSS_PKG_PATH."rss_inc.php" );
require_once( LIBERTY_PKG_PATH."LibertyContent.php" );

$gBitSystem->verifyPackage( 'rss' );
$gBitSystem->verifyFeature( 'liberty_rss' );

$rss->title = $gBitSystem->getConfig( 'liberty_rss_title', $gBitSystem->getConfig( 'site_title' ).' - '.tra( 'Liberty' ) );
$rss->description = $gBitSystem->getConfig( 'liberty_rss_description', $gBitSystem->getConfig( 'site_title' ).' - '.tra( 'RSS Feed' ) );

// check permission to view liberty pages
if( !$gBitUser->hasPermission( 'p_wiki_view_page' ) ) {
	require_once( RSS_PKG_PATH."rss_error.php" );
} else {
	// check if we want to use the cache file
	$cacheFile = TEMP_PKG_PATH.RSS_PKG_NAME.'/'.LIBERTY_PKG_NAME.'_'.$rss_version_name.'.xml';
	$rss->useCached( $rss_version_name, $cacheFile ); // use cached version if age < 1 hour

	$liberty = new LibertyContent();
	$listHash = array(
		'max_records' => $gBitSystem->getConfig( 'liberty_rss_max_records', 10 ),
		'sort_mode' => 'last_modified_desc',
		'include_data' => TRUE,
	);
	$feeds = $liberty->getContentList( $listHash );
	$feeds = $feeds['data'];

	// set the rss link
	$rss->link = 'http://'.$_SERVER['HTTP_HOST'].LIBERTY_PKG_DIR.'/list_content.php';

	// get all the data ready for the feed creator
	foreach( $feeds as $feed ) {
		$item = new FeedItem();
		$item->title = $feed['title'];
		$item->link = BIT_BASE_URI.$liberty->getDisplayUrl( $feed['title'], $feed );

		// create a page header that we know what type of data we're looking at
		$description =
			tra( 'Package' ).     ': '.ucfirst( $gLibertySystem->mContentTypes[$feed['content_type_guid']]['handler_package'] ).'<br />'.
			tra( 'Content Type' ).': '.$gLibertySystem->mContentTypes[$feed['content_type_guid']]['content_description'].'<br />';

		// add the parsed data, if there is any
		if( !empty( $feed['data'] ) ) {
			$description .= '<br /><hr /><br />'.tra( 'Content' ).':<br />'.$liberty->parseData( $feed ).'<br /><hr />';
		}

		$item->description = $description;

		$item->date = ( int )$feed['last_modified'];
		$item->source = 'http://'.$_SERVER['HTTP_HOST'].LIBERTY_PKG_URL.'/list_content.php';
		$item->author = $gBitUser->getDisplayName( FALSE, array( 'real_name' => $feed['modifier_real_name'], 'login' => $feed['modifier_user'] ) );

		$item->descriptionTruncSize = $gBitSystem->getConfig( 'rssfeed_truncate', 5000 );
		$item->descriptionHtmlSyndicated = FALSE;

		// pass the item on to the rss feed creator
		$rss->addItem( $item );
	}

	// finally we are ready to serve the data
	echo $rss->saveFeed( $rss_version_name, $cacheFile );
}
?>