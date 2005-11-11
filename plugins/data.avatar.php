<?php
/**
 * @version  $Revision: 1.1.1.1.2.10 $
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
// | Author (TikiWiki): Luis Argerich <lrargerich@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_avatar.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.avatar.php,v 1.1.1.1.2.10 2005/11/11 22:04:08 mej Exp $

/**
 * definitions
 */
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'wiki' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATAAVATAR', 'dataavatar' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'AVATAR',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_avatar',
						'title' => 'Avatar<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'Avatar',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginAvatar',
						'description' => tra("This plugin will display a User's Avatar as a Link to a page."),
						'help_function' => 'data_avatar_help',
						'syntax' => "{AVATAR user= page= float= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAAVATAR, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAAVATAR );

// Help Function
function data_avatar_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>user</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Name of the user who's avatar should be shown (Default = avatar of user accessing page).") . '</td>'
				// Would somebody check the default - it would make more sence if it was the Author of the page instead of whoever is reading it
			.'</tr>'
			.'<tr class="even">'
				.'<td>page</td>'
				.'<td>' . tra( "page name") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Used to make the Avatar a link to a specified page. (Default = Homepage of the Avatar's owner)") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>float</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies how the Avatar is to be alligned on the page. If NOT defined - the text will not wrap around the Avatar. Possible values are:") 
				. ' <strong>left or right</strong> ' . tra("(Default = ") . '<strong>NOT SET</strong>)</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{AVATAR user='admin' page='home' float='right'}";
	return $help;
}

// Load Function
function data_avatar($data, $params) { // Pre-Clyde Changes
// The Parameter user is new - the info was extracted from $data
// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem; 
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATAAVATAR];
	$ret = 'The plugin <strong>"' . $pluginParams['tag'] . '"</strong> has not been completed as yet. ';
	return $ret;
}
}
/******************************************************************************
The code below is from the deprecated AVATAR plugin. All comments and the help routines have been removed. - StarRider
require_once( KERNEL_PKG_PATH.'BitBase.php' );

function wikiplugin_avatar($data, $params) {
	global $gBitSystem;
	global $gBitUser;
	extract ($params, EXTR_SKIP);
	if (isset($float))
		$avatar = $gBitSystem->get_user_avatar($data, $float);
	else
		$avatar = $gBitSystem->get_user_avatar($data);
	if (isset($page)) {
		$avatar = "<a href=\"".WIKI_PKG_URL."index.php?page=$page'>" . $avatar . '</a>';
	} else if ($gBitUser->userExists( array( 'login' => $data ) ) && $gBitSystem->getPreference('user_information', 'public', $data ) == 'public') {
		$avatar = "<a href=\"".USERS_PKG_URL."\"index.php?fHomepage=$data\">" . $avatar . '</a>';
	}
	return $avatar;
}
*/
?>
