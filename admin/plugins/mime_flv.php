<?php
require_once( '../../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

if( function_exists( 'shell_exec' )) {
	$gBitSmarty->assign( 'ffmpeg_path', shell_exec( 'which ffmpeg' ));
}

if( extension_loaded( 'ffmpeg' )) {
	$gBitSmarty->assign( 'ffmpeg_extension', TRUE );
}

$feedback = array();

$rates = array(
	'mp3_param' => array(
		'libmp3lame' => 'libmp3lame',
		'mp3' => 'mp3',
	),
	'video_codec' => array(
		'flv'        => 'Flashvideo using flv codec',
		'h264'       => 'MP4/AVC using h264 codec',
		'h264-2pass' => 'MP4/AVC using h264 codec - 2 passes',
	),
	'video_bitrate' => array(
		160000 => 200,
		240000 => 300,
		320000 => 400,
		400000 => 500,
		480000 => 600,
		560000 => 700,
		640000 => 800,
	),
	'video_width' => array(
		240 => 240,
		320 => 320,
		480 => 480,
		640 => 640,
	),
	'display_size' => array(
		0   => tra( 'Same as encoded video' ),
		240 => tra( 'Small' ),
		320 => tra( 'Medium' ),
		480 => tra( 'Large' ),
		640 => tra( 'Huge' ),
	),
	'audio_bitrate' => array(
		32000  => 32,
		64000  => 64,
		96000  => 96,
		128000 => 128,
	),
	'audio_samplerate' => array(
		11025 => 11025,
		22050 => 22050,
		44100 => 44100,
	),
);
$gBitSmarty->assign( 'rates', $rates );

if( !empty( $_REQUEST['plugin_settings'] )) {
	$flvSettings = array(
		'mime_flv_ffmpeg_path' => array(
			'type'  => 'text',
		),
		'mime_flv_video_codec' => array(
			'type'  => 'text',
		),
		'ffmpeg_mp3_param' => array(
			'type'  => 'text',
		),
		'mime_flv_video_bitrate' => array(
			'type'  => 'numeric',
		),
		'mime_flv_force_encode' => array(
			'type'  => 'checkbox',
		),
		'mime_flv_audio_samplerate' => array(
			'type'  => 'numeric',
		),
		'mime_flv_audio_bitrate' => array(
			'type'  => 'numeric',
		),
		'mime_flv_width' => array(
			'type'  => 'numeric',
		),
		'mime_flv_default_size' => array(
			'type'  => 'numeric',
		),
		'mime_flv_backcolor' => array(
			'type'  => 'text',
		),
		'mime_flv_frontcolor' => array(
			'type'  => 'text',
		),
	);

	foreach( $flvSettings as $item => $data ) {
		if( $data['type'] == 'checkbox' ) {
			simple_set_toggle( $item, LIBERTY_PKG_NAME );
		} elseif( $data['type'] == 'numeric' ) {
			simple_set_int( $item, LIBERTY_PKG_NAME );
		} else {
			$gBitSystem->storeConfig( $item, ( !empty( $_REQUEST[$item] ) ? $_REQUEST[$item] : NULL ), LIBERTY_PKG_NAME );
		}
	}

	$feedback['success'] = tra( 'The plugin was successfully updated' );
}

$gBitSmarty->assign( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:liberty/mime/flv/admin.tpl', tra( 'Flashvideo Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
