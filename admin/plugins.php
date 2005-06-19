<?php
require_once( '../../bit_setup_inc.php' );

$gBitSystem->verifyPermission( 'bit_p_admin' );

if( isset( $_REQUEST['pluginsave'] ) && !empty( $_REQUEST['pluginsave'] ) ) {
	if( !empty( $_REQUEST['default_format'] ) && !empty( $_REQUEST['PLUGINS'][$_REQUEST['default_format']][0] ) ) {
		$gLibertySystem->setActivePlugins( $_REQUEST['PLUGINS'] );
		$gBitSystem->storePreference( 'default_format',$_REQUEST['default_format'] );
		$smarty->assign( 'default_format',$_REQUEST['default_format'] );
	} else {
		$smarty->assign( 'errorMsg', 'You cannot disable the default format');
	}
}

// Sort the plugins to avoild splitting tables
foreach( $gLibertySystem->mPlugins as $key => $row ) {
   $type[$key]  = $row['plugin_type'];
   $guid[$key] = $row['plugin_guid'];
}
array_multisort( $type, SORT_ASC, $guid, SORT_ASC, $gLibertySystem->mPlugins );

$smarty->assign_by_ref( 'gLibertySystem', $gLibertySystem );

//vd( $gLibertySystem->mPlugins );

$gBitSystem->display( 'bitpackage:liberty/admin_plugins.tpl');
?>
