<?php
/**
* System class for handling the liberty package
*
* @package  liberty
* @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertySystem.php,v 1.1.1.1.2.19 2005/11/02 03:14:12 mej Exp $
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
 * Local base defines
 */
define( 'STORAGE_PLUGIN', 'storage' );
define( 'FORMAT_PLUGIN', 'format' );
define( 'DATA_PLUGIN', 'data' );

define( 'LIBERTY_SERVICE_ACCESS_CONTROL', 'access_control' );
define( 'LIBERTY_SERVICE_CATEGORIZATION', 'categorization' );
define( 'LIBERTY_SERVICE_COMMERCE', 'commerce' );
define( 'LIBERTY_SERVICE_MENU', 'menu' );
define( 'LIBERTY_SERVICE_DOCUMENT_GENERATION', 'document_generation' );


define( 'DEFAULT_ACCEPTABLE_TAGS', '<a><br><b><blockquote><cite><code><div><dd><dl><dt><em><h1><h2><h3><h4><hr>'
				 .' <i><it><img><li><ol><p><pre><span><strong><table><tbody><div><tr><td><th><u><ul>'
				 .' <button><fieldset><form><label><input><option><select><textarea>' );

/**
 * Link to base class
 */
require_once( LIBERTY_PKG_PATH.'LibertyBase.php' );

/**
 * System class for handling the liberty package
 *
 * @package liberty
 */
class LibertySystem extends LibertyBase {

	var $mPlugins;
	var $mDataTags;
	var $mContentTypes;

	function LibertySystem() {
		LibertyBase::LibertyBase();
		$this->mDataTags = array();
		$this->loadPlugins();
		$this->loadContentTypes();
	}

	function loadPlugins() {
		$rs = $this->mDb->query( "SELECT * FROM `".BIT_DB_PREFIX."tiki_plugins`", NULL, BIT_QUERY_DEFAULT, BIT_QUERY_DEFAULT );
		while( $rs && !$rs->EOF ) {
			$this->mPlugins[$rs->fields['plugin_guid']] = $rs->fields;
			$rs->MoveNext();
		}
	}

	function scanPlugins() {
		$pluginsPath = LIBERTY_PKG_PATH.'plugins/';
		if( $pluginDir = opendir( $pluginsPath ) ) {
			// Make two passes through the root - 1. to define the DEFINES, and 2. to include the $pScanFile's
			while (false !== ($plugin = readdir($pluginDir))) {
				if( preg_match( '/\.php$/', $plugin ) ) {
					include_once( $pluginsPath.$plugin );
				}
			}
		}
		// match up storage_type_id to plugin_guids. this _id varies from install to install, but guids are the same
		foreach( array_keys( $this->mPlugins ) as $guid ) {
			$handler = &$this->mPlugins[$guid]; //shorthand var alias
			if( !isset( $handler['verified'] ) && $handler['is_active'] =='y' ) {
				// We are missing a plugin!
				$sql = "UPDATE `".BIT_DB_PREFIX."tiki_plugins` SET `is_active`='x' WHERE `plugin_guid`=?";
				$this->mDb->query( $sql, array( $guid ) );
				$handler['is_active'] = 'n';
			} elseif( !empty( $handler['verified'] ) && $handler['is_active'] =='x' ) {
				//We found a formally missing plugin - re-enable it
				$sql = "UPDATE `".BIT_DB_PREFIX."tiki_plugins` SET `is_active`='y' WHERE `plugin_guid`=?";
				$this->mDb->query( $sql, array( $guid ) );
				$handler['is_active'] = 'y';
			} elseif( empty( $handler['verified'] ) && !isset( $handler['is_active'] ) ) {
				//We found a missing plugin - insert it
				$handler['is_active'] = ( ( isset( $handler['auto_activate'] ) && $handler['auto_activate'] == FALSE ) ? 'n' : 'y' );
				$sql = "INSERT INTO `".BIT_DB_PREFIX."tiki_plugins` ( `plugin_guid`, `plugin_type`, `plugin_description`, `is_active` ) VALUES ( ?, ?, ?, ? )";
				$this->mDb->query( $sql, array( $guid, $handler['plugin_type'], $handler['description'], $handler['is_active'] ) );
			}
		}
		asort( $this->mPlugins );
	}

	function loadContentTypes( $pCacheTime=BIT_QUERY_CACHE_TIME ) {
		$rs = $this->mDb->query( "SELECT * FROM `".BIT_DB_PREFIX."tiki_content_types`", NULL, BIT_QUERY_DEFAULT, BIT_QUERY_DEFAULT );
		while( $rs && !$rs->EOF ) {
			$this->mContentTypes[$rs->fields['content_type_guid']] = $rs->fields;
			$rs->MoveNext();
		}
	}

	function registerContentType( $pGuid, $pTypeParams ) {
		if( !isset( $this->mContentTypes ) ) {
			$this->loadContentTypes();
		}
		$pTypeParams['content_type_guid'] = $pGuid;
		if( empty( $this->mContentTypes[$pGuid] ) && !empty( $pTypeParams ) ) {
			$result = $this->mDb->associateInsert( BIT_DB_PREFIX."tiki_content_types", $pTypeParams );
			// we just ran some SQL - let's flush the loadContentTypes query cache
			$this->loadContentTypes( 0 );
		} else {
			if( $pTypeParams['handler_package'] != $this->mContentTypes[$pGuid]['handler_package'] || $pTypeParams['handler_file'] != $this->mContentTypes[$pGuid]['handler_file'] || $pTypeParams['handler_class'] != $this->mContentTypes[$pGuid]['handler_class'] ) {
				$result = $this->mDb->associateUpdate( BIT_DB_PREFIX."tiki_content_types", $pTypeParams, array( 'name'=>'content_type_guid', 'value'=>$pGuid ) );
			}
		}
	}

	function getService( $pPackageName ) {
		global $gBitSystem;
		return $gBitSystem->mPackages[$pPackageName]['service'];
	}

	function registerService( $pServiceName, $pPackageName, $pServiceHash ) {
		$this->mServices[$pServiceName][$pPackageName] = $pServiceHash;
	}

	function hasService( $pServiceName ) {
		return( !empty( $this->mServices[$pServiceName] ) );
	}

	function getServiceValues( $pServiceValue ) {
		global $gBitSystem;
		$ret = NULL;
		if( !empty( $this->mServices ) ) {
			foreach( array_keys( $this->mServices ) as $service ) {
				if( $this->hasService( $service ) ) {
					if( !($package = $gBitSystem->getPreference( 'liberty_service_'.$service )) ) {
						$package = key( $this->mServices[$service] );
					}
					if( !empty( $this->mServices[$service][$package][$pServiceValue] ) ) {
						$ret[$service] = $this->mServices[$service][$package][$pServiceValue];
					}
				}
			}
		}
		return $ret;
	}

	function isPluginActive( $pPluginGuid ) {
		return( isset( $this->mPlugins[$pGuid] ) && ($this->mPlugins[$pGuid] == 'y') );
	}

	function registerDataTag( $pTag, $pPluginGuid ) {
		$this->mDataTags[strtolower($pTag)] = $pPluginGuid;
	}

	function registerPlugin( $pGuid, $pPluginParams ) {
		if( isset($this->mPlugins[$pGuid] ) ) {
			$this->mPlugins[$pGuid]['verified'] = TRUE;
		} else {
			$this->mPlugins[$pGuid]['verified'] = FALSE;
		}
		$this->mPlugins[$pGuid] = array_merge( $this->mPlugins[$pGuid], $pPluginParams );
	}

	// @parameter pPluginGuids an array of all the plugin guids that are active. Any left out are *inactive*!
	function setActivePlugins( $pPluginGuids ) {
		if( is_array( $pPluginGuids ) ) {
			$sql = "UPDATE `".BIT_DB_PREFIX."tiki_plugins` SET `is_active`='n' WHERE `is_active`!='x'";
			$this->mDb->query( $sql );
			foreach( array_keys( $this->mPlugins ) as $guid ) {
				$this->mPlugins[$guid]['is_active'] = 'n';
			}

			foreach( array_keys( $pPluginGuids ) as $guid ) {
				$sql = "UPDATE `".BIT_DB_PREFIX."tiki_plugins` SET `is_active`='y' WHERE `plugin_guid`=?";
				$this->mDb->query( $sql, array( $guid ) );
				$this->mPlugins[$guid]['is_active'] = 'y';
			}
			// we just ran some SQL - let's flush the loadPlugins query cache
			$this->loadPlugins( 0 );
		}
	}

	function getPluginInfo( $pGuid ) {
		$ret = NULL;
		if( !empty( $pGuid )
			&& !empty( $this->mPlugins[$pGuid] )
		) {
			$ret = $this->mPlugins[$pGuid];
		}
		return $ret;
	}

	function getPluginFunction( $pGuid, $pFunctionName ) {
		$ret = NULL;
		if( !empty( $pGuid )
			&& !empty( $this->mPlugins[$pGuid] )
			&& !empty( $this->mPlugins[$pGuid][$pFunctionName] )
			&& function_exists( $this->mPlugins[$pGuid][$pFunctionName] )
		) {
			$ret = $this->mPlugins[$pGuid][$pFunctionName];
		}
		return $ret;
	}

	// Get the URL to the icon for the mime type passed in. This should probably check for files of multiple image types instead of just jpg
	function getMimeThumbnailURL($pMimeType) {
		$ret = NULL;
		$parts = split('/',$pMimeType);
		if (count($parts) > 1) {
			global $gBitSmarty;
			require_once $gBitSmarty->_get_plugin_filepath('function','biticon');

			$ext = $parts[1];
			$biticon = array(
				'ipackage' => 'liberty',
				'ipath' => 'mime/',
				'iname' => $ext,
				'iexplain' => $ext,
				'url' => 'only',
			);
			if( !$ret = smarty_function_biticon( $biticon,$gBitSmarty ) ) {
				$biticon['iname'] = 'generic';
				$ret = smarty_function_biticon( $biticon,$gBitSmarty );
			}
		}
		return $ret;

	}
}

function parse_data_plugins(&$data, &$preparsed, &$noparsed, &$pParser ) {
	global $gLibertySystem;
	// Find the plugins
	// note: $curlyTags[0] is the complete match, $curlyTags[1] is plugin name, $curlyTags[2] is plugin arguments
	preg_match_all("/\{([A-Za-z]+)([^\}]*)\}/", $data, $curlyTags);

	if( count($curlyTags[0]) ) {
		// if true, replace only CODE plugin, if false, replace all other plugins
		$code_first = true;

		// Process plugins in reverse order, so that nested plugins are handled
		// from the inside out.
		$i = count($curlyTags[0]) - 1;

		while ($i >= 0) {
			$plugin_start = $curlyTags[0][$i];
			$plugin = $curlyTags[1][$i];
			$pos = strpos( $data, $plugin_start ); // where plugin starts
			$dataTag = strtolower( $plugin );
			if (
				// when in CODE parsing mode, replace only CODE plugins
				(($code_first && ($dataTag == 'code'))
				   // when NOT in CODE parsing mode, replace all other plugins
				   || (!$code_first && ($dataTag <> 'code')))
				&& isset( $gLibertySystem->mDataTags[$dataTag] )
				&& ( $pluginInfo = $gLibertySystem->getPluginInfo( $gLibertySystem->mDataTags[$dataTag] ) )
				&& ( $gLibertySystem->getPluginFunction( $gLibertySystem->mDataTags[$dataTag], 'load_function' ) )
				&& ( $loadFunc = $gLibertySystem->getPluginFunction( $gLibertySystem->mDataTags[$dataTag], 'load_function' ) )
				// make sure we don't have a closing plugin
				&& ( empty( $pluginInfo['requires_pair'] ) || (strtolower($plugin_start) != '{'.$dataTag.'}') )
			) {

				if( $pluginInfo['requires_pair'] ) {
					$plugin_end = '{'.$plugin.'}';
					$pos_end = strpos(strtolower( $data ), strtolower( $plugin_end ), $pos); // where plugin data ends
				} else {
					$pos_end = $pos + strlen( $curlyTags[0][$i] );
					$plugin_end = '';
				}

//print "			if ( ((($code_first) && ($plugin == 'CODE')) || ((!$code_first) && ($plugin <> 'CODE'))) && ($pos_end > $pos)) { <br/>";

				// Extract the plugin data
				$plugin_data_len = $pos_end - $pos - strlen($curlyTags[0][$i]);

				$plugin_data = substr($data, $pos + strlen($plugin_start), $plugin_data_len);
//print "		$plugin_data_len = $pos_end - $pos - strlen(".$curlyTags[0][$i].")		substr( $pos + strlen($plugin_start), $plugin_data_len);";

				$arguments = array();
				// Construct argument list array
				$paramString = str_replace('&gt;', '>', trim( $curlyTags[2][$i] ) );
				if( preg_match( '/^\(.*=>.*\)$/', $paramString ) ) {
					$paramString = preg_replace('/[\(\)]/', '', $paramString);
					//we have the old style parms like {CODE (in=>1)}
					$params = split(',', trim( $paramString ));

					foreach ($params as $param) {
						// the following str_replace line is to decode the &gt; char when html is turned off
						// perhaps the plugin syntax should be changed in 1.8 not to use any html special chars
						$parts = split( '=>?', $param );

						if (isset($parts[0]) && isset($parts[1])) {
							$name = trim($parts[0]);
							$arguments[$name] = trim($parts[1]);
						}
					}
				} else {
					$paramString = trim( $curlyTags[2][$i], " \t()" );
					$paramString = str_replace("&quot;", '"', $paramString);
					$arguments = parse_xml_attributes( $paramString );
				}

				if( $ret = $loadFunc( $plugin_data, $arguments ) ) {
					// temporarily replace end of lines so tables and other things render properly
//					$ret = preg_replace( "/\n/", '#EOL', $ret );

					// Handle pre- & no-parse sections and plugins inserted by this plugin
					if( is_object( $pParser ) ) {
						// we were passed in a parser object, assume tikiwiki that has parse_first method
						$pParser->parse_pp_np( $ret, $preparsed, $noparsed );
					} else {
						// just nuke all np/pp for now in non tikiwiki formats
						$ret = preg_replace( "/\~(\/?)[np]p\~/", '', $ret );

					}
					// Replace plugin section with its output in data
					$data = substr_replace($data, $ret, $pos, $pos_end - $pos + strlen($plugin_end));
				}
			}
			$i--;
			// if we are in CODE parsing mode and list is done, switch to 'parse other plugins' mode and start all over
			if (($code_first) && ($i < 0)) {
				$i = count($curlyTags[0]) - 1;

				$code_first = false;
			}
		} // while
	}
}

global $gLibertySystem;
$gLibertySystem = new LibertySystem();

?>
