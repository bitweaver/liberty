<?php

	global $smarty, $gBitSystem, $gContent;

//	vd( $gContent->mInfo );
	$smarty->assign_by_ref( 'pageInfo', $gContent->mInfo );

	$gBitSystem->display( 'bitpackage:liberty/display_content.tpl' );

?>
