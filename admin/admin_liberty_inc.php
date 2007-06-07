<?php
$formLibertyCache = array(
	"liberty_cache_pages" => array(
		'label' => 'External page cache',
		'note' => 'Enabling this will download and cache external pages that are included.',
	),
	"liberty_cache_images" => array(
		'label' => 'External image cache',
		'note' => 'Enabling this will download and cache external images that are included.',
	),
);
$gBitSmarty->assign( 'formLibertyCache', $formLibertyCache );

$formLibertyFeatures = array(
	"liberty_display_status" => array(
		'label' => 'Display content status',
		'note' => 'Display a status selection menu when editing content (not fully developed, experimental feature).',
	),
	"liberty_allow_change_owner" => array(
		'label' => 'Allow Owner Change',
		'note' => 'Allow users with the proper permission to change the owner of content.',
	),
	"liberty_inline_diff" => array(
		'label' => 'Use Inline Diff',
		'note' => 'Uses PEAR Text_Wiki, which is installed with the command pear install Text_Wiki, for an inline diff of revisions instead of the line based unified diff tool. This is particularly useful when using a WYSIWYG editor which tends to put things all on one line. Note that this diff can take advantage of the PECL xdiff package for speed if you have that installed.',
	),
);

if( $gBitSystem->isPackageActive( 'quota' )) {
	$formLibertyFeatures['liberty_quota'] = array(
		'label' => 'Quota Usage System',
		'note' => 'Limit users\' disk usage.',
	);
}

if( $gBitSystem->isPackageActive( 'protector' )) {
	$formLibertyFeatures['protector_single_group'] = array(
		'label' => 'Protector System Single group per content item',
		'note' => 'Limit the protector system to asign a content item to a single group. The alternative allows content in multiple groups.',
	);
}
$gBitSmarty->assign( 'formLibertyFeatures', $formLibertyFeatures );

$formLibertyHtmlPurifierFeatures = array(
	'liberty_html_pure_escape_bad' => array(
		'label' => 'Escape invalid HTML',
		'note' => ' Escapes invlid HTML as text. Otherwise invalid HTML is silently dropped. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#Core.EscapeInvalidTags">this</a> and <a href="http://htmlpurifier.org/live/configdoc/plain.html#Core.EscapeInvalidChildren">this</a> for more information.',
		'default' => 'y'
	),
	'liberty_html_pure_disable_extern' => array(
		'label' => 'Disable External Links',
		'note' => 'Disables links to external websites which is effective against spam. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.DisableExternal">this</a> for more information.',
		'default' => 'n'
	),
	'liberty_html_pure_disable_extern_res' => array(
		'label' => 'Disable External Resounces',
		'note' => 'Disables the embedding of external resource like images from other hosts. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.DisableExternalResources">this</a> for more information.',
		'default' => 'y'
	),
	'liberty_html_pure_disable_res' => array(
		'label' => 'Disable All Resources',
		'note' => 'Disables the embedding of all resources preventing users from including pictures at all. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.DisableResources">this</a> for more information.',
		'default' => 'n'
	),
	'liberty_html_pure_disable_uri' => array(
		'label' => 'Disable all URIs',
		'note' => 'Disables all URIs in all forms within submitted content. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.Disable">this</a> for more information.',
		'default' => 'n'
	),
	'liberty_html_pure_use_redirect' => array(
		'label' => 'Use Redirect',
		'note' => 'Uses the redirect service in the Redirect URI. This can be handy to track clicks out and prevent leacks of PageRank. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#URI.Munge">this</a> for more information.',
		'default' => 'n'
	),
	'liberty_html_pure_strict_html' => array(
		'label' => 'Force Strict',
		'note' => 'Determines if the purification matches the Transitional or Strict rule sets. See <a href="http://htmlpurifier.org/live/configdoc/plain.html#HTML.Strict">this</a> for more information.',
		'default' => 'y'
	),
	'liberty_html_pure_xhtml' => array(
		'label'  => 'Force XHTML',
		'note' => 'Determine if purification forces only XHTML tags or if it allows standard HTML.',
		'default' => 'y'
	),
	// TODO: We should parse the plugins directory to generate these
	// so that new plugins just have to be dropped in the dir and turned on.
	'liberty_html_pure_allow_youtube' => array(
		'label' => 'Allow YouTube',
		'note' => 'Allow YouTube videos to be passed through.',
		'default' => 'n'
	),
	// not entirely sure where this should go. liberty plugins? here? some other tab?
	'liberty_use_span_wrapper' => array(
		'label'  => 'Use a span wrapper',
		'note' => 'Some plugins such as the attachment plugin wrap their output with a div. This might not work well when you are using a WYSYWIG such as TinyMCE or FCKeditor in combination with HTML Purifier. There are Pros and Cons to using either wrapper.',
		'default' => 'n'
	),
);
$gBitSmarty->assign( 'formLibertyHtmlPurifierFeatures', $formLibertyHtmlPurifierFeatures );

$formLibertyTextareaFeatures = array(
	"liberty_textarea_height" => array(
		'label' => 'Default Textarea Height',
		'note' => 'The default height of textareas.',
		'default' => '20',
	),
	"liberty_textarea_width" => array(
		'label' => 'Default Textarea Width',
		'note' => 'The default width of textareas.',
		'default' => '35',
	),
);
$gBitSmarty->assign( 'formLibertyTextareaFeatures', $formLibertyTextareaFeatures );

$attachmentStyleOptions = array(
	"standard" => tra( 'Standard attachment system<br /><small>Allows a single upload when content is saved.</small>' ),
	"multiple" => tra( 'Multiple attachments<br /><small>Allow multiple attachments in a single upload.</small>' ),
	"ajax"     => tra( 'Ajax attachments<br /><small>Allow Ajax attachments where attachment is made before save so attachment id can be used in current edit.</small>' ),
);
$gBitSmarty->assign( 'attachmentStyleOptions', $attachmentStyleOptions );


$cacheTimes = array(
	0      => tra( "(no cache)" ),
	600    => "10 ".tra( "minutes" ),
	1800   => "30 ".tra( "minutes" ),
	3600   => "1 ".tra( "hour" ),
	7200   => "2 ".tra( "hours" ),
	14400  => "4 ".tra( "hours" ),
	28800  => "8 ".tra( "hours" ),
	86400  => "1 ".tra( "day" ),
	604800 => "1 ".tra( "week" ),
);
$gBitSmarty->assign( "cacheTimes", $cacheTimes );


$formCaptcha = array(
	"liberty_use_captcha_freecap" => array(
		'label' => 'Use Freecap',
		'note' => 'Use the Freecap captcha library to create the captcha. This produces nicer and better images than the bitweaver internal captcha method.',
	),
	"freecap_use_dict" => array(
		'label' => 'Use Dictionary',
		'note' => 'Freecap can use real words or use random letters. Random letters are more difficult to decipher for humans as well as for bots.',
	),
);
$gBitSmarty->assign( 'formCaptcha', $formCaptcha );

$formImageFeatures = array(
	"liberty_jpeg_originals" => array(
		'label' => 'JPEG Originals',
		'note' => 'Automatically create JPEG versions of original images named \'original.jpg\' in the attachment directory with other thumbnails.',
		'page' => '',
	),
	"liberty_png_thumbnails" => array(
		'label' => 'PNG Thumbnails',
		'note' => 'Automatically generated thumbnails of original images will be saved as PNG images instead of JPEGs. This will preserve transparency in GIF and PNG uploads but such transparency is not well supported in MSIE 6. MSIE 7 mostly works now as do most other browsers.',
		'page' => '',
	),
);
$gBitSmarty->assign( 'formImageFeatures', $formImageFeatures );

$formValues = array( 'image_processor', 'liberty_attachment_link_format', 'comments_per_page', 'comments_default_ordering', 'comments_default_display_mode' );

if( !empty( $_REQUEST['change_prefs'] )) {
	$errors = array();
	$formFeatures = array_merge( $formLibertyCache, $formLibertyFeatures, $formImageFeatures, $formCaptcha, $formLibertyHtmlPurifierFeatures );
	foreach( $formFeatures as $item => $data ) {
		simple_set_toggle( $item, LIBERTY_PKG_NAME );
	}
	foreach( $formLibertyTextareaFeatures as $item => $data ) {
		simple_set_value( $item, LIBERTY_PKG_NAME );
	}
	simple_set_value('liberty_html_purifier', LIBERTY_PKG_NAME );
	simple_set_value( 'liberty_attachment_style', LIBERTY_PKG_NAME );
	$gBitSystem->storeConfig('liberty_cache', $_REQUEST['liberty_cache'], LIBERTY_PKG_NAME );
	$gBitSystem->storeConfig('liberty_auto_display_attachment_thumbs', $_REQUEST['liberty_auto_display_attachment_thumbs'], LIBERTY_PKG_NAME );

	if( !empty($_REQUEST['blacklisted_html_tags'] )) {
	    $tags = preg_replace( '/\s/', '', $_REQUEST['blacklisted_html_tags'] );
		if( strlen( $tags ) > 250 ) {
			$tags = substr( $tags, 0, 250 );
			$errors['blacklist'] = 'The blacklisted tags list has been shortened. You can only have 250 characters for blacklisted tags.';
		}
		$gBitSystem->storeConfig('blacklisted_html_tags', $tags , LIBERTY_PKG_NAME );
	}
	if( $_REQUEST['approved_html_tags'] != DEFAULT_ACCEPTABLE_TAGS ) {
		$tags = preg_replace( '/\s/', '', $_REQUEST['approved_html_tags'] );
		$lastAngle = strrpos( $tags, '>' ) + 1;
		if( strlen( $tags ) > 250 || ($lastAngle < strlen( $tags ))) {
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

$thumbSizes[''] = tra( 'Off' );
foreach( array_keys( $gThumbSizes ) as $thumb ) {
	$thumbSizes[$thumb] = tra( ucfirst( $thumb ));
}
$gBitSmarty->assign( 'thumbSizes', $thumbSizes );

$tags = $gBitSystem->getConfig( 'approved_html_tags', DEFAULT_ACCEPTABLE_TAGS );

$gBitSmarty->assign( 'approved_html_tags', $tags );
?>
