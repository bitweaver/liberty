<?php
// $Id$
/**
 * assigned_modules
 *
 * @author   StarRider <starrrider@sourceforge.net>
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
 * @copyright Copyright (c) 2004, bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * @license Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
 */
/******************
 * Initialization *
 ******************/
global $gLibertySystem;
define( 'PLUGIN_GUID_DATAGESHIDATA','datageshidata' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'GESHIDATA',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_GeshiData',
	'title' => 'GeshiData',
	'help_page' => 'DataPluginGeshiData',
	'description' => tra("This plugin is a documentation tool for the bitweaver site. It will display some of information defined in the GeSHi (Generic Syntax Highlighter) package."),
	'help_function' => 'data_help_GeshiData',
	'syntax' => "{GESHIDATA doall= lang= info= }",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAGESHIDATA, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAGESHIDATA );
/*****************
 * Help Function *
 *****************/
function data_help_GeshiData() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>'.tra( "Key" ).'</th>'
				.'<th>'.tra( "Type" ).'</th>'
				.'<th>'.tra( "Comments" ).'</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>doall</td>'
				.'<td>'.tra( "boolean").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Provided to display the information for Every Language.")
					.'<br />'.tra( "Default =").' <strong>False</strong> '.tra( "- So only specific information is Displayed.")
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>lang</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Specifies the Language to gather the information from. Possible values are:")
					.'<br /><strong>ActionScript / Ada / Apache Log File=Apache / ASM (NASM based)= Asm / ASP / Bash / C '
					.'/ C for Macs=C_Mac / AutoCAD DCL=CadDcl / AutoCAD LISP=CadLisp / C++=Cpp / C#=CSharp / CSS / D '
					.'/ Delphi / Diff Output=Diff / HTML (4.0.1)=Html4Strict / Java / JavaScript / Lisp / Lua / MatLab '
					.'/ MpAsm / NullSoft Installer=Nsis / Objective C=ObjC / OpenOffice.org Basic=OoBas / Oracle8 '
					.'/ Pascal / Perl  / Php / Php-Brief / Python / QuickBasic=QBasic / Smarty / SQL / VisualBasic=Vb '
					.'/ VB.NET=VbNet / VHDL / VisualFoxPro / XML</strong>. '
					.'<br />'.tra("The Default = ").'<strong> PHP </strong>'
				.'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>info</td>'
				.'<td>'.tra("key-word").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Determines the information to be displayed. The Key-words are:")
					.'<br /><strong>Comment / Comment1 / Comment2</strong>'
					.'<br /><strong>'.tra("Note:").'</strong> '.tra( "With the").' <strong>Comment</strong> '
					.tra("Key-words - the difference is").' <strong>Comment</strong> '.tra("returns the Line Comment Character / while")
						.' <strong>Comment1</strong> '.tra("returns the starting Block Comment Character and")
						.' <strong>Comment2</strong> '.tra("returns the ending Block Comment Character.")
					.'<br />'.tra( "There is <strong>No</strong> Default for this parameter.")
				.'</td>'
			.'</tr>'
		.'</table>'
		.tra("Example: ")."{GESHIDATA doall='True' }"
		.'<br />'.tra("Example: ")."{GESHIDATA lang='XML' info='Comment' }";
	return $help;
}
/****************
* Load Function *
 ****************/
function data_GeshiData($data, $params) {
	if ( !file_exists( UTIL_PKG_PATH . 'geshi/geshi.php' )) { // Exit - Unable to display the code.
		return pluginError('GeshiData', tra('The Plugin GeshiData Can Not Function Without The GeSHi Code!'));
	}
	include_once( UTIL_PKG_PATH.'geshi/geshi.php' );
	extract ($params);
// NOTE: The the order of the next 2 arrays is critical - if you change one of them then change the other in the same way
	$langKeyWordArray =	array('actionscript',	'ada',	'apache',			'asm',				'asp',	'bash',	'c',	'c_mac',		'caddcl',		'cadlisp',		'cpp',	'csharp',	'css',	'd',	'delphi',	'diff',			'html4strict',	'java',	'javascript',	'lisp',	'lua',	'matlab',	'mpasm',	'nsis',					'objc',			'oobas',				'oracle8',	'pascal',	'perl',	'php',	'php-brief',	'python',	'qbasic',		'smarty',	'sql',	'vb',			'vbnet',	'vhdl',	'visualfoxpro',	'xml'); // Used by Key-word test
	$langNames =		array('ActionScript',	'Ada',	'Apache Log File',	'ASM (NASM based)',	'ASP',	'Bash',	'C',	'C for Macs',	'AutoCAD DCL',	'AutoCAD LISP',	'C++',	'C#',		'CSS',	'D',	'Delphi',	'Diff Output',	'HTML (4.0.1)',	'Java',	'JavaScript',	'Lisp',	'Lua',	'MatLab',	'MpAsm',	'NullSoft Installer',	'Objective C',	'OpenOffice.org Basic',	'Oracle8',	'Pascal',	'Perl',	'Php',	'Php_Brief',	'Python',	'QuickBasic',	'Smarty',	'SQL',	'VisualBasic',	'VB.NET',	'VHDL',	'VisualFoxPro',	'XML'); // Used when displaying everything
	$infoKeyWordArray = array('comment', 'comment1', 'comment2'); // Used by a Key-word test

	$lang = (isset($lang) && in_array(trim(strtolower($lang)),$langKeyWordArray)) ? trim(strtolower($lang)) : 'php';
	$info = (isset($info) && in_array(trim(strtolower($info)),$infoKeyWordArray)) ? trim(strtolower($info)) : '';

	if (-!isset($doall)) {
		if (in_array($info,$infoKeyWordArray)) {
			$obj = new GeSHi('Function code(){ }', $lang, UTIL_PKG_PATH.'geshi/geshi' );
			switch ($info) {
				case 'comment' : {
					$datArr = $obj->language_data['COMMENT_SINGLE'];
					$ret = (count($datArr)>0) ? $datArr[key($datArr)] : '';
					return $ret;
				}
				case 'comment1' : {
					$datArr = $obj->language_data['COMMENT_MULTI'];
					$ret = (count($datArr)>0) ? key($datArr) : '';
					return $ret;
				}
				case 'comment2' : {
					$datArr = $obj->language_data['COMMENT_MULTI'];
					$ret = (count($datArr)>0) ? $datArr[key($datArr)] : '';
					return $ret;
		}	}	}
		return pluginError('GeshiData', tra('The value placed in the parameter').' <strong>$info='.$info.'</strong> '.tra("was not a valid Key-word."));
	} else {
		$ret = '<div class="box">';
		$ret .=		'<div class="error" style="text-align:center;">'; // The Header
		$ret .=			'<big><big><strong>'.tra('Language Properties').'</strong></big></big>';
		$ret .=		'</div><hr />';
		$ret .=		'<div class="boxcontent">'; // The Body
		$cnt = 0;
		foreach ($langKeyWordArray as $i) {
			$obj = new GeSHi('Function code(){ }', $i, UTIL_PKG_PATH.'geshi/geshi' );
			$ret .=		'<table class="data help" style="width: 100%;" border="2" cellpadding="4">';
			$ret .=			'<tr>';
			$ret .=				'<th colspan="3" style="text-align: center;"><strong><large>'.$obj->language_data['LANG_NAME'].'</large></strong></th>';
			$ret .=			'</tr>';
			$ret .=			'<tr>';
			$ret .=				'<th style="text-align: center;"><strong><large>'.tra("Line Comment").'</large></strong></th>';
			$ret .=				'<th style="text-align: center;"><strong><large>'.tra("Block Comment Start").'</large></strong></th>';
			$ret .=				'<th style="text-align: center;"><strong><large>'.tra("Block Comment End").'</large></strong></th>';
			$ret .=			'</tr>';
			$ret .= (!($cnt%2)) ?
							'<tr class="odd">' : '<tr class="even">';
			$datArr = $obj->language_data['COMMENT_SINGLE'];
			$ret .= 			'<td style="text-align: center;">';
			$ret .=				(count($datArr)>0) ? $datArr[key($datArr)] : 'None';
			$ret .=				'</td>';
			$datArr = $obj->language_data['COMMENT_MULTI'];
			$ret .=				'<td style="text-align: center;">';
			if ($i != 'html4strict')
				$ret .=			(count($datArr)>0) ? key($datArr) : 'None';
			else $ret .=		'&lt;!--';
			$ret .=				'</td>';
			$ret .=				'<td style="text-align: center;">';
			if ($i != 'html4strict')
				$ret .=					(count($datArr)>0) ? $datArr[key($datArr)] : 'None';
			else $ret .=		'--&gt;';
			$ret .=				'</td>';
			$ret .=			'</tr>';
			$ret .=		'</table>';
			$cnt++;
		} // foreach
		$ret .=		'</div>';
		$ret .=	'</div>';
	}
	return $ret;
}
?>
