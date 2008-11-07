<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/plugins/processor.imagick.php,v 1.10 2008/11/07 10:57:53 nickpalmer Exp $
 *
 * Image processor - extension: php-imagick
 * @package  liberty
 * @subpackage plugins_processor
 * @author   spider <spider@steelsun.com>
 */

/**
 * liberty_imagick_resize_image 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_imagick_resize_image( &$pFileHash ) {
	if( $func = liberty_imagick_get_function( 'resize_image' )) {
		return $func( $pFileHash );
	}
}

/**
 * liberty_imagick_rotate_image 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_imagick_rotate_image( &$pFileHash ) {
	if( $func = liberty_imagick_get_function( 'rotate_image' )) {
		return $func( $pFileHash );
	}
}

/**
 * liberty_imagick_can_thumbnail_image 
 * 
 * @param array $pMimeType 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_imagick_can_thumbnail_image( $pMimeType ) {
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
 * liberty_imagick_get_function will automagically pick the correct function based on the version of imagick extension installed 
 * 
 * @return valid function.
 */
function liberty_imagick_get_function( $pFunction ) {
	$ret = FALSE;
	if( extension_loaded( 'imagick' )) {
		if( function_exists( 'imagick_readimage' )) {
			$version = 0;
		} elseif( class_exists( 'Imagick' )) {
			$version = 2;
		}
	}

	if( isset( $version ) && !empty( $pFunction )) {
		$func = 'liberty_imagick'.$version.'_'.$pFunction;
		if( function_exists( $func )) {
			$ret = $func;
		}
	}
	return $ret;
}


// =============================================
// ======== Version 0.9* of php-imagick ========
// =============================================
function liberty_imagick0_resize_image( &$pFileHash ) {
	global $gBitSystem;
	$pFileHash['error'] = NULL;
	$ret = NULL;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		$iImg = imagick_readimage( $pFileHash['source_file'] );
		if( !$iImg ) {
			// $pFileHash['error'] = $pFileHash['name'].' '.tra ( "is not a known image file" );
			$destUrl = liberty_process_generic( $pFileHash, FALSE );
		} elseif( imagick_iserror( $iImg ) ) {
			// $pFileHash['error'] = imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
			$destUrl = liberty_process_generic( $pFileHash, FALSE );
		} else {
			imagick_set_image_quality( $iImg, 85 );
			$iwidth = imagick_getwidth( $iImg );
			$iheight = imagick_getheight( $iImg );
			if( (($iwidth / $iheight) > 0) && !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) ) {
				// we have a portrait image, flip everything
				$temp = $pFileHash['max_width'];
				$pFileHash['max_height'] = $pFileHash['max_width'];
				$pFileHash['max_width'] = $temp;
			}

			$itype = imagick_getmimetype( $iImg );

			// override $mimeExt if we have a custom setting for it
			if( $gBitSystem->isFeatureActive( 'liberty_thumbnail_format' )) {
				$mimeExt = $gBitSystem->getConfig( 'liberty_thumbnail_format' );
			} else {
				list( $type, $mimeExt ) = split( '/', strtolower( $itype ));
			}

			if( $mimeExt = preg_match( "!^(x-)?(png|gif)$!", "$2", $mimeExt )) {
				$targetType = $mimeExt;
				$destExt = '.'.$mimeExt;
			} else {
				$targetType = 'jpeg';
				$destExt = '.jpg';
			}

			if( !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) && ( ($pFileHash['max_width'] < $iwidth || $pFileHash['max_height'] < $iheight ) || $mimeExt != $targetType )) {
				// We have to resize. *ALL* resizes are converted to jpeg or png
				$destUrl = $pFileHash['dest_path'].$pFileHash['dest_base_name'].$destExt;
				$destFile = BIT_ROOT_PATH.'/'.$destUrl;
				$pFileHash['name'] = $pFileHash['dest_base_name'].$destExt;
				// print "			if ( !imagick_resize( $iImg, $pFileHash[max_width], $pFileHash[max_height], IMAGICK_FILTER_LANCZOS, 0.5, $pFileHash[max_width] x $pFileHash[max_height] > ) ) {";

				// Alternate Filter settings can seen here http://www.dylanbeattie.net/magick/filters/result.html

				if ( !imagick_resize( $iImg, $pFileHash['max_width'], $pFileHash['max_height'], IMAGICK_FILTER_CATROM, 1.00, '>' ) ) {
					$pFileHash['error'] .= imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
				}
				// print "2YOYOYOYO $iwidth x $iheight $destUrl <br/>"; flush();

				if( function_exists( 'imagick_set_attribute' ) ) {
					// this exists in the PECL package, but not php-imagick
					$imagick_set_attribute($iImg,array("quality"=>1) );
				}

				if( !imagick_writeimage( $iImg, $destFile ) ) {
					$pFileHash['error'] .= imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
				}
				$pFileHash['size'] = filesize( $destFile );
			} else {
				// print "GENERIC";
				$destUrl = liberty_process_generic( $pFileHash, FALSE );
			}
		}
		$ret = $destUrl;
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return $ret;
}

function liberty_imagick0_rotate_image( &$pFileHash ) {
	$ret = FALSE;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		$iImg = imagick_readimage( $pFileHash['source_file'] );
		if( !$iImg ) {
			$pFileHash['error'] = $pFileHash['name'].' '.tra ( "is not a known image file" );
		} elseif( imagick_iserror( $iImg ) ) {
			$pFileHash['error'] = imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
		} elseif( empty( $pFileHash['degrees'] ) || !is_numeric( $pFileHash['degrees'] ) ) {
			$pFileHash['error'] = tra( 'Invalid rotation amount' );
		} else {
			if ( !imagick_rotate( $iImg, $pFileHash['degrees'] ) ) {
				$pFileHash['error'] .= imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
			}
			if( !imagick_writeimage( $iImg, $pFileHash['source_file'] ) ) {
				$pFileHash['error'] .= imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
			}
		}
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return( empty( $pFileHash['error'] ) );
}



// ============================================
// ======== Version 2.* of php-imagick ========
// ============================================
function liberty_imagick2_resize_image( &$pFileHash ) {
	global $gBitSystem;
	$pFileHash['error'] = NULL;
	$ret = NULL;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] )) {
		$im = new Imagick();
		$im->readImage( $pFileHash['source_file'] );
		if( !$im->valid()) {
			$destUrl = liberty_process_generic( $pFileHash, FALSE );
		} else {
			$im->setCompressionQuality( 85 );
			$iwidth = $im->getImageWidth();
			$iheight = $im->getImageHeight();
			/*
			 * the math on this was bad and the property assignments were bad - those are fixed. 
			 * however this is disabled since its being invoked by default, which prevents the max height
			 * from being enforced on portrait images which is counter intuitive. by default the bounding
			 * rectangle should be enforced. if someone wants this feature, then a flag for this needs to
			 * be created which will likely require some bigger change up stream, like in gThumbSizes and
			 * in liberty_generate_thumbnails()
			 * -wjames5
			if((( $iwidth / $iheight ) < 1 ) && !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] )) {
				// we have a portrait image, flip everything
				$temp = $pFileHash['max_height'];
				$pFileHash['max_height'] = $pFileHash['max_width'];
				$pFileHash['max_width'] = $temp;
			}
			 */

			// override $mimeExt if we have a custom setting for it
			if( $gBitSystem->isFeatureActive( 'liberty_thumbnail_format' )) {
				$mimeExt = $gBitSystem->getConfig( 'liberty_thumbnail_format' );
			} else {
				list( $type, $mimeExt ) = split( '/', strtolower( $pFileHash['type'] ));
			}

			if( preg_match( "!(png|gif)!", $mimeExt )) {
				$targetType = $mimeExt;
				$destExt = '.'.$mimeExt;
			} else {
				$targetType = 'jpeg';
				$destExt = '.jpg';
			}

			if( !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) && (( $pFileHash['max_width'] < $iwidth || $pFileHash['max_height'] < $iheight ) || $mimeExt != $targetType )) {
				$destUrl = $pFileHash['dest_path'].$pFileHash['dest_base_name'].$destExt;
				$destFile = BIT_ROOT_PATH.'/'.$destUrl;
				$pFileHash['name'] = $pFileHash['dest_base_name'].$destExt;

				// create thumb and write
				$im->thumbnailImage( $pFileHash['max_width'],  $pFileHash['max_height'], TRUE );
				$im->writeImage( $destFile );

				$pFileHash['size'] = filesize( $destFile );
			} else {
				$destUrl = liberty_process_generic( $pFileHash, FALSE );
			}
		}

		// destroy object
		$im->destroy();

		$ret = $destUrl;
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return $ret;
}

function liberty_imagick2_rotate_image( &$pFileHash ) {
	$ret = FALSE;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] )) {
		$im = new Imagick();
		$im->readImage( $pFileHash['source_file'] );
		if( !$im->valid()) {
			$destUrl = liberty_process_generic( $pFileHash, FALSE );
		} elseif( empty( $pFileHash['degrees'] ) || !is_numeric( $pFileHash['degrees'] )) {
			$pFileHash['error'] = tra( 'Invalid rotation amount' );
		} else {
			$im->rotateImage( new ImagickPixel(), $pFileHash['degrees'] );
			$im->writeImage( $pFileHash['source_file'] );
		}
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return( empty( $pFileHash['error'] ));
}
?>
