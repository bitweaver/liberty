<?php
/**
 * $Header$
 *
 * Image processor - extension: php-imagick
 * @package  liberty
 * @subpackage plugins_processor
 * @author   spider <spider@steelsun.com>
 */

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

// ============================================
// ======== Version 2.* of php-imagick ========
// ============================================
function liberty_imagick_resize_image( &$pFileHash ) {
	global $gBitSystem;
	$pFileHash['error'] = NULL;
	$ret = NULL;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] )) {
		$im = new Imagick();
		$im->readImage( $pFileHash['source_file'] );
		if( !$im->valid()) {
			$destFile = liberty_process_generic( $pFileHash, FALSE );
		} else {
			$im->setCompressionQuality( $gBitSystem->getConfig( 'liberty_thumbnail_quality', 85 ));
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
				list( $type, $mimeExt ) = explode( '/', strtolower( $pFileHash['type'] ));
			}

			if( preg_match( "!(png|gif)!", $mimeExt )) {
				$targetType = $mimeExt;
				$destExt = '.'.$mimeExt;
			} else {
				$targetType = 'jpeg';
				$destExt = '.jpg';
			}

			if( !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) && (( $pFileHash['max_width'] < $iwidth || $pFileHash['max_height'] < $iheight ) || $mimeExt != $targetType )) {
				if( !empty( $pFileHash['dest_file'] ) ) {
					$destFile = $pFileHash['dest_file'];
				} else {
					$destFile = STORAGE_PKG_PATH.$pFileHash['dest_branch'].$pFileHash['dest_base_name'].$destExt;
				}
				$pFileHash['name'] = $pFileHash['dest_base_name'].$destExt;

				// create thumb and write
				$im->thumbnailImage( $pFileHash['max_width'],  $pFileHash['max_height'], TRUE );
				$im->writeImage( $destFile );

				$pFileHash['size'] = filesize( $destFile );
			} else {
				$destFile = liberty_process_generic( $pFileHash, FALSE );
			}
		}

		// destroy object
		$im->destroy();

		$ret = $destFile;
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return $ret;
}

function liberty_imagick_rotate_image( &$pFileHash ) {
	$ret = FALSE;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] )) {
		$im = new Imagick();
		$im->readImage( $pFileHash['source_file'] );
		if( !$im->valid()) {
			$destFile = liberty_process_generic( $pFileHash, FALSE );
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

