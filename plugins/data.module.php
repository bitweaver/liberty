<?php
/**
 * @version  $Revision: 1.10 $
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
// | Author (TikiWiki): Mose <mose@users.sourceforge.net>
// | Reworked for Bitweaver  by: Christian Fowler <spiderr@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.module.php,v 1.10 2006/02/09 14:52:47 squareing Exp $

/**
 * definitions
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
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>module</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra( "(required)" ) . '</td>'
				.'<td>' . tra( "Name of module you want to display.")
			.'</tr>'
			.'<tr class="even">'
				.'<td>package</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra( "(required)" ) . '</td>'
				.'<td>' . tra( "Package the module is part of.")
			.'</tr>'
			.'<tr class="even">'
				.'<td>rows</td>'
				.'<td>' . tra( "numeric" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Number of rows you wish to show.")
			.'</tr>'
			.'<tr class="odd">'
				.'<td colspan="3">' . tra( "Additional arguments and values depend on the selected module." )
			.'</tr>'
		.'</table>'
		. tra( "Example: " ) . '{MODULE module=last_changes package=liberty title="Recent Changes"}';
	return $help;
}

function data_datamodule( $data, $params ) {
	global $gBitThemes, $gBitSmarty, $gBitThemes;

	$out = '';
	$ret = ' ';

	extract( $params , EXTR_SKIP);

	if( !empty( $module ) && !empty( $package ) ) {
		// not sure if we can use the php file, since it sets everything to NULL when passed in - xing
		global $module_rows;
		$module_rows = !empty( $rows ) ? $rows : 10;
		//$php = constant( strtoupper( $package ).'_PKG_PATH' ).'modules/mod_'.$module.'.php';
		// TODO: assigning variables to template doesn't work since they are replaced by module paramaters set in the php file - even when it's not in use! - xing
		if( is_file( constant( strtoupper( $package ).'_PKG_PATH' ).'modules/mod_'.$module.'.tpl' ) ) {
			$tpl = 'bitpackage:'.$package.'/mod_'.$module.'.tpl';
		} else {
			return '<div class="error">'.tra( "The module / package combination you entered is not valid" ).'</div>';
		}
	} else {
		return '<div class="error">'.tra( "Both paramters 'module' and 'package' are required" ).'</div>';
	}

	if( !$out = $gBitSmarty->fetch( $tpl ) ) {
		if( $gBitThemes->isCustomModule( $module ) ) {
			$info = $gBitThemes->getCustomModule( $module );
			$gBitSmarty->assign_by_ref( 'user_title', $info["title"] );
			$gBitSmarty->assign_by_ref( 'user_data', $info["data"] );
			$out = $gBitSmarty->fetch( 'modules/user_module.tpl' );
		}
	}
	$out = eregi_replace( "\n", "", $out );

	// deal with custom styling
	$style = '';
	$style_options = array( 'float', 'width', 'background', 'color' );
	foreach( $params as $param => $value ) {
		if( in_array( $param, $style_options ) ) {
			$style .= $param.':'.$value.';';
		}
	}

	if( !empty( $style ) ) {
		$style = ' style="'.$style.'"';
	}

	if( $out ) {
		$ret = '<div'.$style.'>'.$out.'</div>';
	}
	return $ret;
}
?>
