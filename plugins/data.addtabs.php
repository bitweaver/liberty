<?php
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_include.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.addtabs.php,v 1.1.1.1.2.2 2005/06/24 07:50:58 starrrider Exp $
// Initialization
define( 'PLUGIN_GUID_DATAADDTABS', 'dataaddtabs' );
global $gLibertySystem;
global $gContent;
$pluginParams = array ( 'tag' => 'ADDTABS',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_addtabs',
						'title' => 'AddTabs',
						'description' => tra("Will join the contents from several sources in a Tabbed Interface."),
						'help_function' => 'data_addtabs_help',
						'tp_helppage' => "http://www.bitweaver.org/wiki/DataPluginAddtabs",
						'syntax' => "{addtabs tab1= tab2= tab3= . . . tab99= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAADDTABS, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAADDTABS );

// Help Function
function data_addtabs_help() {
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
				.'<td>' . tra( "Will create a Tab interface on a page. The name on each tab is the name given to the imported page.The value sent with the TabX parameter is a Numeric Content Id. This allows blog posts, images, wiki pages . . . (and more) to be added.")
				. tra("<br /><strong>Note 1:</strong> A listing of Content Id's can be found ") 
				. '<a href="'.LIBERTY_PKG_URL.'list_content.php" title="Launch BitWeaver Content Browser in New Window" onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">' . tra( "Here" ) . '</a>'
				. tra("<br /><strong>Note 2:</strong> The order used when the tabs are specified does not matter. The Tabname does - Tab1 is always first and Tab99 will always be last.</td>")
			.'</tr>'
		.'</table>'
		. tra("Example: ") . '{addtabs tab1=15 tab2=12 tab3=11}';
	return $help;
}

function data_addtabs($data, $params) {
	extract ($params);
	$ret = "<div id='tab-system' class='tabsystem'>";
	for ($i = 1; $i <= 99; $i++) {
		if( isset( ${'tab'.$i} ) && is_numeric( ${'tab'.$i} ) ) {
			$obj = LibertyBase::getLibertyObject( ${'tab'.$i} );
			if( $obj->load() ) {
				$ret .= "<div class='tabpage '><h3>" .$obj->getTitle() . "</h3><div class='contents'>" . $obj->parseData() . "</div></div><!-- end .tabpage -->";
//				$ret .= "<div class='tabpage '><h3>" . ${'tab'.$i} . "</h3><div class='contents'>" . $obj->parseData() . "</div></div><!-- end .tabpage -->";
				$good=True;
			}
		}
	}
	$ret .= "</div>";
	if( !$good ) {
		$ret = "The Plugin AddTabs requires valid parameters. Numeric content id numbers can use the parameter names 'tab1' thru 'tab99'";
	}
	return $ret;
}
?>
