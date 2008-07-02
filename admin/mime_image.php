<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

$settings = array(
	'mime_image_panoramas' => array(
		'label' => 'Panorama Images',
		'note' => 'When users upload 360&deg; panoramic images, they can enable a flash viewer to view these. This can greatly enhance the image viewing experience for panoramic images. (<a class="external" href="http://pan0.net/fspp">example</a>)',
		'type' => 'checkbox',
	),
);
$gBitSmarty->assign( 'settings', $settings );

if( $gBitSystem->getConfig( 'image_processor' ) != 'magickwand' ) {
	$gBitSmarty->assign( 'image_processor_warning', TRUE );
}

$feedback = array();
if( !empty( $_REQUEST['settings_store'] )) {
	foreach( $settings as $item => $data ) {
		if( $data['type'] == 'checkbox' ) {
			simple_set_toggle( $item, TREASURY_PKG_NAME );
		} elseif( $data['type'] == 'numeric' ) {
			simple_set_int( $item, TREASURY_PKG_NAME );
		} else {
			$gBitSystem->storeConfig( $item, ( !empty( $_REQUEST[$item] ) ? $_REQUEST[$item] : NULL ), TREASURY_PKG_NAME );
		}
	}
}
$gBitSmarty->assign( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:liberty/admin_mime_image.tpl', tra( 'Image Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
