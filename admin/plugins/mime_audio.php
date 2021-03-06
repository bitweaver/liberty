<?php
require_once( '../../../kernel/includes/setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

if( function_exists( 'shell_exec' )) {
	$gBitSmarty->assign( 'ffmpeg_path', shell_exec( 'which ffmpeg' ));
	$gBitSmarty->assign( 'mplayer_path', shell_exec( 'which mplayer' ));
	$gBitSmarty->assign( 'lame_path', shell_exec( 'which lame' ));
}

$feedback = array();

$options = array(
	'mp3_lib' => array(
		'libmp3lame' => 'libmp3lame',
		'mp3'        => 'mp3',
	),
	'audio_bitrate' => array(
		32000  => 32,
		64000  => 64,
		96000  => 96,
		128000 => 128,
		160000 => 160,
		192000 => 192,
	),
	'audio_samplerate' => array(
		11025 => 11025,
		22050 => 22050,
		44100 => 44100,
	),
);
$gBitSmarty->assign( 'options', $options );

if( !empty( $_REQUEST['plugin_settings'] )) {
	$audioSettings = array(
		'ffmpeg_path' => array(
			'type'  => 'text',
		),
		'ffmpeg_mp3_lib' => array(
			'type'  => 'text',
		),
		'mplayer_path' => array(
			'type'  => 'text',
		),
		'lame_path' => array(
			'type'  => 'text',
		),
		'mime_audio_ffmpeg_use' => array(
			'type'  => 'checkbox',
		),
		'mime_audio_samplerate' => array(
			'type'  => 'numeric',
		),
		'mime_audio_bitrate' => array(
			'type'  => 'numeric',
		),
		'mime_audio_lame_options' => array(
			'type'  => 'text',
		),
		'mime_audio_lame_options' => array(
			'type'  => 'text',
		),
		'mime_audio_backcolor' => array(
			'type'  => 'text',
		),
		'mime_audio_frontcolor' => array(
			'type'  => 'text',
		),
		'mime_audio_force_encode' => array(
			'type'  => 'checkbox',
		),
	);

	foreach( $audioSettings as $item => $data ) {
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
$gBitSystem->display( 'bitpackage:liberty/mime/audio/admin.tpl', tra( 'Audio Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
