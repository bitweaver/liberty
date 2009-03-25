<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/admin/upgrades/2.1.3.php,v 1.1 2009/03/25 02:32:49 spiderr Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => LIBERTY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Minor fix to ip columns to support IPv6",
	'post_upgrade' => NULL,
);

// all we are doing is change the column type of user_id for liberty_content_history.
// postgresql < 8.2 doesn't allow easy column type changing
// and therefore we need to undergo this annoying dance.
$gBitInstaller->registerPackageUpgrade( $infoHash, array(

// copy data into new column
array( 'QUERY' =>
	// postgres > 8.2 needs to have the type cast
	array(
		'PGSQL' => array(	"ALTER TABLE `".BIT_DB_PREFIX."users_cnxn ALTER `ip` TYPE VARCHAR(39)" ,
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_content ALTER `ip` TYPE VARCHAR(39)", 
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_content_history` ALTER `ip` TYPE VARCHAR(39)",
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_action_log` ALTER `ip` TYPE VARCHAR(39)",
		),
		'OCI' => array(	"ALTER TABLE `".BIT_DB_PREFIX."users_cnxn MODIFY (`ip` TYPE VARCHAR2(39))" ,
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_content MODIFY (`ip` TYPE VARCHAR2(39))", 
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_content_history` MODIFY (`ip` TYPE VARCHAR2(39))",
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_action_log` MODIFY (`ip` TYPE VARCHAR2(39))",
		),
		'MYSQL' => array(	"ALTER TABLE `".BIT_DB_PREFIX."users_cnxn MODIFY `ip` TYPE VARCHAR(39)" ,
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_content MODIFY `ip` TYPE VARCHAR(39)", 
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_content_history` MODIFY `ip` TYPE VARCHAR(39)",
							"ALTER TABLE `".BIT_DB_PREFIX."liberty_action_log` MODIFY `ip` TYPE VARCHAR(39)",
		),
	),
),

));
?>
