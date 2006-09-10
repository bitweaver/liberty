<?php
$formLibertyFeatures = array(
	"liberty_cache_pages" => array(
		'label' => 'External page cache',
		'note' => 'Enabling this will download and cache external pages that are included.',
	),
	"liberty_cache_images" => array(
		'label' => 'External image cache',
		'note' => 'Enabling this will download and cache external images that are included.',
	),
);
if( $gBitSystem->isPackageActive( 'quota' ) ) {
	$formLibertyFeatures['liberty_quota'] = array(
		'label' => 'Quota Usage System',
		'note' => 'Limit users\' disk usage.',
		'page' => '',
	);
}
$gBitSmarty->assign( 'formLibertyFeatures', $formLibertyFeatures );

$formImageFeatures = array(
	"liberty_jpeg_originals" => array(
		'label' => 'JPEG Originals',
		'note' => 'Automatically create JPEG versions of original images named \'original.jpg\' in the attachment directory with other thumbnails.',
		'page' => '',
	),
);
$gBitSmarty->assign( 'formImageFeatures', $formImageFeatures );

$formValues = array( 'image_processor', 'liberty_attachment_link_format', 'comments_per_page', 'comments_default_ordering', 'comments_default_display_mode' );

if( !empty( $_REQUEST['change_prefs'] ) ) {
	$errors = array();
	$formFeatures = array_merge( $formLibertyFeatures, $formImageFeatures );
	foreach( $formFeatures as $item => $data ) {
		simple_set_toggle( $item, LIBERTY_PKG_NAME );
	}

	$gBitSystem->storeConfig('liberty_cache', $_REQUEST['liberty_cache'] );
	$gBitSystem->storeConfig('liberty_auto_display_attachment_thumbs', $_REQUEST['liberty_auto_display_attachment_thumbs'] );

	if( $_REQUEST['approved_html_tags'] != DEFAULT_ACCEPTABLE_TAGS ) {
		$tags = preg_replace( '/\s/', '', $_REQUEST['approved_html_tags'] );
		$lastAngle = strrpos( $tags, '>' ) + 1;
		if( strlen( $tags ) > 250 || ($lastAngle < strlen( $tags ) ) ) {
			$tags = substr( $tags, 0, 250 );
			$tags = substr( $tags, 0, $lastAngle );
			$errors['warning'] = 'The approved tags list has been shortened. You can only have 250 characters for approved tags.';
		}
		$gBitSystem->storeConfig('approved_html_tags', $tags , LIBERTY_PKG_NAME );
	}
	$gBitSmarty->assign_by_ref( 'errors', $errors );

	foreach( $formValues as $item ) {
		simple_set_value( $item, LIBERTY_PKG_NAME );
	}
}

$gBitSmarty->assign( 'thumbSizes', array( '' => 'Off', 'icon'=>tra('Icon'), 'avatar'=>tra('Avatar'), 'small'=>tra('Small'), 'medium'=>tra('Medium'), 'large'=>tra('Large') ) );

$tags = $gBitSystem->getConfig( 'approved_html_tags', DEFAULT_ACCEPTABLE_TAGS );

$gBitSmarty->assign( 'approved_html_tags', $tags );
?>
