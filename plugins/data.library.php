<?php
// $Id: data.library.php,v 1.1 2006/03/03 07:07:15 starrrider Exp $
/**
 * assigned_modules
 *
 * @author   StarRider <starrrider@sourceforge.net>
 * @version  $Revision: 1.1 $
 * @package  liberty
 * @subpackage plugins_data
 * @copyright Copyright (c) 2004, bitweaver.org
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @license Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 */
/******************
 * Initialization *
 ******************/
global $gLibertySystem;
define( 'PLUGIN_GUID_DATALIBRARY','datalibrary' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'LIB',
						'auto_activate' => TRUE,
						'requires_pair' => TRUE,
						'load_function' => 'data_library',
						'title' => 'Library (LIB)',
						'help_page' => 'DataPluginLibrary',
						'description' => tra("This plugin uses Key-words to execute the Plugin Library Functions."),
						'help_function' => 'data_help_library',
						'syntax' => "{LIB func= <strong>Plus</strong> the Parameters defined by each Function}",
						'variable_syntax' => TRUE,
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATALIBRARY, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATALIBRARY );

/*****************
 * Help Function *
 *****************/
function data_help_library() {
global $gLibertySystem;

// Functions inside Functions don't normally make sense - But they do in this case!
	function addSelector ($LibFunctionArray) {
		$cnt = 0;
		$ret =		'<div class="box">'
						.'<select size="10" style="width: 100%;" onchange="javascript:flipMulti(this.options[this.selectedIndex].value,'."'2','2'".')"';
		foreach($LibFunctionArray as $v) {
			$cnt++;
			$ret .=			'<option value="'.$v['funcWindow'].'" ';
			$ret .= ($cnt==1) ? 'selected="selected">' : '>';
			$ret .=				$v['FuncName']
							.'</option>';
		}
		$ret .=	'		</select>'
					.'</div>';
		return $ret;
	}

	function addFuncData ($LibFunctionArray) {
	global $inEditor;
		$ret = '';
		foreach($LibFunctionArray as $v) {
			$tbl = 	'<table class="data help">'
						.'<tr><th colspan="2" style="text-align: center;"><strong><big>'.tra("Function").' '.$v['FuncName'].'</big></strong></th></tr>'
						.'<tr class="odd"><td colspan="2">description => '.$v['FuncDesc'].'</td></tr>'
						.'<tr class="even"><td colspan="2">syntax => '.$v['syntax'].'</td></tr>'
						.'<tr class="odd"><td colspan="2">help_page => '.$v['helppage'].'</td></tr>';
			if (isset($inEditor)) $tbl .=
						'<tr class="even">'
							.'<td style="text-align: center;" title="'.tra("Click to Visit the Help Page on bitweaver.org in a new window").'">'
								.'<input type="button" value="Visit the Help Page" onClick="javascript:popUpWin'."('http://bitweaver/wiki/index.php?page=".$v['helppage']."','standard',800,".'800)"></input>'
							.'</td>'
							.'<td style="text-align: center;" title="'.tra("Click to Insert the Syntax into the page").'">'
								.'<input type="button" value="Insert the Syntax" onClick="javascript:insertAt(\'editwiki\',\''.$v['syntax'].'\')"></input>'
							.'</td>'
						.'</tr>';
			$tbl .= '</table>';
			$ret .= libToggleBox($v['funcWindow'],$tbl,TRUE,'box');
		}
		return $ret;
	}

	function addParmList ($LibFunctionArray) {
		$ret = '';
		foreach($LibFunctionArray as $v) {
			$parms = '';
			$cnt = 0;
			foreach($v['Params'] as $p) {
				if ($p['Name']!='data') $cnt++;
				$parms .= libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
							$p['Name'], // Name
							$p['Type'], // Type
							$p['Descr'], // Description
							$p['Required'], // Required
							$p['Default'], // Default
							'', // Notes
							$p['KeyWords'] // KeyWords
			  	);
			}
			if ($cnt) {
				$tbl = '<div style="text-align: center;"><strong><big>'.tra("Function Specific Parameters").'</big></strong></div>';
				$tbl .=	libHelpTable($parms); // Creates the Table
			} else $tbl =	'<div style="text-align: center;"><strong><big>'.tra("No Function Specific Parameters").'</big></strong></div>';
			$ret .= libToggleBox($v['paramWindow'],$tbl,TRUE,'box');
		}
		return $ret;
	}

	function addNoScript($LibFunctionArray,$firstFunction) {
		global $inEditor;
		$ret =		'<script type="text/javascript">'
						."flipMulti('".$firstFunction."','2','2')"
					.'</script>'
					.'<noscript>';
		foreach($LibFunctionArray as $v) {
			$fData =	'<div class="box">'
							.'<div style="text-align: center;"><strong><big>'.tra('Function').' '.$v['FuncName'].'<hr />'
								.'<table class="data help">'
									.'<tr class="odd"><td>'.tra('description').' => '.$v['FuncDesc'].'</td></tr>'
									.'<tr class="even"><td>'.tra('syntax').' => '.$v['syntax'].'</td></tr>'
									.'<tr class="odd"><td>'.tra('help_page').' => '.$v['helppage'].'</td></tr>';
			if (isset($inEditor)) $fData .=
									'<tr class="even">'
										.'<td style="text-align: center;" title="'.tra("Click to Visit the Help Page on bitweaver.org in a new window").'">'
											.'<input type="button" value="Visit the Help Page" onClick="javascript:popUpWin'."('http://bitweaver/wiki/index.php?page=".$v['helppage']."','standard',800,".'800)"></input>'
										.'</td>'
										.'<td style="text-align: center;" title="'.tra("Click to Insert the Syntax into the page").'">'
											.'<input type="button" value="Insert the Syntax" onClick="javascript:insertAt(\'editwiki\',\''.$v['syntax'].'\')"></input>'
										.'</td>'
									.'</tr>';
			$fData .= 			'</table>'
							.'</div>';
			$fData .= 		'<div style="text-align: center;"><strong><big>'.tra("Function Specific Parameters").'</big></strong></div>';
// Need to update this
			$parms = '';
			foreach($v['Params'] as $p)
				$parms .= libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
							$p['Name'], // Name
							$p['Type'], // Type
							$p['Descr'], // Description
							$p['Required'], // Required
							$p['Default'], // Default
							'', // Notes
							$p['KeyWords'] // KeyWords
			  	);
			$fData .=	libHelpTable($parms); // Creates the Table
		}
		$fData .= 		'</div>';
		$ret .=	libHeaderBox($header='Function Data',$fData);
		$ret .=		'</noscript>';
		return $ret;
	}

	function addData($LibFunctionArray) {
		$ret =	'<table class="data help">'
					.'<tr>'
						.'<td style="width: 35%; text-align: center;"><strong><big>'.tra("Function Selector").'</big></strong></tb>'
						.'<td style="text-align: center;"><strong><big>'.tra("Function Data").'</big></strong></tb>'
					.'</tr>'
					.'<tr>'
						.'<td style="vertical-align: top;">'.addSelector($LibFunctionArray).'</td>'
						.'<td style="vertical-align: top;">'.addFuncData($LibFunctionArray).'</td>'
					.'</tr>'
					.'<tr>'
						.'<td colspan="2">'.addParmList($LibFunctionArray).'</td>'
					.'</tr>'
				.'</table>';
		return $ret;
	}

	$LibFunctionArray = $gLibertySystem->getLibFunctions();
	if (!empty($LibFunctionArray)) {
		libNatSort2D($LibFunctionArray,'FuncName'); // Sort the array
		foreach($LibFunctionArray as $k => $v) {
			$tmp = '';
			$mt = (microtime() * 1000000);
			if (!isset($firstFunction)) $firstFunction = $mt;
			$LibFunctionArray[$k]['funcWindow'] = $mt;
			$LibFunctionArray[$k]['paramWindow'] = $mt+1;
			foreach($v['Params'] as $p)
				if ($p['Name'] != 'data') $tmp .= $p['Name'].'= ';
			$LibFunctionArray[$k]['syntax'] = '{LIB func='."'".$v['FuncName']."' ".$tmp.'}';
			$LibFunctionArray[$k]['helppage'] = 'DataPluginLibrary#'.$v['FuncName'];
	}	}

// print_r($LibFunctionArray);
	$jsWindow = (microtime() * 1000000);

// Create the Help Data
	$help = libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
				'func', // Name
				'string', // Type
				tra('Specifies the Library Function to execute.'), // Description
				TRUE // Required
			  );
	$help = libHelpTable($help); // Creates the Table
	$help = libPluginHelp(
		$help,
		tra("Each Function defines the Parameters it uses. This includes the Parameter's Name, the data Type expected, if the data is required or not, and what the Parameter does. Some Functions use data placed between the Code Blocks. All of this is explained below."),
		"{LIBEXAMPLE x1='Parameter 1' x2='Parameter 2' x3='Parameter 3' x4='Parameter 4' x5='Parameter 5' }" // An Example
	);
	$help .= libToggleBox($jsWindow,addData($LibFunctionArray),FALSE,'box');
	$help .= addNoScript($LibFunctionArray,$firstFunction);
	return $help;
}

/****************
* Load Function *
 ****************/
function data_library($data, $params) {
global $gLibertySystem;
	extract ($params);
	if (!isset($func) || empty($func))
		return libPluginError('Library', tra('This Plugin can not function without a value in the').' "<strong>func</strong>" '.tra('parameter!'));

	if (!$found = $gLibertySystem->isLibFunction(trim(strtolower($func))))
		return libPluginError('Library', tra('The function specified in the parameter')."<strong> func=&#039;$func&#039; </strong>".tra('does not exist!'));
	$func = $found['FuncName'];
	$libCommand = '$ret = '.$func.'(';
	$addComma = FALSE;
	$paramArr = (isset($found['Params'])) ? $found['Params'] : FALSE;
	if (!$paramArr)
		return libPluginError('Library', tra('The necessary data does not exist in the').'<strong> '.tra('Library Functions').'!</strong>');
	ksort($paramArr); // Making sure somebody didn't add a 0,2,1,5
	foreach ($paramArr as $pVal) {
		$Ok = FALSE;
		$pName = $pVal['Name'];
		$pType = strtolower(trim($pVal['Type']));
		$pReq = $pVal['Required'];
		$errMsg1 = tra('The Library Function').'<strong> func=&#039;'.$func.'&#039; </strong>';
		// String Conversions - Either convert the variable or Exit with an Error Message
		if (isset($$pName) || !empty($$pName)) { // The Parameter Exists and has a value
			$errMsg2 = ' '.tra('Value for the Parameter').' <strong>'.$pName.'=&#039;&#039;</strong> '.tra('but was given').' <strong>'.$pName.'=&#039;'.$$pName.'&#039;</strong>';
			if ($pType == 'key-word')
				$p = libConvertUserInput($$pName, $pType, $pVal['KeyWords']);
			else $p = libConvertUserInput($$pName, $pType);
			if ($pType == 'string') $$pName = str_replace("'","~039~",$$pName);
			if (!$p[0] && $pReq) {
				if ($pType == 'real' || $pType == 'float') $errMsg = $errMsg1.tra('Required a Real').$errMsg2;
				elseif ($pType == 'int' || $pType == 'integer') $errMsg = $errMsg1.tra('Required an Integer').$errMsg2;
				elseif ($pType == 'boolean') $errMsg = $errMsg1.tra('Required a Boolean').$errMsg2;
				elseif ($pType == 'key-word') {
					$errMsg = $errMsg1.tra('Required a Key-Word').$errMsg2.'<br />'.tra('The Key-Words are:').'<strong> ';
					$kwArr = $pVal['KeyWords'];
					$cnt = 0;
					foreach ($kwArr as $v) {
						$errMsg .= ($cnt==0) ? $v : ' / '.$v;
						$cnt++;
					}
					$errMsg .= '</strong>';
				} else $errMsg = $errMsg1.tra('produced an Error').' - '.$p[1];
				return libPluginError('Library', $errMsg);
			}
		} // Finish it!
		if ($addComma)	$libCommand .= (isset($$pName)) ? ", '".$$pName."'" : ", ''";
		else $libCommand .= (isset($$pName)) ? "'".$$pName."'" : "''";
		$addComma = TRUE;
	}
	$libCommand .= ');'; // $libCommand should look like this -
	if (!eval($libCommand)) return $ret;
	else return libPluginError('Library', tra('There was an error in the Evaluated String. It was:').'<br /> '.$libCommand);
}
?>
