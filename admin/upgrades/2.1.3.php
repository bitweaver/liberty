<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/admin/upgrades/2.1.3.php,v 1.3 2009/03/31 16:05:43 dansut Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => LIBERTY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Minor fix to ip columns to support IPv6",
	'post_upgrade' => NULL,
);

// Increase the size of the IP column to cope with IPv6
$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'QUERY' =>
	array(
		'PGSQL' => array(
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_content` ALTER `ip` TYPE VARCHAR(39)",
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_content_history` ALTER `ip` TYPE VARCHAR(39)",
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_action_log` ALTER `ip` TYPE VARCHAR(39)",
		),
		'OCI' => array(
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_content` MODIFY (`ip` TYPE VARCHAR2(39))",
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_content_history` MODIFY (`ip` TYPE VARCHAR2(39))",
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_action_log` MODIFY (`ip` TYPE VARCHAR2(39))",
		),
		'MYSQL' => array(
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_content` MODIFY `ip` VARCHAR(39)",
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_content_history` MODIFY `ip` VARCHAR(39)",
			"ALTER TABLE `".BIT_DB_PREFIX."liberty_action_log` MODIFY `ip` VARCHAR(39)",
		),
	),
),

));
?>
