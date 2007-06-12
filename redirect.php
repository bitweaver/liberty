<?php
require_once('../bit_setup_inc.php');
if (!empty($_REQUEST['q'])) {
    // ToDo: It would be nice to be able to track hits out...
    header( 'Location:'.$_REQUEST['q'] );
}
else {
    $_REQUEST['error'] = tra('The redirect did not include a url.');
    include( KERNEL_PKG_PATH . 'error.php' );
}
?>