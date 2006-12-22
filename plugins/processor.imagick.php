<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/plugins/processor.imagick.php,v 1.1 2006/12/22 11:53:17 squareing Exp $
 *
 * Image processor - extension: php-imagick
 * @package  liberty
 * @author   spider <spider@steelsun.com>
 */

/**
 * liberty_imagick_resize_image 
 * 
 * @param array $pFileHash 
 * @param array $pFormat 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_imagick_resize_image( &$pFileHash, $pFormat = NULL ) {
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
			list($type, $mimeExt) = split( '/', strtolower( $itype ) );
			if( !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) && ( ($pFileHash['max_width'] < $iwidth || $pFileHash['max_height'] < $iheight ) || ($mimeExt != 'jpeg')) ) {
				// We have to resize. *ALL* resizes are converted to jpeg
				$destExt = '.jpg';
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

/**
 * liberty_imagick_rotate_image 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_imagick_rotate_image( &$pFileHash ) {
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

/**
 * liberty_imagick_can_thumbnail_image 
 * 
 * @param array $pMimeType 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_imagick_can_thumbnail_image( $pMimeType ) {
	$ret = FALSE;
	if( !empty( $pMimeType ) ) {
		$ret = preg_match( '/^image/i', $pMimeType );
	}
	return $ret;
}
?>
