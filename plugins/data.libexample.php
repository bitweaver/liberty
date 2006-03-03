<?php
// $id: data.libexample.php,v 1.0 2005/07/14 09:03:36 starrider Exp $
/**
 * assigned_modules
 *
 * @author   StarRider starrrider@sourceforge.net
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
define( 'PLUGIN_GUID_DATALIBEXAMPLE', 'datalibexample' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'LIBEXAMPLE',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_libExample',
						'title' => 'Library Example (LIBEXAMPLE)',
						'help_page' => 'DataPluginLibExample',
						'description' => tra("This Plugin does nothing useful. It functions as a template for the creation of new plugins using the Plugin Library."),
						'help_function' => 'data_help_libExample',
						'syntax' => "{LIBEXAMPLE x1= x2= x3= x4= x5= }",
						'plugin_type' => DATA_PLUGIN
					);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATALIBEXAMPLE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATALIBEXAMPLE );
/*****************
 * Help Function *
 *****************/
function data_help_libExample() {
/* In the Plugin Library there are 3 functions that help with the creation of a Plugins Help Function. They are:
 * function libHelpParam($name='',$type='',$descr='',$req=FALSE,$default='',$notes=NULL,$keywords=NULL) {
 * where:		$name - Required - string containing the Parameters Name
 *				$type - Required - string containing (normally) string/number/key-word - translated
 *				$descr - Required - string containing the description - No translation is done in function
 *				$req - boolean - True=manditory / False=optional / Default=False
 *				$default - string containing the default settings for this parameter / in not specified = "There is No Default"
 *				$notes - a string or array of strings - placed in a Div Box
 *				$keywords - a string or array ('keyword'=>'Description'=translated) of Keywords used by the parameter
 * function libHelpTable($parms='');
 * where:		$parms contains everything to be placed in the table
 * function libPluginHelp($tbl='',$notes=NULL,$example='',$demo=FALSE);
 * where:		$tbl contains the table (useually created with libHelpTable)
 *				$notes - a string or array of strings - placed in a Div Box
 *				$example - a string be displayed - like this: {CODE source='php' num='5'}Code to Display{CODE}
 *				$demo - if TRUE the $example will be executed in an expandable div box as a demonstration
 *********************************************************************************************************************/

// For Parameter (x1) - This is the simplest - only the first 3 arguments are required
	$help = libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
				'x1', // Name
				'string', // Type
				tra('Specifies something.') // Description
			  );

// There are a couple rules for translations that should be adheared to. They are:
// * Never translate anything that is coded - like Parameter Names / KeyWords / or values (True/False)
//     This is true even when your only mentioning a parameter or value in text because a translator can only see the
//     phrase to translate / like: "Yes does Something" - in Spanish "Si" may mean "Yes" but it your code isn't translated
// * Use the same word / phrases as often as possible
// * Avoid using a Beginning or Ending space character - too easy for the translater to miss and looks #$%^& when he does

// The next Parameter (x2) added Req(uired) / Default Value and a Note - A Note can be a string
	$help .= libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
				'x2', // Name
				'string', // Type
				tra('Specifies something else.'), // Description
				TRUE, // Required
				// If it has a Default Value / Why wouldn it be Required? - Oh! I see! StarRider wrote it!
				tra('The Default').' = "<strong>'.tra('I Laugh / You Cry - Sorry').'</strong>"', // Default
				tra('This Note is a String') // Notes (in String)
			);

// The next Parameter (x3) adds several Notes - A Note can also be an Array
	$help .= libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
				'x3', // Name
				'string', // Type
				tra('Specifies something different.'), // Description
				'', // Required
				tra('The Default').' = <strong>'.tra('Sorry About That').'</strong>', // Default
				array(	tra( "This is Note 1 in an Array"), // Notes (in Array)
						tra( "This is Note 2 in an Array"),
						tra( "This is Note 3 in an Array")
			  )		 );

// The next Parameter (x4) has No Notes / Adds KeyWords - as a String
	$help .= libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
				'x4', // Name
				'key-word',	// Type
				tra('Pick Your Favorite Pet.'), // Description
				'',		// Required
				tra('The Default').' = <strong>Aardvark</strong>', // Default
				'', //No Notes
				'Aardvark / Buffalo / Cheeta / Dingo / Elk' // Key-Words (in String)
			);

// The next Parameter (x5) has No Notes / Adds KeyWords - as an Array
	$help .= libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
				'x5', // Name
				'key-word',	// Type
				tra("Your Boss is a ???? (we can't display that so be nice)"), // Description
				'',		// Required
				tra('The Default').' = <strong>Aardvark</strong> - '.tra('Sorry About That').'</strong>', // Default
				'', //No Notes
				array(	'Asp'		=> tra('Off with his Head! Chop!'), // Key-Words (in Array)
						'Bug'		=> tra('Be quick - somebody step on him! Squish!'),
						'Coon'		=> tra('Let the Dogs loose & grab your gun! Bang!'),
						'Dog'		=> tra('Somebody should Spad him! Snip!'),
						'Earthworm'	=> tra('Lets go fishing? Splash!')
			  )		  );

// Finishing it
	return libPluginHelp(
				libHelpTable($help), // Creates the Table
				NULL, // Notes - same as libHelpParam
				"{LIBEXAMPLE x1='Parameter 1' x2='Parameter 2' x3='Parameter 3' x4='Parameter 4' x5='Parameter 5' }" // An Example
			);
}
/****************
* Load Function *
 ****************/
function data_libExample($data, $params) {
	extract ($params);
	$ret = tra('This Plugin does Nothing at this time. It functions as a template for the creation of new plugins using the Plugin Library.');
	return $ret;
}
?>
