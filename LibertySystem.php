<?php
/**
* System class for handling the liberty package
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
 * Local base defines
 */
// Plugin Definitions
define( 'STORAGE_PLUGIN', 'storage' );
define( 'FORMAT_PLUGIN', 'format' );
define( 'DATA_PLUGIN', 'data' );
define( 'MIME_PLUGIN', 'mime' );
define( 'FILTER_PLUGIN', 'filter' );

if( !defined( 'LIBERTY_DEFAULT_MIME_HANDLER' )) {
	define( 'LIBERTY_DEFAULT_MIME_HANDLER', 'mimedefault' );
}

// Service Definitions
define( 'LIBERTY_SERVICE_ACCESS_CONTROL', 'access_control' );
define( 'LIBERTY_SERVICE_CATEGORIZATION', 'categorization' );
define( 'LIBERTY_SERVICE_COMMERCE', 'commerce' );
define( 'LIBERTY_SERVICE_CONTENT_TEMPLATES', 'content_templates' );
define( 'LIBERTY_SERVICE_DOCUMENT_GENERATION', 'document_generation' );
define( 'LIBERTY_SERVICE_FORUMS', 'forums' );
define( 'LIBERTY_SERVICE_GROUP', 'groups' );
define( 'LIBERTY_SERVICE_MAPS', 'map_display' );
define( 'LIBERTY_SERVICE_METADATA', 'metadata' );
define( 'LIBERTY_SERVICE_MENU', 'menu' );
define( 'LIBERTY_SERVICE_RATING', 'rating' );
define( 'LIBERTY_SERVICE_REBLOG', 'reblogging_rss_feeds' );
define( 'LIBERTY_SERVICE_SEARCH', 'search' );
define( 'LIBERTY_SERVICE_THEMES', 'themes' );
define( 'LIBERTY_SERVICE_TOPICA', 'topica' );
define( 'LIBERTY_SERVICE_TRANSLATION', 'translation' );
define( 'LIBERTY_SERVICE_TRANSLITERATION', 'transliteration' );
define( 'LIBERTY_SERVICE_LIBERTYSECURE', 'security' );
define( 'LIBERTY_SERVICE_MODCOMMENTS', 'comment_moderation' );
define( 'LIBERTY_SERVICE_UPLOAD', 'upload' );

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
	function __construct( $pExtras = TRUE ) {
		parent::__construct();

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
		// all active plugins
		$configs = array_keys( $gBitSystem->getConfigMatch( "/^{$this->mSystem}_plugin_status_/i", 'y' ));

		// first we include the default one - this allows other plugins to make use of default functions
		if( $this->mSystem == LIBERTY_PKG_NAME ) {
			if( $key = array_search( 'liberty_plugin_status_'.LIBERTY_DEFAULT_MIME_HANDLER , $configs )) {
				unset( $configs[$key] );
			}
			array_unshift( $configs, 'liberty_plugin_status_'.LIBERTY_DEFAULT_MIME_HANDLER );
		}

		foreach( $configs as $config ) {
			$pluginGuid = preg_replace( "/^{$this->mSystem}_plugin_status_/", '', $config, 1 );
			if( $pluginFile = $gBitSystem->getConfig( "{$this->mSystem}_plugin_path_$pluginGuid" ) ) {
				if( is_file( BIT_ROOT_PATH.$pluginFile )) {
					$this->mPluginFilePath = BIT_ROOT_PATH.$pluginFile;
					include_once( BIT_ROOT_PATH.$pluginFile );
				}
			} elseif( $pluginFile = $gBitSystem->getConfig( "{$this->mSystem}_plugin_file_$pluginGuid" ) ) {
				// TODO: all this is deprecated and doesn't really rock bitweavers boat anymore - we use the _plugin_path_ setting now.
				// this code here is only relevant if a user has updated bitweaver and scanAllPlugins() hasn't been called yet.
				// scanAllPlugins() is called during the upgrade in the installer so we really are only keeping this here for CVS users
				// and people who use nexus since it makes use of this plugin system as well.
				// - xing - Saturday Jul 05, 2008   20:47:29 CEST

				// check for the plugin in the default location - in case bitweaver root path changed.

				if( file_exists( $pluginFile )) {
					$this->mPluginFilePath = $pluginFile;
					include_once( $pluginFile );
				} else {
					$defaultFile = $this->mPluginPath.basename( $pluginFile );
					if( file_exists( $defaultFile )) {
						$this->mPluginFilePath = $defaultFile;
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
		if( empty( $pPluginsPath )) {
			$pPluginsPath = $this->mPluginPath;
		}

		// check for plugins in plugins/ dir
		if( $pluginHandle = opendir( $pPluginsPath )) {
			while( FALSE !== ( $plugin = readdir( $pluginHandle ) ) ) {
				$pattern = "/^{$pPrefixPattern}.*\.php$/";
				if( preg_match( $pattern, $plugin ) ) {
					$this->mPluginFilePath = $pPluginsPath.$plugin;
					include_once( $pPluginsPath.$plugin );
				}
			}
		}

		// check for liberty plugins in other packages as well
		if( $this->mSystem == LIBERTY_PKG_NAME && $pkgHandle = opendir( BIT_ROOT_PATH )) {
			while( FALSE !== ( $dirName = readdir( $pkgHandle ))) {
				if( preg_match( '/^\w/', $dirName )  && $dirName != 'CVS' && is_dir( $pluginDir = BIT_ROOT_PATH.$dirName.'/liberty_plugins/' ) && ( $pluginHandle = opendir( $pluginDir ))) {
					while( FALSE !== ( $plugin = readdir( $pluginHandle ))) {
						if( preg_match( "/^{$pPrefixPattern}.*\.php$/", $plugin )) {
							$this->mPluginFilePath = $pluginDir.$plugin;
							include_once( $pluginDir.$plugin );
						}
					}
				}
			}
		}

		// keep plugin list in sorted order
		if( !empty( $this->mPlugins ) and is_array( $this->mPlugins ) ) {
			asort( $this->mPlugins );
		}

		// only execute the following if this class hasn't been extended
		if( $this->mSystem == LIBERTY_PKG_NAME ) {
			// There must be at least one format plugin active and set as the default format
			$format_plugin_count = $default_format_found = 0;
			$current_default_format_guid = $gBitSystem->getConfig( 'default_format' );
			foreach( $this->mPlugins as $guid => $plugin ) {
				// load all the requirements that we can display them on the plugin page
				if( $requirement_func = $this->getPluginFunction( $guid, 'requirement_function', FALSE, TRUE )) {
					$this->mPlugins[$guid]['requirements'] = $requirement_func();
				}

				if( $this->isPluginActive( $guid )) {
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
			$plugin_file = $this->mPluginPath.'format.tikiwiki.php';
			if( $format_plugin_count == 0 || $default_format_found == 0 && is_file( $plugin_file ) ) {
				require_once( $plugin_file );
				$this->setActivePlugin( PLUGIN_GUID_TIKIWIKI );
				$gBitSystem->storeConfig( 'default_format', PLUGIN_GUID_TIKIWIKI, $this->mSystem );
				//make memory match db
				$this->loadActivePlugins();
			}
		}

		// remove any config settings for plugin files that have been removed
		$plugins = $gBitSystem->getConfigMatch( "/^{$this->mSystem}_plugin_path_/" );
		foreach( $plugins as $config => $path ) {
			if( !is_file( BIT_ROOT_PATH.$path )) {
				$guid = str_replace( "{$this->mSystem}_plugin_path_", '', $config );
				$gBitSystem->storeConfigMatch( "/^{$this->mSystem}_plugin_\w+_$guid/i", NULL );
			}
		}

		// TODO: we can remove this at some point since it's not really important - it just clears out stuff from the database that we don't use anymore
		$gBitSystem->storeConfigMatch( "/^{$this->mSystem}_plugin_file_/", NULL );
	}

	/**
	 * Check to see if a given plugin is activ or not
	 *
	 * @param $pPluginGuid Plugin GUID of the plugin you want to check
	 * @return TRUE if the plugin is active, FALSE if it's not
	 **/
	function isPluginActive( $pPluginGuid ) {
		return( !empty( $this->mPlugins[$pPluginGuid]['is_active'] ) && ( $this->mPlugins[$pPluginGuid]['is_active'] == 'y' ));
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
		$this->mDataTags[strtolower( $pTag )] = $pPluginGuid;
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
		// plugins can set their own file_name. this is not mandatory but makes sure we store the path to the correct file
		// this is useful for files that are included by other plugins
		if( !empty( $pPluginParams['file_name'] )) {
			$pluginPath = dirname( $this->mPluginFilePath )."/".$pPluginParams['file_name'];
		} else {
			$pluginPath = $this->mPluginFilePath;
		}

		if( !empty( $pGuid ) && !empty( $pluginPath ) && is_file( $pluginPath ) ) {
			// store the relative path - we need to store the path to all plugins and not just active ones since we don't have access to this information when we use setActivePlugins()
			$gBitSystem->storeConfig( "{$this->mSystem}_plugin_path_".$pGuid, str_replace( BIT_ROOT_PATH, "", $pluginPath ), LIBERTY_PKG_NAME );
			$settings['is_active'] = $gBitSystem->getConfig( "{$this->mSystem}_plugin_status_".$pGuid );
			if( empty( $settings['is_active'] ) && !empty( $pPluginParams['auto_activate'] )) {
				$this->setActivePlugin( $pGuid );
			}
			$settings['plugin_guid'] = $pGuid;
			$this->mPlugins[$pGuid]  = array_merge( $settings, $pPluginParams );
		}

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
			$gBitSystem->storeConfigMatch( "/^{$this->mSystem}_plugin_status/i", NULL, 'n', LIBERTY_PKG_NAME );
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

		// the requirement function can return a set of tables, indexes and sequences that need to be created for the plugin to work.
		if( $requirement_func = $this->getPluginFunction( $pPluginGuid, 'requirement_function' )) {
			$reqs = $requirement_func( TRUE );
			if( !empty( $reqs['schema']['tables'] )) {
				// fetch a list of tables in the database that we know if we need to insert any plugin ones
				if( strlen( BIT_DB_PREFIX ) > 0 ) {
					$lastQuote = strrpos( BIT_DB_PREFIX, '`' );
					if( $lastQuote != FALSE ) {
						$lastQuote++;
					}
					$prefix = substr( BIT_DB_PREFIX, $lastQuote );
				} else {
					$prefix = '';
				}

				global $gBitDbType, $gBitDbHost, $gBitDbUser, $gBitDbPassword, $gBitDbName;
				$db = &ADONewConnection( $gBitDbType );
				if( $db->Connect( $gBitDbHost, $gBitDbUser, $gBitDbPassword, $gBitDbName )) {
					$dict = NewDataDictionary( $db );

					if( !$gBitSystem->mDb->getCaseSensitivity() ) {
						$dict->connection->nameQuote = '';
					}

					if( $dbTables = $gBitSystem->mDb->MetaTables( 'TABLES', FALSE, ( $prefix ? $prefix.'%' : NULL ))) {
						// If we use MySql check which storage engine to use
						if( isset( $_SESSION['use_innodb'] )) {
							if( $_SESSION['use_innodb'] == TRUE ) {
								$build = array( 'NEW', 'MYSQL' => 'ENGINE=INNODB' );
							} else {
								$build = array( 'NEW', 'MYSQL' => 'ENGINE=MYISAM' );
							}
						} else {
							$build = 'NEW';
						}

						// create tables
						foreach( $reqs['schema']['tables'] as $table => $tableDict ) {
							$fullTable = $prefix.$table;
							if( !in_array( $fullTable, $dbTables )) {
								if( $sql = $dict->CreateTableSQL( $fullTable, $tableDict, $build )) {
									$ret = $dict->ExecuteSQLArray( $sql );
									if( $ret === FALSE ) {
										$errors[] = 'Failed to create table '.$completeTableName;
										$tablesInstalled = TRUE;
									}
								}
							}
						}

						// only continue if we installed at least one table
						if( !empty( $tablesInstalled )) {
							$schemaQuote = strrpos( BIT_DB_PREFIX, '`' );
							$sequencePrefix = ( $schemaQuote ? substr( BIT_DB_PREFIX,  $schemaQuote + 1 ) : BIT_DB_PREFIX );

							// create indexes
							if( !empty( $reqs['schema']['indexes'] )) {
								foreach( $reqs['schema']['indexes'] as $idx => $idxDict ) {
									$completeTableName = $sequencePrefix.$reqs['schema']['indexes'][$idx]['table'];
									if( $sql = $dict->CreateIndexSQL( $idx, $completeTableName, $reqs['schema']['indexes'][$idx]['cols'], $reqs['schema']['indexes'][$idx]['opts'] )) {
										$ret = $dict->ExecuteSQLArray( $sql );
										if( $ret === FALSE ) {
											$errors[] = 'Failed to create index '.$completeTableName;
										}
									}
								}
							}

							// create sequences
							if( !empty( $reqs['schema']['sequences'] )) {
								// If we use InnoDB for MySql we need this to get sequence tables created correctly.
								if( isset( $_SESSION['use_innodb'] ) ) {
									if( $_SESSION['use_innodb'] == TRUE ) {
										$gBitInstallDb->_genSeqSQL = "create table %s (id int not null) ENGINE=INNODB";
									} else {
										$gBitInstallDb->_genSeqSQL = "create table %s (id int not null) ENGINE=MYISAM";
									}
								}

								foreach( array_keys( $reqs['schema']['sequences'] ) as $sequenceIdx ) {
									if( !$gBitInstallDb->CreateSequence( $sequencePrefix.$sequenceIdx, $reqs['schema']['sequences'][$sequenceIdx]['start'] )) {
										$errors[] = 'Failed to create sequence '.$sequencePrefix.$sequenceIdx;
									}
								}
							}
						}
					}
				}
			}
		}

		return( !empty( $errors ) ? $errors : NULL );
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
	 * @param string $pGuid GUID of plugin used - if empty, we get all available functions of that type in all active plugins
	 * @param string $pFunctionName Function type we want to use
	 * @param string $pGetDefault Get default function for a given plugin type such as 'mime'
	 * @param string $pGetInactive don't worry if plugin is active or not
	 * @access public
	 * @return function name on success, NULL on failure
	 */
	function getPluginFunction( $pGuid, $pFunctionName, $pGetDefault = FALSE, $pGetInactive = FALSE ) {
		if(( $this->isPluginActive( $pGuid ) || $pGetInactive ) && !empty( $this->mPlugins[$pGuid][$pFunctionName] ) && function_exists( $this->mPlugins[$pGuid][$pFunctionName] )) {
			$ret = $this->mPlugins[$pGuid][$pFunctionName];
		}

		// if we can't get a function on the first round, we fetch the default
		if( empty( $ret ) && $pGetDefault == 'mime' && $pGuid != LIBERTY_DEFAULT_MIME_HANDLER ) {
			$ret = $this->getPluginFunction( LIBERTY_DEFAULT_MIME_HANDLER, $pFunctionName );
		}

		return( !empty( $ret ) ? $ret : NULL );
	}

	/**
	 * getPluginFunctions Get a list of functions of a given type
	 *
	 * @param string $pFunctionName Function type we want to get
	 * @access public
	 * @return array of functions with the GUID as key
	 */
	function getPluginFunctions( $pFunctionName ) {
		foreach( $this->mPlugins as $guid => $plugin ) {
			if( $this->isPluginActive( $guid ) && !empty( $plugin[$pFunctionName] ) && function_exists( $plugin[$pFunctionName] )) {
				$ret[$guid] = $plugin[$pFunctionName];
			}
		}

		return( !empty( $ret ) ? $ret : array() );
	}

	/**
	 * getMimeTemplate will fetch an appropriate template to display a given filetype
	 *
	 * @param string $pTemplate Basename of the template
	 * @param string $pGuid GUID of plugin
	 * @access public
	 * @return resource path to template
	 */
	function getMimeTemplate( $pTemplate, $pGuid = LIBERTY_DEFAULT_MIME_HANDLER ) {
		$ret = NULL;
		if( $this->isPluginActive( $pGuid ) && ( $plugin = $this->getPluginInfo( $pGuid )) && !empty( $plugin[$pTemplate.'_tpl'] )) {
			$ret = $plugin[$pTemplate.'_tpl'];
		} elseif( $pGuid != LIBERTY_DEFAULT_MIME_HANDLER ) {
			$ret = $this->getMimeTemplate( $pTemplate );
		}
		return $ret;
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
				$ret[] = $plugin[$pTemplate.'_tpl'];
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
		$gBitSystem->storeConfigMatch( "/^{$this->mSystem}_plugin_/", NULL );
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
				// translate name
				// content_description backward compatibility for now
				$row['content_description'] = $row['content_name'] = tra( $row['content_name'] );
				if( !empty( $row['content_name_plural'] ) ){
					$row['content_name_plural'] = tra( $row['content_name_plural'] );
				}
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
		global $gBitSystem;
		if ( !$this->mDb->isValid() ) return;
		if( !isset( $this->mContentTypes ) ) {
			$this->loadContentTypes();
		}
		$pTypeParams['content_type_guid'] = $pGuid;
		// automagically populate plural name value if none is set using most comment english of appending 's'
		if( empty( $pTypeParams['content_name_plural'] ) ){
			$pTypeParams['content_name_plural'] = $pTypeParams['content_name'].'s';
		}
		$this->mDb->StartTrans();
		if( empty( $this->mContentTypes[$pGuid] ) && !empty( $pTypeParams ) ) {
			$result = $this->mDb->associateInsert( BIT_DB_PREFIX."liberty_content_types", $pTypeParams );
			// we just ran some SQL - let's flush the loadContentTypes query cache
			$this->loadContentTypes( 0 );
		} else {
			if( $pTypeParams['handler_package'] != $this->mContentTypes[$pGuid]['handler_package'] ||
				$pTypeParams['handler_file'] != $this->mContentTypes[$pGuid]['handler_file'] ||
				$pTypeParams['handler_class'] != $this->mContentTypes[$pGuid]['handler_class'] ||
				( empty( $this->mContentTypes[$pGuid]['content_name_plural'] ) && version_compare( $gBitSystem->getVersion( LIBERTY_PKG_NAME ), '2.1.4', '>=' ) ) // temporary update condition during migration of content_description to content_name remove after april 20 2011
				) {
				$result = $this->mDb->associateUpdate( BIT_DB_PREFIX."liberty_content_types", $pTypeParams, array( 'content_type_guid'=>$pGuid ) );
				// we just ran some SQL - let's flush the loadContentTypes query cache
				$this->loadContentTypes( 0 );
			}
		}
		$this->mDb->CompleteTrans();
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
	 * Get the display name of the content type
	 * @param boolean $pPlural true will return the plural form of the content type display name
	 * @return string the display name of the content type
 	 */
	function getContentType( $pContentTypeGuid ){
		$ret = NULL;
		if( !isset( $this->mContentTypes ) ) {
			$this->loadContentTypes();
		}
		if( !empty( $this->mContentTypes[$pContentTypeGuid] ) ) {
		 	$ret = $this->mContentTypes[$pContentTypeGuid];
		}
		return $ret;
	}

	/**
	 * Get the display name of the content type
	 * @param boolean $pPlural true will return the plural form of the content type display name
	 * @return string the display name of the content type
 	 */
	function getContentClassName( $pContentTypeGuid ) {
		$ret = NULL;
		if( !isset( $this->mContentTypes ) ) {
			$this->loadContentTypes();
		}
		if( !empty( $this->mContentTypes[$pContentTypeGuid] ) && $this->requireHandlerFile( $this->mContentTypes[$pContentTypeGuid] ) ) {
		 	$ret = $this->mContentTypes[$pContentTypeGuid]['handler_class'];
		}
		return $ret;
	}

	/**
	 * Get the display name of the content type
	 * @param boolean $pPlural true will return the plural form of the content type display name
	 * @return string the display name of the content type
 	 */
	function getContentTypeName( $pContentTypeGuid, $pPlural=FALSE ){
		$ret = NULL;
		if( $pPlural && isset( $this->mContentTypes[$pContentTypeGuid]['content_name_plural'] ) ) {
			$ret = tra( $this->mContentTypes[$pContentTypeGuid]['content_name_plural'] );
		} elseif( !empty( $this->mContentTypes[$pContentTypeGuid]['content_name'] ) ) {
		 	$ret = tra( $this->mContentTypes[$pContentTypeGuid]['content_name'] );
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
		deprecated( 'You are calling the deprecated method getContentTypeDescription, use getContentTypeName( $pPlural )' );
		return $this->getContentTypeName( $pContentType );
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
	function registerService( $pServiceName, $pPackageName, $pServiceHash, $pOptions = array()  ) {
		$this->mServices[$pServiceName] = array(
		   										'package' => $pPackageName,
		   										'services'	=> $pServiceHash,
												'description' => !empty( $pOptions['description'] ) ? $pOptions['description'] : NULL,
												'required' => !empty( $pOptions['required'] ) ? $pOptions['required'] : FALSE,
											 );
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
					// DEPRECATED - this is mostly circular logic - getting the package name from itself to look itself up
					// Service names are key values - regardless of package
					// Accessing services directly by name infact allows multiple packages to provide the same kind of service
					/*
					if( !($package = $gBitSystem->getConfig( 'liberty_service_'.$service )) ) {
						$package = key( $this->mServices[$service] );
					}
					if( !empty( $this->mServices[$service][$package][$pServiceValue] ) ) {
						$ret[$service] = $this->mServices[$service][$package][$pServiceValue];
					}
					*/
					if( !empty( $this->mServices[$service]['services'][$pServiceValue] ) ) {
						$ret[$service] = $this->mServices[$service]['services'][$pServiceValue];
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
	public static function getMimeThumbnailURL($pMimeType, $pExt=NULL) {
		$ret = NULL;
		$parts = explode( '/',$pMimeType );
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
		if( !is_windows() ) {
			$pFileHash['type'] = $gBitSystem->verifyMimeType( $pFileHash['tmp_name'] );
		}

		if( $pFileHash['type'] == 'application/binary' || $pFileHash['type'] == 'application/octet-stream' || $pFileHash['type'] == 'application/octetstream' ) {
			$pFileHash['type'] = $gBitSystem->lookupMimeType( $pFileHash['name'] );
		}

		foreach( $this->getPluginsOfType( MIME_PLUGIN ) as $handler => $plugin ) {
			if( $this->isPluginActive( $handler ) && !empty( $plugin['mimetypes'] ) && is_array( $plugin['mimetypes'] )) {
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
?>
