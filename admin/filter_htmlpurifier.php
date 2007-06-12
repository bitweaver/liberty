<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

$htmlPurifier = array(
	'htmlpure_escape_bad' => array(
		'label' => 'Escape invalid HTML',
		'note' => ' Escapes invlid HTML as text. Otherwise invalid HTML is silently dropped. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#Core.EscapeInvalidTags">this</a> and <a href="http://htmlpurifier.org/live/configdoc/plain.html#Core.EscapeInvalidChildren">this</a> for more information.',
		'default' => 'y'
	),
	'htmlpure_disable_extern' => array(
		'label' => 'Disable External Links',
		'note' => 'Disables links to external websites which is effective against spam. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.DisableExternal">this</a> for more information.',
		'default' => 'n'
	),
	'htmlpure_disable_extern_res' => array(
		'label' => 'Disable External Resounces',
		'note' => 'Disables the embedding of external resource like images from other hosts. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.DisableExternalResources">this</a> for more information.',
		'default' => 'y'
	),
	'htmlpure_disable_res' => array(
		'label' => 'Disable All Resources',
		'note' => 'Disables the embedding of all resources preventing users from including pictures at all. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.DisableResources">this</a> for more information.',
		'default' => 'n'
	),
	'htmlpure_disable_uri' => array(
		'label' => 'Disable all URIs',
		'note' => 'Disables all URIs in all forms within submitted content. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.Disable">this</a> for more information.',
		'default' => 'n'
	),
	'htmlpure_use_redirect' => array(
		'label' => 'Use Redirect',
		'note' => 'Uses the redirect service in the Redirect URI. This can be handy to track clicks out and prevent leacks of PageRank. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.Munge">this</a> for more information.',
		'default' => 'n'
	),
	'htmlpure_strict_html' => array(
		'label' => 'Force Strict',
		'note' => 'Determines if the purification matches the Transitional or Strict rule sets. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#HTML.Strict">this</a> for more information.',
		'default' => 'y'
	),
	'htmlpure_xhtml' => array(
		'label'  => 'Force XHTML',
		'note' => 'Determine if purification forces only XHTML tags or if it allows standard HTML.',
		'default' => 'y'
	),
	// TODO: We should parse the plugins directory to generate these
	// so that new plugins just have to be dropped in the dir and turned on.
	'htmlpure_allow_youtube' => array(
		'label' => 'Allow YouTube',
		'note' => 'Allow YouTube videos to be passed through.',
		'default' => 'n'
	),
);
$gBitSmarty->assign( 'htmlPurifier', $htmlPurifier );

if( !empty( $_REQUEST['apply'] )) {
	$formFeatures = array_merge( $htmlPurifier );
	foreach( $formFeatures as $item => $data ) {
		simple_set_toggle( $item, LIBERTY_PKG_NAME );
	}
	$errors = array();
	if( !empty($_REQUEST['blacklisted_html_tags'] )) {
	    $tags = preg_replace( '/\s/', '', $_REQUEST['blacklisted_html_tags'] );
		if( strlen( $tags ) > 250 ) {
			$tags = substr( $tags, 0, 250 );
			$errors['blacklist'] = 'The blacklisted tags list has been shortened. You can only have 250 characters for blacklisted tags.';
		}
		$gBitSystem->storeConfig('blacklisted_html_tags', $tags , LIBERTY_PKG_NAME );
	}
	$gBitSmarty->assign($errors);
}

$gBitSystem->display( 'bitpackage:liberty/filter_htmlpurifier.tpl', 'HTML Purifier' );
?>
