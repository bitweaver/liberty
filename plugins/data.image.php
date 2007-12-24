<?php
/**
 * @version  $Revision: 1.3 $
 * @package  liberty
 * @subpackage plugins_storage
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAIMAGE', 'dataimage' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'image',
	'title'         => 'Fisheye Image',
	'description'   => tra( "Display an image in other content. This plugin only works with files that have been uploaded using fisheye." ),
	'help_page'     => 'DataPluginImage',

	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'syntax'        => '{image id= }',
	'path'          => LIBERTY_PKG_PATH.'plugins/data.image.php',
	'plugin_type'   => DATA_PLUGIN,

	// display icon in quicktags bar
	'biticon'       => '{biticon ilocation=quicktag iname=image-x-generic iexplain="Image"}',
	'taginsert'     => '{image id= size= nolink=}',

	// functions
	'help_function' => 'data_image_help',
	'load_function' => 'data_image',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAIMAGE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAIMAGE );


function data_image( $pData, $pParams ) {
	global $gBitSystem, $gBitSmarty;
	$ret = ' ';

	if( @BitBase::verifyId( $pParams['id'] ) && $gBitSystem->isPackageActive( 'fisheye' )) {
		require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );
		require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'display_bytes' );

		$item = new FisheyeImage( $pParams['id'], NULL );

		if( $item->load() ) {
			// insert source url if we need the original file
			if( !empty( $pParams['size'] ) && $pParams['size'] == 'original' ) {
				$thumburl = $item->mInfo['image_file']['source_url'];
			} elseif( $item->mInfo['image_file']['thumbnail_url'] ) {
				$thumburl = ( !empty( $pParams['size'] ) && !empty( $item->mInfo['image_file']['thumbnail_url'][$pParams['size']] ) ? $item->mInfo['image_file']['thumbnail_url'][$pParams['size']] : $item->mInfo['image_file']['thumbnail_url']['medium'] );
			}

			// check if we have a valid thumbnail
			if( !empty( $thumburl )) {
				$description = $item->mInfo['title'];

				// set up image first
				$ret = '<img'.
					' alt="'.  $description.'"'.
					' title="'.$description.'"'.
					' src="'  .$thumburl.'"'.
					' />';

				if( !empty( $pParams['nolink'] ) ) {
				}
				else if ( empty( $pParams['size'] ) || $pParams['size'] != 'original' ) {
					$ret = '<a href="'.trim( $item->mInfo['image_file']['source_url'] ).'">'.$ret.'</a>';
				}

			} else {
				$ret = tra( "There was a problem getting an image for the file." );
			}
		} else {
			$ret = tra( "The image id given is not valid." );
		}
	} else {
		$ret = tra( "The image id given is not valid." );
	}
	return $ret;
}

function data_image_help() {
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
				.'<td>' . tra( "Image id number of Image to display inline.") . tra( "You can use either content_id or id." ).'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>size</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "If the File is an image, you can specify the size of the thumbnail displayed. Possible values are:") . ' <strong>avatar, small, medium, large, original</strong> '
				. tra( "(Default = " ) . '<strong>medium</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>nolink</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Remove hotlink from element. Used to display fixed copies of an image item.")
			.'</tr>'
		.'</table>'
		. tra( "Example: ") . "{image id='13' size='small'}";
	return $help;
}
?>
