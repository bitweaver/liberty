<?php

// $Header: /cvsroot/bitweaver/_bit_liberty/preview.php,v 1.1 2007/04/08 18:00:35 nickpalmer Exp $

// Copyright( c ) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
require_once( '../bit_setup_inc.php' );

global $gContent, $gBitSystem, $gBitSmarty;
include_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
// If we can't find one make an invalid one to keep the template happy.
if (empty($gContent)) {
	$gContent = new LibertyContent();
	$gBitSmarty->assign_by_ref('gContent', $gContent);
}
// Should we tell the template to generate a closeclick icon
if (isset($_REQUEST['closeclick'])) {
	$gBitSmarty->assign('closeclick', true);
}

header( 'Content-Type: text/html; charset=utf-8' );
echo $gContent->getPreview();