<?php
/**
 * @version  $Revision: 1.1 $
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
// $Id: data.flashvideo.php,v 1.1 2007/02/11 18:07:26 squareing Exp $

/**
 * definitions
 */
global $gBitSystem;

define( 'PLUGIN_GUID_DATAFLASHVIDEO', 'dataflashvideo' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'flashvideo',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_flashvideo',
	'title'         => 'Flash Video',
	'help_page'     => 'DataPluginAttachment',
	'description'   => tra( "Display flashvideo in content. This requires videos" ),
	'help_function' => 'data_flashvideo_help',
	'syntax'        => '{flashvideo id= size= align= }',
	'path'          => LIBERTY_PKG_PATH.'plugins/data.flashvideo.php',
	'security'      => 'registered',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon iclass= "quicktag icon" ipackage=quicktags iname=flashvideo iexplain="Image"}',
	'taginsert'     => '{flashvideo id= align= size= description=}',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAFLASHVIDEO, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAFLASHVIDEO );


function data_flashvideo_help() {
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
				<td>display</td>
				<td>'.tra( 'keyword (optional)' ).'</td>
				<td>'.tra( "If you are attaching a file and you only want to display the description and not the image that goes with it, use: display=desc" ).'</td>
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

function data_flashvideo( $pData, $pParams ) { // NOTE: The original plugin had several parameters that have been dropped
	// at a minimum, return blank string (not empty) so we still replace the tag
	$ret = ' ';
	if( empty( $pParams['id'] )) {
		// The Manditory Parameter is missing. we are not gonna trow an error, and just return empty since
		// many sites use the old style required second "closing" empty tag
		return $ret;
	}

	$liba = new LibertyAttachable();
	if( !$att = $liba->getAttachment( $pParams['id'] )) {
		$ret = tra( "The flashvideo id given is not valid." );
		return $ret;
	}

	if( !empty( $att['flv_url'] )) {
		$div = liberty_plugins_div_style( $pParams );

		// this has to be on one line to prevent the parser from adding <br/>s and other crappy stuff.
		$ret = '<p id="flv_player_'.$pParams['id'].'"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this video.</p><script type="text/javascript"> var FO = { movie:"'.TREASURY_PKG_URL.'libs/flv_player/flvplayer.swf",width:"320",height:"250",majorversion:"7",build:"0",bgcolor:"#FFFFFF",flashvars:"file='.$att['flv_url'].'&showdigits=false&autostart=false&image='.$att['thumbnail_url']['medium'].'&showfsbutton=true&repeat=false" }; UFO.create( FO, "flv_player_'.$pParams['id'].'");</script>';

		// link to page by page_id
		if( @BitBase::verifyId( $pParams['page_id'] )) {
			require_once( WIKI_PKG_PATH.'BitPage.php');
			$wp = new BitPage( $pParams['page_id'] );
			if( $wp->load()) {
				$pParams['link'] = $wp->getDisplayUrl();
			}
		// link to any content by content_id
		} elseif( isset( $pParams['content_id'] ) && is_numeric( $pParams['content_id'] )) {
			if( $obj = LibertyBase::getLibertyObject( $pParams['content_id'] )) {
				$pParams['link'] = $obj->getDisplayUrl();
			}
		// link to page by page_name
		} elseif( isset( $pParams['page_name'] )) {
			require_once( WIKI_PKG_PATH.'BitPage.php');
			$wp = new BitPage();
			$pParams['link'] = $wp->getDisplayUrl( $pParams['page_name'] );
		}

		if( !empty( $div['description'] ) && !empty( $pParams['display'] ) && ( $pParams['display'] == 'desc' || $pParams['display'] == 'description' )) {
			$ret = ( !empty( $div['description'] )  ? $div['description'] : '' );
			$nodiv = TRUE;
		} else {
			$ret .= ( !empty( $div['description'] )  ? '<br />'.$div['description']  : '' );
		}

		// finally, wrap the output with a div
		if( empty( $nodiv )) {
			$ret =
				'<div class="'.( !empty( $div['class'] ) ? $div['class'] : "flashvideo-plugin" ).'" style="'.$div['style'].'">'.
				$ret.'</div>';
		}
	} else {
		$ret = tra( "The flashvideo id given is not valid." );
	}

	return $ret;
}
?>
