<?php
require_once( '../../../kernel/setup_inc.php' );
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

$panWidth = array(
	1500 => '1500 x 750',
	2000 => '2000 x 1000 (about 1MB)',
	2500 => '2500 x 1250',
	3000 => '3000 x 1500 (about 2MB)',
	3500 => '3500 x 1750',
	4000 => '4000 x 2000 (about 3-4MB)',
	4500 => '4500 x 2250',
	5000 => '5000 x 2500 (about 5-6MB)',
);
$gBitSmarty->assign( 'panWidth', $panWidth );

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
	simple_set_int( 'mime_image_panorama_width', $_REQUEST['mime_image_panorama_width'] );
}
$gBitSmarty->assign( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:liberty/mime/image/admin.tpl', tra( 'Image Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
