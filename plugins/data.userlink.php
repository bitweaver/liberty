<?php
/**
 * @version  $Revision: 1.1.2.3 $
 * @package  liberty
 * @subpackage plugins_data
 * @author bigwasp bigwasp@sourceforge.net
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author (TikiWiki): Damian Parker <damosoft@users.sourceforge.net>
// | Reworked & Undoubtedly Screwed-Up for (Bitweaver) 
// | by: StarRider <starrrider@sourceforge.net>
// | Reworked from: wikiplugin_usercount.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.userlink.php,v 1.1.2.3 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAUSERLINK', 'datauserlink' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'USERLINK',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_userlink',
						'title' => 'UserLink shows a link to the UserPage for a given login or email', // Remove this line when the plugin becomes operational
//						'title' => 'User',                                                                                       // and Remove the comment from the start of this line
						'help_page' => 'DataPluginUserLink',
						'description' => tra("Will show a link to the userpage"),
						'help_function' => 'data_userlink_help',
						'syntax' => "{USERLINK login='bigwasp'}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAUSERLINK, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAUSERLINK );

// Help Function
function data_userlink_help() {
	$help =
	        '<table class="data help">'
		        .'<tr>'
			        .'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments") . '</th>'
			.'</tr>'
			.'<tr class="odd">'
			        .'<td>login</td>' 
			        .'<td>' . tra( "string" ) . '<br />' . tra( "(optional)") . '</td>'
				.'<td>' . tra( "The login name to gernerate the link" ) . '</td>'
			.'</tr>'
			.'<tr class="even">'
			        .'<td>email</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra( "(optional)") . '</td>'
				.'<td>' . tra( "The mailadress to gernerate the link" ) . '</td>'	
			.'</tr>'
		.'</table>'
		.tra("Example: ") . "{USERLINK login='admin'}";
	return $help;			
}

// Load Function
function data_userlink($data, $params) {
        global $gBitUser;
	$nFlag = 0;
	$myHash = array();
	$ret = '';
	extract ($params, EXTR_SKIP);
	if (isset($login)) {
	   $myHash['login'] = $login;
	   $nFlag++;
	}
	If (isset($email)) {
           $myHash['email'] = $email;
	   $nFlag++;
	}   	   
	If (isset($user_id)) {
	   $myHash['user_id'] = $user_id;
	   $nFlag++;
	}   
	
	// $nFlag check if only exactly one parameter was given!
	if ($nFlag == 1) {
	    $user = $gBitUser->userExists($myHash); 
	    
	    if(!$user==Null) {
	      $tmpUser = $gBitUser->getUserInfo(array('user_id'=>$user));
	      $ret = $gBitUser->getDisplayName(TRUE,$tmpUser) ;
	    }
	    
	 }
	return $ret;
}
?>
