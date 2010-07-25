<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
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
// | Authors: drewslater <andrew@andrewslater.com>
// +----------------------------------------------------------------------+
// $Id$

/**
 * definitions
 */
global $gBitSystem;

define( 'PLUGIN_GUID_DATAATTACHMENT', 'dataattachment' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'attachment',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_attachment',
	'title'         => 'Attachment',
	'help_page'     => 'DataPluginAttachment',
	'description'   => tra("Display attachment in content"),
	'help_function' => 'data_attachment_help',
	'syntax'        => '{attachment id= size= align= }',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon ilocation=quicktag iname=mail-attachment iexplain="Attachment"}',
	'taginsert'     => '{attachment id= align= size= description= alt=}',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAATTACHMENT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAATTACHMENT );


function data_attachment_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>id</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "Id number of Attachment to display inline.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>size</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "If the Attachment is an image, you can specify the size of the thumbnail displayed. Possible values are:") . ' <strong>avatar, small, medium, large, original</strong> '
				. tra( "(Default = " ) . '<strong>medium</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>description</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "The text to use in the title attribute or as the link text if output=desc. Will also be used for the alt attribute if no alt is specified. This text is parsed." )
				.tra( "(Default = " ) . '<strong>'.tra( 'Image' ).'</strong>)</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>alt</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The text to use in the alt tag. Will also be used for the title attribute if no description is specified.")
				. tra("(Default = ") . '<strong>'.tra( 'Image' ).'</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>link</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Allows you to specify a relative or absolute URL the image will link to if clicked. You can also link to one of the sizes of the image: icon, avatar, small, medium, large, original and download (insert download link, which will activate the download counter). If set to false, no link is inserted.")
				. tra("(Default = ") . '<strong>'.tra( 'link to image details page' ).'</strong>)</td>'
			.'</tr>'
			.'<tr class="even">
				<td>page_id</td>
				<td>'.tra( 'numeric (optional)' ).'</td>
				<td>'.tra( "To include any wiki page you can use it's page_id number." ).'</td>
			</tr>
			<tr class="odd">
				<td>content_id</td>
				<td>'.tra( 'numeric (optional)' ).'</td>
				<td>'.tra( 'To include any content from bitweaver insert the appropriate numeric content id. This can include blog posts, images, wiki texts...<br />
					Available content can be viewed <a href="'.LIBERTY_PKG_URL.'list_content.php">here</a>' ).'</td>
			</tr>
			<tr class="even">
				<td>output</td>
				<td>'.tra( 'keyword (optional)' ).'</td>
				<td>'.tra( "If you are attaching a file and you only want to display the description and not the image that goes with it, use: output=desc. If you want to force the use of a thumbnail, use output=thumbnail." ).'</td>
			</tr>'
			.'<tr class="odd">'
				.'<td>'.tra( "styling" ).'</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Multiple styling options available: width, height, background, background-color, border, color, display, float, font, font-family, font-size, font-weight, margin, overflow, padding, text-align, align. Please view <a href='http://www.w3.org/TR/CSS21/indexlist.html'>CSS guidelines</a> on what values these settings take.").'</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . ' ' . "{ATTACHMENT id='13' size='small' text-align='center' link='http://www.google.com'}"
		. '<br />'
		. tra("Example: ") . ' ' . "{ATTACHMENT id='11' description='Text, the link will be wrapped around' output=desc}";
	return $help;
}

function data_attachment( $pData, $pParams, $pCommonObject, $pParseHash ) {
	require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );

	// at a minimum, return blank string (not empty) so we still replace the tag
	$ret = ' ';

	// The Manditory Parameter is missing. we are not gonna trow an error, and 
	// just return empty since many sites use the old style required second 
	// "closing" empty tag
	if( empty( $pParams['id'] )) {
		return $ret;
	}

	if( !$att = LibertyMime::getAttachment( $pParams['id'], $pParams )) {
		$ret = tra( "The attachment id given is not valid." );
		return $ret;
	}
	// we will do slightly different stuff if this is using a mime plugin
	if( !empty( $att['is_mime'] )) {
		global $gBitSmarty, $gLibertySystem, $gContent;
		// convert parameters into display properties
		$wrapper = liberty_plugins_wrapper_style( $pParams );
		// work out custom display_url if there is one
		if( @BitBase::verifyId( $pParams['page_id'] )) {
			// link to page by page_id
			// avoid endless loops

			require_once( WIKI_PKG_PATH.'BitPage.php');
			$wp = new BitPage( $pParams['page_id'] );
			if( $wp->load() ) {
				$wrapper['display_url'] = $wp->getDisplayUrl();
			}
		} elseif( @BitBase::verifyId( $pParams['content_id'] )) {
			// link to any content by content_id
			if( $obj = LibertyBase::getLibertyObject( $pParams['content_id'] )) {
				$wrapper['display_url'] = $obj->getDisplayUrl();
			}
		} elseif( !empty( $pParams['page_name'] )) {
			// link to page by page_name
			require_once( WIKI_PKG_PATH.'BitPage.php');
			$wp = new BitPage();
			$wrapper['display_url'] = $wp->getDisplayUrl( $pParams['page_name'] );
		} elseif( !empty( $pParams['link'] ) && $pParams['link'] == 'false' ) {
			// no link
		} elseif( !empty( $pParams['link'] )) {
			// Allow the use of icon, avatar, small, medium and large to link to certain size of image directly
			if( !empty( $att['thumnail_url'][$pParams['link']] )) {
				$pParams['link'] = $att['thumnail_url'][$pParams['link']];

			// Allow the use of 'original' to link to original file directly
			} elseif( $pParams['link'] == 'original' && !empty( $att['source_url'] )) {
				$pParams['link'] = $att['source_url'];

			// Allow the use of 'download' to link to download link. this will allow us to count downloads
			} elseif( $pParams['link'] == 'download' && !empty( $att['download_url'] )) {
				$pParams['link'] = $att['download_url'];

			// Adjust class name if we are leaving this server
			} elseif( !strstr( $pParams['link'], $_SERVER["SERVER_NAME"] ) && strstr( $pParams['link'], '//' )) {
				$wrapper['href_class'] = 'class="external"';
			}
			$wrapper['display_url'] = $pParams['link'];
		} else {
			$wrapper['display_url'] = $att['display_url'];
		}

		if( !empty( $wrapper['description'] )) {
			$parseHash['content_id'] = $pParseHash['content_id'];
			$parseHash['user_id']    = $pParseHash['user_id'];
			$parseHash['no_cache']   = TRUE;
			$parseHash['data']       = $wrapper['description'];
			$wrapper['description_parsed'] = $pCommonObject->parseData( $parseHash );
		}

		// pass stuff to the template
		$gBitSmarty->assign( 'attachment', $att );
		$gBitSmarty->assign( 'wrapper', $wrapper );
		$gBitSmarty->assign( 'thumbsize', (( !empty( $pParams['size'] ) && ( $pParams['size'] == 'original' || !empty( $att['thumbnail_url'][$pParams['size']] ))) ? $pParams['size'] : 'medium' ));

		//Carry only these attributes to the image tags
		$width = !empty( $pParams['width'] ) ? $pParams['width'] : '';
		$gBitSmarty->assign( 'width', $width );

		$height = !empty( $pParams['height'] ) ? $pParams['height'] : '';
		$gBitSmarty->assign( 'height', $height );

		$mimehandler = (( !empty( $wrapper['output'] ) && $wrapper['output'] == 'thumbnail' ) ? LIBERTY_DEFAULT_MIME_HANDLER : $att['attachment_plugin_guid'] );
		$ret = $gBitSmarty->fetch( $gLibertySystem->getMimeTemplate( 'attachment', $mimehandler ));
	} else {
		// TODO: legacy code - should be faded out if possible


		// insert source url if we need the original file
		if( !empty( $pParams['size'] ) && $pParams['size'] == 'original' ) {
			$thumburl = $att['source_url'];
		} elseif( !empty( $att['thumbnail_url'] )) {
			$thumburl = ( !empty( $pParams['size'] ) && !empty( $att['thumbnail_url'][$pParams['size']] ) ? $att['thumbnail_url'][$pParams['size']] : $att['thumbnail_url']['medium'] );
		}

		// Figure out alt attribute.
		if( empty( $wrapper['alt'] )) {
			if ( empty( $wrapper['description'] )) {
				$alt = tra( 'Image' );
			} else {
				$alt = $wrapper['description'];
			}
		} else {
			$alt = $wrapper['alt'];
		}
		// check if we have a valid thumbnail
		if( !empty( $thumburl ) ) {
			$wrapper = liberty_plugins_wrapper_style( $pParams );
			// set up image first
			$ret = '<img'.
				' alt="'.  $alt .'"'.
				' title="'. ( empty($wrapper['description']) ? $alt : $wrapper['description'] ). '"'.
				' src="'  .$thumburl.'"'.
				' />';

			$ret .= ( !empty( $att['file_details'] ) ? '<br />'.$att['file_details'] : '' );

			// link to page by page_id
			if( @BitBase::verifyId( $pParams['page_id'] ) ) {
				require_once( WIKI_PKG_PATH.'BitPage.php');
				$wp = new BitPage( $pParams['page_id'] );
				if( $wp->load() ) {
					$pParams['link'] = $wp->getDisplayUrl();
				}
				// link to any content by content_id
			} elseif( isset( $pParams['content_id'] ) && is_numeric( $pParams['content_id'] ) ) {
				if( $obj = LibertyBase::getLibertyObject( $pParams['content_id'] ) ) {
					$pParams['link'] = $obj->getDisplayUrl();
				}
				// link to page by page_name
			} elseif( isset( $pParams['page_name'] ) ) {
				require_once( WIKI_PKG_PATH.'BitPage.php');
				$wp = new BitPage();
				$pParams['link'] = $wp->getDisplayUrl( $pParams['page_name'] );
			}

			if( !empty( $pParams['output'] ) && ( $pParams['output'] == 'desc' || $pParams['output'] == 'description' )) {
				$ret = ( !empty( $wrapper['description'] )  ? $wrapper['description'] : $att['filename'] );
				$nowrapper = TRUE;
			} else {
				$ret .= ( !empty( $wrapper['description'] )  ? '<br />'.$wrapper['description']  : '' );
			}

			// use specified link as href. insert default link to source only when 
			// source not already displayed
			if( !empty( $pParams['link'] ) && $pParams['link'] == 'false' ) {
			} elseif( !empty( $pParams['link'] ) ) {
				if(( strstr( $pParams['link'], $_SERVER["SERVER_NAME"] )) || (!strstr( $pParams['link'], '//' ))) {
					$class = '';
				} else {
					$class = 'class="external"';
				}

				$ret = '<a '.$class.' href="'.trim( $pParams['link'] ).'">'.$ret.'</a>';
			} elseif( !empty( $att['download_url'] ) ) {
				$ret = '<a href="'.trim( $att['download_url'] ).'">'.$ret.'</a>';
			} elseif( !empty( $att['display_url'] ) ) {
				$ret = '<a href="'.trim( $att['display_url'] ).'">'.$ret.'</a>';
			} elseif( empty( $pParams['size'] ) || $pParams['size'] != 'original' ) {
				$ret = '<a href="'.trim( $att['source_url'] ).'">'.$ret.'</a>';
			}

			// finally, wrap the output.
			if( empty( $nowrapper )) {
				$ret = '<'.$wrapper['wrapper'].' class="'.( isset( $wrapper ) && !empty( $wrapper['class'] ) ? $wrapper['class'] : "att-plugin" ).'" style="'.$wrapper['style'].'">'.$ret.'</'.$wrapper['wrapper'].'>';
			}
		} else {
			$ret = tra( "The attachment id given is not valid." );
		}
	}
	return $ret;
}
?>
