<?php
/**
* System class for handling the liberty package
*
* @package  liberty
* @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertySystem.php,v 1.56 2007/01/11 10:10:04 squareing Exp $
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
// Plugin Definitions
define( 'STORAGE_PLUGIN', 'storage' );
define( 'FORMAT_PLUGIN', 'format' );
define( 'DATA_PLUGIN', 'data' );

// Service Definitions
define( 'LIBERTY_SERVICE_ACCESS_CONTROL', 'access_control' );
define( 'LIBERTY_SERVICE_CATEGORIZATION', 'categorization' );
define( 'LIBERTY_SERVICE_COMMERCE', 'commerce' );
define( 'LIBERTY_SERVICE_CONTENT_TEMPLATES', 'content_templates');
define( 'LIBERTY_SERVICE_DOCUMENT_GENERATION', 'document_generation' );
define( 'LIBERTY_SERVICE_FORUMS', 'forums' );
define( 'LIBERTY_SERVICE_GEO', 'global_positioning' );
define( 'LIBERTY_SERVICE_MAPS', 'map_display' );
define( 'LIBERTY_SERVICE_METADATA', 'metadata' );
define( 'LIBERTY_SERVICE_MENU', 'menu' );
define( 'LIBERTY_SERVICE_RATING', 'rating');
define( 'LIBERTY_SERVICE_SEARCH', 'search');
define( 'LIBERTY_SERVICE_TRANSLATION', 'translation');
define( 'LIBERTY_SERVICE_TRANSLITERATION', 'transliteration');

define( 'LIBERTY_TEXT_AREA', 'editliberty');
define( 'LIBERTY_UPLOAD', 'upload');

// Set of default acceptable HTML tags
define( 'DEFAULT_ACCEPTABLE_TAGS', '<a><br><b><blockquote><cite><code><div><dd><dl><dt><em><h1><h2><h3><h4><hr>'
		.'<i><it><img><li><ol><p><pre><span><strong><table><tbody><div><tr><td><th><u><ul>'
		.'<button><fieldset><form><label><input><option><select><textarea>' );


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

	// Hash of plugin data
	var $mPlugins = array();

	// Liberty data tags
	var $mDataTags;

	// Content Status
	var $mContentStatus;

	// Content types
	var $mContentTypes;

	// File name of last plug that registered
	var $mPluginFileName;

	// Package using LibertySystem
	// this makes it possible to extend LibertySystem by another package
	var $mSystem = LIBERTY_PKG_NAME;


	/**
	 * Initiate Class
	 **/
	function LibertySystem( $pExtras = TRUE ) {
		LibertyBase::LibertyBase();
		if( $pExtras ) {
			$this->mDataTags = array();
			$this->loadContentTypes();
		}
	}

	// ****************************** Plugin Functions
	/**
	 * Load only active plugins from disk
	 *
	 * @return none
	 * @access public
	 **/
	function loadActivePlugins() {
		global $gBitSystem;
		$active_plugins = $gBitSystem->getConfigMatch( "/^{$this->mSystem}_plugin_status_/i", 'y' );
		foreach( $active_plugins as $key=>$value ) {
			$pluginGuid = preg_replace( "/^{$this->mSystem}_plugin_status_/", '', $key,1 );
			if( $pluginFile = $gBitSystem->getConfig( "{$this->mSystem}_plugin_file_$pluginGuid" ) ) {
				// check for the plugin in the default location - in case bitweaver root path changed.
				if ( file_exists( $pluginFile ) ) {
					$this->mPluginFileName = $pluginFile;
					include_once( $pluginFile );
				} else {
					$defaultFile = LIBERTY_PKG_PATH.'plugins/'.basename( $pluginFile );
					if( file_exists( $defaultFile ) ) {
						$this->mPluginFileName = $defaultFile;
						include_once( $defaultFile );
					}
				}
			}
		}
	}

	/**
	 * Load all plugins found in specified directory
	 * Use loadActivePlugins to load only the active plugins
	 *
	 * @return none
	 **/
	function scanAllPlugins( $pPluginsPath = NULL ) {
		global $gBitSystem;
		if( empty( $pPluginsPath ) ) {
			$pPluginsPath = LIBERTY_PKG_PATH.'plugins/';
		}

		if( $pluginDir = opendir( $pPluginsPath ) ) {
			while( FALSE !== ( $plugin = readdir( $pluginDir ) ) ) {
				if( preg_match( '/\.php$/', $plugin ) ) {
					$this->mPluginFileName = $pPluginsPath.$plugin;
					include_once( $pPluginsPath.$plugin );
				}
			}
		}

		// keep plugin list in sorted order
		asort( $this->mPlugins );

		// There must be at least one format plugin active and set as the default format
		$format_plugin_count = 0;
		$default_format_found = 0;
		$current_default_format_guid = $gBitSystem->getConfig( 'default_format' );
		foreach( $this->mPlugins as $guid => $plugin ) {
			if( $plugin['is_active'] == 'y' ) {
				if( $plugin['plugin_type'] == FORMAT_PLUGIN ) {
					$format_plugin_count++;
				}
				if( $current_default_format_guid == $guid ) {
					$default_format_found++;
				}
			}
		}

		// if no current default format or no format plugins active
		// activate format.tikiwiki and make it the default format plugin
		// This happens during installation and therefore requires that we include the plugin file for the constant definitions

		// only execute the following if this class hasn't been extended
		if( $this->mSystem == LIBERTY_PKG_NAME ) {
			$plugin_file = LIBERTY_PKG_PATH.'plugins/format.tikiwiki.php';
			if( $format_plugin_count == 0 || $default_format_found == 0 && is_file( $plugin_file ) ) {
				require_once( $plugin_file );
				$guid = PLUGIN_GUID_TIKIWIKI;
				$config_name = "{$this->mSystem}_plugin_status_" . $guid;
				$config_value = 'y';
				$gBitSystem->storeConfig( $config_name, $config_value, $this->mSystem );
				$gBitSystem->storeConfig( 'default_format', PLUGIN_GUID_TIKIWIKI, $this->mSystem );
				//make memory match db
				$this->loadActivePlugins();
			}
		}

		// remove any config settings for plugins that were not on disk
		$active_plugins = $gBitSystem->getConfigMatch("/^{$this->mSystem}_plugin_status_/i");
		foreach( $active_plugins as $key=>$value ) {
			$plugin_guid = preg_replace( "/^{$this->mSystem}_plugin_status_/", '', $key,1 );
			if( !isset( $this->mPlugins[$plugin_guid] ) ) {
				$config_name = "{$this->mSystem}_plugin_status_" . $guid;
				$gBitSystem->storeConfig( $config_name, NULL, $this->mSystem );
				$config_name = "{$this->mSystem}_plugin_file_" . $guid;
				$gBitSystem->storeConfig( $config_name, NULL, $this->mSystem );
			}
		}
	}

	/**
	 * Check to see if a given plugin is activ or not
	 *
	 * @param $pPluginGuid Plugin GUID of the plugin you want to check
	 * @return TRUE if the plugin is active, FALSE if it's not
	 **/
	function isPluginActive( $pPluginGuid ) {
		vd( $this->mPlugins[$pPluginGuid] );
		return( isset( $this->mPlugins[$pPluginGuid] ) && ( $this->mPlugins[$pPluginGuid]['is_active'] == 'y' ) );
	}

	/**
	 * Allow data plugins to register their tag
	 * 
	 * @param string $pTag Tag of plugin, e.g.: TOC
	 * @param string $pPluginGuid GUID of plugin, e.g.: PLUGIN_GUID_TOC
	 * @access public
	 * @return void
	 */
	function registerDataTag( $pTag, $pPluginGuid ) {
		$this->mDataTags[strtolower($pTag)] = $pPluginGuid;
	}

	/**
	 * Allow plugins to register themselves using this function. Data is added directly to the list of existing plugins
	 *
	 * @param $pGuid GUID of plugin
	 * @param $pPluginParams Set of plugin parameters (see treasury/plugins/mime.*.php for example)
	 * @return none
	 * @access public
	 **/
	function registerPlugin( $pGuid, $pPluginParams ) {
		global $gBitSystem;
		#save the plugin_guid <=> filename mapping
		$config_name = "{$this->mSystem}_plugin_file_".$pGuid;
		$gBitSystem->storeConfig( $config_name, $this->mPluginFileName, LIBERTY_PKG_NAME );
		$config_name = "{$this->mSystem}_plugin_status_".$pGuid;
		$plugin_status = $gBitSystem->getConfig( $config_name );
		if( empty( $plugin_status ) && isset( $pPluginParams['auto_activate'] ) && $pPluginParams['auto_activate'] == TRUE ) {
			$plugin_status = 'y';
			$gBitSystem->storeConfig( $config_name, $plugin_status, LIBERTY_PKG_NAME );
		}
		$this->mPlugins[$pGuid]['is_active'] = $plugin_status;
		$this->mPlugins[$pGuid]['filename'] = $this->mPluginFileName;
		$this->mPlugins[$pGuid]['plugin_guid'] = $pGuid;
		$this->mPlugins[$pGuid]['verified'] = TRUE;
		$this->mPlugins[$pGuid] = array_merge( $this->mPlugins[$pGuid], $pPluginParams );
	}

	/**
	 * setActivePlugins 
	 * 
	 * @param array $pPluginGuids an array of all the plugin guids that are active. Any left out are *inactive*!
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function setActivePlugins( $pPluginGuids ) {
		global $gBitSystem;

		if( is_array( $pPluginGuids ) ) {
			# zap list of plugins from DB
			$gBitSystem->setConfigMatch( "/^{$this->mSystem}_plugin_status/i", NULL, 'n', LIBERTY_PKG_NAME );
			foreach( array_keys( $this->mPlugins ) as $guid ) {
				$this->mPlugins[$guid]['is_active'] = 'n';
			}

			#set active those specified
			foreach( array_keys( $pPluginGuids ) as $guid ) {
				if( $pPluginGuids[$guid][0] == 'y' ) {
					$this->setActivePlugin( $guid );
				}
			}
			//load any plugins made active, but not already loaded
			$this->loadActivePlugins();
		}
	}

	/**
	 * set a single plugin as active and store the appropriate information in the database
	 * 
	 * @param array $pPluginGuid the plugin guid we want to set active
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function setActivePlugin( $pPluginGuid ) {
		global $gBitSystem;
		$gBitSystem->storeConfig( "{$this->mSystem}_plugin_status_".$pPluginGuid, 'y', LIBERTY_PKG_NAME );
		if( isset( $this->mPlugins[$pPluginGuid] )) {
			$this->mPlugins[$pPluginGuid]['is_active'] = 'y';
		}
	}

	/**
	 * getPluginInfo 
	 * 
	 * @param array $pGuid 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getPluginInfo( $pGuid ) {
		$ret = NULL;
		if( !empty( $pGuid )
			&& !empty( $this->mPlugins[$pGuid] )
		) {
			$ret = $this->mPlugins[$pGuid];
		}
		return $ret;
	}

	/**
	 * getPluginFunction 
	 * 
	 * @param array $pGuid 
	 * @param array $pFunctionName 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
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

	/**
	 * This function will purge all plugin settings set in kernel_config. useful when the path to plugins changes 
	 * or plugins don't seem to be working
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function resetAllPluginSettings() {
		global $gBitSystem;
		//$this->mPlugins = array();
		$confs = $gBitSystem->getConfigMatch( "/^{$this->mSystem}_plugin_/i" );
		foreach( array_keys( $confs ) as $config ) {
			$gBitSystem->storeConfig( $config, NULL, $this->mSystem );
		}

		if( $this->mSystem == LIBERTY_PKG_NAME ) {
			// also remove the default format
			$gBitSystem->storeConfig( 'default_format', NULL, $this->mSystem );
		}
		$this->scanAllPlugins();
	}




	// ****************************** Content Type Functions
	/**
	 * getContentStatus 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getContentStatus() {
		return( $this->mDb->getAssoc( "SELECT `content_status_id`,`content_status_name` FROM `".BIT_DB_PREFIX."liberty_content_status`" ) );
	}

	/**
	 * Load all available content types into $this->mContentTypes
	 *
	 * @return none
	 **/
	function loadContentTypes( $pCacheTime=BIT_QUERY_CACHE_TIME ) {
		if( $rs = $this->mDb->query( "SELECT * FROM `".BIT_DB_PREFIX."liberty_content_types`", NULL, BIT_QUERY_DEFAULT, BIT_QUERY_DEFAULT ) ) {
			while( $row = $rs->fetchRow() ) {
				// translate the content description
				$row['content_description'] = tra( $row['content_description'] );
				$this->mContentTypes[$row['content_type_guid']] = $row;
			}
		}
	}

	/**
	 * Register new content type
	 *
	 * @return none
	 * @access public
	 **/
	function registerContentType( $pGuid, $pTypeParams ) {
		if( !isset( $this->mContentTypes ) ) {
			$this->loadContentTypes();
		}
		$pTypeParams['content_type_guid'] = $pGuid;
		if( empty( $this->mContentTypes[$pGuid] ) && !empty( $pTypeParams ) ) {
			$result = $this->mDb->associateInsert( BIT_DB_PREFIX."liberty_content_types", $pTypeParams );
			// we just ran some SQL - let's flush the loadContentTypes query cache
			$this->loadContentTypes( 0 );
		} else {
			if( $pTypeParams['handler_package'] != $this->mContentTypes[$pGuid]['handler_package'] || $pTypeParams['handler_file'] != $this->mContentTypes[$pGuid]['handler_file'] || $pTypeParams['handler_class'] != $this->mContentTypes[$pGuid]['handler_class'] ) {
				$result = $this->mDb->associateUpdate( BIT_DB_PREFIX."liberty_content_types", $pTypeParams, array( 'content_type_guid'=>$pGuid ) );
			}
		}
	}

	/**
	 * Get the description of a given content type
	 *
	 * @param $pContentType Content type GUID you want the description for
	 * @return Content type description
	 * @access public
	 **/
	function getContentTypeDescription( $pContentType ) {
		$ret = NULL;
		if( !empty( $this->mContentTypes[$pContentType]['content_description'] ) ) {
			$ret = $this->mContentTypes[$pContentType]['content_description'];
		}
		return $ret;
	}




	// ****************************** Service Functions
	/**
	 * Get the service details of a given package
	 *
	 * @param $pPackageName Package name of you want the service details for
	 * @return Service details if the package has them - FALSE if the package is not a service
	 * @access public
	 **/
	function getService( $pPackageName ) {
		global $gBitSystem;
		return( !empty( $gBitSystem->mPackages[$pPackageName]['service'] ) ? $gBitSystem->mPackages[$pPackageName]['service'] : NULl );
	}

	/**
	 * Register package as service - hash added to $this->mServices
	 *
	 * $pServiceHash Service hash details. see existing service hashes found in <package>/bit_setup_inc.php for examples and details
	 * @return none
	 * @access public
	 **/
	function registerService( $pServiceName, $pPackageName, $pServiceHash ) {
		$this->mServices[$pServiceName][$pPackageName] = $pServiceHash;
	}

	/**
	 * Check to see if a package has any service capabilities
	 *
	 * @return TRUE on success, FALSE on failure
	 * @access public
	 **/
	function hasService( $pServiceName ) {
		return( !empty( $this->mServices[$pServiceName] ) );
	}

	/**
	 * Get contents of a given service value
	 *
	 * @param $pServiceValue Service value you want to work to get
	 * @return Value of a given service value
	 * @access private
	 **/
	function getServiceValues( $pServiceValue ) {
		global $gBitSystem;
		$ret = NULL;
		if( !empty( $this->mServices ) ) {
			foreach( array_keys( $this->mServices ) as $service ) {
				if( $this->hasService( $service ) ) {
					if( !($package = $gBitSystem->getConfig( 'liberty_service_'.$service )) ) {
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

	/**
	 * Return an associate array of the non empty return values of all the functions with the given arguments
	 *
	 * @param $pFunction the liberty name of the function to invoke
	 * @param $pArgs an array of arguments to the function
	 * @return array an associate array of the non empty return values
	 */
	function invokeServiceFunctionR($pFunction,$pArgs) {
		$ret = array();
		if( $funcs = $this->getServiceValues( $pFunction ) ) {
			foreach( $funcs as $serv => $func ) {
				if( function_exists( $func ) ) {
					$v = call_user_func_array($func,$pArgs);
					if (!empty($v)) {
						$ret[$serv] =$v;
					}
				}
			}
		}
		return $ret;
	}




	// ****************************** Miscellaneous Functions
	/**
	 * Get the URL to the icon for the mime type passed in. This should probably check for files of multiple image types instead of just jpg 
	 * 
	 * @param string $pMimeType Mime type of the file
	 * @param string $pExt Extension of the file - used to get backup mime icon
	 * @access public
	 * @return Full image HTML tag to mime icon
	 */
	function getMimeThumbnailURL($pMimeType, $pExt=NULL) {
		$ret = NULL;
		$parts = split( '/',$pMimeType );
		if( count( $parts ) > 1 ) {
			global $gBitSmarty;
			require_once $gBitSmarty->_get_plugin_filepath( 'function','biticon' );

			$ext = strtolower( $parts[1] );
			$biticon = array(
				'ipackage' => 'liberty',
				'ipath' => 'mime/',
				'iname' => $ext,
				'iexplain' => $ext,
				'url' => 'only',
			);

			if( !$ret = smarty_function_biticon( $biticon,$gBitSmarty ) ) {
				$biticon['iname'] = strtolower( $pExt );
				if( !$ret = smarty_function_biticon( $biticon,$gBitSmarty ) ) {
					$biticon['iname'] = 'generic';
					$ret = smarty_function_biticon( $biticon,$gBitSmarty );
				}
			}
		}
		return $ret;
	}
}

/**
 * This crazy function will parse all the data plugin stuff found within any 
 * parsed text section
 * 
 * @param array $data Data to be parsed
 * @param array $preparsed 
 * @param array $noparsed 
 * @param array $pParser 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function parse_data_plugins( &$data, &$preparsed, &$noparsed, &$pParser, &$pCommonObject ) {
	global $gLibertySystem;
	// Find the plugins
	// note: $curlyTags[0] is the complete match, $curlyTags[1] is plugin name, $curlyTags[2] is plugin arguments
	preg_match_all("/\{\/?([A-Za-z]+)([^\}]*)\}/", $data, $curlyTags);

	if( count( $curlyTags[0] ) ) {
		// if true, replace only CODE plugin, if false, replace all other plugins
		$code_first = true;

		// Process plugins in reverse order, so that nested plugins are handled
		// from the inside out.
		$i = count( $curlyTags[0] ) - 1;
		$paired_tag_seen = array();
		while( $i >= 0 ) {
			$plugin_start = $curlyTags[0][$i];
			$plugin = $curlyTags[1][$i];
			$pos = strpos( $data, $plugin_start ); // where plugin starts
			$dataTag = strtolower( $plugin );
			// hush up the return of this in case someone uses curly braces to enclose text
			$pluginInfo = $gLibertySystem->getPluginInfo( @$gLibertySystem->mDataTags[$dataTag] ) ;

			// only process a standalone unpaired tag or the start tag for a paired tag
			if( empty( $paired_close_tag_seen[$dataTag] ) || $paired_close_tag_seen[$dataTag] == 0 ) {
				$paired_close_tag_seen[$dataTag] = 1;
			} else {
				$paired_close_tag_seen[$dataTag] = 0;
			}

			$is_opening_tag = 0;
			if( ( empty( $pluginInfo['requires_pair'] ) && (strtolower($plugin_start) != '{/'. $dataTag . '}' ) )
				|| (strpos( $plugin_start, ' ' ) > 0)
				|| (strtolower($plugin_start) == '{'.$dataTag.'}' && !$paired_close_tag_seen[$dataTag] )
			) {
				$is_opening_tag = 1;
			}

			if(
				// when in CODE parsing mode, replace only CODE plugins
				( ( $code_first && ( $dataTag == 'code' ) )
					// when NOT in CODE parsing mode, replace all other plugins
					|| ( !$code_first && ( $dataTag <> 'code' ) )
				)
				&& isset( $gLibertySystem->mDataTags[$dataTag] )
				&& ( $pluginInfo )
				&& ( $gLibertySystem->getPluginFunction( $gLibertySystem->mDataTags[$dataTag], 'load_function' ) )
				&& ( $loadFunc = $gLibertySystem->getPluginFunction( $gLibertySystem->mDataTags[$dataTag], 'load_function' ) )
				&& ( $is_opening_tag )
			) {

				if( $pluginInfo['requires_pair'] ) {
					$plugin_end = '{/'.$plugin.'}';
					$pos_end = strpos( strtolower( $data ), strtolower( $plugin_end ), $pos ); // where plugin data ends
					$plugin_end2 = '{'.$plugin.'}';
					$pos_end2 = strpos( strtolower( $data ), strtolower( $plugin_end2 ), $pos+1 ); // where plugin data ends

					if( ( $pos_end2 > 0 && $pos_end2 > 0 && $pos_end2 < $pos_end ) || $pos_end === false ) {
						$pos_end = $pos_end2;
						$plugin_end = $plugin_end2;
					}
				} else {
					$pos_end = $pos + strlen( $curlyTags[0][$i] );
					$plugin_end = '';
				}

//print "			if ( ((($code_first) && ($plugin == 'CODE')) || ((!$code_first) && ($plugin <> 'CODE'))) && ($pos_end > $pos)) { <br/>";

				// Extract the plugin data
				$plugin_data_len = $pos_end - $pos - strlen( $curlyTags[0][$i] );

				$plugin_data = substr( $data, $pos + strlen( $plugin_start ), $plugin_data_len );
//print "		$plugin_data_len = $pos_end - $pos - strlen(".$curlyTags[0][$i].")		substr( $pos + strlen($plugin_start), $plugin_data_len);";

				$arguments = array();
				// Construct argument list array
				$paramString = str_replace( '&gt;', '>', trim( $curlyTags[2][$i] ) );
				if( preg_match( '/^\(.*=>.*\)$/', $paramString ) ) {
					$paramString = preg_replace( '/[\(\)]/', '', $paramString );
					//we have the old style parms like {CODE (in=>1)}
					$params = split( ',', trim( $paramString ) );

					foreach( $params as $param ) {
						// the following str_replace line is to decode the &gt; char when html is turned off
						// perhaps the plugin syntax should be changed in 1.8 not to use any html special chars
						$parts = split( '=>?', $param );

						if( isset( $parts[0] ) && isset( $parts[1] ) ) {
							$name = trim( $parts[0] );
							$arguments[$name] = trim( $parts[1] );
						}
					}
				} else {
					$paramString = trim( $curlyTags[2][$i], " \t()" );
					$paramString = str_replace("&quot;", '"', $paramString);
					$arguments = parse_xml_attributes( $paramString );
				}

				if( $ret = $loadFunc( $plugin_data, $arguments, $pCommonObject ) ) {
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
			if( ( $code_first ) && ( $i < 0 ) ) {
				$i = count( $curlyTags[0] ) - 1;
				$code_first = false;
			}
		} // while
	}
}

global $gLibertySystem;
$gLibertySystem = new LibertySystem();



// generic functions that make the life of a plugin easier
/**
 * pass in the plugin paramaters and out comes a hash with usable styling information
 * 
 * @param array $pParamHash 
 * @access public
 * @return hash full of styling goodies
 */
function liberty_plugins_div_style( $pParamHash ) {
	$ret = array();
	$ret['style'] = $ret['description'] = '';

	if( !empty( $pParamHash ) && is_array( $pParamHash ) ) {
		foreach( $pParamHash as $key => $value ) {
			if( !empty( $value ) ) {
				switch( $key ) {
					// description
					case 'desc':
						$key = 'description';
					case 'description':
						$ret[$key] = $value;
						break;
					// styling
					case 'background-color':
						$key = 'background';
					case 'width':
					case 'height':
						if( preg_match( "/^\d+(em|px|%|pt)$/", trim( $value ) ) ) {
							$ret['style'] .= "{$key}:{$value};";
						} elseif( preg_match( "/^\d+$/", $value ) ) {
							$ret['style'] .= "{$key}:{$value}px;";
						}
					case 'float':
					case 'width':
					case 'height':
					case 'padding':
					case 'margin':
					case 'background':
					case 'border':
					case 'text-align':
					case 'color':
					case 'font':
					case 'font-size':
					case 'font-weight':
					case 'font-family':
						$ret['style'] .= "{$key}:{$value};";
						break;
					// align and float are special
					case 'align':
						if( $value == 'center' || $value == 'middle' ) {
							$ret['style'] .= 'text-align:center;';
						} else {
							$ret['style'] .= "float:{$value};";
						}
						break;
					// default just gets re-assigned
					default:
						$ret[$key] = $value;
						break;
				}
			}
		}
	}

	return $ret;
}
?>
