<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/liberty_lib.php,v 1.19 2007/11/20 02:14:30 spiderr Exp $
 * @package liberty
 * @subpackage functions
 */

// ================== Liberty Plugin Parsing ==================
/**
 * This crazy function will parse all the data plugin stuff found within any 
 * parsed text section
 * 
 * @param array $pData Data to be parsed
 * @access public
 * @return void
 */
function parse_data_plugins( &$pData, &$pReplace, &$pCommonObject, $pParseHash ) {
	global $gLibertySystem, $gBitSystem;

	// note: $curlyTags[0] is the complete match, $curlyTags[1] is plugin name, $curlyTags[2] is plugin arguments
	preg_match_all( "/\{\/?([A-Za-z0-9]+)([^\}]*)\}/", $pData, $curlyTags, PREG_OFFSET_CAPTURE );

	if( count( $curlyTags[0] ) ) {
		// if TRUE, replace only CODE plugin, if false, replace all other plugins
		$code_first = TRUE;

		// Process plugins in reverse order, so that nested plugins are handled from the inside out.
		$i = count( $curlyTags[0] ) - 1;
		while( $i >= 0 ) {
			$plugin_start = $curlyTags[0][$i][0];
			$plugin = $curlyTags[1][$i][0];
			// Work out where the plugin starts. This can not be done using the 
			// positional data from $curlyTags since the position might have 
			// changed since the last cycle. We therefore need to determine the 
			// position direclty. - xing - Thursday Nov 01, 2007   22:55:10 CET
			//$pos = $curlyTags[0][$i][1];
			$pos = strpos( $pData, $plugin_start );
			$dataTag = strtolower( $plugin );
			// hush up the return of this in case someone uses curly braces to enclose text
			$pluginInfo = $gLibertySystem->getPluginInfo( @$gLibertySystem->mDataTags[$dataTag] ) ;

			// only process a standalone unpaired tag or the start tag for a paired tag
			if( empty( $paired_close_tag_seen[$dataTag] ) || $paired_close_tag_seen[$dataTag] == 0 ) {
				$paired_close_tag_seen[$dataTag] = 1;
			} else {
				$paired_close_tag_seen[$dataTag] = 0;
			}

			$is_opening_tag = FALSE;
			if(( empty( $pluginInfo['requires_pair'] ) && ( strtolower( $plugin_start ) != '{/'. $dataTag . '}' ))
				|| ( strpos( $plugin_start, ' ' ) > 0 )
				|| ( strtolower( $plugin_start ) == '{'.$dataTag.'}' && !$paired_close_tag_seen[$dataTag] )
			) {
				$is_opening_tag = TRUE;
			}

			if(
				// when in CODE parsing mode, replace only CODE plugins
				( ( $code_first && ( $dataTag == 'code' ) )
					// when NOT in CODE parsing mode, replace all other plugins
					|| ( !$code_first && ( $dataTag <> 'code' ) )
				)
				&& !empty( $gLibertySystem->mDataTags[$dataTag] )
				&& !empty( $pluginInfo )
				&& ( $loadFunc = $gLibertySystem->getPluginFunction( $gLibertySystem->mDataTags[$dataTag], 'load_function' ) )
				&& ( $is_opening_tag )
			) {

				if( $pluginInfo['requires_pair'] ) {
					$plugin_end = '{/'.$plugin.'}';
					$pos_end = strpos( strtolower( $pData ), strtolower( $plugin_end ), $pos ); // where plugin data ends
					$plugin_end2 = '{'.$plugin.'}';
					$pos_end2 = strpos( strtolower( $pData ), strtolower( $plugin_end2 ), $pos + 1 ); // where plugin data ends

					if( ( $pos_end2 > 0 && $pos_end2 > 0 && $pos_end2 < $pos_end ) || $pos_end === FALSE ) {
						$pos_end = $pos_end2;
						$plugin_end = $plugin_end2;
					}
				} else {
					$pos_end = $pos + strlen( $curlyTags[0][$i][0] );
					$plugin_end = '';
				}

				// vd( "if ( ((($code_first) && ($plugin == 'CODE')) || ((!$code_first) && ($plugin <> 'CODE'))) && ($pos_end > $pos)) { <br/>" );

				// Extract the plugin data
				$plugin_data_len = $pos_end - $pos - strlen( $curlyTags[0][$i][0] );
				$plugin_data = substr( $pData, $pos + strlen( $plugin_start ), $plugin_data_len );

				// vd( "$plugin_data_len = $pos_end - $pos - strlen(".$curlyTags[0][$i][0].") substr( $pos + strlen($plugin_start), $plugin_data_len);" );

				$arguments = array();
				// Construct argument list array
				$paramString = str_replace( '&gt;', '>', trim( $curlyTags[2][$i][0] ) );
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
					$paramString = trim( $curlyTags[2][$i][0], " \t()" );
					$paramString = str_replace("&quot;", '"', $paramString);
					$arguments = parse_xml_attributes( $paramString );
				}

				if( $ret = $loadFunc( $plugin_data, $arguments, $pCommonObject, $pParseHash )) {
					$key = md5( mt_rand() );
					$pReplace[] = array(
						'key'  => $key,
						'data' => $ret,
					);

					// don't modify data if $pos is FALSE
					if( $pos !== FALSE ) {
						$pData = substr_replace( $pData, $key, $pos, $pos_end - $pos + strlen( $plugin_end ));
					}
				}
			}
			$i--;
			// if we are in CODE parsing mode and list is done, switch to 'parse other plugins' mode and start all over
			if( ( $code_first ) && ( $i < 0 ) ) {
				$i = count( $curlyTags[0] ) - 1;
				$code_first = FALSE;
			}
		} // while
	}
}

/**
 * This function replaces pre- and no-parsed sections with unique keys and 
 * saves the section contents for later reinsertion. It is needed by 
 * parse_data_plugins() to extract sections that don't require parsing
 * 
 * @param array $pData data that might contain ~np~ or ~pp~ strings
 * @param array $preparsed array that is updated by refrerence with key and data that needs to be substituted later
 * @param array $noparsed array that is updated by refrerence with key and data that needs to be substituted later
 * @access public
 * @return void
 */
function parse_protect( &$pData, &$pReplace ) {
	// Find all sections delimited by ~pp~ ... ~/pp~
	preg_match_all( "/\~pp\~(.*?)\~\/pp\~/s", $pData, $preparse );
	if( count( $preparse[0] )) {
		foreach( array_unique( $preparse[1] ) as $pp ) {
			$aux["key"]  = md5( mt_rand() );
			$aux["data"] = "<pre><code>".htmlspecialchars( $pp )."</code></pre>";
			$pReplace[]  = $aux;
			$pData       = str_replace( "~pp~$pp~/pp~", $aux['key'], $pData );
		}
	}

	// now remove <pre>...<pre> sections
	preg_match_all( "!(<pre[^>]*>)(.*?)(</pre[^>]*>)!si", $pData, $preparse );
	if( count( $preparse[0] )) {
		foreach( $preparse[2] as $key => $pre ) {
			$aux["key"]  = md5( mt_rand() );
			$aux["data"] = $preparse[1][$key].htmlspecialchars( $pre ).$preparse[3][$key];
			$pReplace[]  = $aux;
			$pData       = str_replace( $preparse[1][$key].$pre.$preparse[3][$key], $aux['key'], $pData );
		}
	}

	// and now ~np~...~/np~ sections
	preg_match_all( "/\~np\~(.*?)\~\/np\~/s", $pData, $noparse );
	if( count( $noparse[0] )) {
		foreach( array_unique( $noparse[1] ) as $np ) {
			$aux["key"]  = md5( mt_rand() );
			$aux["data"] = htmlspecialchars( $np );
			$pReplace[]  = $aux;
			$pData       = str_replace( "~np~$np~/np~", $aux['key'], $pData );
		}
	}

	/* don't quite understand why this has to be so complicated - i've replaced the code below with the short section above
	// Find all sections delimited by ~np~ ... ~/np~
	if( preg_match( "!\~np\~(.*?)\~/np\~!s", $pData, $nparse )) {
		$new_data = '';
		$nopa     = '';
		$state    = TRUE;
		$skip     = FALSE;
		$dlength  = strlen( $pData );

		for( $i = 0; $i < $dlength; $i++ ) {
			$tag5 = substr( $pData, $i, 5 );
			$tag4 = substr( $tag5, 0, 4 );
			$tag1 = substr( $tag4, 0, 1 );

			// Beginning of a noparse section found
			if( $state && $tag4 == '~np~' ) {
				$i    += 3;
				$state = FALSE;
				$skip  = TRUE;
			}

			// Termination of a noparse section found
			if( !$state && $tag5 == '~/np~' ) {
				$state       = TRUE;
				$i          += 4;
				$skip        = TRUE;
				$key         = md5( mt_rand() );
				$new_data   .= $key;
				$aux["key"]  = $key;
				$aux["data"] = htmlspecialchars( $nopa );
				$pReplace[]  = $aux;
				$nopa        = '';
			}

			if( !$skip ) {              // This character is not part of a noparse tag
				if( $state ) {          // This character is not within a noparse section
					$new_data .= $tag1;
				} else {                // This character is within a noparse section
					$nopa     .= $tag1;
				}
			} else {                    // Tag is now skipped over
				$skip = FALSE;
			}
		}
		$pData = $new_data;
	}
	 */
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
			$pObject->verifyViewPermission();
		}
	}
}

/**
 * liberty_content_edit 
 * 
 * @param array $pObject 
 * @param array $pParamHash 
 * @access public
 * @return void
 */
function liberty_content_edit( &$pObject ) {
	include_once( LIBERTY_PKG_PATH.'edit_help_inc.php' );
	include_once( LIBERTY_PKG_PATH."edit_storage_inc.php" );
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
	$canThumbFunc = liberty_get_function( 'can_thumbnail' );
	if( !empty( $canThumbFunc ) && $canThumbFunc( $pFileHash['upload']['type'] ) && $pFileHash['upload']['name'] != 'Thumbs.db' ) {
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
	$ret = 'liberty_'.$gBitSystem->getConfig( 'image_processor', 'gd' ).'_'.$pType.'_image';
	return( function_exists( $ret ) ? $ret : FALSE );
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

	// allow custom selection of thumbnail sizes
	if( empty( $pFileHash['thumbnail_sizes'] ) ) {
		if ( isset( $gThumbSizes ) ){
			$pFileHash['thumbnail_sizes'] = array_keys($gThumbSizes);
		}else{
			$pFileHash['thumbnail_sizes'] = array( 'icon', 'avatar', 'small', 'medium', 'large' );
		}
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
			if (!empty($gThumbSizes[$thumbSize]['width'])) {
				$pFileHash['max_width'] = $gThumbSizes[$thumbSize]['width'];
			}
			else {
				// Have to unset since we reuse $pFileHash
				unset( $pFileHash['max_width'] );
			}
			if (!empty($gThumbSizes[$thumbSize]['height'])) {
				$pFileHash['max_height'] = $gThumbSizes[$thumbSize]['height'];
			}
			else {
				// Have to unset since we reuse $pFileHash
				unset( $pFileHash['max_height'] );
			}
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
 * @param boolean $pMimeImage specify if you want to fetch an alternative image or not (default TRUE)
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
				$path = str_replace( "//", "/", dirname( $pFilePath ).'/'.$size.'.'.$ext );
				$ret[$size] = BIT_ROOT_URL.$path;
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

/**
 * get a set of image size options based on $gThumbSizes
 * 
 * @param string $pEmptyOption string to use as empty option - if set to FALSE no empty string is eincluded - Note that string is tra()'d
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function get_image_size_options( $pEmptyOption = 'Disable this feature' ) {
	global $gThumbSizes;
	$ret = array();
	if( !empty( $pEmptyOption )) {
		$ret[''] = tra( $pEmptyOption );
	}
	foreach( $gThumbSizes as $key => $size ) {
		$ret[$key] = tra( ucfirst( $key ))." ( {$size['width']} x {$size['height']} ".tra( 'pixels' )." )";
	}
	return $ret;
}

/**
 * get_leadtitle will fetch the string before the liberty_subtitle_delimiter
 * 
 * @param string $pString string that should be checked for the delimiter
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function get_leadtitle( $pString ) {
	global $gBitSystem;
	return( substr( $pString, 0, strpos( $pString, $gBitSystem->getConfig( 'liberty_subtitle_delimiter', ':' ))));
}

/**
 * get_subtitle will fetch the string after the liberty_subtitle_delimiter
 * 
 * @param string $pString string that should be checked for the delimiter
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function get_subtitle( $pString ) {
	global $gBitSystem;
	if(( $start = strpos( $pString, $gBitSystem->getConfig( 'liberty_subtitle_delimiter', ':' ))) !== FALSE ) {
		return( substr( $pString, ( $start + 1 )));
	}
}
?>
