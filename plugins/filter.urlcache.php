<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/filter.urlcache.php,v 1.3 2008/11/09 09:08:55 squareing Exp $
 * @package  liberty
 * @subpackage plugins_filter
 */

/*
 * IMPORTANT - READ
 * This filter is not ready for usage yet. it has to be re-written
 * This is a collection of code from throughout bitweaver that was once part of this feature in the good old tikiwiki days
 *
 * other files related to this feature are:
 * 		- kernel/view_cache.php
 * 		- kernel/admin/list_cache.php
 * 		- and their related tpl files
 *
 *
 * Feel free to fix this stuff.
 *
 *
 * Further down there are some functions that were pulled from BitSystem and 
 * need to go somewhere else.
 * I'm not sure where the best place for this stuff is - perhaps it would be 
 * best to move all this stuff to a separate package - xing
 */


/**
 * definitions ( guid character limit is 16 chars )
 * /
define( 'PLUGIN_GUID_FILTERURLCACHE', 'filterurlcache' );

global $gLibertySystem;

$pluginParams = array (
	'title'              => 'External Links Cache',
	'description'        => 'If you insert a link to an external page, this filter will proceede to cache that page to ensure that you can view the page, even if it moves or gets removed from the original location.',
	'auto_activate'      => FALSE,
	'plugin_type'        => FILTER_PLUGIN,

	// filter functions
	'presplit_function'  => 'urlcache_postparsefilter',
	'postparse_function' => 'urlcache_postparsefilter',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERURLCACHE, $pluginParams );
 * /

function urlcache_links( $links, &$pCommonObject ) {
	global $gBitSystem;
	if( $gBitSystem->isFeatureActive( 'liberty_cache_pages' ) && $pCommonObject ) {
		foreach( $links as $link ) {
			if( !$pCommonObject->urlcache_is_cached( $link ) ) {
				$pCommonObject->urlcache_store($link);
			}
		}
	}
}

/**
 * Check if given url is currently cached locally
 *
 * @param string URL to check
 * @return integer Id of the cached item
 * @todo LEGACY FUNCTIONS that need to be cleaned / moved / or deprecated & deleted
 * /
function urlcache_is_cached($url) {
	// return false until this is fixed
	return FALSE;

	$query = "select `cache_id`  from `".BIT_DB_PREFIX."liberty_link_cache` where `url`=?";
	// sometimes we can have a cache_id of 0(?!) - seen it with my own eyes, spiderr
	$ret = $this->mDb->getOne($query, array( $url ) );
	return $ret;
}

/**
 * Cache given url
 * If \c $data present (passed) it is just associated \c $url and \c $data.
 * Else it will request data for given URL and store it in DB.
 * Actualy (currently) data may be proviced by TIkiIntegrator only.
 * @param string URL to cache
 * @param string Data to be cached
 * @return bool True if item was successfully cached
 * @todo LEGACY FUNCTIONS that need to be cleaned / moved / or deprecated & deleted
 * /
function urlcache_store($url, $data = '') {
	// Avoid caching internal references... (only if $data not present)
	// (cdx) And avoid other protocols than http...
	// 03-Nov-2003, by zaufi
	// preg_match("_^(mailto:|ftp:|gopher:|file:|smb:|news:|telnet:|javascript:|nntp:|nfs:)_",$url)
	// was removed (replaced to explicit http[s]:// detection) bcouse
	// I now (and actualy use in my production Tiki) another bunch of protocols
	// available in my konqueror... (like ldap://, ldaps://, nfs://, fish://...)
	// ... seems like it is better to enum that allowed explicitly than all
	// noncacheable protocols.
	if (((strstr($url, 'tiki-') || strstr($url, 'messu-')) && $data == '')
		|| (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://'))
			return false;
	// Request data for URL if nothing given in parameters
	// (reuse $data var)
	if( $data == '' ) {
		$data = bit_http_request( $url );
	}

	// If stuff inside [] is *really* malformatted, $data
	// will be empty.  -rlpowell
	if( !$this->isCached( $url ) && is_string($data)) {
		global $gBitSystem;
		$refresh = $gBitSystem->getUTCTime();
		$query = "insert into `".BIT_DB_PREFIX."liberty_link_cache`(`url`,`data`,`refresh`) values(?,?,?)";
		$result = $this->mDb->query($query, array($url,BitDb::db_byte_encode($data),$refresh) );
		return !isset( $error );
	} else {
		return FALSE;
	}
}

function urlcache_postparsefilter( $pData, $pFilterHash ) {
	$notcachedlinks = $this->get_links_nocache($data);

	$cachedlinks = array_diff($links, $notcachedlinks);

	$this->cache_links($cachedlinks,$pCommonObject);

	// prepare link for pattern usage
	$link2 = str_replace("/", "\/", preg_quote($link));

	//use of urlencode for using cached versions of dynamic sites
	$cosa = "<a class=\"bitcache\" href=\"".KERNEL_PKG_URL."view_cache.php?url=".urlencode($link)."\">(cache)</a>";

	$pattern = "/(?<!\[)\[$link2\|([^\]\|]+)\|([^\]]+)\]/";
	$data = preg_replace($pattern, "<a $class href='$link'>$1</a>", $data);
	$pattern = "/(?<!\[)\[$link2\|([^\]\|]+)\]/";
	$data = preg_replace($pattern, "<a $class href='$link'>$1</a> $cosa", $data);
	$pattern = "/(?<!\[)\[$link2\]/";
	$data = preg_replace($pattern, "<a $class href='$link'>$link</a> $cosa", $data);
}





// functions pulled from BitSystem

function list_cache($offset, $max_records, $sort_mode, $find) {

	if ($find) {
	$findesc = '%' . $find . '%';

	$mid = " where (`url` like ?) ";
	$bindvars=array($findesc);
	} else {
	$mid = "";
	$bindvars=array();
	}

	$query = "select `cache_id` ,`url`,`refresh` from `".BIT_DB_PREFIX."liberty_link_cache` $mid order by ".$this->mDb->convertSortmode($sort_mode);
	$query_cant = "select count(*) from `".BIT_DB_PREFIX."liberty_link_cache` $mid";
	$result = $this->mDb->query($query,$bindvars,$max_records,$offset);
	$cant = $this->mDb->getOne($query_cant,$bindvars);
	$ret = array();

	while ($res = $result->fetchRow()) {
	$ret[] = $res;
	}

	$retval = array();
	$retval["data"] = $ret;
	$retval["cant"] = $cant;
	return $retval;
}

function refresh_cache($cache_id) {
	global $gBitSystem;
	$query = "select `url`  from `".BIT_DB_PREFIX."liberty_link_cache`
	where `cache_id`=?";

	$url = $this->mDb->getOne($query, array( $cache_id ) );
	$data = bit_http_request($url);
	$refresh = $gBitSystem->getUTCTime();
	$query = "update `".BIT_DB_PREFIX."liberty_link_cache`
	set `data`=?, `refresh`=?
	where `cache_id`=? ";
	$result = $this->mDb->query($query, array( $data, $refresh, $cache_id) );
	return true;
}

function remove_cache($cache_id) {
	$query = "delete from `".BIT_DB_PREFIX."liberty_link_cache` where `cache_id`=?";

	$result = $this->mDb->query($query, array( $cache_id ) );
	return true;
}

function get_cache($cache_id) {
	$query = "select * from `".BIT_DB_PREFIX."liberty_link_cache`
	where `cache_id`=?";

	$result = $this->mDb->query($query, array( $cache_id ) );
	$res = $result->fetchRow();
	return $res;
}
/* */
?>
