<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/plugins/processor.magickwand.php,v 1.3 2007/02/16 17:08:59 nickpalmer Exp $
 *
 * Image processor - extension: php-magickwand
 * @package  liberty
 * @author   spider <spider@steelsun.com>
 */

/**
 * liberty_magickwand_resize_image 
 * 
 * @param array $pFileHash 
 * @param array $pFormat 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_magickwand_resize_image( &$pFileHash, $pFormat = NULL, $pThumbnail = false ) {
	global $gBitSystem;
	// static var here is crucial
	static $rgbConverts = array();
	$magickWand = NewMagickWand();
	$pFileHash['error'] = NULL;
	$ret = NULL;
	$isPdf = preg_match( '/pdf/i', $pFileHash['type'] );
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		// This has to come BEFORE the MagickReadImage
		if( $isPdf ) {
			MagickSetImageUnits( $magickWand, MW_PixelsPerInchResolution );
			$rez =  empty( $pFileHash['max_width'] ) || $pFileHash['max_width'] == MAX_THUMBNAIL_DIMENSION ? 250 : 72;
			MagickSetResolution( $magickWand, 300, 300 );
		}
		if( $error = liberty_magickwand_check_error( MagickReadImage( $magickWand, $pFileHash['source_file'] ), $magickWand ) ) {
			// $pFileHash['error'] = $error;
			$destUrl = liberty_process_generic( $pFileHash, FALSE );
		} else {
			if( MagickGetImageColorspace( $magickWand ) == MW_CMYKColorspace ) {
				MagickRemoveImageProfile( $magickWand, "ICC" );
				MagickSetImageProfile( $magickWand, 'ICC', file_get_contents( UTIL_PKG_PATH.'icc/USWebCoatedSWOP.icc' ) );	
				MagickProfileImage($magickWand, 'ICC', file_get_contents( UTIL_PKG_PATH.'icc/srgb.icm' ) ); 

				MagickSetImageColorspace( $magickWand, MW_RGBColorspace );
				if( !empty( $pFileHash['thumbnail_sizes'] ) && in_array( 'original', $pFileHash['thumbnail_sizes'] )
					&& $gBitSystem->isFeatureActive( 'liberty_jpeg_originals' )
					&& empty( $rgbConverts[$pFileHash['dest_path']] )
				) {
					// Colorpsace conversion  - jpeg version of original
					$originalHash = $pFileHash;
					$originalHash['dest_base_name'] = 'original';
					$originalHash['name'] = 'original.jpg';
					$originalHash['max_width'] = MAX_THUMBNAIL_DIMENSION;
					$originalHash['max_height'] = MAX_THUMBNAIL_DIMENSION;
					$originalHash['colorspace_conversion'] = TRUE;
					// keep track of all files we have colorspace converted to avoid infinite loops
					$rgbConverts[$pFileHash['dest_path']] = TRUE;
					$originalHash['original_path'] = liberty_magickwand_resize_image( $originalHash );
				}
			}
			if( $isPdf ) {
				MagickResetIterator( $magickWand );
				MagickNextImage( $magickWand );
			}
			MagickSetImageCompressionQuality( $magickWand, 85 );
			$iwidth = round( MagickGetImageWidth( $magickWand ) );
			$iheight = round( MagickGetImageHeight( $magickWand ) );
			$itype = MagickGetImageMimeType( $magickWand );

			if ($pThumbnail && $gBitSystem->isFeatureActive('liberty_png_thumbnails')) {
				$format = 'PNG';
			}
			else {
				$format = 'JPG';
			}
			MagickSetImageFormat( $magickWand, $format );

			if( empty( $pFileHash['max_width'] ) || empty( $pFileHash['max_height'] ) || $pFileHash['max_width'] == MAX_THUMBNAIL_DIMENSION || $pFileHash['max_height'] == MAX_THUMBNAIL_DIMENSION ) {
				$pFileHash['max_width'] = $iwidth;
				$pFileHash['max_height'] = $iheight;
			} elseif( (($iwidth / $iheight) < 1) && !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) ) {
				// we have a portrait image, flip everything
				$temp = $pFileHash['max_width'];
				$pFileHash['max_height'] = $pFileHash['max_width'];
				$pFileHash['max_width'] = round( ($iwidth / $iheight) * $pFileHash['max_height'] );
			} elseif( !empty( $pFileHash['max_width'] ) ) {
				$pFileHash['max_height'] = round( ($iheight / $iwidth) * $pFileHash['max_width'] );
			}
			// Make sure not to scale up
			if( $pFileHash['max_width'] > $iwidth && $pFileHash['max_height'] > $iheight) {
				$pFileHash['max_width'] = $iwidth;
				$pFileHash['max_height'] = $iheight;
			} 

			list($type, $mimeExt) = split( '/', strtolower( $itype ) );
			if ($gBitSystem->isFeatureActive('liberty_png_thumbnails')) {
				$targetType = 'png';
				$destExt = '.png';
			}
			else {
				$targetType = 'jpeg';
				$destExt = '.jpg';
			}
			if( !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) && ( ($pFileHash['max_width'] < $iwidth || $pFileHash['max_height'] < $iheight ) || ($mimeExt != $targetType)) || !empty( $pFileHash['colorspace_conversion'] ) ) {
				$destUrl = $pFileHash['dest_path'].$pFileHash['dest_base_name'].$destExt;
				$destFile = BIT_ROOT_PATH.'/'.$destUrl;
				$pFileHash['name'] = $pFileHash['dest_base_name'].$destExt;
				// Alternate Filter settings can seen here http://www.dylanbeattie.net/magick/filters/result.html
				if ( $error = liberty_magickwand_check_error( MagickResizeImage( $magickWand, $pFileHash['max_width'], $pFileHash['max_height'], MW_CatromFilter, 1.00 ), $magickWand ) ) {
					$pFileHash['error'] .= $error;
				}
				if( $error = liberty_magickwand_check_error( MagickWriteImage( $magickWand, $destFile ), $magickWand ) ) {
					$pFileHash['error'] .= $error;
				}
				$pFileHash['size'] = filesize( $destFile );
			} else {
				$destUrl = liberty_process_generic( $pFileHash, FALSE );
			}
		}
		$ret = $destUrl;
	} else {
		$pFileHash['error'] = "No source file to resize";
	}
	DestroyMagickWand( $magickWand );
	return $ret;
}

/**
 * liberty_magickwand_rotate_image 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_magickwand_rotate_image( &$pFileHash ) {
	$ret = FALSE;
	$magickWand = NewMagickWand();
	$pFileHash['error'] = NULL;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		if( $error = liberty_magickwand_check_error( MagickReadImage( $magickWand, $pFileHash['source_file'] ), $magickWand ) ) {
			$pFileHash['error'] = $error;
		} elseif( empty( $pFileHash['degrees'] ) || !is_numeric( $pFileHash['degrees'] ) ) {
			$pFileHash['error'] = tra( 'Invalid rotation amount' );
		} else {
			$bgWand = NewPixelWand('white');
			if( $error = liberty_magickwand_check_error( MagickRotateImage( $magickWand, $bgWand, $pFileHash['degrees'] ), $magickWand ) ) {
				$pFileHash['error'] .= $error;
			}
			if( $error = liberty_magickwand_check_error( MagickWriteImage( $magickWand, $pFileHash['source_file'] ), $magickWand ) ) {
				$pFileHash['error'] .= $error;
			}
		}
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return( empty( $pFileHash['error'] ) );
}

/**
 * liberty_magickwand_check_error 
 * 
 * @param array $pResult 
 * @param array $pWand 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_magickwand_check_error( $pResult, $pWand ) {
	$ret = FALSE;
	if( $pResult === FALSE && WandHasException( $pWand ) ) {
		$ret = 'An image processing error occurred : '.WandGetExceptionString($pWand);
	}
	return $ret;
}

/**
 * liberty_magickwand_can_thumbnail_image 
 * 
 * @param array $pMimeType 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_magickwand_can_thumbnail_image( $pMimeType ) {
	$ret = FALSE;
	if( !empty( $pMimeType ) ) {
		$ret = preg_match( '/(^image|pdf)/i', $pMimeType );
	}
	return $ret;
}
?>
