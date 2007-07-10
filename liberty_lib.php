<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/liberty_lib.php,v 1.2 2007/07/10 20:47:43 squareing Exp $
 * @package liberty
 * @subpackage functions
 */

// ================== Liberty Plugin Parsing ==================
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
	global $gLibertySystem, $gBitSystem;
	// Find the plugins
	if( $gBitSystem->isPackageActive( 'stencil' ) ) {
		require_once( STENCIL_PKG_PATH.'BitStencil.php' );
		$data = preg_replace_callback("/\{\{\/?([^|]+)([^\}]*)\}\}/", 'parse_stencil_data', $data );
	}

	// note: $curlyTags[0] is the complete match, $curlyTags[1] is plugin name, $curlyTags[2] is plugin arguments
	preg_match_all("/\{\/?([A-Za-z0-9]+)([^\}]*)\}/", $data, $curlyTags);

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


// ================== Liberty Plugin Helper ==================
/**
 * pass in the plugin paramaters and out comes a hash with usable styling information
 * 
 * @param array $pParamHash 
 * @access public
 * @return hash full of styling goodies
 */
function liberty_plugins_wrapper_style( $pParamHash ) {
	global $gBitSystem;

	$ret = array();
	$ret['style'] = $ret['description'] = '';

	if( !empty( $pParamHash ) && is_array( $pParamHash )) {
		// if align is right and text-align isn't set, we'll align that right as well
		if( empty( $pParamHash['text-align'] ) && ( !empty( $pParamHash['align'] ) && $pParamHash['align'] == 'right' || !empty( $pParamHash['align'] ) && $pParamHash['align'] == 'right' )) {
			$pParamHash['text-align'] = 'right';
		}

		// this defines what the wrapper should be - div or span
		// if someone sets this value manually, they know what they are doing
		if( empty( $pParamHash['wrapper'] )) {
			$pParamHash['wrapper'] = 'div';

			if( $gBitSystem->isFeatureActive( 'liberty_use_span_wrapper' )) {
				// set to 'span' if desired
				$pParamHash['wrapper'] = 'span';

				// force display:block to the "div" if not specified otherwise
				if( empty( $pParamHash['display'] )) {
					$pParamHash['display'] = "block";
				}
			}
		}

		foreach( $pParamHash as $key => $value ) {
			if( !empty( $value )) {
				switch( $key ) {
					// description
					case 'desc':
						$key = 'description';
					case 'description':
						$ret[$key] = $value;
						break;
					// styling
					case 'width':
					case 'height':
						if( preg_match( "/^\d+(em|px|%|pt)$/", trim( $value ))) {
							$ret['style'] .= "{$key}:{$value};";
						} elseif( preg_match( "/^\d+$/", $value )) {
							$ret['style'] .= "{$key}:{$value}px;";
						}
						break;
					case 'background':
					case 'background-color':
					case 'float':
					case 'padding':
					case 'margin':
					case 'background':
					case 'border':
					case 'overflow':
					case 'text-align':
					case 'color':
					case 'font':
					case 'font-size':
					case 'font-weight':
					case 'font-family':
					case 'display':
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


// ================== Liberty Service Functions ==================
/**
 * liberty_content_load_sql 
 * 
 * @access public
 * @return content load sql
 */
function liberty_content_load_sql() {
	global $gBitSystem, $gBitUser;
	$ret = array();
	if ($gBitSystem->isFeatureActive('liberty_display_status') && !$gBitUser->hasPermission('p_liberty_edit_all_status')) {
		$ret['where_sql'] = " AND lc.`content_status_id` < 100 AND ( (lc.`user_id` = '".$gBitUser->getUserId()."' AND lc.`content_status_id` > -100) OR lc.`content_status_id` > 0 )";
	}
	// Make sure owner comes out properly for all content
	if ($gBitSystem->isFeatureActive('liberty_allow_change_owner') && $gBitUser->hasPermission('p_liberty_edit_content_owner')) {
		$ret['select_sql'] = " , lc.`user_id` AS owner_id";
	}
	return $ret;
}

/**
 * liberty_content_list_sql 
 * 
 * @access public
 * @return content list sql
 */
function liberty_content_list_sql() {
	global $gBitSystem, $gBitUser;
	$ret = array();
	if ($gBitSystem->isFeatureActive('liberty_display_status') && !$gBitUser->hasPermission('p_liberty_edit_all_status')) {
		$ret['where_sql'] = " AND lc.`content_status_id` < 100 AND ( (lc.`user_id` = '".$gBitUser->getUserId()."' AND lc.`content_status_id` > -100) OR lc.`content_status_id` > 0 )";
	}
	return $ret;
}

/**
 * liberty_content_preview 
 * 
 * @param array $pObject 
 * @access public
 * @return void
 */
function liberty_content_preview( &$pObject ) {
	global $gBitSystem, $gBitUser;
	if ($gBitSystem->isFeatureActive('liberty_display_status') && ($gBitUser->hasPermission('p_liberty_edit_content_status') || $gBitUser->hasPermission('p_libert_edit_all_status'))) {
		$pObject->mInfo['content_status_id'] = $_REQUEST['content_status_id'];
	}
	if ($gBitSystem->isFeatureActive('liberty_allow_change_owner') && $gBitUser->hasPermission('p_liberty_edit_content_owner')) {
		$pObject->mInfo['owner_id'] = $_REQUEST['owner_id'];
	}
}

/**
 * liberty_content_display 
 * 
 * @param array $pObject 
 * @param array $pParamHash 
 * @access public
 * @return void
 */
function liberty_content_display( &$pObject, &$pParamHash ) {
	if( $pObject->isValid() ) {
		global $gBitUser, $gBitSystem;

		// make sure user has appropriate permissions to view this content
		if( !empty( $pParamHash['perm_name'] )) {
			$pObject->verifyPermission( $pParamHash['perm_name'] );
		}
	}
}


// ================== Liberty File Processing Functions ==================
/**
 * Process uploaded files. Will automagically generate thumbnails for images
 *
 * @param array $pFileHash Data require to process the files
 * @param array $pFileHash['upload']['name'] (required) Name of the uploaded file
 * @param array $pFileHash['upload']['type'] (required) Mime type of the file uploaded
 * @param array $pFileHash['upload']['dest_path'] (required) Relative path where you want to store the file
 * @param array $pFileHash['upload']['source_file'] (required) Absolute path to file including file name
 * @param boolean $pFileHash['upload']['thumbnail'] (optional) Set to FALSE if you don't want to generate thumbnails
 * @param array $pFileHash['upload']['thumbnail_sizes'] (optional) Decide what sizes thumbnails you want to create: icon, avatar, small, medium, large
 * @param boolean $pMoveFile (optional) specify if you want to move or copy the original file
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_process_upload( &$pFileHash ) {
	// Check for evil file extensions that could be execed on the server
	if( preg_match( '/(.pl|.php|.php3|.php4|.phtml|.py|.cgi|.asp|.jsp|.sh|.shtml)$/', $pFileHash['upload']['name'] ) ) {
		$pFileHash['upload']['type'] = 'text/plain';
		$pFileHash['upload']['name'] = $pFileHash['upload']['name'].'.txt';
	}
	// Thumbs.db is a windows My Photos/ folder file, and seems to really piss off imagick
	if( (preg_match( '/^image\/*/', $pFileHash['upload']['type'] ) || preg_match( '/pdf/i', $pFileHash['upload']['type'] ) ) && $pFileHash['upload']['name'] != 'Thumbs.db' ) {
		$ret = liberty_process_image( $pFileHash['upload'] );
	} else {
		$ret = liberty_process_generic( $pFileHash['upload'] );
	}
	return $ret;
}

/**
 * liberty_process_archive
 *
 * @param array $pFileHash
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_process_archive( &$pFileHash ) {
	// sanity check: make sure tmp_name isn't empty. will scan / if it is
	if( !is_array( $pFileHash ) || empty( $pFileHash['tmp_name'] ) || empty( $pFileHash['name'] ) ) {
		return FALSE;
	}

	$cwd = getcwd();
	// if the file has been uploaded using a form, we'll process the uploaded
	// file directly. if it's been ftp uploaded or some other method used,
	// we'll copy the file. in the case of xuploaded files, the files have been
	// processed but don't have to be copied
	if( empty( $pFileHash['preprocessed'] ) && !is_uploaded_file( $pFileHash['tmp_name'] ) && is_file( $pFileHash['tmp_name'] ) ) {
		$tmpDir = get_temp_dir();
		$copyFile = tempnam( !empty( $tmpDir ) ? $tmpDir : '/tmp', $pFileHash['name'] );
		copy( $pFileHash['tmp_name'], $copyFile );
		$pFileHash['tmp_name'] = $copyFile;
	}

	$dir = dirname( $pFileHash['tmp_name'] );
	$upExt = strtolower( substr( $pFileHash['name'], ( strrpos( $pFileHash['name'], '.' ) + 1 ) ) );
	$baseDir = $dir.'/';
	if( is_file( $pFileHash['tmp_name'] ) ) {
		global $gBitUser;
		$baseDir .= $gBitUser->mUserId;
	}

	$destDir = $baseDir.'/'.basename( $pFileHash['tmp_name'] );
	// this if is very important logic back so subdirs get processed properly
	if( ( is_dir( $baseDir ) || mkdir( $baseDir ) ) && @mkdir( $destDir ) ) {
		// Some commands don't nicely support extracting to other directories
		chdir( $destDir );
		list( $mimeType, $mimeExt ) = split( '/', strtolower( $pFileHash['type'] ) );
		switch( $mimeExt ) {
			case 'x-rar-compressed':
			case 'x-rar':
				$shellResult = shell_exec( "unrar x \"{$pFileHash['tmp_name']}\" \"$destDir\"" );
				break;
			case 'x-bzip2':
			case 'bzip2':
			case 'x-gzip':
			case 'gzip':
			case 'x-tgz':
			case 'x-tar':
			case 'tar':
				switch( $upExt ) {
					case 'gz':
					case 'tgz': $compressFlag = '-z'; break;
					case 'bz2': $compressFlag = '-j'; break;
					default: $compressFlag = ''; break;
				}
				$shellResult = shell_exec( "tar -x $compressFlag -f \"{$pFileHash['tmp_name']}\"  -C \"$destDir\"" );
				break;
			case 'x-zip-compressed':
			case 'x-zip':
			case 'zip':
				$shellResult = shell_exec( "unzip \"{$pFileHash['tmp_name']}\" -d \"$destDir\"" );
				break;
			case 'x-stuffit':
			case 'stuffit':
				$shellResult = shell_exec( "unstuff -d=\"$destDir\" \"{$pFileHash['tmp_name']}\" " );
				break;
			default:
				if( $upExt == 'zip' ) {
					$shellResult = shell_exec( "unzip \"{$pFileHash['tmp_name']}\" -d \"$destDir\"" );
				} elseif( $upExt == 'rar' ) {
					$shellResult = shell_exec( "unrar x \"{$pFileHash['tmp_name']}\" \"$destDir\"" );
				} elseif( $upExt == 'sit' || $upExt == 'sitx' ) {
					print( "unstuff -d=\"$destDir\" \"{$pFileHash['tmp_name']}\" " );
					$shellResult = shell_exec( "unstuff -d=\"$destDir\" \"{$pFileHash['tmp_name']}\" " );
				} else {
					$destDir = NULL;
				}
				break;
		}
	}
	//vd($shellResult);
	chdir( $cwd );

	// if we created a copy of the original, we remove it
	if( !empty( $copyFile ) ) {
		@unlink( $copyFile );
	}

	if( preg_match( "!^/+$!", $destDir )) {
		// obviously something went horribly wrong
		return FALSE;
	} else {
		return $destDir;
	}
}

/**
 * liberty_process_generic
 *
 * @param array $pFileHash
 * @param array $pMoveFile
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_process_generic( &$pFileHash, $pMoveFile=TRUE ) {
	$ret = NULL;
	$destBase = $pFileHash['dest_path'].$pFileHash['name'];
	$actualPath = BIT_ROOT_PATH.$destBase;
	if( is_file( $pFileHash['source_file']) ) {
		if( $pFileHash['source_file'] == $actualPath ) {
			// do nothing if source and dest are the same
		} elseif( $pMoveFile ) {
			if( is_uploaded_file( $pFileHash['source_file'] ) ) {
				move_uploaded_file( $pFileHash['source_file'], $actualPath );
			} else {
				rename( $pFileHash['source_file'], $actualPath );
			}
		} else {
			copy( $pFileHash['source_file'], $actualPath );
		}
		$ret = $destBase;
	}
	$pFileHash['size'] = filesize( $actualPath );

	return $ret;
}


/**
 * liberty_process_image
 *
 * @param array $pFileHash
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_process_image( &$pFileHash ) {
	global $gBitSystem;
	$ret = NULL;

	list($type, $ext) = split( '/', strtolower( $pFileHash['type'] ) );
	mkdir_p( BIT_ROOT_PATH.$pFileHash['dest_path'] );
	if( $resizePath = liberty_process_generic( $pFileHash ) ) {
		$pFileHash['source_file'] = BIT_ROOT_PATH.$resizePath;
		//set permissions if possible - necessary for some wonky shared hosting environments
		if(chmod($pFileHash['source_file'], 0644)){
			//does nothing, but fails elegantly
		}
		$nameHold = $pFileHash['name'];
		$sizeHold = $pFileHash['size'];
		$ret = $pFileHash['source_file'];
		// do not thumbnail only if intentionally set to FALSE
		if( !isset( $pFileHash['thumbnail'] ) || $pFileHash['thumbnail']==TRUE ) {
			liberty_generate_thumbnails( $pFileHash );
		}
		$pFileHash['name'] = $nameHold;
		$pFileHash['size'] = $sizeHold;
	}
	return $ret;
}

/**
 * liberty_clear_thumbnails will clear all thummbnails found in a given directory
 *
 * @param array $pFileHash['dest_path'] should contain the path to the dir where we should remove thumbnails
 * @access public
 * @return TRUE on success, FALSE on failure
 */
function liberty_clear_thumbnails( &$pFileHash ) {
	if( !empty( $pFileHash['dest_path'] )) {
		// get thumbnails we want to remove
		if( $thumbs = liberty_fetch_thumbnails( $pFileHash['dest_path'], NULL, NULL, FALSE )) {
			foreach( $thumbs as $thumb ) {
				$thumb = BIT_ROOT_PATH.$thumb;
				if( is_writable( $thumb ) ) {
					unlink( $thumb );
				}
			}
			// just to make sure that we have all thumbnails cleared, we run through another round
			if( $thumbs = liberty_fetch_thumbnails( $pFileHash['dest_path'], NULL, NULL, FALSE )) {
				foreach( $thumbs as $thumb ) {
					$thumb = BIT_ROOT_PATH.$thumb;
					if( is_writable( $thumb ) ) {
						unlink( $thumb );
					}
				}
			}
		}
	}
	return TRUE;
}

/**
 * liberty_get_function
 *
 * @param array $pType
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_get_function( $pType ) {
	global $gBitSystem;
	return 'liberty_'.$gBitSystem->getConfig( 'image_processor', 'gd' ).'_'.$pType.'_image';
}

/**
 * liberty_generate_thumbnails
 *
 * @param array $pFileHash
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_generate_thumbnails( &$pFileHash ) {
	global $gBitSystem, $gThumbSizes;
	$resizeFunc = liberty_get_function( 'resize' );

	// allow custom selecteion of thumbnail sizes
	if( empty( $pFileHash['thumbnail_sizes'] ) ) {
		$pFileHash['thumbnail_sizes'] = array( 'icon', 'avatar', 'small', 'medium', 'large' );
	}

	if(
		( !preg_match( '/image\/(gif|jpg|jpeg|png)/', strtolower( $pFileHash['type'] )) && $gBitSystem->isFeatureActive( 'liberty_jpeg_originals' ))
		|| in_array( 'original', $pFileHash['thumbnail_sizes'] )
	) {
		// jpeg version of original
		$pFileHash['dest_base_name'] = 'original';
		$pFileHash['name'] = 'original.jpg';
		$pFileHash['max_width'] = MAX_THUMBNAIL_DIMENSION;
		$pFileHash['max_height'] = MAX_THUMBNAIL_DIMENSION;
		$pFileHash['original_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
	}

	// override $mimeExt if we have a custom setting for it
	if( $gBitSystem->isFeatureActive( 'liberty_thumbnail_format' )) {
		$mimeExt = $gBitSystem->getConfig( 'liberty_thumbnail_format' );
	} else {
		list( $type, $mimeExt ) = split( '/', strtolower( $pFileHash['type'] ));
	}

	if( preg_match( "!(png|gif)!", $mimeExt )) {
		$destExt = '.'.$mimeExt;
	} else {
		$destExt = '.jpg';
	}

	foreach( $pFileHash['thumbnail_sizes'] as $thumbSize ) {
		if( isset( $gThumbSizes[$thumbSize] )) {
			$pFileHash['dest_base_name'] = $thumbSize;
			$pFileHash['name'] = $thumbSize.$destExt;
			$pFileHash['max_width'] = $gThumbSizes[$thumbSize]['width'];
			$pFileHash['max_height'] = $gThumbSizes[$thumbSize]['height'];
			$pFileHash['icon_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash, TRUE );
		}
	}
}

/**
 * fetch all available thumbnails for a given item. if no thumbnails are present, get thumbnailing image or the appropriate mime type icon
 *
 * @param string $pFilePath Relative path to file we want to get thumbnails for (needs to include file name for mime icons)
 * @param string $pAltImageUrl URL to an alternative fallback image such as a background thumbnailer image
 * @param array $pThumbSizes array of images to search for in the pFilePath
 * @access public
 * @return array of available thumbnails or mime icons
 */
function liberty_fetch_thumbnails( $pFilePath, $pAltImageUrl = NULL, $pThumbSizes = NULL, $pMimeImage = TRUE ) {
	global $gBitSystem, $gThumbSizes;
	$ret = array();

	if( empty( $pThumbSizes )) {
		$pThumbSizes = array_keys( $gThumbSizes );
	}

	// liberty file processors automatically pick the best format for us. we can force a format though.
	$exts = array( $gBitSystem->getConfig( 'liberty_thumbnail_format', 'jpg' ), 'jpg', 'png', 'gif' );
	// using array_unique on the above will give us the best order to look for the thumbnails
	$exts = array_unique( $exts );

	// $pFilePath might already be the absolute path or it might already contain BIT_ROOT_URL
	if( !( $pFilePath = preg_replace( "!^".preg_quote( BIT_ROOT_PATH, "!" )."!", "", $pFilePath ))) {
		$pFilePath = preg_replace( "!^".preg_quote( BIT_ROOT_URL, "!" )."!", "", $pFilePath );
	}

	// if the filepath ends with a traling / we know it's a dir. we just assume that the original file is a jpg
	// this has no outcome on the following code unless we don't find anythig and we need to get the mime type thumb
	if( preg_match( "!/$!", $pFilePath )) {
		$pFilePath .= 'dummy.jpg';
	}

	foreach( $pThumbSizes as $size ) {
		foreach( $exts as $ext ) {
			if( empty( $ret[$size] ) && is_readable( BIT_ROOT_PATH.dirname( $pFilePath ).'/'.$size.'.'.$ext )) {
				$ret[$size] = str_replace( "//", "/", BIT_ROOT_URL.dirname( $pFilePath ).'/'.$size.'.'.$ext );
			}
		}

		if( $pMimeImage && empty( $ret[$size] )) {
			if( $pAltImageUrl ) {
				$ret[$size] = $pAltImageUrl;
			} else {
				$ret[$size] = LibertySystem::getMimeThumbnailURL( $gBitSystem->lookupMimeType( $pFilePath ), substr( $pFilePath, strrpos( $pFilePath, '.' ) + 1 ));
			}
		}
	}

	return $ret;
}

/**
 * fetch a single available thumbnail for a given item. if no thumbnail is present, return NULL
 *
 * @param string $pFilePath Relative path to file we want to get thumbnails for (needs to include file name for mime icons)
 * @param string $pThumbSize image size to search for in the pFilePath
 * @param string $pAltImageUrl path to alternative image that will be shown if nothing is found
 * @param boolean $pMimeImage specify if you want to get a mime image if nothing is found
 * @access public
 * @return string url
 */
function liberty_fetch_thumbnail_url( $pFilePath, $pThumbSize = 'small', $pAltImageUrl = NULL, $pMimeImage = FALSE ) {
	if( !empty( $pFilePath )) {
		$ret = liberty_fetch_thumbnails( $pFilePath, $pAltImageUrl, array( $pThumbSize ), $pMimeImage );
	}
	return( !empty( $ret[$pThumbSize] ) ? $ret[$pThumbSize] : NULL );
}
?>