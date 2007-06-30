<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/plugins/processor.gd.php,v 1.6 2007/06/30 13:39:17 squareing Exp $
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
		if( chkgd2() ) {
			$t = imagecreatetruecolor( $tw, $ty );
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

		switch( $targetType ) {
			case 'png':
				if( imagetypes() & IMG_PNG ) {
					// png alpha stuff - needs more testing - spider
					//     imagecolorallocatealpha ( $t, 0, 0, 0, 127 );
					//     $ImgWhite = imagecolorallocate($t, 255, 255, 255);
					//     imagefill($t, 0, 0, $ImgWhite);
					//     imagecolortransparent($t, $ImgWhite);
					$destFile = BIT_ROOT_PATH.'/'.$destUrl.$destExt;
					imagesavealpha( $t, TRUE );
					imagealphablending( $t, FALSE );
					imagecopyresampled( $t, $img, 0, 0, 0, 0, $tw, $ty, $size_x, $size_y );
					imagepng( $t, $destFile );
					break;
				}
			case 'gif':
				// This must go immediately before default so default will be hit for PHP's without gif support
				if( imagetypes() & IMG_GIF ) {
					$destFile = BIT_ROOT_PATH.'/'.$destUrl.$destExt;
					imagecolortransparent( $t );
					imagegif( $t, $destFile );
					break;
				}
			default:
				$destFile = BIT_ROOT_PATH.'/'.$destUrl.$destExt;
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
	if( !empty( $pMimeType ) ) {
		$ret = preg_match( '/^image/i', $pMimeType );
	}
	return $ret;
}
?>
