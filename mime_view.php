<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_liberty/Attic/mime_view.php,v 1.2 2008/05/11 08:37:45 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
require_once( '../bit_setup_inc.php' );
require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );

// fetch the attachment details
if( !( $attachment = LibertyMime::getAttachment( $_REQUEST['attachment_id'], $_REQUEST ))) {
	$gBitSystem->fatalError( tra( "The Attachment ID given is not valid" ));
}
$gBitSmarty->assign( 'attachment', $attachment );

// first we need to check the permissions of the content the attachment belongs to since they inherit them
$gContent = LibertyBase::getLibertyObject( $attachment['content_id'] );
$gContent->verifyViewPermission();
$gBitSmarty->assign( 'gContent', $gContent );

// what template are we going to use to display this attachment
$gBitSmarty->assign( 'view_template', LibertyMime::getMimeTemplate( 'view', $attachment['attachment_plugin_guid'] ));

$gBitSystem->display( 'bitpackage:liberty/mime_view.tpl', tra( "View File" ));
?>
