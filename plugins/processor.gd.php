<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/plugins/processor.gd.php,v 1.9 2007/07/29 14:23:25 squareing Exp $
 *
 * Image processor - extension: php-gd
 * @package  liberty
 * @author   spider <spider@steelsun.com>
 */

/**
 * liberty_gd_resize_image
 *
 * @param array $pFileHash
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_gd_resize_image( &$pFileHash, $pThumbnail = FALSE ) {
  	global $gBitSystem;
	$ret = NULL;
	list($iwidth, $iheight, $itype, $iattr) = @getimagesize( $pFileHash['source_file'] );
	list($type, $ext) = split( '/', strtolower( $pFileHash['type'] ) );
	$destUrl = $pFileHash['dest_path'].$pFileHash['dest_base_name'];
	if( ( empty( $pFileHash['max_width'] ) || empty( $pFileHash['max_height'] ) ) || ( $iwidth <= $pFileHash['max_width'] && $iheight <= $pFileHash['max_height'] && ( $ext == 'gif' || $ext == 'png'  || $ext == 'jpg'   || $ext == 'jpeg' ) ) ) {
		// Keep the same dimensions as input file
		$pFileHash['max_width'] = $iwidth;
		$pFileHash['max_height'] = $iheight;
	} elseif( $iheight && (($iwidth / $iheight) > 0) && !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) ) {
		// we have a portrait image, flip everything
		$temp = $pFileHash['max_width'];
		$pFileHash['max_height'] = $pFileHash['max_width'];
		$pFileHash['max_width'] = $temp;
	}

	// we need to scale and/or reformat
	$fp = fopen( $pFileHash['source_file'], "rb" );
	$data = fread( $fp, filesize( $pFileHash['source_file'] ) );
	fclose ($fp);
	if( function_exists( "ImageCreateFromString" ) ) {
		$img = @imagecreatefromstring($data);
	}

	if( !empty( $img ) ) {
		$size_x = imagesx($img);
		$size_y = imagesy($img);
	}

	if( !empty( $img ) && $size_x && $size_y ) {
		if( $size_x > $size_y && !empty( $pFileHash['max_width'] ) ) {
			$tscale = ((int)$size_x / $pFileHash['max_width']);
		} elseif( !empty( $pFileHash['max_height'] ) ) {
			$tscale = ((int)$size_y / $pFileHash['max_height']);
		} else {
			$tscale = 1;
		}
		$tw = ((int)($size_x / $tscale));
		$ty = ((int)($size_y / $tscale));
		if( get_gd_version() > 1 ) {
			$t = imagecreatetruecolor( $tw, $ty );
			imagesavealpha( $t, TRUE );
			imagealphablending( $t, FALSE );
			imagecopyresampled( $t, $img, 0, 0, 0, 0, $tw, $ty, $size_x, $size_y );
		} else {
			$t = imagecreate( $tw, $ty );
			//$imagegallib->ImageCopyResampleBicubic($t, $img, 0, 0, 0, 0, $tw, $ty, $size_x, $size_y);
		}


		// override $mimeExt if we have a custom setting for it
		if( $gBitSystem->isFeatureActive( 'liberty_thumbnail_format' )) {
			$mimeExt = $gBitSystem->getConfig( 'liberty_thumbnail_format' );
		} else {
			// make sure we have image_type_to_extension available
			include_once( UTIL_PKG_PATH.'PHP_Compat/Compat/Function/image_type_to_mime_type.php' );
			list( $type, $mimeExt ) = split( '/', strtolower( image_type_to_mime_type( $itype )));
		}

		if( preg_match( "!(png|gif)!", $mimeExt )) {
			$targetType = $mimeExt;
			$destExt = '.'.$mimeExt;
		} else {
			$targetType = 'jpeg';
			$destExt = '.jpg';
		}

		$destFile = BIT_ROOT_PATH.'/'.$destUrl.$destExt;

		switch( $targetType ) {
			case 'png':
				if( imagetypes() & IMG_PNG ) {
					// png alpha stuff - needs more testing - spider
					//     imagecolorallocatealpha ( $t, 0, 0, 0, 127 );
					//     $ImgWhite = imagecolorallocate($t, 255, 255, 255);
					//     imagefill($t, 0, 0, $ImgWhite);
					//     imagecolortransparent($t, $ImgWhite);
					imagepng( $t, $destFile );
					break;
				}
			case 'gif':
				// This must go immediately before default so default will be hit for PHP's without gif support
				if( imagetypes() & IMG_GIF ) {
					imagecolortransparent( $t );
					imagegif( $t, $destFile );
					break;
				}
			default:
				imagejpeg( $t, $destFile );
				break;
		}

		// set permissions if possible - necessary for some wonky shared hosting environments
		if( chmod( $pFileHash['source_file'], 0644 )){
			// does nothing, but fails elegantly
		}

		$pFileHash['name'] = $pFileHash['dest_base_name'].$destExt;
		$pFileHash['size'] = filesize( $destFile );
		$ret = $destUrl.$destExt;
	} elseif( $iwidth && $iheight ) {
		$ret = liberty_process_generic( $pFileHash, FALSE );
	}

	return $ret;
}

/**
 * liberty_gd_rotate_image
 *
 * @param array $pFileHash
 * @param array $pFormat
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_gd_rotate_image( &$pFileHash, $pFormat = NULL ) {
	if( !function_exists( 'imagerotate' ) ) {
		$pFileHash['error'] = "Rotate is not available on this webserver.";
	} elseif( empty( $pFileHash['degrees'] ) || !is_numeric( $pFileHash['degrees'] ) ) {
		$pFileHash['error'] = tra( 'Invalid rotation amount' );
	} else {
		// we need to scale and/or reformat
		$fp = fopen( $pFileHash['source_file'], "rb" );
		$data = fread( $fp, filesize( $pFileHash['source_file'] ) );
		fclose ($fp);
		if( function_exists("ImageCreateFromString") ) {
			$img = @imagecreatefromstring($data);
		}

		if( !empty( $img ) ) {
			// image rotate degrees seems back ass words.
			$rotateImg = imagerotate ( $img, (-1 * $pFileHash['degrees']), 0 );
			if( !empty( $rotateImg ) ) {
				imagejpeg( $rotateImg, $pFileHash['source_file'] );
			} else {
				$pFileHash['error'] = "Image rotation failed.";
			}
		} else {
			$pFileHash['error'] = "Image could not be opened for rotation.";
		}
	}

	return( empty( $pFileHash['error'] ) );
}

/**
 * liberty_gd_can_thumbnail_image
 *
 * @param array $pMimeType
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_gd_can_thumbnail_image( $pMimeType ) {
	$ret = FALSE;
	if( !empty( $pMimeType )) {
		$ret = preg_match( '/^image/i', $pMimeType );
	}
	return $ret;
}

/**
 * get_gd_version 
 * 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function get_gd_version( $pFullVersion = FALSE ) {
	if( empty( $_SESSION['gd_version'] )) {
		$gd = gd_info();
		$_SESSION['gd_version'] = preg_replace( "!\D*([\d|\.]*)!", "$1", $gd['GD Version'] );
	}

	if( $pFullVersion ) {
		return $_SESSION['gd_version'];
	} else {
		return preg_replace( "!^(\d)+.*$!", "$1", $_SESSION['gd_version'] );
	}
}

// nicked from http://at2.php.net/manual/en/function.gd-info.php
if( !function_exists( 'gd_info' )) {
	function gd_info() {
		$array = Array(
			"GD Version"         => "",
			"FreeType Support"   => 0,
			"FreeType Support"   => 0,
			"FreeType Linkage"   => "",
			"T1Lib Support"      => 0,
			"GIF Read Support"   => 0,
			"GIF Create Support" => 0,
			"JPG Support"        => 0,
			"PNG Support"        => 0,
			"WBMP Support"       => 0,
			"XBM Support"        => 0
		);
		$gif_support = 0;

		ob_start();
		eval("phpinfo();");
		$info = ob_get_contents();
		ob_end_clean();

		foreach(explode("\n", $info) as $line) {
			if(strpos($line, "GD Version")!==false)
				$array["GD Version"] = trim(str_replace("GD Version", "", strip_tags($line)));
			if(strpos($line, "FreeType Support")!==false)
				$array["FreeType Support"] = trim(str_replace("FreeType Support", "", strip_tags($line)));
			if(strpos($line, "FreeType Linkage")!==false)
				$array["FreeType Linkage"] = trim(str_replace("FreeType Linkage", "", strip_tags($line)));
			if(strpos($line, "T1Lib Support")!==false)
				$array["T1Lib Support"] = trim(str_replace("T1Lib Support", "", strip_tags($line)));
			if(strpos($line, "GIF Read Support")!==false)
				$array["GIF Read Support"] = trim(str_replace("GIF Read Support", "", strip_tags($line)));
			if(strpos($line, "GIF Create Support")!==false)
				$array["GIF Create Support"] = trim(str_replace("GIF Create Support", "", strip_tags($line)));
			if(strpos($line, "GIF Support")!==false)
				$gif_support = trim(str_replace("GIF Support", "", strip_tags($line)));
			if(strpos($line, "JPG Support")!==false)
				$array["JPG Support"] = trim(str_replace("JPG Support", "", strip_tags($line)));
			if(strpos($line, "PNG Support")!==false)
				$array["PNG Support"] = trim(str_replace("PNG Support", "", strip_tags($line)));
			if(strpos($line, "WBMP Support")!==false)
				$array["WBMP Support"] = trim(str_replace("WBMP Support", "", strip_tags($line)));
			if(strpos($line, "XBM Support")!==false)
				$array["XBM Support"] = trim(str_replace("XBM Support", "", strip_tags($line)));
		}

		if($gif_support==="enabled") {
			$array["GIF Read Support"]   = 1;
			$array["GIF Create Support"] = 1;
		}

		if($array["FreeType Support"]==="enabled")
			$array["FreeType Support"] = 1;

		if($array["T1Lib Support"]==="enabled")
			$array["T1Lib Support"] = 1;

		if($array["GIF Read Support"]==="enabled")
			$array["GIF Read Support"] = 1;

		if($array["GIF Create Support"]==="enabled")
			$array["GIF Create Support"] = 1;

		if($array["JPG Support"]==="enabled")
			$array["JPG Support"] = 1;

		if($array["PNG Support"]==="enabled")
			$array["PNG Support"] = 1;

		if($array["WBMP Support"]==="enabled")
			$array["WBMP Support"] = 1;

		if($array["XBM Support"]==="enabled")
			$array["XBM Support"] = 1;

		return $array;
	}
}
?>
