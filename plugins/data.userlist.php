<?php
/**
 * @version  $Revision: 1.1.1.1.2.8 $
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
// | Author (TikiWiki): sQuare
// | Reworked & Undoubtedly Screwed-Up for (Bitweaver) 
// | by: StarRider <starrrider@sourceforge.net>
// | Reworked from: wikiplugin_userlist.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.userlist.php,v 1.1.1.1.2.8 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAUSERLIST', 'datauserlist' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'USERLIST',
						'auto_activate' => FALSE,
						'requires_pair' => TRUE,
						'load_function' => 'data_userlist',
						'title' => 'UserList<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'UserList',,                                                                                       // and Remove the comment from the start of this line
						'help_page' => 'DataPluginUserList',
						'description' => tra("This plugin will displays an alphabetically sorted list of registered users. A Group Name can be included to filter Groups to be listed."),
						'help_function' => 'data_userlist_help',
						'syntax' => "{USERLIST num= userspage= alpha= total= email= }GroupName{USERLIST}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAUSERLIST, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAUSERLIST );

// Help Function
function data_userlist_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{USERLIST" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::num::" . tra(" | ::boolean:: |  if True then the listing is numbered. __Optional__ - can be omitted - the __Default = False__ (any value = True) .\n");
	$back.= "::userspage::" . tra(" | ::boolean:: | if True then a link is provided to each user that has a personal userpage. __Optional__ - can be omitted - the __Default = True__ (any value = False).\n");
	$back.= "::alpha::" . tra(" | ::boolean:: | if True then the listing is sectioned alphabetically - using the first letter in the users name. __Optional__ - can be omitted - the __Default = True__ (any value = False) .\n");
	$back.= "::email::" . tra(" | ::boolean:: | shows the email address of each user - if their email address is public. __Optional__ - can be omitted - the __Default = False__ (any value = True).\n");
	$back.= "::total::" . tra(" | ::boolean:: | shows the total number of users in list at the bottom of the listing. __Optional__ - can be omitted - the __Default = True__ (any value = False).||^");
	$back.= tra("^__Example:__ ") . "~np~{USERLIST(num=>1,email=>1)}{USERLIST} ~/np~^";
	return $back;
}

// Load Function
function data_userlist($data, $params) {
	$ret = "This plugin has not been completed as yet. ";
	return $ret;
}
/******************************************************************************
The code below is from the deprecated USERLIST plugin. All comments and the help routines have been removed. - StarRider

include_once( WIKI_PKG_PATH.'BitPage.php');

function wikiplugin_userprint($num,$user,$userspage,$email,$wp_user_count) {
	global $wikilib,$gBitUser, $gBitSystem;
	// alternate class of every row
	if ($wp_user_count % 2) {
		$class = 'even';
	}
	else {
		$class = 'odd';
	}
	$ret = ('<tr class="'.$class.'">');

	// get email address and check if it's public
	$user_info = $gBitUser->getUserInfo( array( 'login' => $user ) );
	$eml = scrambleEmail($user_info['email']);
	$public = FALSE; // cheap hack from spiderr to get things working. $wikilib->get_user_preference($user,'email is public');

	if ($num) {
		$ret .= ('<td>__'.$wp_user_count.'__</td>');
	}

	if( $gBitSystem->isPackageActive( 'messu' ) ) {
		$messu_img = ('<a class="wiki" href="'.MESSU_PKG_URL.'compose.php?to='.$user.'" title="'.tra('Send a message to').' '.$user.'"><img src="'.IMG_PKG_URL.'icons/icon_ultima.gif" width="20" height="10" border="0" alt="'.tra('Send message').'" /></a> ');
	} else {
		$messu_img = ('');
	}
	$ret .= ('<td>'.$messu_img.$user.'</td>');

	// if you want to show all email addresses indstead of only public ones, uncomment following line
		$public = 'y';

	if ($email) {
		if ($public == 'y' && $eml != '') {
			$ret .= '<td>[mailto:'.$eml.'|'.$eml.']</td>';
		}
		else {
			$ret .= '<td>&nbsp;</td>';
		}
	}

	if ($userspage) {
		if ($wikilib->pageExists('userPage'.$user)) {
			$ret .= '<td>((userPage'.$user.'|'.$user.' Homepage))</td>';
		}
		else {
			$ret .= '<td>&nbsp;</td>';
		}
	}

	$ret .= ("</tr>\r");

	return $ret;
}

// function used to sort an array - NOT case-sensitive
function wikiplugin_compare_users($a, $b) {
   return strcmp(strtolower($a), strtolower($b));
}

function wikiplugin_userlist($data, $params) {
	global $gBitUser, $gBitSystem;

	extract ($params, EXTR_SKIP);
	$num = (isset($num)) ? True : False;				     // Default = False 
	$userspage = (!isset($userspage)) ? True : False;   // Default = True 
	$alpha = (!isset($alpha)) ? True : False; 				  // Default = True 
	$total = (!isset($total)) ? True : False; 				   // Default = True 
	$email = (isset($email)) ? True : False; 				 // Default = False 
	
	$colcount = 1;

	$ret = '<table class="normal">';
	$ret .= '<tr>';

	if ($num) {
		$ret .= '<th>&nbsp;</th>';
		$colcount++;
	}

	$ret .= '<th>'.tra('__UserName__').'</th>';

	if ($email) {
		$ret .= '<th>'.tra('__email Address__').'</th>';
		$colcount++;
	}

	if ($userspage) {
		$ret .= '<th>'.tra('__UserPage__').'</th>';
		$colcount++;
	}

	$ret .= '</tr>';

	if (!$data) {
		$userdata = $gBitUser->get_users(0);

		foreach ($userdata['data'] as $usertemp) {
			$users[] = $usertemp['login'];
		}
	} else {
		$users = $gBitUser->get_group_users($data);
	}

	usort($users, 'wikiplugin_compare_users');	// sort the users

	$wp_user_count = 0;
	foreach ($users as $user) {
		if ($wp_user_count >= 1) {
			$prev_user = $users[$wp_user_count-1];
		}
		else {
			$prev_user = 0;
		}
		$wp_user_count++;

		if ($alpha) {
			if (strtolower($prev_user[0]) != strtolower($user[0])) {
				$ret .= ("<tr><th colspan='".$colcount."'>__::Section - ".strtoupper($user[0])."::__</th></tr>\r");
			}
		}

		$ret .= (wikiplugin_userprint($num,$user,$userspage,$email,$wp_user_count));
	}

	if ($total) {
		$ret .= ("<tr><td class='odd' colspan='".$colcount."'>&nbsp;</td></tr>\r");
		$ret .= ("<tr><td class='odd' colspan='".$colcount."'>".tra('__Total number of users').": ".$wp_user_count."__</td></tr>\r");
	}

	$ret .= ('</table>');

	return $ret;
}
*/
?>
