<?php
/**
 * @version  $Header$
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERHTMLPURIFIER', 'filterhtmlpure' );

global $gLibertySystem;

$pluginParams = array (
	// plugin title
	'title'                    => 'HTMLPurifier',
	// help page on bitweaver org that explains this plugin
	'help_page'                => 'HTMLPurifier',
	// brief description of the plugin
	'description'              => 'Uses <a href="http://htmlpurifier.org">HTMLPurifier</a> to cleanup the HTML submitted to your site and ensure that it is standards compliant and does not contain anything malicious. It is also used to ensure that the various places that input is split for previews does not cause bad markup to break the page. This filter is <strong>highly</strong> recommended if you are allowing HTML but is still good for sites that are not using thse formats for the ability to cleanup markup which has been split for preview properly though this may disable certain plugins that insert non standards compliant code.',
	// should this plugin be active or not when loaded for the first time
	'auto_activate'            => FALSE,
	// type of plugin
	'plugin_type'              => FILTER_PLUGIN,
	// url to page with options for this plugin
	'plugin_settings_url'      => LIBERTY_PKG_URL.'admin/plugins/filter_htmlpurifier.php',

	// various filter functions and when they are called
	// called before the data is parsed
	//	'pre_function'       => 'htmlpure_filter',
	// called after the data has been parsed
	'preparse_function'  => 'htmlpure_filter',
	// called before the data is parsed if there is a split
	//	'presplit_function'  => 'htmlpure_filter',
	// called after the data has been parsed if there is a split
	'postsplit_function' => 'htmlpure_filter',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERHTMLPURIFIER, $pluginParams );

function htmlpure_filter( &$pString, &$pFilterHash, $pObject ) {
	global $gHtmlPurifier, $gBitSystem;

	if (!isset($gHtmlPurifier)) {
		$pear_version = false;

		if (@include_once("PEAR.php")) {		
			if(@include_once("HTMLPurifier.php")) {
				// for backward compatibility checks
				$htmlp_version = NULL;

				// If using 3.10+
				if(!class_exists("HTMLPurifier_Config")) {
					@include_once("HTMLPurifier.auto.php");
					$auto_config = true;
					$htmlp_version = 3.1;
				}

				$config = htmlpure_getDefaultConfig( $htmlp_version, $pObject );


				// As suggested here:  http://www.bitweaver.org/forums/index.php?t=8554
				$gHtmlPurifier = new HTMLPurifier($config);

				// how plugins are registered changed in v3.1 
				// old way of adding plugins before v3.1
				if ( !$htmlp_version >= 3.1 ) {
					htmlpure_legacyAddFilters();
				}
			}
		}
	}

	// Did we manage to create one?
	if (isset($gHtmlPurifier)) { 
		/* Clean up the paragraphs a bit */
		//		$start = $pData;
		$pString = htmlpure_cleanupPeeTags($pString);
		//		$pee = $pString;
		//		$pString = html_entity_decode( $pString );
		if( empty( $pFilterHash['htmlp_config'] ) ){
			$pString = $gHtmlPurifier->purify( $pString );
		}else{
			$htmlp_version = $gHtmlPurifier->version;
			$config = htmlpure_getDefaultConfig( $htmlp_version, $pObject );

			/* if we've received custom configurations for the particular parse then we deal with them
			   for now were expecting config data that htmlpurfier doesn't really handle in a nice way
			   so we stuff it into the 'info' hash under a 'bitweaver' name space.

			   @TODO ideally this might also look for native htmlpurifier config values in the keys and
			   then adjust as necessary which is why $config is passed in here. -wjames5
			  */
			foreach( $pFilterHash['htmlp_config'] as $key => $val ){
				$config->def->info['bitweaver'][$key] = $val;
			}

			$pString = $gHtmlPurifier->purify( $pString, $config );
		}

		// If we have another parse step they may be escaping
		// entities so change quotes back.
		if (empty($pFilterHash['format_guid']) || 
		    $pFilterHash['format_guid'] != 'bithtml') {
		  $pString = preg_replace('|&quot;|', '"', $pString);
		  $pString = preg_replace('|&#039;|', "'", $pString);
		}

		/*
		echo "<br/><hr/><br/>".$start;
		include_once( 'Text/Diff.php' );
		include_once( 'Text/Diff/Renderer/inline.php' );
		$diff = &new Text_Diff(explode("\n", $start), explode("\n",$pee));
		$renderer = &new Text_Diff_Renderer_inline();
		echo "<br/><hr/><br/>". $renderer->render($diff);

		echo "<br/><hr/><br/>".$pString;
		include_once( 'Text/Diff.php' );
		include_once( 'Text/Diff/Renderer/inline.php' );
		$diff = &new Text_Diff(explode("\n", $pee), explode("\n",$pString));
		$renderer = &new Text_Diff_Renderer_inline();
		echo "<br/><hr/><br/>". $renderer->render($diff);
		 */
	} else {
		bit_error_log("HTMLPurifier not installed. Install with: pear channel-discover htmlpurifier.org; pear install hp/HTMLPurifier;");
	}

	return $pString;
}

function htmlpure_getDefaultConfig( &$htmlp_version, $pObject=NULL ){
	global $gBitSystem;

	$config = HTMLPurifier_Config::createDefault();
	// Necessary setup for custom configuration I think. http://htmlpurifier.org/docs/enduser-customize.html
	//$config->set( 'HTML.DefinitionID', STORAGE_PKG_PATH );
	//$config->set('HTML.DefinitionRev', 1);
	//$config->set('Cache.DefinitionImpl', null); // remove this later!


	// Set the cache path
	$config->set('Cache.SerializerPath', rtrim( TEMP_PKG_PATH, '/' ) );

	if ($gBitSystem->getConfig('htmlpure_escape_bad', 'y') == 'y') {
		$config->set('Core.EscapeInvalidTags', true);
		$config->set('Core.EscapeInvalidChildren', true);
	}
	if ($gBitSystem->getConfig('htmlpure_use_redirect') == 'y') {
		$config->set('URI.Munge', LIBERTY_PKG_URL.'redirect.php?q=%s');
	}
	if ($gBitSystem->getConfig('htmlpure_strict_html', 'y') == 'y') {
		$config->set('HTML.Strict', true);
	}
	if ($gBitSystem->getConfig('htmlpure_xhtml', 'n') == 'n') {
		$config->set('HTML.XHTML', true);
	}

	$hasAdmin = FALSE;
	if( is_a( $pObject, 'LibertyContent' ) ) {
		// check to see if last editor has ability to admin content, if so, ease up on the purification restraints
		if( $gBitSystem->isPackageActive( 'protector' )) {
			$query = "SELECT urp.`role_id` 
					  FROM `".BIT_DB_PREFIX."users_roles_map` urm 
						INNER JOIN `".BIT_DB_PREFIX."users_role_permissions` urp ON (urp.`role_id`=urm.`role_id`) 
					  WHERE urm.`user_id`=? AND (urp.`perm_name`=? OR urp.`perm_name`='p_admin')";
		} else {
			$query = "SELECT ugp.`group_id` 
				  FROM `".BIT_DB_PREFIX."users_groups_map` ugm 
					INNER JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON (ugp.`group_id`=ugm.`group_id`) 
				  WHERE ugm.`user_id`=? AND (ugp.`perm_name`=? OR ugp.`perm_name`='p_admin')";
		}
		// cache for 15 minutes
		$hasAdmin = $pObject->mDb->getOne( $query, array( $pObject->getField( 'modifier_user_id' ), $pObject->mAdminContentPerm ), NULL, NULL, 900 );
	}

	if( $hasAdmin ) {
		// Last person to edit this file has admin permission for this entire class of content, let freedom ring
		$config->set( 'CSS.AllowTricky', true );

		$css = $config->getCSSDefinition();
        $css->info['position'] = new HTMLPurifier_AttrDef_CSS_Composite(array( new HTMLPurifier_AttrDef_Enum(array('absolute', 'fixed', 'relative', 'static', 'inherit')) ) );
        $css->info['top'] = new HTMLPurifier_AttrDef_CSS_Composite(array( new HTMLPurifier_AttrDef_CSS_Length()));
        $css->info['left'] = new HTMLPurifier_AttrDef_CSS_Composite(array( new HTMLPurifier_AttrDef_CSS_Length()));
        $css->info['bottom'] = new HTMLPurifier_AttrDef_CSS_Composite(array( new HTMLPurifier_AttrDef_CSS_Length()));
        $css->info['right'] = new HTMLPurifier_AttrDef_CSS_Composite(array( new HTMLPurifier_AttrDef_CSS_Length()));
//$def =& $config->getHTMLDefinition();
//$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
	} else {
		if ($gBitSystem->getConfig('htmlpure_disable_extern') == 'y') {
			$config->set('URI.DisableExternal', true);
		}
		if ($gBitSystem->getConfig('htmlpure_disable_extern_res', 'y') == 'y') {
			$config->set('URI.DisableExternalResources', true);
		}
		if ($gBitSystem->getConfig('htmlpure_disable_res') == 'y') {
			$config->set('URI.DisableResources', true);
		}
		if ($gBitSystem->getConfig('htmlpure_disable_uri') == 'y') {
			$config->set('URI.Disable', true);
		}

		// Set that we are using a div to wrap things.
		$config->set('HTML.BlockWrapper', 'div');

		// set plugins
		// TODO: devise a way to parse plugins dir
		// and check for the right property here
		// so new plugins are just drop in place.
		if ( $htmlp_version >= 3.1 ){
			$custom_filters = array();

			// Disable included YouTube filter, we have our own
			$config->set('Filter.YouTube', false);

			if ($gBitSystem->isFeatureActive('htmlpure_allow_youtube')) {
				require_once(UTIL_PKG_INCLUDE_PATH.'htmlpure/Filter/YouTube.php');
				$custom_filters[] = new HTMLPurifier_Filter_YouTube();
			}
			if ($gBitSystem->isFeatureActive('htmlpure_allow_cnbc')) {
				require_once(UTIL_PKG_INCLUDE_PATH.'htmlpure/Filter/CNBC.php');
				$custom_filters[] = new HTMLPurifier_Filter_CNBC();
			}

			if( !empty( $custom_filters ) ){
				$config->set('Filter.Custom', $custom_filters );
			}
		}

		$blacklistedTags = $gBitSystem->
			getConfig('blacklisted_html_tags', '');

		$def = $config->getHTMLDefinition();
		// HTMLPurifier doesn't have a blacklist feature. Duh guys!
		// Note that this has to come last since the other configs
		// may tweak the def.
		foreach (explode(',',$blacklistedTags) as $tag) {
			unset($def->info[$tag]);
		}

		if ($gBitSystem->getConfig('htmlpure_force_nofollow', 'y') == 'y') {
			if( !class_exists("HTMLPurifier_AttrTransform_ForceValue") ){
				class HTMLPurifier_AttrTransform_ForceValue extends HTMLPurifier_AttrTransform
				{
					var $name, $value;
					function HTMLPurifier_AttrTransform_ForceValue($name, $value) {
						$this->name  = $name;
						$this->value = $value;
					}
					function transform($attr, $config, $context) {
						$attr[$this->name] = $this->value;
						return $attr;
					}
				}
			}
			$def->info['a']->attr_transform_post['rel'] = new HTMLPurifier_AttrTransform_ForceValue('rel', 'nofollow');
		}
	}

	return $config;
}

function htmlpure_legacyAddFilters(){
	global $gHtmlPurifier, $gBitSystem;

	if ( $gBitSystem->isFeatureActive('htmlpure_allow_youtube') ) {
		require_once(UTIL_PKG_INCLUDE_PATH.'htmlpure/Filter/YouTube.php');

		$gHtmlPurifier->addFilter(new HTMLPurifier_Filter_YouTube());
	}
	if ($gBitSystem->isFeatureActive('htmlpure_allow_cnbc')) {
		require_once(UTIL_PKG_INCLUDE_PATH.'htmlpure/Filter/CNBC.php');
		$gHtmlPurifier->addFilter(new HTMLPurifier_Filter_CNBC());
	}
}

function htmlpure_cleanupPeeTags( $pee ) {

	// Convert us some form feeds for better cross platform support
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee);

	// Strip out lots of duplicate newlines now
	$pee = preg_replace("#\n\n+#", "\n\n", $pee);

	// Pee in block quotes - Removed as we now have purifier insert a div instead. See above.
	//	$pee = preg_replace('#<blockquote(.*?(?:[^>]*))>(.*?)</blockquote>#s', '<blockquote$1><p>$2</p></blockquote>', $pee);

	// Strip empty pee
	$pee = preg_replace('#<p>\s*</p>#', '', $pee);

	// Unpee pre blocks
	$pee = preg_replace('#(<pre.*?(?:[^>]*)>)(.*?)</pre>#si',
		" '$1' . preg_replace('#<br[\s/]*(?:[^>]*)/>#', '"."\n"."',
		preg_replace('#<p[\s]*(?:[^>]*)>#', '"."\n"."',
		preg_replace('#</p[\s]*(?:[^>]*)>#', '', '$2'))). '</pre>'", $pee);

	// Fixup align divs so we can keep them.
	$pee = preg_replace('#<div(.*?)align="(.*?)"(.*?)>#', '<div$1style="text-align:$2;"$3>', $pee);

	return $pee;
}

?>
