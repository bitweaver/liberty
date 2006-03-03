<?php
// $Id: a.library.php,v 1.1 2006/03/03 07:07:15 starrrider Exp $
/**
 * assigned_modules
 *
 * @author   StarRider starrrider@sourceforge.net
 *
 * @version  $Revision: 1.1 $
 * @package  liberty
 * @subpackage plugins_data
 * @copyright Copyright (c) 2004, bitweaver.org
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @license Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 **********
 * Note 1 *
 **********
 * This file does not need to be included / required / or otherwise specified. It is loaded when the Liberty Plugins are
 * loaded. The primary reason for using this file is to reduce duplicated code while increasing functionality. Unlike a Plugin
 * that can be turned off - this file can not be made unaccessable. This makes it a convienient location for adding functions
 * that are common to most Plugins. Parameter Testing and Boxes are a prime example.
 **********
 * Note 2 *
 **********
 * When adding new functions that would be benificial to a User - then add the function's specifics with libRegister.
 * An explanation of the Array passed to it is provided with the function libBox. There is also a Cut and Paste version
 * located at the end of this file. When finished - your function automatically becomes available to Users through the
 * Plugin Library {LIB} and is visible in {LIB}'s Help. Be sure to add the functionallity to the {LIB} Help Page (DataPluginLibrary)
 * in an Example. There are instructions at the top of that page in a {COMMENT} that allow the Help Link in {LIB Help}
 * to find your Example.
 **********
 * Note 3 *
 **********
 * The function libArray2String (which is used by libRegister) has problems with single quotes. I believe I have eliminated
 * the problem by replacing all single quotes to &#039; - but to be on the safe side - Don't use single quotes in your text.
 */

global $gLibertySystem;
/*************************************** Boxes **************************************/
// function:	libBox
// desc:		Returns a Div Box with the Message inside it. Border controls the Class used.
// typ. useage:	$ret = libBox('Message',True_Use_Class_Box/False_Use_Class_BoxContents,If_True_Reduce_Top&Bottom_Padding);
// added by: 	StarRider
// date:		12/15/05
// arguments:	$msg is the text string to be displayed
//				$border (boolean) determins if the div class is True=box / False=boxcontent
//				$odd (boolean) allows the spacing of libBoxes to be consistent
function libBox($msg='',$border=TRUE,$odd=FALSE) {
	if (!empty($msg)) {
		if ($odd) $ret = ($border) ? '<div class="box" style="padding-top:1px;padding-bottom:1px;">' : '<div class="boxcontent" style="padding-top:0;padding-bottom:0;">';
		else $ret = ($border) ? '<div class="box">' : '<div class="boxcontent">';
		$ret .=		$msg;
		$ret .=	'</div>';
	} else $ret = ' ';
	return $ret;
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> 'libBox', // {LIB} The name of the function - as it is defined
	'FuncDesc'	=> tra('Returns a Div Box with the Message inside it. The Border Color/Shape/Visibility are controled by the CSS Class used - but the Class "box" normally has a visible Border while the Class "boxcontent" does not.'), // A brief description of what the function does.
	'Params'	=> array( // {LIB} The parameters the function will accept.
		'0' => array( // {LIB} This Key must match the Argument Order of the function
			'Name' => 'data', // {LIB} Provides a Parameter Name for User Input - Variable Naming Rules Apply
    			// Note: When 'data' is specified - everything between the code blocks is used
			'Type' => 'string', // {LIB} Used for String Conversions
				// Note: Valid Type are: string/int/integer/real/float/boolean/key-word
			'Required' => TRUE, // {LIB} Specify True if the function will not operate without it - True=='manditory'/False='optional'
			'Default' => '', // {LIB} Provides a value when not Specified by the User
			'Descr' => '', // A brief description of what the parameter does.
			'KeyWords' => array() // Used by {LIB} and the Help Routine
//			'KeyWords' => array('KeyWord1','KeyWord2','KeyWord3','Ect.') // Used by {LIB} and the Help Routine
				// {LIB} provides for 'Type' = 'key-word'. *** This Has Not Been Tested ***
// NOTE:	Even though this function has more parameters - we don't have to include them unless we want to.
//			In this case - providing the User with a function that creates a box without a border only makes me ask Why?
//			The differences made with with Odd are not visible in the Wiki - so why include them.
)	)	)	);


// function:	libToggleBox
// desc:		Returns an Div Expandable Box - Any Class can be specified / default is NONE
// typ. useage:	$ret = libToggleBox('Message');
// added by: 	StarRider
// date:		12/15/05
// arguments:	$id is the Windows ID to make the box
//				$msg is the text string to be displayed
//				$hidden (boolean) determins if the Box is hidden by default True=Hidden / False=Expanded
//				$clas a valid CSS Class - default = none
// Note: JavaScript is used to Contract the box so it will be displayed if JavaScript is not active
function libToggleBox($id,$msg,$hidden=TRUE,$clas='') {
	if (isset($id) && isset($msg)) {
		$ret = '<div id='.$id.' style="display:block;"'.((empty($clas)) ? '>' : ' class="'.$clas.'">');
		$ret .=		$msg;
		$ret .=	'</div>';
		if ($hidden) $ret .= '<script type="text/javascript">'."hide('$id')</script>";
	} else $ret = ' ';
	return $ret;
} // Function Registration Not Recomended


// function:	libHeaderBox
// desc:		Returns a Formatted Box with a Header & Message
// typ. useage:	$ret = libHeaderBox('Header', 'Message');
// added by: 	StarRider
// date:		12/15/05
// arguments:	$header is a string centered at the top of the box
//				$msg is the text string to be displayed
// Note:		Using <big><strong> (about h3) because MAKETOC insists that every <h1>...<h6> is a TOC entry
function libHeaderBox($header='',$msg='') {
	if (!empty($header) && !empty($msg)) {
		$ret  =	'<div class="box">';
		$ret .=		'<div class="boxtitle" style="text-align:center;">'; // The Header
		$ret .=			'<big><strong>'.$header.'</strong></big>';
		$ret .=		'</div>';
		$ret .=		libBox($msg,$border=FALSE);
		$ret .=	'</div>';
	} else $ret = ' ';
	return $ret;
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> 'libHeaderBox',
	'FuncDesc'	=> tra('Returns a Formatted Box with Header & Message Sections.'),
	'Params'	=> array(
		'0' => array(
			'Name' => 'header', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('A string centered at the Top of the Box.'),
			'KeyWords' => array()
		),
		'1' => array(
			'Name' => 'data', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => '',
			'KeyWords' => array()
)	)	)	);

// function:	libContractedHeaderBox
// desc:		Identical to libHeaderBox except the inner Message Box can be Expanded/Contracted
// typ. useage:	$ret = libContractedHeaderBox('Header', 'Message', TRUE);
// added by: 	StarRider
// date:		12/15/05
// arguments:	$header is a string centered at the top of the box
//				$msg is the text string to be displayed
//				$hidden is the default state of that window
function libContractedHeaderBox($header='',$msg='',$hidden=TRUE) {
	$winId = microtime() * 1000000;
	if (!empty($header) && !empty($msg)) {
		$tbl =	'<table><tr>'
					.'<td style="width: 5%; text-align: center;">'.libAddPlusMinusIcon($winId,$hidden).'</td>'
					.'<td style="text-align: center;">'.$header.'</td>'
					.'<td style="width: 5%;"></td>'
				.'</tr></table>';
		$ret =	libHeaderBox($tbl,libToggleBox($winId,$msg));
	} else $ret = ' ';
	return $ret;
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> 'libContractedHeaderBox',
	'FuncDesc'	=> tra('Returns a Formatted Box with a Header & Message Sections. The Message Section can be Expanded or Contracted'),
	'Params'	=> array(
		'0' => array(
			'Name' => 'header', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('A string centered at the Top of the Box.'),
			'KeyWords' => array()
		),
		'1' => array(
			'Name' => 'data', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => '',
			'KeyWords' => array()
)	)	)	);


// function:	libErrorBox
// desc:		Identical to libHeaderBox except the header uses the CSS Class Error which is normally Red
// typ. useage:	if (Whatever-Error) return libErrorBox(Message_Header, Error_Message);
// added by: 	StarRider
// date:		12/15/05
// arguments:	$header is a string centered at the top of the box
//				$msg is the text string to be displayed
// Note:		Using <big><strong> (about h2) because MAKETOC insists that every <h1>...<h6> is a TOC entry
function libErrorBox($header='',$msg='') {
	if (!empty($header) && !empty($msg)) {
		$ret  =	'~np~<div class="box">';
		$ret .=		'<div class="error" style="text-align:center;">'; // The Header
		$ret .=			'<big><strong>'.$header.'"</strong></big>';
		$ret .=		'</div><hr />';
		$ret .=		'<div class="boxcontent">'; // The Body
		$ret .=			$msg;
		$ret .=		'</div>';
		$ret .=	'</div>~/np~';
	} else $ret = ' ';
	return $ret;
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> 'libErrorBox',
	'FuncDesc'	=> tra('This function is nearly identical to libHeaderBox except the header uses the div error class which is normally red.'),
	'Params'	=> array(
		'0' => array(
			'Name' => 'header', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('A string centered at the Top of the Box.'),
			'KeyWords' => array()
		),
		'1' => array(
			'Name' => 'data', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => '',
			'KeyWords' => array()
)	)	)	);


// function:	libPluginError
// desc:		A modified version of libErrorBox dedicated to Plugins
// typ. useage:	if (Whatever-Error) return libPluginError(Plugins_Name, Error_Message);
// added by: 	StarRider
// date:		12/15/05
// arguments:	$plugin is the name of the Plugin making the call.
//				$msg is the text string to be displayed
function libPluginError($plugin='',$msg='') {
	if (!empty($plugin) && !empty($msg)) {
		$ret = libErrorBox(tra('Error in the Plugin "').$plugin,$msg);
	} else $ret = ' ';
	return $ret;
} // Function Registration Not Needed


// function:	libTabBox
// desc:		Produces an Tabbed Box
// typ. useage:	$ret = libTabBox('My Title', 'My Message');
// added by: 	StarRider
// date:		12/15/05
// arguments:	$title is the string to be placed in the Tab
//				$body will be added between the divs without any formatting
function libTabBox($title='',$body='') {
	if (!empty($title) && !empty($body)) {
		$ret =	'<div class="tabpane">';
		$ret .=		'<div class="tabpage">';
		$ret .=			'<div class="tab"><strong><big>'.$title.'</big></strong></div>';
		$ret .=			$body;
		$ret .=		'</div>';
		$ret .=	'</div>';
	} else $ret = ' ';
	return $ret;
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> 'libTabBox',
	'FuncDesc'	=> tra('Returns a Tab Box with the').' title '.tra('placed in the Tab and the').' message '.tra('in the Box'),
	'Params'	=> array(
		'0' => array(
			'Name' => 'title', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('A string placed in the Boxes Tab.'),
			'KeyWords' => array()
		),
		'1' => array(
			'Name' => 'data', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => '',
			'KeyWords' => array()
)	)	)	);


// function:	libContractedTabBox
// desc:		Identical to libTabBox except the Box can be Expanded/Contracted
// typ. useage:	$ret = libTabBox(
// added by: 	StarRider
// date:		12/15/05
// arguments:	$title is the string to be placed in the Tab
//				$body will be added between the divs without any formatting
function libContractedTabBox($title='',$body='',$hidden=TRUE) {
	$winId = microtime() * 1000000;
	if (!empty($title) && !empty($body)) {
		$header =	'<div style="text-align: center;">'.$title.'<br />'.libAddPlusMinusIcon($winId,$hidden).'</div>';
		$ret = libTabBox($header,libToggleBox($winId,$body));
	} else $ret = ' ';
	return $ret;
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> 'libContractedTabBox',
	'FuncDesc'	=> tra('Returns a Tab Box with the').' title '.tra('placed in the Tab and the').' message '.tra('in a Box that can be Expanded or Contracted.'),
	'Params'	=> array(
		'0' => array(
			'Name' => 'title', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('A string placed in the Boxes Tab.'),
			'KeyWords' => array()
		),
		'1' => array(
			'Name' => 'data', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => '',
			'KeyWords' => array()
)	)	)	);


// function:	libAddAnchor
// desc:		Returns an Anchor which can be invisible or include Text. If Level is set then the Text uses <h$level>
// typ. useage:	$ret = libAddAnchor('PageLocation', 'Descriptive Text', 1)
// added by: 	StarRider
// date:		12/15/05
// arguments:	$name - is the anchor name. Uses htmlspecialchars.
//				$txt - is a string that provides a visible clue to where the Anchor is located
//				$level - if defined it will place text in <h> blocks - Which are visible to {MAKETOC}
function libAddAnchor($name='',$txt='',$level=FALSE) {
	if (!empty($name)) {
		$level = ($level && ($level > 0 && $level < 7)) ? $level : FALSE; // level can be 1-6 or FALSE
		$txt = ($level && empty($txt)) ? $name : $txt; // Only needed when level is set but $txt has no value
		$name = htmlspecialchars($name, ENT_QUOTES);
		$ret = '<a name="'.$name.'">';
		$ret .= ($level) ? '<h'.$level.'>'.$txt.'</h'.$level.'>' : $txt;
		$ret .= '</a>';
	} else $ret = ' ';
	return $ret;
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> 'libAddAnchor',
	'FuncDesc'	=> tra('Places an Anchor on the page. All spaces and other non-valid characters are modified. Examples are:')
		.'<ul><li>'.tra('An Ampersand (&amp;) becomes')." <strong>&#038;amp;</strong></li>"
		.'<li>'.tra('A Double Quote (&quot;) becomes')." <strong>&#038;quot;</strong></li>"
		.'<li>'.tra('A Single Quote (&#039;) becomes')." <strong>&#038;#039;</strong></li>"
		.'<li>'.tra('A Less Than character (&lt;) becomes')." <strong>&#038;lt;</strong></li>"
		.'<li>'.tra('A Greater Than character (&gt;) becomes')." <strong>&#038;gt;</strong></li></ul>"
		.'<br />'.tra('None of this is a concern if the links are created with')." <strong>libAddAnchorLink</strong>",
	'Params'	=> array(
		'0' => array(
			'Name' => 'anchor', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('The name given to the Anchor'),
			'KeyWords' => array()
		),
		'1' => array(
			'Name' => 'txt', 'Type' => 'string', 'Required' => FALSE, 'Default' => '',
			'Descr' => tra('A string displayed in the Anchor.'),
			'KeyWords' => array()
		),
		'2' => array(
			'Name' => 'level', 'Type' => 'int', 'Required' => FALSE, 'Default' => '0='.tra('None'),
			'Descr' => tra('Allows the parameter').'<strong> txt </strong>'.tra('to be visible to {MAKETOC} so it will be included in the Table of Contents. Should be from').'<strong>1 to 6</strong>',
			'KeyWords' => array()
)	)	)	);


// function:	libAddAnchorLink
// desc:		Returns a Link to the specified Anchor. Uses htmlspecialchars.
// typ. useage:	$ret = libAddAnchorLink('PageLocation','Jump to Page Location')
// added by: 	StarRider
// date:		1/15/06
// arguments:	$name is the Anchor name. htmlspecialchars ensures that it is a valid name.
//				$txt - a string used as the link. If not supplied that Anchor's Name is appended to 'Jump to '
function libAddAnchorLink($name='',$txt='') {
	if (!empty($name)) {
		$n = htmlspecialchars($name, ENT_QUOTES);
		if (empty($txt)) $txt = tra('Jump to').' '.$name;
		$ret = '<a href="'.'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'#'.$n.'">'.$txt.'</a>';
	} else $ret = ' ';
	return $ret;
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> 'libAddAnchorLink',
	'FuncDesc'	=> tra('Places a Link to an Anchor on the page. The name of the anchor is passed through htmlspecialchars to convert spaces and other non-valid characters. See')
		.'<strong> libAddAnchor </strong>'.tra('for examples.'),
	'Params'	=> array(
		'0' => array(
			'Name' => 'anchor', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('The name of the Anchor to link to.'),
			'KeyWords' => array()
		),
		'1' => array(
			'Name' => 'txt', 'Type' => 'string', 'Required' => FALSE, 'Default' => tra('If not provided - the default is: <strong>Jump to</strong> and the string provided in the parameter').' anchor.',
			'Descr' => tra('The text to be used as the link.'),
			'KeyWords' => array()
)	)	)	);


/*************************************** Icons **************************************/
// function:	libAddPlusMinusIcon
// desc:		Adds a +/- icon. The Icon will Expand/Contract the window $idWin
// typ. useage:	$ret = libPlusMinusIcon($Window_to_be_Controlled,Hidden_By_Default);
// added by: 	StarRider
// date:		12/15/05
// arguments:	$idWin is Window that the Icon will control
//				$hidden is the default state of that window
function libAddPlusMinusIcon($idWin,$hidden=FALSE) {
	$idIcon = (microtime() * 1000000);
	if (isset($idWin)) {
		$ret =	'<a onclick="flipIcon('."'".$idWin."','".$idIcon."')".'">';
		$ret .=		'<img id="'.$idIcon.'" src="'.LIBERTY_PKG_URL.'icons/';
		$ret .=			($hidden) ? 'collapsed.gif">' : 'expanded.gif">';
		$ret .=		'</img>';
		$ret .=	'</a>';
	} else $ret = ' ';
	return $ret;
} // Function Registration Not Recomended


// function:	libAddHelpIcon
// desc:		Adds a Help Icon where specified. The Icon will Expand/Contract the window $idWin
// typ. useage:	$ret = libAddHelpIcon($Window_to_Control);
// added by: 	StarRider
// date:		12/15/05
// arguments:	$data will be added between the divs
function libAddHelpIcon($idWin) {
	if (isset($idWin)) {
		$ret =	'<a onclick="flip(\''.$idWin.'\')">';
		$ret .=		'<img src="'.LIBERTY_PKG_URL.'icons/help.gif"></img>';
		$ret .=	'</a>';
	} else $ret = ' ';
	return $ret;
} // Function Registration Not Recomended


/************************************ Plugin Help ***********************************/
// I wanted to see if these would be any easier. What do you think?
static $pluginRowCnt = 0;
// function:	libPluginHelp
// desc:		Creates the Plugin's help ready to be inserted.
// typ. useage:	return libPlugin($helptable,$example,$notes);
// added by: 	StarRider
// date:		01/23/06
// arguments:	$tbl - Required - string containing the table (useually created with libHelpTable)
//				$notes - a string or array of strings - placed in a Div Box
//				$example - a string - like this: {CODE source='php' num='5'}Code to Display{CODE}
function libPluginHelp($tbl='',$notes=NULL,$example='') {
global $gLibertySystem;
	if (!empty($tbl)) {
		$ret = $tbl;
		if (!empty($example)) {
			$mt = (microtime() * 1000000);
			$ret .= 	'<div class="box"><strong>'.tra('Example:').' </strong>'.$example.'</div>';
		}
		if (is_array($notes) && !is_null($notes)) {
			$ret .= 	'<div class="box"><strong>'.tra('Notes:').'</strong><br /><ol>';
			foreach ($notes as $note) $ret .= '<li>'.$note.'</li>';
			$ret .= 	'</ol></div>';
		}
		elseif (is_string($notes) && !empty($notes))
			$ret .= 	'<div class="box"><strong>'.tra('Note:').' </strong>'.$notes.'</div>';
	}
	if (isset($ret)) return $ret;
}


// function:	libHelpTable
// desc:		Creates the table normally used in every Plugin's Help Function
// typ. useage:	$helptable = libHelpTable($parms);
// added by: 	StarRider
// date:		01/23/06
// arguments:	$parms contains everything to be placed in the table
function libHelpTable($parms='') {
global $pluginRowCnt;
	$pluginRowCnt = 0;
	if (!empty($parms)) {
		$ret = '<table class="data help">'
					.'<tr>'
						.'<th>'.tra( "Key" ).'</th>'
						.'<th>'.tra( "Type" ).'</th>'
						.'<th>'.tra( "Comments" ).'</th>'
					.'</tr>'
					.$parms
				.'</table>';
		return $ret;
}	}

// function:	libHelpParam
// desc:		Creates a standard tr/td row for each Parameter.
// typ. useage:	$parms = libHelpParam('ParameterName','type',TRUE,'My Description','The default value is = TRUE','A Note','The KeyWords');
// added by: 	StarRider
// date:		01/23/06
// arguments:	$name - Required - string containing the Parameters Name
//				$type - Required - string containing (normally) string/number/key-word
//				$descr - Required - string containing the description - No translation is done here
//				$req - boolean - True=manditory / False=optional / Default=False
//				$default - string containing the default settings for this parameter / in not specified = "There is No Default"
//				$notes - a string or array of strings - placed in a Div Box
//				$keywords - a string or array ('keyword'=>'Description'=translated) of Keywords used by the parameter
function libHelpParam($name='',$type='',$descr='',$req=FALSE,$default='',$notes=NULL,$keywords=NULL) {
global $pluginRowCnt;
	if (!empty($name) && !empty($type) && !empty($descr)) {
		$pluginRowCnt++;
		$ret = (!($pluginRowCnt%2)) ?	'<tr class="odd">' : '<tr class="even">';
		$ret .=								 '<td>'.$name.'</td>'
											.'<td>'.$type.'<br />('.(($req) ? tra('Manditory') : tra('Optional')).')</td>'
											.'<td>'.$descr;
		if (is_array($keywords) && !is_null($keywords) && !empty($keywords)) {
			$ret .= 							'<br />'.tra('The Key-Words Are:').'<br /><ul>';
			$cnt = 0;
			$max = count($keywords)-1;
			foreach ($keywords as $key => $word) {
				if ($max < 10) $ret .= 				'<li><strong>'.$key.'</strong> = '.tra($word).'</li>';
				else $ret .= 						'"<strong>'.$key.'</strong>" = '.tra($word).((++$cnt == $max) ? ' / ' : '');
			}
			$ret .= 							'</ul>';
		}
		elseif (is_string($keywords) && !empty($keywords))
			$ret .= 							'<br />'.tra('The Key-Words Are:').'<strong> '.$keywords.'</strong>';

		$ret .= (empty($default)) ?				'<br />'.tra('There is').' <strong>'.tra('No Default').'.</strong>' : '<br />'.$default;
		if (is_array($notes) && !is_null($notes)) {
			$ret .= 							'<br /><div class="box"><strong>'.tra('Notes:').'</strong><br /><ol>';
			foreach ($notes as $note) $ret .= 		'<li>'.$note.'</li>';
			$ret .= 							'</ol></div>';
		}
		elseif (is_string($notes) && !empty($notes))
			$ret .= 							'<br /><div class="box"><strong>'.tra('Note:').' </strong>'.$notes.'</div>';
		$ret .=								'</td>'
										.'</tr>';
	} else $ret = ' ';
	return $ret;
}


/*************************************** Misc Functions **************************************/
// function:	libConvertUserInput
// desc:		Does the basic testing needed for User Input
// typ. useage:	$parms = libConvertUserInput($Parameter,
// added by: 	StarRider
// date:		01/23/06
// arguments:	$val - The Variable to be Tested/Converted
//				$vType - string with basic types - Can Be: String/Real/Float/Int/Integer/Boolean/Key-Word
//					If Boolean - String Values can be: True/False/Yes/No/On/Off -* OR *- their Translation
//					If Key-Word - The Array $arr must be specified.
//				$arr - array containg Key-Words - example: array('on','off','yes','no');
function libConvertUserInput(&$val,$vType='',$arr=NULL) {
	if (is_null($val)) $val = ''; // Eliminate all Nulls
	if (empty($val)) return array(FALSE, 'The Variable Was Empty');
	if (empty($vType)) return array(FALSE, tra('There Was No Type Specified.'));
	$vType = strtolower(trim($vType));
	if ($vType == 'real' || $vType == 'float') { // Real Conversion
		$val = trim($val);
		if (is_numeric($val)) {
			settype($val, 'float');
			return array(TRUE, $vType, $val);
		} else return array(FALSE, tra('The Variable Was Not Numeric'));
	}
	if ($vType == 'int' || $vType == 'integer') { // Integer Conversion
		$val = trim($val);
		if (is_numeric($val)) {
			settype($val, 'integer');
			return array(TRUE, $vType, $val);
		} else return array(FALSE, tra('The Variable Was Not Numeric'));
	}
	if ($vType == 'boolean') { // Boolean Conversion
		$val = strtolower(trim($val));
		if ($val=='true' || $val=='yes' || $val=='on' || $val==tra('true') || $val==tra('yes') || $val==tra('on') ) {
			$val = TRUE;
			return array(TRUE, $vType, $val);
		} elseif ($val=='false' || $val=='no' || $val=='off' || $val==tra('false') || $val==tra('no') || $val==tra('off') ) {
			$val = FALSE;
			return array(TRUE, $vType, $val);
		} else return array(FALSE, tra('The Variable Did Not Contain a Boolean String').' (True/False/Yes/No/On/Off)');
	}
	if ($vType == 'key-word') { // Key-Word Conversion
		if (is_null($arr)) return array(FALSE, tra('A Key-Word Was Specified But No Key-Word Array Was Provided'));
		if (in_array(strtolower(trim($val)), $arr)) {
			$val = strtolower(trim($val));
			return array(TRUE, $vType, $val);
		} else return array(FALSE, tra('The Key-Word Was Not Found'));
	}
	if ($vType == 'string') return array(TRUE, $vType, $val);
	else return array(FALSE, tra('Unknown Data Type'));
} // Function Registration Not Recomended


// function:	libArray2String
// desc:		Returns a string for storage or transmittal that can be used to recreate the array with eval($Returned_String);
//				This function works with Multi-Dimensional and Associative Arrays
//				It is especially useful when passing the contents of an array in a Form
// typ. useage:	$form .= '<input type="hidden" name="MyArray" value="'.libArray2String($MyArray,'MyArray').'"></input>';
// where the	$tmp = (!empty($_REQUEST['MyArray'])) ? $_REQUEST['MyArray'] : FALSE;
// Form is		if ($tmp) eval($tmp); // Instant $MyArray
// processed:	else $MyArray = FALSE; // Could also generate an Error Message
// added by: 	StarRider
// date:		01/1/06
// arguments:	$ar is an array - can be Simple / Multi-Dimensional /or/ Associative
//				$name is the name of the array to be recreated
// Note 1:		I had a problem with Quotes in the original array causing problems when the string was processed with Eval
//				As it now stands - all single quotes are converted to "&#039;" - if this causes problems - after the array
//				is created run -------> str_replace('&#039;',"'",$YourArray)
// Note 2:		Equal (==) & Identical (===)
//				$originalArray is always == $evalArray BUT $originalArray is NOT always === $evalArray
//				Associative Arrays are always Identical but simple array created like this: array( 1, 2, 3) are Not
//				I "believe" that this is because the keys in the $evalArray are specifically assigned - I did see hints of
//				this while writing the function. The keys in a simple array seem to be generated when needed and unless they
//				are specified they are not stored. This: array( 1, 2=>2, 3) only stores key 2 and leaves key 1 undefined
function libArray2String($ar=null, $name='Array', $recursiveLevel=0) {
 	$ret = '';
 	$level = $recursiveLevel;
 	if ($level == 0) { // First Pass Only
		$arrName = str_replace(' ', '_', $name); // No spaces in a variable name!
		if (substr($arrName,0,1)!='$') $arrName = '$'.trim($arrName); // Kill Spaces and add $
	}
	if (is_array($ar)) {
		if ($level == 0) $ret = $arrName.' = array(';
	 	$cnt = 0;
		foreach ($ar as $key => $val) {
			$cnt++;
			$ret .= ($cnt==1) ? " '".$key."' => " : ", '".$key."' => "; // Add the Key
			if (is_array($ar[$key])) $ret .= 'array('.libArray2String($ar[$key],$name,$level+1).')'; // Recursive
			else $ret .= (is_Null($val)) ? "NULL" : "'".str_replace("'","~039~",$val)."'"; // Add the value
		}
	 	if ($level == 0) $ret .= ");"; // All Recursion is Finished - About to Exit
	}
	return $ret;
} // Function Registration Not Recomended


// function:	libNatSort2D
// desc:		Does a Natural Sort on a 2 Dimensional Array. If a Key is Specified - the sorting will
//				be based on that key.
// used by:		edit_help_inc.php and the plugin Library {LIB}
// example:		$arr[] = array('FirstName' => 'Tom',  'LastName' => 'Jones');
//				$arr[] = array('FirstName' => 'Mary', 'LastName' => 'Poppins');
// typ. useage:	libNatSort2D($arr, 'FirstName');
// origin:		From the Php Manual (natsort) - A User Contributed Function originally named natsort2d
// author:		mroach at mroach dot com (28-Jun-2005 05:08) & mbirth at webwriters dot de (09-Jan-2004 08:31)
// added by: 	StarRider
// date:		1/19/06
// arguments:	$arrIn - the array to be sorted.
//				$index - a key in the array to sort on.
function libNatSort2D(&$arrIn,$index=null) {
    $arrTemp = array();
    $arrOut = array();
    foreach ( $arrIn as $key=>$value ) {
        reset($value);
        $arrTemp[$key] = is_null($index) ? current($value) : $value[$index];
    }
    natsort($arrTemp);
    foreach ( $arrTemp as $key=>$value ) { $arrOut[$key] = $arrIn[$key]; }
    $arrIn = $arrOut;
} // Function Registration Not Recomended


// ***************** Functions used by 2 or more plugins ***************** \\
// function:	unhtmlentities
// desc:		????????????
// origin:		relocated from the Plugins Code & Snippet
// added by: 	StarRider
// date:		Pre-bitweaver
// arguments:	$string
function unhtmlentities($str) {
	$tTbl = get_html_translation_table(HTML_ENTITIES);
	$tTbl = array_flip($tTbl);
	return strtr($str, $tTbl);
} // Function Registration Not Recomended


// function:	decodeHTML
// desc:		?????????????
// origin:		relocated from the Plugin Code
// added by:
// date:		Pre-bitweaver
// arguments:	$string
function decodeHTML($str) {
    $str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
    $str = preg_replace("/&#([0-9]+);/me", "chr('\\1')", $str);
    return $str;
} // Function Registration Not Recomended

/* Save for Cut & Paste
} $gLibertySystem->registerLibFunction( array(
	'FuncName'	=> '',
	'FuncDesc'	=> tra('What?'),
	'Params'	=> array(
		'0' => array(
			'Name' => '', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('What?'),
			'KeyWords' => array()
		),
		'1' => array(
			'Name' => '', 'Type' => 'string', 'Required' => TRUE, 'Default' => '',
			'Descr' => tra('What?'),
			'KeyWords' => array()
)	)	)	);

*/
?>
