<?php
// $Header:
/**
 * assigned_modules
 *
 * @author   StarRider starrrider@sourceforge.net
 * @version  $Revision: 1.1.2.1 $
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
define( 'PLUGIN_GUID_SPYTEXT', 'spytext' );
$pluginParams = array ( 'tag' => 'SPYTEXT',
						'auto_activate' => TRUE,
						'requires_pair' => TRUE,
						'load_function' => 'data_spytext',
						'title' => 'Spy Text (SPYTEXT)',
						'help_page' => 'DataPluginSpyText',
						'description' => tra("Allows text to be stored that is only visible to a List of Spys or to a Spy Agency (Group). To anyone else (except an Admin) the text is not be visible."),
						'help_function' => 'data_spytext_help',
						'syntax' => "{SPYTEXT spy= agency= sender= to= members= hidden= title= width= icon= alert= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_SPYTEXT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_SPYTEXT );

/*****************
 * Help Function *
 *****************/
function data_spytext_help() {
 	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>spy</td>'
				.'<td>' . tra( "UserName(s)") . '<br />' .tra( "(optional)") . '</td>'
				.'<td>' . tra( "A List of Spy(s) who can see the Message. Each Spy must be seperated with the | character like this:")
					."<br /><strong>spy='Fire|Spider|Lester|Xing'</strong>"
					.'<br />' .tra( "The Message will <strong>ONLY</strong> be displayed to Spys. <strong>HINT:</strong> The Admin is a Spy!")
			  . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>agency</td>'
				.'<td>' . tra( "GroupName(s)") . '<br />' .tra( "(optional)") . '</td>'
				.'<td>' . tra( "A List of Spy Agency(s) (Groups) that will be allowed to see the Messages. Each Agency must be seperated ")
					.tra( "with the | character like this:")
					."<br /><strong>agency='Devellopers|Editors'</strong>"
					.'<br />' .tra( "The Message will <strong>ONLY</strong> be displayed to Spys. <strong>HINT:</strong> The Admin is a Spy!")
				.'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>sender</td>'
				.'<td>' . tra( "UserName") . '<br />' .tra( "(optional)") . '</td>'
				.'<td>' . tra( "A List of Spy(s) who wrote the Message. Each Author must be seperated with the | character like this:")
					."<br /><strong>sender='StarRider|Wolffy'</strong>"
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>to</td>'
				.'<td>' . tra( "boolean/string") . '<br />' .tra( "(optional)") . '</td>'
				.'<td>' .tra( "Determines if an Address Box will be displayed before the Message. The Address Box is always in it's own ")
					.tra( "DropDown box which contains 3 lines where the") .' <strong>spy(s) / agency(s) / & sender(s)</strong> '
					.tra( "are identified. These lines are only displayed if a valid UserName / GroupName is found for that line. ")
					.'<br />' . tra( "The Default Header for each line is:")
					.'<br />Line 1 for Spy (Users) = <strong>"To the Spy:"</strong> '
					.'<br />Line 2 for Agency (Groups) = <strong>"To the Agency:"</strong> '
					.'<br />Line 3 for Sender (Users) = <strong>"From Your Source(s):"</strong> '
					.'<br />'. tra( "The Address Box is <strong>NOT</strong> displayed by Default. Specifing")
					."<br /><strong>to='TRUE'</strong> ". tra( "will enable the Address Box with the Default Headers. The Address Headers can ")
					.tra( "be redefined by using the the | character to seperate the headers like this:")
					."<br /><strong>to='To My Friends:|To My Colleagues In:|Your Friend:'</strong>" . tra( "The * character can also be used ")
					.tra( 'to say "Use This Default" but Replace this one. - Like this:')
					."<br /><strong>to='*|*|From the Sexiest Spy Ever:'</strong>"
				.'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>members</td>'
				.'<td>' . tra( "boolean") . '<br />' .tra( "(optional)") . '</td>'
				.'<td>' .tra( "The Ultimate in Mole Tools. When TRUE / Includes a listing of all Members in each Agency (Group). ")
					.tra( "The listing is added to the Agency Line of the To Box. Think Twice before making this TRUE. The listing ")
					.tra( "can be quite extensive on large sites. A better way of viewing this information is to visit ")
					.'<a href="'.USERS_PKG_URL.'index.php" title="Launch User Listing Browser in New Window" '
					.'onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">'
					.tra( "User Index" ) . '</a> ' 
					.'<br />' . tra( "The Default =") . ' <strong>FALSE</strong> ' . tra( "Members are <strong>NOT</strong> displayed.")
				.'</td>'
			.'<tr class="even">'
				.'<td>hidden</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Determines if the message is in a Stationary or DropDown Box.")
					.tra( "<br />By Default: <strong>Every</strong> SpyText Message is encased in a Stationary Box. Passing ")
					.tra( "<strong>Any</strong> value in this parameter will cause the Message to become a DropDown Box. The link that ")
					.tra( "<strong>Expands/Contracts</strong> the DropDown Box is supplied by the parameters")
					. ' <strong>title or icon</strong>. '
				.'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>title</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The Title Bar is something like a Horizontal Rule with the Text Centered on it. It is used as a link to")
					.tra( " <strong>Expand/Contract</strong> a") .' <strong>hidden</strong> ' . tra( "Message Box.") 
					.'<br />' . tra( "The Title Bar is only visible when the Message Box is") . ' <strong>hidden</strong> '
					.'<br />' . tra( "By Default Title used by the Title Bar is") . ' <strong>"A Hidden Message"</strong>. '
					.'<br />' . tra( "Specifying any value in this parameter will change the Default Title.")
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Controls the width of the text area on the Title Bar. The value is a percentage of available space but")
					.tra( " <strong>Do Not</strong> include the % character.")
					.'<br />' . tra( "The Default =") . ' <strong>20</strong>'
				.'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>icon</td>'
				.'<td>' . tra( "URL/Content Id #") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "An Icon can be used as the link to display the Message Box if desired. When activated, the Title becomes ")
					.tra( "part of the") . ' <strong>hidden</strong> ' . tra( "Message Box.")
					.tra( "<br />By Default - an Icon is <strong>Not</strong> displayed. Specifing :")
					."<br /><strong>icon='TRUE'</strong> ". tra( "will cause the Default Icon to be displayed. Any Image stored on the site ")
					.tra( "can be used by specifing it's Content Id Number like this:")
					."<br /><strong>icon='#125'</strong> ". tra( "<strong>Please Note:</strong> the # character Must be included.")
					.tra( "Any other value specified in this parameter is assumed to be a <strong>Valid URL</strong>.")
					.'<br />' . tra( "<strong>Note:</strong> - a listing of Content Id's can be found ") 
					.'<a href="'.LIBERTY_PKG_URL.'list_content.php" title="Launch BitWeaver Content Browser in New Window" '
					.'onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">'
					.tra( "Here" ) . '</a> '
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>alert</td>'
				.'<td>' . tra( "boolean/string") . '<br />' .tra( "(optional)") . '</td>'
				.'<td>' . tra( "Determines if an Alert Box is attached to the page. This is intended to provide the Less-Than-Swift Spy ")
					.tra( "with a Subtle Hint that there just might be something for them to look at.")
					.'<br />' . tra( "The Alert Box is <strong>Never</strong> displayed by Default. Specifing:")
					."<br /><strong>alert='TRUE'</strong> ". tra( "will enable the Alert Box with the Default Headers. The Default Headers is:")
					.'<br /><strong>"Wake Up Charlie! There is a message on this page for you. Use your Secret Decoder Ring!"</strong> '
					.tra( "<br />In this instance - <strong>Charlie</strong> is the UserName of the user viewing the page. The string:")
					.'<br /><strong>*UserName*<strong> ' . tra("is replaced with name of the spy viewing the page every place it is found.")
					.tra( "Passing <strong>ANY</strong> other value will replace Default Message.")
					.'<br />' . tra( "<strong>Note:</strong> The Administrator is a Spy and will be Alerted - with a slightly different message.")

				.'</td>'
			.'</tr>'
		.'</table>';
	return $help;
}

/*****************
 * Load Function *
 *****************/
function data_spytext($data, $params) {
	global $gLibertySystem; 
	global $gBitUser;
	global $gBitInstaller;

//	$to = FALSE;
//	$icon = FALSE;
//	$alert = TRUE;
	extract ($params);
	
	if (empty($data)) { // If there is NO data to display - why do anything - get out of here
		return " ";
	} // **************** Elvis has left the building!
  
	$isSpy = ($gBitUser->isAdmin()) ? TRUE : FALSE; // Admin should always see SpyText
    $isRealSpy = FALSE; // But Admin should not have to see all the damn Alerts
	if (isset($spy)) { // Is the current user on the Spy List
		$spyArray = explode("|", trim(strtolower($spy))); // spy's allowed to see the message
		foreach ($spyArray as $i) {
			if ($i == (strtolower($gBitUser->getTitle()))) {
				$isSpy = TRUE;
				$isRealSpy = TRUE;
	}	}	}
    
	if (isset($agency)) { // Is the current user in one of the Spy Agencies
		$spyArray = explode("|", trim(strtolower($agency))); // create an array from the string
		$groups = $gBitUser->getGroups();
		foreach ($spyArray as $i) {
			foreach ($groups as $g) {
				if (trim(strtolower($g['group_name'])) == $i) {
					$isSpy = TRUE;
					$isRealSpy = TRUE;
	}	}	}	}

	if (!$isSpy) { // The current user is NOT a Spy - get out of here
		return " ";
	} // **************** Elvis has left the building!

	$addToBox = (isset($to)) ? TRUE : FALSE;
	if ($addToBox) {
        $toLine = 'To the Spy: '; // Set Default
        $agencyLine = 'To the Agency: '; // Set Default
        $senderLine = 'From Your Source: '; // Set Default
        $header = explode("|", $to);
		$toLine     = (isset($header[0]) && (($header[0] != '*') && ($header[0] != 'TRUE'))) ? $header[0] .' ' : $toLine;
		$agencyLine = (isset($header[1]) && ($header[1] != '*')) ? $header[1] .' ' : $agencyLine;
		$senderLine = (isset($header[2]) && ($header[2] != '*')) ? $header[2] .' ' : $senderLine;
			
		$addToLine = FALSE;
		if (isset($spy)) { // Provide a Listing of all spys tested
			$spyArray = explode("|", $spy); // Strip Out the | character
			natcasesort ($spyArray); // Sort it
	        foreach ($spyArray as $i) { // TODO - Remove All Non-valid users - ($i == $i)
				if ($i == $i) {
					$toLine = ($addToLine) ? $toLine . ', ' : $toLine; // misses the first and last Spy
					$toLine = $toLine.'<a href="'.USERS_PKG_URL.'index.php?home='
						.$i.'" title="Home Page for '.$i.'"><strong>'.$i.'</strong></a>';
					$addToLine = True;
			}	}
			$toLine = '<tr><td style="vertical-align: top;">' .$toLine. '</td></tr>';
		}
			
		$addAgencyLine = FALSE;
		if (isset($agency)) { // Provide a Listing of all agencies tested
			$agency_array = explode("|", $agency); // Strip Out the | character
			natcasesort ($agency_array); // Sort it
            foreach ($agency_array as $i) { // TODO - Remove all Non-valid groups - ($i == $i)
				if ($i == $i) {
					$agencyLine = ($addAgencyLine) ? $agencyLine .', ' : $agencyLine; // misses the first and last Agency
					$agencyLine = $agencyLine . '<strong>' .$i. '</strong>';
					$addAgencyLine = True;
			}	}
			$addMembers = (isset($members)) ? TRUE : FALSE;
			if ($addMembers) { // TODO
	            foreach ($agency_array as $i) { // TODO - Remove all Non-valid groups - ($i == $i)
					if ($i == $i) {
						$agencyLine = $agencyLine . '<br /><strong>' .$i. '</strong> Members: ';
						// TODO - Add each member of valid Group Here
			}	}	}
			$agencyLine = '<tr><td style="vertical-align: top;">' . $agencyLine . '</td></tr>';
		}
			
		$addSenderLine = FALSE;
		if (isset($sender)) { // Provide a Listing of all senders tested
			$spyArray = explode("|", $sender); // Strip Out the | character
			natcasesort ($spyArray); // Sort it
	        foreach ($spyArray as $i) { // TODO - Remove All Non-valid users - ($i == $i)
				if ($i == $i) {
					$senderLine = ($addSenderLine) ? $senderLine .', ' : $senderLine; // misses the first and last Sender
					$senderLine = $senderLine .'<a href="'.USERS_PKG_URL.'index.php?home='
					.$i. '" title="Home Page for ' .$i. '"><strong>' . $i . '</strong></a>';
					$addSenderLine = True;
			}	}
			$senderLine = '<tr><td style="vertical-align: top;">' . $senderLine . '</td></tr>';
		}
			
		$ab = (microtime() * 1000000);
		$toHeader = '<div>'
			.'<table width="100%" border="0" cellspacing="0" cellpadding="0">'
				.'<tr>'
					.'<td width=42%><hr></td>'
					.'<td style="text-align:center">'
						.'<a title="Expand/Contract for Addresses" href="javascript:flip(' .$ab. ')">Address</a>'
					.'</td>'
					.'<td width=42%><hr></td>'
				.'</tr>'
			.'</table>'
		.'</div>';
			
		$addToBox = ($addToBox && ($addToLine || ($addAgencyLine || $addSenderLine))) ? TRUE : FALSE;
		if ($addToBox) {
			$toBox = '<div>'; // Wrap's Arround Everything in toBox
				$toBox = $toBox . $toHeader . '<div style="display:none;" id="' .$ab. '"><table border="1">';
					$toBox = ($addToLine) ? $toBox . $toLine : $toBox; // Drop toLine if nothing on it
					$toBox = ($addAgencyLine) ? $toBox . $agencyLine : $toBox; // Drop agencyLine if nothing on it
					$toBox = ($addSenderLine) ? $toBox . $senderLine : $toBox; // Drop fromLine if nothing on it
				$toBox = $toBox . '</table></div>';
			$toBox = $toBox . '</div>';
	}	}
		
	$mt = (microtime() * 1000000);
	$hidden = (isset($hidden)) ? TRUE : FALSE;
	$useIcon = (isset($icon)) ? TRUE : FALSE;
	if ($hidden) {
		if ($useIcon) {
			if ((strtoupper(trim($icon))) == 'TRUE') {
				$spyLink = '<a href="javascript:flip('.$mt.')"><img src="'.LIBERTY_PKG_URL.'icons/spy.gif"></img></a>'; // Default
			}
            // --------------------------> TODO - Need Same thing with Content Id No's
            if (!isset($spyLink)) {
				$spyLink = '<a href="javascript:flip('.$mt.')"><img src="'.$icon.'"></img></a>'; // Last Choice - A URL
			}
		} else { // It's not linked to an Icon - so - It needs Title Bar
			$width = (isset($width)) ? $width : '20';
			$width = ((100 - $width) / 2) . '%';
			$title = (isset($title)) ? $title : 'A Hidden Message';
			$spyLink = '<div>'
				.'<table width="100%" border="0" cellspacing="0" cellpadding="0">'
					.'<tr>'
						.'<td width='.$width.'><hr></td>'
						.'<td style="text-align:center">'
							.'<a title="Expand/Contract for Hidden Text" href="javascript:flip('.$mt.')">'.$title.'</a>'
						.'</td>'
						.'<td width='.$width.'><hr></td>'
					.'</tr>'
				.'</table>'
			.'</div>';
		}
	}
    $ret = ($hidden) ? $spyLink. '<div class="help box" style="display:none;" id="' .$mt. '">' : '';
//	$ret = $ret .'<div class="help box">';
    $ret = ($addToBox) ? $ret.$toBox : $ret;
	$ret = $ret .'<div class="help box" style="text-align:left;">'.$data.'</div>';
//    $ret = $ret.'</div>'; 
    $ret = ($hidden) ? $ret.'</div>' : $ret;


// I'm NOT Sure if this should be include or not - especially the way I have it set up
// I have reduced the number of Alerts that an Admin would recieve - Only Hidden Messages
	$spyAlert = FALSE;
	if (isset($alert)) {
		if (strtoupper(trim($alert)) == 'TRUE') {
			$spyAlert = TRUE;
			$spyMsg = ($isRealSpy) ? 'Wake Up *UserName*!\nThere is a message on this page for you.\nUse your Secret Decoder Ring!' : '';
		} else {
			$spyAlert = TRUE;
			$spyMsg = $alert;
	}	}
	if (($gBitUser->isAdmin()) && ($hidden)) {
		$spyMsg = '*UserName*\nThere is Hidden SpyText on this page.';
		$spyAlert = TRUE;
	}
	if ($spyAlert) {
		$spyArray = explode("*", $spyMsg); // Process the *UserName*
		for ($i = 0; $i <= ((count($spyArray))-1); $i++) {
			$spyArray[$i] = (trim(strtoupper($spyArray[$i])) == 'USERNAME') ? $gBitUser->getTitle() : $spyArray[$i];
		}
		$spyMsg = implode(" ", $spyArray);
		echo "<script>window.alert(\"" .$spyMsg. "\");</script>";
	}

/* The following is the testing page used for this plugin - copy it into a page

))SpyText(( / Without Data Nothing will be Displayed
{SPYTEXT }{SPYTEXT}
__NOTE__ The same thing applies if
The current user is not on the list of users(spy)
The current user is not in the list of the groups(agency)

))SpyText(( / When No Parameters are set - only the data box is displayed
{SPYTEXT }Data{SPYTEXT}

By the way - this is kind of Cute - Try Looking at this page without being logged in
None of the ))SpyText(( messages are visible to an Anonymous users

That wouldn't be worth spit if it couldn't be reversed
By adding Anonymous to the list of groups(agency) - Anonymous users can now see the message
The problem is that EVERY Flipping User can also see it - and because of the way we do Groups
I don't see an easy way around that
))SpyText(( / With agency='Anonymous'
{SPYTEXT agency='Anonymous' }Data{SPYTEXT}

So Lets Play a Buildup Game - Each Call Adds to the Original

))SpyText(( w/ to='TRUE'
__Note__ The address box is not displayed because spy/agency/& sender are not defined - so there is nothing to put in it
{SPYTEXT to='TRUE' }Data{SPYTEXT} 

))SpyText(( w/ spy='Spider|Xing|Confusus'
(On TODO List) Confusus is not a user so he should not be displayed
{SPYTEXT spy='Spider|Xing|Confusus' to='TRUE' }Data{SPYTEXT} 

))SpyText(( w/ agency='Aardvarks|Testing'
(On TODO List) Aardvarks is not a Group so it should not be displayed
{SPYTEXT spy='Spider|Xing|Confusus' agency='Aardvarks|Testing' to='TRUE' }Data{SPYTEXT} 

))SpyText(( w/ sender='StarRider|Wolffy' {SPYTEXT spy='Spider|Xing|Confusus' agency='Aardvarks|Testing' sender='StarRider|Wolffy' to='TRUE' }Data{SPYTEXT} 

Testing the parameter to / Was True / Replacing a single Header
))SpyText(( w/ to='*|*|The Sexiest Spy Ever:'
{SPYTEXT spy='Spider|Xing|Confusus' agency='Aardvarks|Testing' sender='StarRider|Wolffy' to='*|*|The Sexiest Spy Ever:' }Data{SPYTEXT} 

Testing the parameter to / Assigning all new Headers
))SpyText(( w/ to='To:|Groups:|From:'
{SPYTEXT spy='Spider|Xing|Confusus' agency='Aardvarks|Testing' sender='StarRider|Wolffy' to='To:|Groups:|From:' }Data{SPYTEXT} 


Testing the parameter to / Not Assigning Enough Headers
))SpyText(( w/ to='To:'
{SPYTEXT spy='Spider|Xing|Confusus' agency='Aardvarks|Testing' sender='StarRider|Wolffy' to='To:' }Data{SPYTEXT} 

Turning On Members - this time only
Another item on the TODO List - but Sectioning has been added
{SPYTEXT spy='Spider|Xing|Confusus' agency='Aardvarks|Testing' sender='StarRider|Wolffy' to='To:' members='TRUE' }Data{SPYTEXT} 

-=::OK - Lets Get Hidden::=-
This Should display only the Title Bar - Everything else is collapsed
))SpyText(( Parameter List = spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE'
{SPYTEXT spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' }Data{SPYTEXT} 

Added to=TRUE
))SpyText(( Parameter List = spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE'
{SPYTEXT spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE' }Data{SPYTEXT} 

Added title='Aardvarks' & width='25'
))SpyText(( Parameter List = spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE' title='Aardvarks' width='25'
{SPYTEXT spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE' title='Aardvarks' width='25'}Data{SPYTEXT} 

-=::OK - Lets Do Icons::=-
Starting Over - Added icon=TRUE
))SpyText(( Parameter List = spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE' icon='TRUE'
{SPYTEXT spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE' icon='TRUE' }Data{SPYTEXT} 

An Icon can still be placed in the middle {SPYTEXT spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE' icon='TRUE' }Data{SPYTEXT} of a line? Dosen't look too bad expanded either.

Changed icon to a URL 
))SpyText(( Parameter List = spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE' icon='http://localhost/Bitweaver/liberty/icons/imagick_logo.jpg'
{SPYTEXT spy='Spider|Xing' agency='Aardvarks|Testing' sender='StarRider|Wolffy' hidden='TRUE' to='TRUE' icon='http://localhost/Bitweaver/liberty/icons/imagick_logo.jpg' }Data{SPYTEXT} 


*////////////////////////////////////////////////////////////
	 
	return $ret;
}
?>
