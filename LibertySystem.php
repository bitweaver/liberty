<?php
/**
* System class for handling the liberty package
*
* @package  liberty
* @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertySystem.php,v 1.105 2008/06/20 07:49:09 lsces Exp $
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
define( 'MIME_PLUGIN', 'mime' );
define( 'FILTER_PLUGIN', 'filter' );

// Service Definitions
define( 'LIBERTY_SERVICE_ACCESS_CONTROL', 'access_control' );
define( 'LIBERTY_SERVICE_BLOGS', 'blogs' );
define( 'LIBERTY_SERVICE_CATEGORIZATION', 'categorization' );
define( 'LIBERTY_SERVICE_COMMERCE', 'commerce' );
define( 'LIBERTY_SERVICE_CONTENT_TEMPLATES', 'content_templates' );
define( 'LIBERTY_SERVICE_DOCUMENT_GENERATION', 'document_generation' );
define( 'LIBERTY_SERVICE_FORUMS', 'forums' );
define( 'LIBERTY_SERVICE_GROUP', 'groups' );
define( 'LIBERTY_SERVICE_GEO', 'global_positioning' );
define( 'LIBERTY_SERVICE_MAPS', 'map_display' );
define( 'LIBERTY_SERVICE_METADATA', 'metadata' );
define( 'LIBERTY_SERVICE_MENU', 'menu' );
define( 'LIBERTY_SERVICE_RATING', 'rating' );
define( 'LIBERTY_SERVICE_REBLOG', 'reblogging_rss_feeds' );
define( 'LIBERTY_SERVICE_SEARCH', 'search' );
define( 'LIBERTY_SERVICE_THEMES', 'themes' );
define( 'LIBERTY_SERVICE_TAGS', 'tags' );
define( 'LIBERTY_SERVICE_TOPICA', 'topica' );
define( 'LIBERTY_SERVICE_TRANSLATION', 'translation' );
define( 'LIBERTY_SERVICE_TRANSLITERATION', 'transliteration' );
define( 'LIBERTY_SERVICE_LIBERTYSECURE', 'security' );
define( 'LIBERTY_SERVICE_MODCOMMENTS', 'comment_moderation' );

define( 'LIBERTY_TEXT_AREA', 'editliberty' );
define( 'LIBERTY_UPLOAD', 'upload' );

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

	// Packages using LibertySystem
	// this makes it possible to extend LibertySystem by another package
	var $mSystem = LIBERTY_PKG_NAME;
	var $mPluginPath;


	/**
	 * Initiate Class
	 **/
	function LibertySystem( $pExtras = TRUE ) {
		LibertyBase::LibertyBase();

		// if mPluginPath hasn't been set, we set it for liberty plugins
		if( empty( $this->mPluginPath )) {
			$this->mPluginPath = LIBERTY_PKG_PATH.'plugins/';
		}

		// extras - only needed by liberty
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
		// check for default storage plugin and add even if direct use is disabled
		if ( !in_array( 'liberty_plugin_status_bitfile', $active_plugins ) ) $active_plugins['liberty_plugin_status_bitfile'] = 'n';
		foreach( $active_plugins as $key=>$value ) {
			$pluginGuid = preg_replace( "/^{$this->mSystem}_plugin_status_/", '', $key,1 );
			if( $pluginFile = $gBitSystem->getConfig( "{$this->mSystem}_plugin_file_$pluginGuid" ) ) {
				// check for the plugin in the default location - in case bitweaver root path changed.
				if ( file_exists( $pluginFile ) ) {
					$this->mPluginFileName = basename( $pluginFile );
					include_once( $pluginFile );
				} else {
					$defaultFile = $this->mPluginPath.basename( $pluginFile );
					if( file_exists( $defaultFile ) ) {
						$this->mPluginFileName = basename( $defaultFile );
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
	 * @param string $pPluginsPath Set the path where to scan for plugins
	 * @param string $pPrefixPattern Perl regex for filenames can start with to prevent inclusion of unwanted filenames (e.g. (data\.|storage\.)). Final regex: /^{$pPrefixPattern}.*\.php$/ 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function scanAllPlugins( $pPluginsPath = NULL, $pPrefixPattern = NULL ) {
		global $gBitSystem;
		if( empty( $pPluginsPath ) ) {
			$pPluginsPath = $this->mPluginPath;
		}

		if( $pluginDir = opendir( $pPluginsPath ) ) {
			while( FALSE !== ( $plugin = readdir( $pluginDir ) ) ) {
				$pattern = "/^{$pPrefixPattern}.*\.php$/";
				if( preg_match( $pattern, $plugin ) ) {
					$this->mPluginFileName = basename( $plugin );
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
			$plugin_file = $this->mPluginPath.'format.tikiwiki.php';
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
			// zap list of plugins from DB
			$gBitSystem->setConfigMatch( "/^{$this->mSystem}_plugin_status/i", NULL, 'n', LIBERTY_PKG_NAME );
			foreach( array_keys( $this->mPlugins ) as $guid ) {
				$this->mPlugins[$guid]['is_active'] = 'n';
			}

			// set active those specified
			foreach( array_keys( $pPluginGuids ) as $guid ) {
				if( $pPluginGuids[$guid][0] == 'y' ) {
					$this->setActivePlugin( $guid );
				}
			}
			// load any plugins made active, but not already loaded
			$this->loadActivePlugins();

			// finally we need to remove all cache files since the content has been changed
			LibertyContent::expungeCache();
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
		if( !empty( $pGuid ) && !empty( $this->mPlugins[$pGuid] )) {
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
			&& ( empty( $this->mPlugins[$pGuid]['is_active'] ) || $this->mPlugins[$pGuid]['is_active'] != 'n' || $pGuid = 'bitfile' )
			&& !empty( $this->mPlugins[$pGuid][$pFunctionName] )
			&& function_exists( $this->mPlugins[$pGuid][$pFunctionName] )
		) {
			$ret = $this->mPlugins[$pGuid][$pFunctionName];
		}
		return $ret;
	}

	/**
	 * getMimeTemplate will fetch an appropriate template to display a given filetype
	 * 
	 * @param array $pTemplate Name of the template
	 * @param array $pGuid GUID of plugin
	 * @access public
	 * @return resource path to template
	 */
	function getMimeTemplate( $pTemplate, $pGuid = LIBERTY_DEFAULT_MIME_HANDLER ) {
		if( $this->isPluginActive( $pGuid ) && $plugin = $this->getPluginInfo( $pGuid )) {
			if( !empty( $plugin[$pTemplate.'_tpl'] )) {
				return $plugin[$pTemplate.'_tpl'];
			} elseif( $pGuid != LIBERTY_DEFAULT_MIME_HANDLER ) {
				return $this->getMimeTemplate( $pTemplate );
			} else {
				return NULL;
			}
		}
	}

	/**
	 * getAllMimeTemplates will fetch templates of a given type from all active plugins
	 * 
	 * @param array $pTemplate Name of the template
	 * @access public
	 * @return array of resource paths to templates
	 */
	function getAllMimeTemplates( $pTemplate ) {
		$ret = array();
		foreach( $this->getPluginsOfType( MIME_PLUGIN ) as $guid => $plugin ) {
			if( $this->isPluginActive( $guid ) && !empty( $plugin[$pTemplate.'_tpl'] )) {
				$ret = $plugin[$pTemplate.'_tpl'];
			}
		}
		return $ret;
	}

	/**
	 * getPluginsOfType will fetch all plugins of a given type
	 * 
	 * @param string $pPluginType 
	 * @access public
	 * @return an array of plugins of a given type
	 */
	function getPluginsOfType( $pPluginType ) {
		$ret = array();
		if( !empty( $pPluginType )) {
			foreach( $this->mPlugins as $guid => $plugin ) {
				if( !empty( $plugin['plugin_type'] ) && $plugin['plugin_type'] == $pPluginType ) {
					$ret[$guid] = $plugin;
				}
			}
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
	 * requireHandlerFile will require_once() the handler file if given the hash found in $gLibertySystem->mContentTypes[content_type_guid]
	 * 
	 * @param array $pContentTypeHash the hash found in $gLibertySystem->mContentTypes[content_type_guid]
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function requireHandlerFile( $pContentTypeHash ) {
		$ret = FALSE;
		if( defined( strtoupper( $pContentTypeHash['handler_package'] ).'_PKG_PATH' )) {
			require_once( constant( strtoupper( $pContentTypeHash['handler_package'] ).'_PKG_PATH' ).$pContentTypeHash['handler_file'] );
			$ret = TRUE;
		}
		return $ret;
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
		return( !empty( $gBitSystem->mPackages[$pPackageName]['service'] ) ? $gBitSystem->mPackages[$pPackageName]['service'] : NULL );
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

			$ret = BIT_BASE_URI.$ret;
		}
		return $ret;
	}

	/**
	 * Will return the plugin that is responsible for the given mime type
	 *
	 * @param string $pFileHash['mimetype'] (required if no tmp_name) Mime type of file that needs to be dealt with
	 * @param string $pFileHash['tmp_name'] (required if no mimetype) Full path to file that needs to be dealt with
	 * @access public
	 * @return handler plugin guid
	 * TODO: Currently this will return the first found handler - might want to have a sort order?
	 **/
	function lookupMimeHandler( &$pFileHash ) {
		global $gBitSystem;

		if( empty( $this->mPlugins )) {
			$this->scanAllPlugins( NULL, "mime\." );
		}

		// we will do our best to work out what this file is.
		// both these methods use a different method for fetching the filetype
		// this can be particularly important when fetching the mime-type of video files.
		// ! Windows looses the file extension when creating the tmp file
		// need a better way of handling this
		if ( !is_windows() ) {
			$pFileHash['type'] = $gBitSystem->verifyMimeType( $pFileHash['tmp_name'] );
		}
		if( $pFileHash['type'] == 'application/binary' || $pFileHash['type'] == 'application/octet-stream' || $pFileHash['type'] == 'application/octetstream' ) {
			$pFileHash['type'] = $gBitSystem->lookupMimeType( $pFileHash['name'] );
		}

		foreach( $this->mPlugins as $handler => $plugin ) {
			if( $plugin['is_active'] && !empty( $plugin['mimetypes'] ) && is_array( $plugin['mimetypes'] )) {
				foreach( $plugin['mimetypes'] as $pattern ) {
					if( preg_match( $pattern, $pFileHash['type'] )) {
						return $handler;
					}
				}
			}
		}

		return LIBERTY_DEFAULT_MIME_HANDLER;
	}
}

global $gLibertySystem;
$gLibertySystem = new LibertySystem();
?>
