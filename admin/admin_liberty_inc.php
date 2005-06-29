<?php
$formLibertyFeatures = array(
	"liberty_quota" => array(
		'label' => 'Quota Usage System',
		'note' => 'Limit users\' disk usage.',
		'page' => '',
	),
	"liberty_auto_display_attachment_thumbs" => array(
		'label' => 'Auto-Display Attachment Thumbnails',
		'note' => 'This will automatically display thumbnails of all attachments of a given page (usually in the top right corner of the page). You can still display the items inline as well.',
		'page' => '',
	),
);

if( $gBitSystem->isPackageActive( 'tinymce' ) ) {
	$formLibertyFeatures["tinymce_ask"] = array(
		'label' => 'WYSIWYG confirmation',
		'note' => 'Ask before using the WYSIWYG editor tinymce when clicking on a textarea. If you disable this feature, we strongly suggest you enable HTML content format as the only content format and also disable quicktags.',
		'page' => '',
	);
}
$smarty->assign('formLibertyFeatures', $formLibertyFeatures);

$formCommentFeatures = array(
	"comments_reorganise_page_layout" => array(
		'label' => 'Position Comments at top of page',
		'note' => 'When posting a comment, comments are moved to the top of the page. This can be very disorienting and is only recommended when your site uses comments extensively.',
		'page' => '',
	),
	"comments_display_option_bar" => array(
		'label' => 'Display Comments option bar',
		'note' => 'Display an option bar above comments to specify how they should be sorted and how they should be displayed. Useful if your site uses comments extensively.',
		'page' => '',
	),
);
$smarty->assign('formCommentFeatures', $formCommentFeatures);

if (!empty($_REQUEST['change_prefs'])) {
	$errors = array();
	$formFeatures = array_merge( $formLibertyFeatures, $formCommentFeatures );
	foreach( $formFeatures as $item => $data ) {
		simple_set_toggle( $item );
	}

	if( $_REQUEST['approved_html_tags'] != DEFAULT_ACCEPTABLE_TAGS ) {
		$tags = preg_replace( '/\s/', '', $_REQUEST['approved_html_tags'] );
		$lastAngle = strrpos( $tags, '>' ) + 1;
		if( strlen( $tags ) > 250 || ($lastAngle < strlen( $tags ) ) ) {
			$tags = substr( $tags, 0, 250 );
			$tags = substr( $tags, 0, $lastAngle );
			$errors['warning'] = 'The approved tags list has been shortened. You can only have 250 characters for approved tags.';
		}
		
		$gBitSystem->storePreference('approved_html_tags', $tags , LIBERTY_PKG_NAME );
	}
	$smarty->assign_by_ref( 'errors', $errors );

	$gBitSystem->storePreference('image_processor', (!empty( $_REQUEST['image_processor'] ) ? $_REQUEST['image_processor'] : NULL ) , LIBERTY_PKG_NAME );
	$gBitSystem->storePreference('liberty_attachment_link_format', ( !empty( $_REQUEST['liberty_auto_display_attachment_thumbs'] ) ? $_REQUEST['liberty_auto_display_attachment_thumbs'] : NULL ), LIBERTY_PKG_NAME );
}

$tags = $gBitSystem->getPreference( 'approved_html_tags', DEFAULT_ACCEPTABLE_TAGS );

$smarty->assign('approved_html_tags', $tags );
?>
