<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/admin/upgrades/2.1.2.php,v 1.1 2009/01/02 20:38:02 squareing Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => LIBERTY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Rename video conversion plugin from <em>flv</em> to <em>video</em> and some other minor cleanups in the database.",
	'post_upgrade' => NULL,
);

$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'QUERY' =>
	array(
		// some specific config value renames
		'SQL92' => array(
			"UPDATE `".BIT_DB_PREFIX."kernel_config` SET `config_name` = 'ffmpeg_mp3_lib' WHERE `config_name` = 'ffmpeg_mp3_param'",
			"UPDATE `".BIT_DB_PREFIX."kernel_config` SET `config_name` = 'ffmpeg_path'    WHERE `config_name` = 'mime_flv_ffmpeg_path'",
			"UPDATE `".BIT_DB_PREFIX."kernel_config` SET `config_name` = 'mplayer_path'   WHERE `config_name` = 'mime_audio_mplayer_path'",
			"UPDATE `".BIT_DB_PREFIX."kernel_config` SET `config_name` = 'lame_path'      WHERE `config_name` = 'mime_audio_lame_path'",
			"DELETE FROM `".BIT_DB_PREFIX."kernel_config` WHERE `config_name` = 'mime_audio_ffmpeg_path'",

			// update liberty_attachments with new attachment GUID
			"UPDATE `".BIT_DB_PREFIX."liberty_attachments` SET `attachment_plugin_guid` = 'mimevideo' WHERE `attachment_plugin_guid` = 'mimeflv'",

			// update kernel config names and values with new ones
			"UPDATE `".BIT_DB_PREFIX."kernel_config` SET `config_name`  = REPLACE( `config_name`,  'flv', 'video' ) WHERE `config_name`  LIKE '%flv%'",
			"UPDATE `".BIT_DB_PREFIX."kernel_config` SET `config_value` = REPLACE( `config_value`, 'flv', 'video' ) WHERE `config_value` LIKE '%flv%'",
		),
	),
),

));
?>
