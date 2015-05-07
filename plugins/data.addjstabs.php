<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
 *
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2015, bitweaver.org
 * +----------------------------------------------------------------------+
 * | All Rights Reserved. See below for details and a complete list of authors.
 * | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 * |
 * | For comments, please use phpdocu.sourceforge.net documentation standards!!!
 * | -> see http://phpdocu.sourceforge.net/
 * +----------------------------------------------------------------------+
 * | Author: StarRider <starrrider@users.sourceforge.net>
 * | port to jstabs <lester@lsces.co.uk>
 * +----------------------------------------------------------------------+
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAADDJSTABS', 'dataaddjstabs' );
global $gLibertySystem;
global $gContent;
$pluginParams = array (
	'tag' => 'ADDJSTABS',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_addjstabs',
	'title' => 'AddJSTabs',
	'help_page' => 'DataPluginAddJSTabs',
	'description' => tra("Will join the contents from several sources in a Tabbed Interface."),
	'help_function' => 'data_addjstabs_help',
	'syntax' => "{ADDTABS tab1= tab2= tab3= . . . tab99= }",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAADDTABS, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAADDTABS );

/**
 * Help Function
 */
function data_addjstabs_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>tab1 - tab99</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Will create a Tab interface on a page using jstab format. The name on each tab is the name given to the imported page.The value sent with the TabX parameter is a Numeric Content Id. This allows blog posts, images, wiki pages . . . (and more) to be added.")
				. tra("<br /><strong>Note 1:</strong> A listing of Content Id's can be found ")
				. '<a href="'.LIBERTY_PKG_URL.'list_content.php" title="Launch BitWeaver Content Browser in New Window" onkeypress="javascript:BitBase.popUpWin(this.href,\'standard\',800,800);" onclick="javascript:BitBase.popUpWin(this.href,\'standard\',800,800);return false;">' . tra( "Here" ) . '</a>'
				. tra("<br /><strong>Note 2:</strong> The order used when the tabs are specified does not matter. The Tabname does - Tab1 is always first and Tab99 will always be last.</td>")
			.'</tr>'
		.'</table>'
		. tra("Example: ") . '{ADDTABS tab1=15 tab2=12 tab3=11}';
	return $help;
}

function data_addjstabs($data, $params) {
	global $gBitSmarty;
	$gBitSmarty->loadPlugin( 'smarty_block_jstab' );
	$gBitSmarty->loadPlugin( 'smarty_block_jstabs' );

	extract ($params, EXTR_SKIP);
	$id = 1000000 * microtime();
	$html = '';
	$good = false;

	for ($i = 1; $i <= 99; $i++) {
		if( isset( ${'tab'.$i} ) ) {
			if (is_numeric( ${'tab'.$i} ) ) {
				if( $obj = LibertyBase::getLibertyObject( ${'tab'.$i} ) ) {
					$params['title'] = $obj->getTitle();
					$html .= smarty_block_jstab( $params, $obj->mInfo['parsed_data'], $gBitSmarty, '' );
					$good=True;
				}
			}
			else {
				$good=false;
			}
		}
	}
	if( !$good ) {
		$html = tra("The plugin AddTabs requires valid parameters. Numeric content id numbers can use the parameter names 'tab1' thru 'tab99'");
	}
	if( !empty( $html )) {
		return smarty_block_jstabs( array(), $html, $gBitSmarty, '' );
	} else {
		return $html;
	}
}
?>
