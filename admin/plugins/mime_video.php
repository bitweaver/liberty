<?php
require_once( '../../../kernel/setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

if( function_exists( 'shell_exec' )) {
	$gBitSmarty->assign( 'ffmpeg_path', shell_exec( 'which ffmpeg' ));
	$gBitSmarty->assign( 'mp4box_path', shell_exec( 'which MP4Box' ));
}

if( extension_loaded( 'ffmpeg' )) {
	$gBitSmarty->assign( 'ffmpeg_extension', TRUE );
}

$feedback = array();

$options = array(
	'me_method' => array(
		'me_method' => 'me_method',
		'me'        => 'me',
	),
	'mp3_lib' => array(
		'libmp3lame' => 'libmp3lame',
		'mp3'        => 'mp3',
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
$options['display_size'] = get_image_size_options( 'Same as encoded video' );
$gBitSmarty->assign( 'options', $options );

if( !empty( $_REQUEST['plugin_settings'] )) {
	$videoSettings = array(
		'ffmpeg_path' => array(
			'type'  => 'text',
		),
		'ffmpeg_mp3_lib' => array(
			'type'  => 'text',
		),
		'ffmpeg_me_method' => array(
			'type'  => 'text',
		),
		'mp4box_path' => array(
			'type'  => 'text',
		),
		'mime_video_video_codec' => array(
			'type'  => 'text',
		),
		'mime_video_video_bitrate' => array(
			'type'  => 'numeric',
		),
		'mime_video_force_encode' => array(
			'type'  => 'checkbox',
		),
		'mime_video_audio_samplerate' => array(
			'type'  => 'numeric',
		),
		'mime_video_audio_bitrate' => array(
			'type'  => 'numeric',
		),
		'mime_video_width' => array(
			'type'  => 'numeric',
		),
		'mime_video_default_size' => array(
			'type'  => 'text',
		),
		'mime_video_backcolor' => array(
			'type'  => 'text',
		),
		'mime_video_frontcolor' => array(
			'type'  => 'text',
		),
	);

	foreach( $videoSettings as $item => $data ) {
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
$gBitSystem->display( 'bitpackage:liberty/mime/video/admin.tpl', tra( 'Flashvideo Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
