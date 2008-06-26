<?php
/**
 * @version  $Revision: 1.11 $
 * @package  liberty
 * @subpackage plugins_storage
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAFILE', 'datafile' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'file',
	'title'         => 'Treasury Files',
	'description'   => tra( "Display a file in content with some useful information. This plugin only works with files that have been uploaded using treasury." ),
	'help_page'     => 'DataPluginAttachment',

	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'syntax'        => '{file id= }',
	'path'          => LIBERTY_PKG_PATH.'plugins/data.file.php',
	'plugin_type'   => DATA_PLUGIN,

	// display icon in quicktags bar
	'biticon'       => '{biticon ilocation=quicktag iname=application-x-executable iexplain="Treasury File"}',
	'taginsert'     => '{file id= align= size= description=}',

	// functions
	'help_function' => 'data_file_help',
	'load_function' => 'data_file',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAFILE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAFILE );


function data_file( $pData, $pParams ) {
	global $gBitSystem, $gBitSmarty;
	$ret = ' ';

	if( @BitBase::verifyId( $pParams['id'] ) && $gBitSystem->isPackageActive( 'treasury' )) {
		require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );
		require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'display_bytes' );

		$item = new TreasuryItem();
		$item->mContentId = $item->getContentIdFromAttachmentId( $pParams['id'] );
		if( $item->load() ) {
			// insert source url if we need the original file
			if( !empty( $pParams['size'] ) && $pParams['size'] == 'original' ) {
				$thumburl = $item->getField( 'source_url' );
			} elseif( $item->getField( 'thumbnail_url' )) {
				$thumburl = ( !empty( $pParams['size'] ) && !empty( $item->mInfo['thumbnail_url'][$pParams['size']] ) ? $item->mInfo['thumbnail_url'][$pParams['size']] : $item->mInfo['thumbnail_url']['medium'] );
			}

			// check if we have a valid thumbnail
			if( !empty( $thumburl )) {
				$wrapper = liberty_plugins_wrapper_style( $pParams );
				$description = !empty( $wrapper['description'] ) ? $wrapper['description'] : $item->getField( 'data' , tra( 'Image' ));

				// set up image first
				$ret = '<img'.
					' alt="'.  $description.'"'.
					' title="'.$description.'"'.
					' src="'  .$thumburl.'"'.
					' />';

				if( $item->getField( 'file_size' )) {
					$ret .= '<br />'.$item->getField( 'title' )."<br /><small>(".$item->getField( 'mime_type' )." ".smarty_modifier_display_bytes( $item->getField( 'file_size' )).")</small>";
				}

				if( !empty( $description ) && !empty( $pParams['output'] ) && ( $pParams['output'] == 'desc' || $pParams['output'] == 'description' )) {
					$ret = $description;
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
				} elseif( empty( $pParams['download'] ) && $item->getField( 'display_url' )) {
					$ret = '<a href="'.trim( $item->getField( 'display_url' )).'">'.$ret.'</a>';
				} elseif( !empty( $pParams['download'] ) && $pParams['download'] = 'direct' ) {
					$ret = '<a href="'.trim( $item->getField( 'source_url' ) ).'">'.$ret.'</a>';
				} elseif( !empty( $pParams['download'] ) && $item->getField( 'download_url' )) {
					$ret = '<a href="'.trim( $item->getField( 'download_url' )).'">'.$ret.'</a>';
				} elseif( empty( $pParams['size'] ) || $pParams['size'] != 'original' ) {
					$ret = '<a href="'.trim( $item->getField( 'source_url' )).'">'.$ret.'</a>';
				}

				// finally, wrap the output.
				if( empty( $nowrapper )) {
					$ret = '<!-- ~np~ --><'.$wrapper['wrapper'].' class="'.( isset( $wrapper ) && !empty( $wrapper['class'] ) ? $wrapper['class'] : "att-plugin" ).'" style="'.$wrapper['style'].'">'.$ret.'</'.$wrapper['wrapper'].'><!-- ~/np~ -->';
				}
			} else {
				$ret = tra( "There was a problem getting an image for the file." );
			}
		} else {
			$ret = tra( "The attachment id given is not valid." );
		}
	} else {
		$ret = tra( "The attachment id given is not valid." );
	}

	return $ret;
}

function data_file_help() {
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
				.'<td>' . tra( "Attachment id number of Attachment to display inline.") . tra( "You can use either content_id or id." ).'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>content_id</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "Content id number of Attachment to display inline.") . tra( "You can use either content_id or id." ).'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>size</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "If the File is an image, you can specify the size of the thumbnail displayed. Possible values are:") . ' <strong>avatar, small, medium, large, original</strong> '
				. tra( "(Default = " ) . '<strong>medium</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>link</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Allows you to specify a relative or absolute URL the image will link to if clicked. If set to false, no link is inserted.")
				. tra("(Default = ") . '<strong>'.tra( 'link to source image' ).'</strong>)</td>'
			.'</tr>'
			.'<tr class="even">
				<td>output</td>
				<td>'.tra( 'keyword (optional)' ).'</td>
				<td>'.tra( "If you are attaching a file and you only want to display the description and not the image that goes with it, use: output=desc" ).'</td>
			</tr>'
			.'<tr class="odd">'
				.'<td>styling</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Multiple styling options available: padding, margin, background, border, text-align, color, font, font-size, font-weight, font-family, align. Please view CSS guidelines on what values these settings take.").'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>download</td>'
				.'<td>'.tra( "boolean").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "When you set download, clicking on the file will directly download it as opposed to linking to the file page." ).'</td>'
			.'</tr>'
		.'</table>'
		. tra( "Example: ") . "{file id='13' size='small' text-align='center'}";
	return $help;
}
?>
