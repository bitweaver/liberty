<?php
/**
 * Data Code plugin settings form, included by liberty/admin/plugins.php?settings=datacode
 * (the plugin declares it as 'plugin_settings_inc'). The dispatcher has
 * checked p_admin, and the ticket when 'plugin_settings' is posted.
 *
 * @package liberty
 * @subpackage plugins
 */
global $gBitSystem, $gBitSmarty, $gLibertySystem;

$feedback = array();

$sources = array(
	'ASP'          => 'ASP',
	'ActionScript' => 'ActionScript',
	'Ada'          => 'Ada',
	'Apache'       => 'Apache Log File',
	'AppleScript'  => 'AppleScript',
	'Asm'          => 'ASM (NASM based)',
	'Bash'         => 'Bash',
	'Blitz Basic'  => 'Blitz Basic',
	'C'            => 'C',
	'CSS'          => 'CSS',
	'CSharp'       => 'C#',
	'C_Mac'        => 'C for Macs',
	'CadDcl'       => 'AutoCAD DCL',
	'CadLisp'      => 'AutoCAD LISP',
	'Cpp'          => 'C++',
	'D'            => 'D',
	'DIV'          => 'DIV',
	'DOS'          => 'DOS',
	'Delphi'       => 'Delphi',
	'Diff'         => 'Diff Output',
	'Eiffel'       => 'Eiffel',
	'FreeBasic'    => 'FreeBasic',
	'GML'          => 'GML',
	'Html4Strict'  => 'HTML (4.0.1)',
	'Inno'         => 'Inno',
	'Java'         => 'Java',
	'JavaScript'   => 'JavaScript',
	'Lisp'         => 'Lisp',
	'Lua'          => 'Lua',
	'MatLab'       => 'MatLab',
	'MpAsm'        => 'MpAsm',
	'MySQL'        => 'MySQL',
	'Niss'         => 'NullSoft Installer',
	'OCaml'        => 'OCaml',
	'ObjC'         => 'Objective C',
	'OoBas'        => 'OpenOffice.org Basic',
	'Oracle8'      => 'Oracle8',
	'Pascal'       => 'Pascal',
	'Perl'         => 'Perl',
	'Php'          => 'Php',
	'Php_Brief'    => 'Php_Brief',
	'Python'       => 'Python',
	'QBasic'       => 'QuickBasic',
	'Ruby'         => 'Ruby',
	'SQL'          => 'SQL',
	'Scheme'       => 'Scheme',
	'Smarty'       => 'Smarty',
	'VHDL'         => 'VHDL',
	'Vb'           => 'VisualBasic',
	'VbNet'        => 'VB.NET',
	'Visual Basic' => 'Visual Basic',
	'VisualFoxPro' => 'VisualFoxPro',
	'XML'          => 'XML',
	'ini'          => 'ini',
);
$gBitSmarty->assign( 'sources', $sources );

if( !empty( $_REQUEST['plugin_settings'] )) {
	simple_set_value( 'liberty_plugin_code_default_source', LIBERTY_PKG_NAME );
	$feedback['success'] = tra( 'The plugin was successfully updated' );
}

$gBitSmarty->assign( 'feedback', $feedback );
$pluginSettingsTemplate = 'bitpackage:liberty/plugins/data_code_admin.tpl';
$pluginSettingsTitle = tra( 'Data Code Plugin Settings' );
