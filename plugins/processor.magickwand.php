<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/plugins/processor.magickwand.php,v 1.23 2010/03/04 20:55:15 spiderr Exp $
 *
 * Image processor - extension: php-magickwand
 * @package  liberty
 * @subpackage plugins_processor
 * @author   spider <spider@steelsun.com>
 */

/**
 * liberty_magickwand_resize_image 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_magickwand_resize_image( &$pFileHash ) {
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
//				These two lines are a hack needed for version of Ghostscript less that 8.60
//				MagickRemoveImageProfile( $magickWand, "ICC" );
//				MagickSetImageProfile( $magickWand, 'ICC', file_get_contents( UTIL_PKG_PATH.'icc/USWebCoatedSWOP.icc' ) );
				MagickProfileImage( $magickWand, 'ICC', file_get_contents( UTIL_PKG_PATH.'icc/srgb.icm' ));
				MagickSetImageColorspace( $magickWand, MW_RGBColorspace );
				$pFileHash['colorspace_conversion'] = TRUE;
			}
			if( $isPdf ) {
				MagickResetIterator( $magickWand );
				MagickNextImage( $magickWand );
			}
			MagickSetImageCompressionQuality( $magickWand, $gBitSystem->getConfig( 'liberty_thumbnail_quality', 85 ));
			$iwidth = round( MagickGetImageWidth( $magickWand ) );
			$iheight = round( MagickGetImageHeight( $magickWand ) );

			// this does not seem to be needed. magickwand will work out what to do by using the destination file extension
			//MagickSetImageFormat( $magickWand, $format );

			if( ( empty( $pFileHash['max_width'] ) && empty( $pFileHash['max_height'] ) ) || ( !empty( $pFileHash['max_width'] ) && $pFileHash['max_width'] == MAX_THUMBNAIL_DIMENSION ) || ( !empty( $pFileHash['max_height'] ) && $pFileHash['max_height'] == MAX_THUMBNAIL_DIMENSION ) ) {
				$pFileHash['max_width'] = $iwidth;
				$pFileHash['max_height'] = $iheight;
			} elseif(( $iwidth / $iheight ) < 1 && !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] )) {
				// we have a portrait image, flip everything
				$temp = $pFileHash['max_width'];
				$pFileHash['max_height'] = $pFileHash['max_width'];
				$pFileHash['max_width'] = round(( $iwidth / $iheight ) * $pFileHash['max_height'] );
			} elseif( !empty( $pFileHash['max_width'] ) ) {
				$pFileHash['max_height'] = round(( $iheight / $iwidth ) * $pFileHash['max_width'] );
			} elseif( !empty( $pFileHash['max_height'] ) ) {
				$pFileHash['max_width'] = round(( $iwidth / $iheight ) * $pFileHash['max_height'] );
			}

			// Make sure not to scale up
			if( $pFileHash['max_width'] > $iwidth && $pFileHash['max_height'] > $iheight) {
				$pFileHash['max_width'] = $iwidth;
				$pFileHash['max_height'] = $iheight;
			}

			$itype = MagickGetImageMimeType( $magickWand );

			// override $mimeExt if we have a custom setting for it
			if( $gBitSystem->isFeatureActive( 'liberty_thumbnail_format' )) {
				$mimeExt = $gBitSystem->getConfig( 'liberty_thumbnail_format' );
			} else {
				list( $type, $mimeExt ) = preg_split( '#/#', strtolower( $itype ));
			}
			$replaced = FALSE;
			$mimeExt = preg_replace( "!^(x-)?(jpeg|png|gif)$!", "$2", $mimeExt, -1, $replaced );
			if( $replaced ) {
				$targetType = $mimeExt;
				$destExt = '.'.$mimeExt;
			}
			if( empty( $destExt ) || $mimeExt == 'jpeg' ) {
				$targetType = 'jpeg';
				$destExt = '.jpg';
			}
			if( !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) && ( ($pFileHash['max_width'] < $iwidth || $pFileHash['max_height'] < $iheight ) || $mimeExt != $targetType ) || !empty( $pFileHash['colorspace_conversion'] ) ) {
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
	global $gBitSystem;
	$ret = FALSE;
	if( !empty( $pMimeType ) ) {
		// allow images, pdf, and postscript thumbnailing (eps, ai, etc...)
		if( $gBitSystem->isFeatureActive( 'liberty_thumbnail_pdf' )) {
			$ret = preg_match( '/(^image|pdf$|postscript$)/i', $pMimeType );
		} else {
			$ret = preg_match( '/^image/i', $pMimeType );
		}
	}
	return $ret;
}

/**
 * liberty_magickwand_convert_colorspace
 * 
 * @param array $pFileHash
 * @param string $pColorSpace - target color space, only 'grayscale' is currently supported
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_magickwand_convert_colorspace_image( &$pFileHash, $pColorSpace ) {
	$ret = FALSE;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		$magickWand = NewMagickWand();
		if( $error = liberty_magickwand_check_error( MagickReadImage( $magickWand, $pFileHash['source_file'] ), $magickWand ) ) {
			bit_log_error( "MagickReadImage Failed:$error ( $pFileHash[source_file] )" );
		}  else {
			MagickRemoveImageProfile( $magickWand, "ICC" );
			switch( strtolower( $pColorSpace ) ) {
				case 'grayscale':
					if( MagickGetImageColorspace( $magickWand ) == MW_GRAYColorspace ) {
						$ret = TRUE;
					} else {
						MagickSetImageColorspace( $magickWand, MW_GRAYColorspace );
						if( empty( $pFileHash['dest_file'] ) ) {
							$pFileHash['dest_file'] = BIT_ROOT_PATH.'/'.$pFileHash['dest_path'].$pFileHash['name'];
						}
						if( $error = liberty_magickwand_check_error( MagickWriteImage( $magickWand, $pFileHash['dest_file'] ), $magickWand ) ) {
							bit_log_error( "MagickWriteImage Failed:$error ( $pFileHash[source_file] )" );
						} else {
							$ret = TRUE;
						}
					}
					break;
			}
		}
		DestroyMagickWand( $magickWand );
	}
	return $ret;
}
?>
