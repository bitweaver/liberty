<?php
/**
 * @version  $Revision: 1.1.1.1.2.5 $
 * @package  Liberty
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
// | Author (TikiWiki): Mose <mose@users.sourceforge.net>
// | Reworked for Bitweaver  by: Christian Fowler <spiderr@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.module.php,v 1.1.1.1.2.5 2005/06/27 10:08:37 lsces Exp $
// Initialization

/* 
Displays a module inlined in page
Parameters
module name : module=>lambda
align : align=>(left|center|right)
max : max=>20
np : np=>(0|1) # (for non-parsed content)
module args : arg=>value (depends on module)
Example:
{MODULE(module=>last_modified_pages,align=>left,max=>3,maxlen=>22)}
{/MODULE}
about module params : all params are passed in $module_params
so if you need to use parmas just add them in MODULE()
like the tracker_id in the above example.
*/
/**
 * \warning zaufi: using cached module template is break the idea of
 *   having different (than system default) parameters for modules...
 *   so cache checking and maintaining currently commented out
 *   'till another solution will be implemented :)
 */

define( 'PLUGIN_GUID_DATAMODULE', 'datamodule' );

global $gLibertySystem;
$pluginParams = array ( 'tag' => 'MODULE',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_datamodule',
						'title' => 'Module',
						'help_page' => 'DataPluginModule',
						'description' => tra("Display a module block in content"),
						'help_function' => 'datamodule_help',
						'syntax' => '{MODULE module= align="right"}',
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAMODULE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAMODULE );

function datamodule_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{MODULE" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::module::" . tra(" | ::name:: | the name of the module to be displayed. __Required__\n");
	$back.= "::align::" . tra(" | ::alignment:: | orientation of the module to the page. Can be  ") . "left / center / right" . tra(". There default is ") . "__left__.\n";
	$back.= "::max::" . tra(" | ::number:: | number of rows the module will be display in. There default is __10__.\n");
	$back.= "::np::" . tra(" | ::boolean:: |  if True (any value = True) the content will not be parsed. There default is __No__.\n");
	$back.= "::args::" . tra(" | ::value:: | this depends on module - some modules require additional arguments passed to them.||^");
	$back.= tra("^__Example:__ ") . "~np~{MODULE(module=>last_modified_pages,align=>left,max=>3,maxlen=>22)}{/MODULE}~/np~^";
	$back.= tra("^__Note:__ Plugin's are __case sensitive__. The Name of the plugin __MUST__ be UPPERCASE. The Key(s) are __always__ lowercase. Some Values are mixed-case but most require lowercase. When in doubt - look at the Example.^");
	return $back;
}

function data_datamodule($data, $params) {
	global $modlib, $cache_time, $smarty, $feature_directory, $ranklib, $feature_trackers, $bitdomain, $user,
		$feature_tasks, $feature_user_bookmarks, $bit_p_tasks, $bit_p_create_bookmarks, $imagegallib;
	require_once( KERNEL_PKG_PATH.'mod_lib.php' );
	$out = '';
	extract ($params);
	if (!isset($align)) {
		$align = 'left';
	}
	if (!isset($max)) {
		$max = '10';
	}
	if (!isset($np)) {
		$np = '0';
	}
	if (!isset($module)) {
		$out = '<form class="box" id="modulebox">';
		$out .= '<br /><select name="choose">';
		$out .= '<option value="">' . tra('Please choose a module'). '</option>';
		$out .= '<option value="" style="background-color:#bebebe;">' . tra('to be used as argument'). '</option>';
		$out .= '<option value="" style="background-color:#bebebe;">{MODULE(module=>name_of_module)}</option>';
		$handle = opendir('modules');
		while ($file = readdir($handle)) {
			if ((substr($file, 0, 4) == "mod-") and (substr($file, -4, 4) == ".php")) {
				$mod = substr(substr(basename($file), 4), 0, -4);
				$out .= "<option value=\"$mod\">$mod</option>";
			}
		}
		$out .= '</select></form>';
	} else {
		if (!isset($args)) {
			$args = '';
		}
//		if ((!file_exists($cachefile)) || (file_exists($nocache)) || ((time() - filemtime($cachefile)) > $cache_time)) {
            $smarty->assign('no_module_controls', 'y');
			if( $out = $smarty->fetch( $module ) ) {
			} else {
				if ($modlib->is_user_module($module)) {
					$info = $modlib->get_user_module($module);
					$smarty->assign_by_ref('user_title', $info["title"]);
					$smarty->assign_by_ref('user_data', $info["data"]);
					$out = $smarty->fetch('modules/user_module.tpl');
				}
			}
           	$smarty->clear_assign('no_module_controls');
//		} else {
//			$fp = fopen($cachefile, "r");
//			$out = fread($fp, filesize($cachefile));
//			fclose ($fp);
//		}
		$out = eregi_replace("\n", "", $out);
	}
	if ($out) {
		if ($np) {
  		    $data = "<div style='float:$align;'>~np~$out~/np~</div>".$data;
		} else {
			$data = "<div style='float:$align;'>$out</div>" . $data;
		}
	} else {
		$data = "<div style='error'>" . tra("Sorry no such module"). "<br/><b>$module</b></div>" . $data;
	}
	return $data;
}
?>
