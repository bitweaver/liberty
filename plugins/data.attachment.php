<?php
/**
 * @version  $Revision: 1.27 $
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Authors: drewslater <andrew@andrewslater.com>
// +----------------------------------------------------------------------+
// $Id: data.attachment.php,v 1.27 2008/06/14 09:04:15 squareing Exp $

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
	'path'          => LIBERTY_PKG_PATH.'plugins/data.attachment.php',
	'security'      => 'registered',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon ilocation=quicktag iname=mail-attachment iexplain="Attachment"}',
	'taginsert'     => '{attachment id= align= size= description=}',
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
				.'<td>link</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Allows you to specify a relative or absolute URL the image will link to if clicked. If set to false, no link is inserted.")
				. tra("(Default = ") . '<strong>'.tra( 'link to source image' ).'</strong>)</td>'
			.'</tr>'
			.'<tr class="even">
				<td>page_id</td>
				<td>'.tra( 'numeric (optional)' ).'</td>
				<td>'.tra( "To include any wiki page you can use it's page_id number." ).'</td>
			</tr>
			<tr class="odd">
				<td>content_id</td>
				<td>'.tra( 'numeric (optional)' ).'</td>
				<td>'.tra( 'To include any content from bitweaver insert the apprpropriate numeric content id. This can include blog posts, images, wiki texts...<br />
					Avaliable content can be viewed <a href="'.LIBERTY_PKG_URL.'list_content.php">here</a>' ).'</td>
			</tr>
			<tr class="even">
				<td>output</td>
				<td>'.tra( 'keyword (optional)' ).'</td>
				<td>'.tra( "If you are attaching a file and you only want to display the description and not the image that goes with it, use: output=desc" ).'</td>
			</tr>'
			.'<tr class="odd">'
				.'<td>'.tra( "styling" ).'</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Multiple styling options available: padding, margin, background, border, text-align, color, font, font-size, font-weight, font-family, align. Please view CSS guidelines on what values these settings take.").'</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{ATTACHMENT id='13' size='small' text-align='center' link='http://www.google.com'}";
	return $help;
}

function data_attachment( $pData, $pParams ) { // NOTE: The original plugin had several parameters that have been dropped
	require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );

	// at a minimum, return blank string (not empty) so we still replace the tag
	$ret = ' ';

	// The Manditory Parameter is missing. we are not gonna trow an error, and 
	// just return empty since many sites use the old style required second 
	// "closing" empty tag
	if( empty( $pParams['id'] ) ) {
		return $ret;
	}

	if( !$att = LibertyMime::getAttachment( $pParams['id'], $pParams )) {
		$ret = tra( "The attachment id given is not valid." );
		return $ret;
	}

	// we will do slightly different stuff if this is using a mime plugin
	if( !empty( $att['is_mime'] )) {
		global $gBitSmarty;

		// pass useful stuff to the template
		$gBitSmarty->assign( 'attachmentParams', $pParams );
		$gBitSmarty->assign( 'attachment', $att );

		$wrapper = liberty_plugins_wrapper_style( $pParams );
		$gBitSmarty->assign( 'wrapper', $wrapper );

		$thumbsize = !empty( $pParams['size'] ) && !empty( $item->mInfo['thumbnail_url'][$pParams['size']] ) ? $pParams['size'] : 'medium';
		$gBitSmarty->assign( 'thumbsize', $thumbsize );

		$template = $gLibertySystem->getMimeTemplate( 'inline', $att['attachment_plugin_guid'] );
		$ret = $gBitSmarty->fetch( $template );
	} else {
		// TODO: legacy code - should be faded out if possible


		// insert source url if we need the original file
		if( !empty( $pParams['size'] ) && $pParams['size'] == 'original' ) {
			$thumburl = $att['source_url'];
		} elseif( !empty( $att['thumbnail_url'] )) {
			$thumburl = ( !empty( $pParams['size'] ) && !empty( $att['thumbnail_url'][$pParams['size']] ) ? $att['thumbnail_url'][$pParams['size']] : $att['thumbnail_url']['medium'] );
		}

		// check if we have a valid thumbnail
		if( !empty( $thumburl ) ) {
			$wrapper = liberty_plugins_wrapper_style( $pParams );

			// set up image first
			$ret = '<img'.
				' alt="'.  ( !empty( $wrapper['description'] ) ? $wrapper['description'] : tra( 'Image' ) ).'"'.
				' title="'.( !empty( $wrapper['description'] ) ? $wrapper['description'] : tra( 'Image' ) ).'"'.
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
