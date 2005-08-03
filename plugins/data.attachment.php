<?php
/**
 * @version  $Revision: 1.1.1.1.2.6 $
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
// $Id: data.attachment.php,v 1.1.1.1.2.6 2005/08/03 07:43:55 lsces Exp $

/**
 * definitions
 */
global $gBitSystem;

define( 'PLUGIN_GUID_DATAATTACHMENT', 'dataattachment' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'ATTACHMENT',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_attachment',
						'title' => 'Attachment',
						'help_page' => 'DataPluginAttachment',
						'description' => tra("Display attachment in content"),
						'help_function' => 'data_attachment_help',
						'syntax' => '{ATTACHMENT id= size= align= }',
						'plugin_type' => DATA_PLUGIN
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
				. tra("(Default = ") . '<strong>medium</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>link</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Allows you to specify a relative or absolute URL the image will link to if clicked. If set to false, no link is inserted.") 
				. tra("(Default = ") . '<strong>'.tra( 'link to source image' ).'</strong>)</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>align</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies how the Image / Attachment is to be alligned on the page. Possible values are:") . ' <strong>left, center, right</strong> '
				. tra("(Default = ") . '<strong>'.tra( 'none - attachment is shown inline' ).'</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>float</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies how the Image / Attachment is to float on the page. Behaviour of float is different to align. Possible values are:") . ' <strong>left, right</strong> '
				. tra("(Default = ") . '<strong>'.tra( 'none - attachment is shown inline' ).'</strong>)</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{ATTACHMENT id='13' size='small' align='center' link='http://www.google.com'}";
	return $help;
}

function data_attachment($data, $params) { // NOTE: The original plugin had several parameters that have been dropped
	// at a minimum, return blank string (not empty) so we still replace the tag
	$ret = ' ';
	if( empty( $params['id'] ) ) { 
		// The Manditory Parameter is missing. we are not gonna trow an error, and just return empty since
		// many sites use the old style required second "closing" empty tag
		return $ret;
	}
	$liba = new LibertyAttachable();
	if( !$att = $liba->getAttachment( $params['id'] ) ) {
	    $ret = tra("__Error__ - The plugin") . " __~np~{ATTACHMENT}~/np~__ " . tra("was given the parameter") . " id=" . $params['id'] . tra(" which is not valid.\n");
   	    return $ret;
   	}

	// insert source url if we need the original file
	if( !empty( $params['size'] ) && $params['size'] == 'original' ) {
		$thumburl = $att['source_url'];
	} else {
		$thumburl = ( !empty( $params['size'] ) && !empty( $att['thumbnail_url'][$params['size']] ) ? $att['thumbnail_url'][$params['size']] : $att['thumbnail_url']['medium'] );
	}

	// use specified link as href. insert default link to source only when source not already displayed
	if( !empty( $params['link'] ) && $params['link'] == 'false' ) {
		$href = '';
	} elseif( !empty( $params['link'] ) ) {
		$href = ' href="'.$params['link'].'"';
	} elseif( empty( $params['size'] ) || $params['size'] != 'original' ) {
		$href = ' href="'.$att['source_url'].'"';
	} else {
		$href = '';
	}

	$aloat = ( !empty( $params['align'] ) ? '<div style="text-align:'.$params['align'].';">' : NULL );
	$aloat = ( ( !empty( $params['float'] ) && empty( $aloat ) ) ? '<div style="float:'.$params['float'].';">' : NULL );
	$ret =  $aloat.'<a'.$href.'"><img src="'.$thumburl.'"/></a>'.(!empty($aloat)?'</div>':'');
	return $ret;
}
?>
