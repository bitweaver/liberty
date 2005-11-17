<?php
/**
 * @version  $Revision: 1.2.2.25 $
 * @package  liberty
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_TIKIWIKI', 'tikiwiki' );
define( 'WIKI_WORDS_REGEX', '[A-z0-9]{2}[\w\d_\-]+[A-Z_][\w\d_\-]+[A-z0-9]+' );

/**
 * @package  liberty
 * @subpackage plugins_format
 */
$pluginParams = array ( 'store_function' => 'tikiwiki_save_data',
						'load_function' => 'tikiwiki_parse_data',
						'verify_function' => 'tikiwiki_verify_data',
						'rename_function' => 'tikiwiki_rename',
						'expunge_function' => 'tikiwiki_expunge',
						'description' => 'TikiWiki Syntax Format Parser',
						'edit_label' => 'Tiki Wiki Syntax',
						'edit_field' => '<input type="radio" name="format_guid" value="'.PLUGIN_GUID_TIKIWIKI.'"',
						'help_page' => 'TikiWikiSyntax',
						'plugin_type' => FORMAT_PLUGIN
					  );

$gLibertySystem->registerPlugin( PLUGIN_GUID_TIKIWIKI, $pluginParams );

/**
 * tikiwiki_save_data
 */
function tikiwiki_save_data( &$pParamHash ) {
	static $parser;
	if( empty( $parser ) ) {
		$parser = new TikiWikiParser();
	}
	if( $pParamHash['edit'] ) {
		$parser->storeLinks( $pParamHash );
	}
}

function tikiwiki_verify_data( &$pParamHash ) {
	$errorMsg = NULL;

	// Removed htmlspecialchars conversion as it permantenly modifies the orginal source. calling htmlentities on parse now.
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
	return( $errorMsg );
}

function tikiwiki_expunge( $pContentId ) {
	$parser = new TikiWikiParser();
	$parser->expungeLinks( $pContentId );
}

function tikiwiki_rename( $pContentId, $pOldName, $pNewName, &$pCommonObject ) {
	$query = "SELECT `from_content_id`, `data`
			  FROM `".BIT_DB_PREFIX."tiki_links` tl
				INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON( tl.`from_content_id`=tc.`content_id` )
			  WHERE `to_content_id` = ?";
	if( $result = $pCommonObject->mDb->query($query, array( $pContentId ) ) ) {
		while( !$result->EOF ) {
			$data = preg_replace( '/(\W|\(\()('.$pOldName.')(\W|\)\))/', '\\1'.$pNewName.'\\3', $result->fields['data'] );
			if( md5( $data ) != md5( $result->fields['data'] ) ) {
				$query = "UPDATE `".BIT_DB_PREFIX."tiki_content` SET `data`=? WHERE `content_id`=?";
				$pCommonObject->mDb->query($query, array( $data, $result->fields['from_content_id'] ) );
			}
			$result->MoveNext();
		}
	}
}

function tikiwiki_parse_data( &$pData, &$pCommonObject ) {
	static $parser;
	if( empty( $parser ) ) {
		$parser = new TikiWikiParser();
	}
	return $parser->parse_data( $pData, $pCommonObject );
}

/**
 * TikiWikiParser
 *
 * @package kernel
 */
class TikiWikiParser extends BitBase {
	var $mWikiWordRegex;
	var $mUseWikiWords;
	var $mPageLookup;
	var $pre_handlers = array();
	var $pos_handlers = array();

	function TikiWikiParser () {
		BitBase::BitBase();

		global $gBitSystem;
		$this->mUseWikiWords = $gBitSystem->getPreference( 'feature_wikiwords' ) == 'y';

		// Setup the WikiWord regex
	    $wiki_page_regex = $gBitSystem->getPreference( 'wiki_page_regex', 'strict' );
		// Please DO NOT modify any of the brackets in the regex(s).
		// It may seem redundent but, really, they are ALL REQUIRED.
		if ($wiki_page_regex == 'strict') {
			$this->mWikiWordRegex = '([A-Za-z0-9_])([\.: A-Za-z0-9_\-])*([A-Za-z0-9_])';
		} elseif ($wiki_page_regex == 'full') {
			$this->mWikiWordRegex = '([A-Za-z0-9_]|[\x80-\xFF])([\.: A-Za-z0-9_\-]|[\x80-\xFF])*([A-Za-z0-9_]|[\x80-\xFF])';
		} else {
			// This is just evil. The middle section means "anything, as long
			// as it's not a | and isn't followed by ))". -rlpowell
			$this->mWikiWordRegex = '([^|\(\)])([^|](?!\)\)))*?([^|\(\)])';
		}

	}


	function add_pre_handler($name) {
		if (!in_array($name, $this->pre_handlers)) {
		$this->pre_handlers[] = $name;
		}
	}

	function add_pos_handler($name) {
		if (!in_array($name, $this->pos_handlers)) {
			$this->pos_handlers[] = $name;
		}
	}


	function extractWikiWords( &$data ) {
		if( $this->mUseWikiWords ) {
			preg_match_all("/\(\(($this->mWikiWordRegex)\)\)/", $data, $words2);
			preg_match_all("/\(\(($this->mWikiWordRegex)\|(.+?)\)\)/", $data, $words3);
			preg_match_all( '/\b('.WIKI_WORDS_REGEX.')\b/', $data, $words );
			$words = array_unique(array_merge($words[1], $words2[1], $words3[1]));
		} else {
			preg_match_all("/\(\(($this->mWikiWordRegex)\)\)/", $data, $words);
			preg_match_all("/\(\(($this->mWikiWordRegex)\|(.+?)\)\)/", $data, $words2);
			$words = array_unique(array_merge($words[1], $words2[1]));
		}
		return $words;
	}


	function storeLinks( &$pParamHash ) {
		global $gBitSystem;
		if (!$gBitSystem->isPackageActive( 'wiki') )
			return;
		$links_already_inserted_table = array();
		if( !empty( $pParamHash['content_id'] ) ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_links` WHERE `from_content_id`=?";
			$result = $this->mDb->query( $query, array( $pParamHash['content_id'] ) );

			$linkPages = $this->extractWikiWords( $pParamHash['edit'] );
			if( is_array( $linkPages ) && count( $linkPages ) ) {
				foreach( $linkPages as $page ) {
					if( !empty( $page ) ) {
// SPIDERFKILL - this query is guaranteed to die - i forget where it came from and why it's here. will debug soon enough...
						$query = "SELECT tp.`content_id` FROM `".BIT_DB_PREFIX."tiki_pages` tp INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON (tc.`content_id` = tp.`content_id`) WHERE tc.`title`=?";
						$result = $this->mDb->query( $query, array( $page ) );
						if( $result->numRows() ) {
							$res = $result->fetchRow();
							$key = $pParamHash['content_id'] . "-" . $res['content_id'];
							if (empty($links_already_inserted_table[$key])) {
								$query = "insert into `".BIT_DB_PREFIX."tiki_links`(`from_content_id`,`to_content_id`) values(?, ?)";
								$result = $this->mDb->query($query, array( $pParamHash['content_id'], $res['content_id'] ) );
							}
							$links_already_inserted_table[$key] = 1;
						}
					}
				}
			}
		}
	}

	function expungeLinks( $pContentId ) {
		if( !empty( $pContentId ) ) {
			$this->mDb->StartTrans();
			$this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."tiki_links` WHERE from_content_id=? OR to_content_id=?", array( $pContentId, $pContentId ) );
			$this->mDb->CompleteTrans();
		}
	}

	// Use tiki_links to get all the existing links in a single query
	function pageExists( $pTitle, $pContentId, $pCommonObject ) {
		$pTitle = strtolower( $pTitle );
		if( !empty( $pContentId ) ) {
			if( empty( $this->mPageLookup ) ) {
				$query = "SELECT LOWER( tc.`title` ) AS `hash_key`, `page_id`, tc.`content_id`, `description`, tc.`last_modified`, tc.`title`
						  FROM `".BIT_DB_PREFIX."tiki_links` tl
						  	INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON( tl.`to_content_id`=tc.`content_id` )
						  	INNER JOIN `".BIT_DB_PREFIX."tiki_pages` tp ON( tp.`content_id`=tc.`content_id` )
						  WHERE tl.`from_content_id`=? ORDER BY tc.`title`";
				if( $result = $this->mDb->query( $query, array( $pContentId ) ) ) {
					$lastTitle = '';
					while( !$result->EOF ) {
						if( $result->fields['title'] == $lastTitle ) {
// TODO - need to check ensure that tiki_links duplicate are properly inserted - spiderr
						}
						$this->mPageLookup[$result->fields['hash_key']][] = $result->fields;
						$lastTitle = $result->fields['title'];
						$result->MoveNext();
					}
				}
			}
			if( !isset( $this->mPageLookup[$pTitle] ) ) {
				$this->mPageLookup[$pTitle] = $pCommonObject->pageExists( $pTitle );
				if( !empty( $this->mPageLookup[$pTitle] ) && ( count( $this->mPageLookup[$pTitle] ) == 1 ) ) {
//  					$this->mDb->query( "INSERT INTO `".BIT_DB_PREFIX."tiki_links` ( `from_content_id`, `to_content_id` ) VALUES ( ?, ? )" , array( $pContentId, $this->mPageLookup[$pTitle][0]['content_id'] ) );
				}
			}
		}
$this->debug(0);
		return( !empty( $this->mPageLookup[$pTitle] ) ? $this->mPageLookup[$pTitle] : NULL );
	}

	function parse_data_raw($data) {
		$data = $this->parseData($data);

		$data = str_replace( WIKI_PKG_URL."index", WIKI_PKG_URL."index_raw", $data );
		return $data;
	}


	// This recursive function handles pre- and no-parse sections and plugins
	function parse_first(&$data, &$preparsed, &$noparsed) {
		global $gLibertySystem;
		$this->parse_pp_np($data, $preparsed, $noparsed);
		// Handle pre- and no-parse sections
		parse_data_plugins( $data, $preparsed, $noparsed, $this );
	}


	// AWC ADDITION
	// This function replaces pre- and no-parsed sections with unique keys
	// and saves the section contents for later reinsertion.
	function parse_pp_np(&$data, &$preparsed, &$noparsed) {
		// Find all sections delimited by ~pp~ ... ~/pp~
		// and replace them in the data stream with a unique key
		preg_match_all("/\~pp\~((.|\n)*?)\~\/pp\~/", $data, $preparse);
		if( count( $preparse[0] ) ) {
			foreach (array_unique($preparse[1])as $pp) {
				$key = md5(BitSystem::genPass());

				$aux["key"] = $key;
				$aux["data"] = $pp;
				$preparsed[] = $aux;
				$data = str_replace("~pp~$pp~/pp~", $key, $data);
			}

			// Temporary remove <pre> tags too
			// TODO: Is this a problem if user insert <PRE> but after parsing
			//	   will get <pre> (lowercase)?? :)
			preg_match_all("/(<[Pp][Rr][Ee]>)((.|\n)*?)(<\/[Pp][Rr][Ee]>)/", $data, $preparse);
			$idx = 0;

			foreach (array_unique($preparse[2])as $pp) {
				$key = md5(BitSystem::genPass());

				$aux["key"] = $key;
				$aux["data"] = $pp;
				$preparsed[] = $aux;
				$data = str_replace($preparse[1][$idx] . $pp . $preparse[4][$idx], $key, $data);
				$idx = $idx + 1;
			}
		}

		if( preg_match("/\~np\~((.|\n)*?)\~\/np\~/", $data, $preparse) ) {
			// Find all sections delimited by ~np~ ... ~/np~
			$new_data = '';
			$nopa = '';
			$state = true;
			$skip = false;

			$dlength=strlen($data);
			for ($i = 0; $i < $dlength; $i++) {
				$tag5 = substr($data, $i, 5);
				$tag4 = substr($tag5, 0, 4);
				$tag1 = substr($tag4, 0, 1);

				// Beginning of a noparse section found
				if ($state && $tag4 == '~np~') {
					$i += 3;
					$state = false;
					$skip = true;
				}

				// Termination of a noparse section found
				if (!$state && ($tag5 == '~/np~')) {
					$state = true;
					$i += 4;
					$skip = true;
					$key = md5(BitSystem::genPass());
					$new_data .= $key;
					$aux["key"] = $key;
					$aux["data"] = $nopa;
					$noparsed[] = $aux;
					$nopa = '';
				}

				if (!$skip) { // This character is not part of a noparse tag
					if ($state) { // This character is not within a noparse section
					$new_data .= $tag1;
					} else { // This character is within a noparse section
					$nopa .= $tag1;
					}
				} else { // Tag is now skipped over
					$skip = false;
				}
			}
			$data = $new_data;
		}
	}


	// This function handles wiki codes for those special HTML characters
	// that textarea won't leave alone.
	function parse_htmlchar(&$data) {
		// cleaning some user input
		$data = preg_replace("/&(?!([a-z]{1,7};))/", "&amp;", $data);

		// oft-used characters (case insensitive)
		$data = preg_replace("/~bs~/i", "&#92;", $data);
		$data = preg_replace("/~hs~/i", "&nbsp;", $data);
		$data = preg_replace("/~amp~/i", "&amp;", $data);
		$data = preg_replace("/~ldq~/i", "&ldquo;", $data);
		$data = preg_replace("/~rdq~/i", "&rdquo;", $data);
		$data = preg_replace("/~lsq~/i", "&lsquo;", $data);
		$data = preg_replace("/~rsq~/i", "&rsquo;", $data);
		$data = preg_replace("/~c~/i", "&copy;", $data);
		$data = preg_replace("/~--~/", "&mdash;", $data);
		$data = preg_replace("/ -- /", " &mdash; ", $data);
		$data = preg_replace("/~lt~/i", "&lt;", $data);
		$data = preg_replace("/~gt~/i", "&gt;", $data);

		// HTML numeric character entities
		$data = preg_replace("/~([0-9]+)~/", "&#$1;", $data);
	}


	function parse_smileys($data) {
		global $gBitSystem;
		if( defined("SMILEYS_PKG_URL") && $gBitSystem->getPreference('package_smileys') == 'y' ) {
			$data = preg_replace("/\(:([^:]+):\)/", "<img alt=\"$1\" class=\"icon\" src=\"".SMILEYS_PKG_URL."icons/$1.gif\" />", $data);
// TODO - biticon can't work cause this is post smarty parse			$data = preg_replace("/\(:([^:]+):\)/", "{biticon ipackage=\"smileys\" iname=\"$1\" iexplain=\"{tr}$1{/tr}", $data);
		}

		return $data;
	}

	function parse_comment_data($data) {
		// rel=\"nofollow\" is support fo Google's Preventing comment spam
		// http://www.google.com/googleblog/2005/01/preventing-comment-spam.html
		$data = preg_replace("/\[([^\|\]]+)\|([^\]]+)\]/", "<a rel=\"nofollow\" href=\"$1\">$2</a>", $data);

		// Segundo intento reemplazar los [link] comunes
		$data = preg_replace("/\[([^\]\|]+)\]/", "<a rel=\"nofollow\" href=\"$1\">$1</a>", $data);
		// Llamar aqui a parse smileys
		$data = $this->parse_smileys($data);
		$data = preg_replace("/---/", "<hr/>", $data);
		// Reemplazar --- por <hr/>
		return $data;
	}

	function get_language($user = false) {
		static $bitLanguage = false;
		global $gBitUser, $gBitSystem;

		if( empty( $bitLanguage ) ) {
			if( $gBitUser->isValid() ) {
				$bitLanguage = $gBitUser->getPreference('bitLanguage', 'en');
			} else {
				$bitLanguage = $this->getPreference('bitLanguage', 'en');
			}
		}

		return $bitLanguage;
	}

	function get_locale($user = false) {
	# TODO move to admin preferences screen
		static $locales = array(
		  'cs' => 'cs_CZ',
			'de' => 'de_DE',
			'dk' => 'da_DK',
			'en' => 'en_US',
			'fr' => 'fr_FR',
			'he' => 'he_IL', # hebrew
			'it' => 'it_IT', # italian
			'pl' => 'pl_PL', # polish
			'po' => 'po',
			'ru' => 'ru_RU',
			'es' => 'es_ES',
			'sw' => 'sw_SW', # swahili
			'tw' => 'tw_TW',
			);

	 if (!isset($locale) or !$locale) {
	  $locale = '';
		if (isset($locales[$this->get_language($user)]))
			$locale = $locales[$this->get_language($user)];
	#print "<pre>get_locale(): locale=$locale\n</pre>";
	 }

		return $locale;
	}

	function get_links($data) {
		$links = array();

		// Match things like [...], but ignore things like [[foo].
		// -Robin
		if (preg_match_all("/(?<!\[)\[([^\[\|\]]+)(\||\])/", $data, $r1)) {
			$res = $r1[1];
			$links = array_unique($res);
		}

		return $links;
	}

	function get_links_nocache($data) {
		$links = array();

		if (preg_match_all("/\[([^\]]+)/", $data, $r1)) {
		$res = array();

		foreach ($r1[1] as $alink) {
			$parts = explode('|', $alink);

			if (isset($parts[1]) && $parts[1] == 'nocache') {
			$res[] = $parts[0];
			} else {
			if (isset($parts[2]) && $parts[2] == 'nocache') {
				$res[] = $parts[0];
			}
			}
			// avoid caching URLs with common binary file extensions
			$extension = substr($parts[0], -4);
			$binary = array(
				'.arj',
				'.asf',
				'.avi',
				'.bz2',
				'.dat',
				'.doc',
				'.exe',
				'.hqx',
				'.mov',
				'.mp3',
				'.mpg',
				'.ogg',
				'.pdf',
				'.ram',
				'.rar',
				'.rpm',
				'.rtf',
				'.sea',
				'.sit',
				'.tar',
				'.tgz',
				'.wav',
				'.wmv',
				'.xls',
				'.zip',
				'ar.Z', // .tar.Z
				'r.gz'  // .tar.gz
				);
				if (in_array($extension, $binary)) {
				$res[] = $parts[0];
				}

		}

		$links = array_unique($res);
		}

		return $links;
	}

	function cache_links($links, &$pCommonObject ) {
		global $gBitSystem;
		if( $gBitSystem->isFeatureActive( 'cachepages' ) ) {
			foreach ($links as $link) {
				if( !$pCommonObject->isCached( $link ) ) {
					$pCommonObject->cacheUrl($link);
				}
			}
		}
	}


	function how_many_at_start($str, $car) {
		$cant = 0;
		$i = 0;
		while (($i < strlen($str)) && (isset($str{$i})) && ($str{$i}== $car)) {
			$i++;
			$cant++;
		}
		return $cant;
	}


	function parse_data( $data, &$pCommonObject ) {
		global $gBitSystem;
		global $gBitUser;
		global $page;
		if( $gBitSystem->isPackageActive( 'wiki' ) ) {
			require_once( WIKI_PKG_PATH.'BitPage.php' );
		}

		if( $gBitSystem->isFeatureActive( 'allow_html' ) ) {
			// this is copied and pasted from format.bithtml.php - xing
			// Strip all evil tags that remain
			// this comes out of gBitSystem->getPreference() set in Liberty Admin
			$acceptableTags = $gBitSystem->getPreference( 'approved_html_tags', DEFAULT_ACCEPTABLE_TAGS );

			// Destroy all script code "manually" - strip_tags will leave code inline as plain text
			if( !preg_match( '/\<script\>/', $acceptableTags ) ) {
				$data = preg_replace( "/(\<script)(.*?)(script\>)/si", '', $data );
			}

			$data = strip_tags( $data, $acceptableTags );
		} else {
			// convert HTML to chars
			$data = htmlspecialchars( $data, ENT_NOQUOTES, 'UTF-8' );
		}

		// Process pre_handlers here
		foreach ($this->pre_handlers as $handler) {
			$data = $handler($data);
		}

		$data = preg_replace( '/(\)\))('.WIKI_WORDS_REGEX.')(\(\()/', "~np~" . "$2" . "~/np~", $data);

		// Handle pre- and no-parse sections and plugins
		$preparsed = array();
		$noparsed = array();
		$this->parse_first($data, $preparsed, $noparsed);

		// Extract [link] sections (to be re-inserted later)
		$noparsedlinks = array();

		// This section matches [...].
		// Added handling for [[foo] sections.  -rlpowell
		preg_match_all("/(?<!\[)\[([^\[][^\]]+)\]/", $data, $noparseurl);

		foreach (array_unique($noparseurl[1])as $np) {
			$key = md5(BitSystem::genPass());

			$aux["key"] = $key;
			$aux["data"] = $np;
			$noparsedlinks[] = $aux;
			$data = str_replace("$np", $key, $data);
		}

		// Replace special characters
		//done after url catching because otherwise urls of dyn. sites will be modified
		$this->parse_htmlchar($data);

		//$data = strip_tags($data);
		// BiDi markers
		$bidiCount = 0;
		$bidiCount = preg_match_all("/(\{l2r\})/", $data, $pages);
		$bidiCount += preg_match_all("/(\{r2l\})/", $data, $pages);

		$data = preg_replace("/\{l2r\}/", "<div dir='ltr'>", $data);
		$data = preg_replace("/\{r2l\}/", "<div dir='rtl'>", $data);
		$data = preg_replace("/\{lm\}/", "&lrm;", $data);
		$data = preg_replace("/\{rm\}/", "&rlm;", $data);
		// smileys
		$data = $this->parse_smileys($data);

		// Replace links to slideshows
		if ($gBitSystem->getPreference('feature_drawings') == 'y') {
			// Replace drawings
			// Replace rss modules
			$pars = parse_url($_SERVER["REQUEST_URI"]);

			$pars_parts = split('/', $pars["path"]);
			$pars = array();

			for ($i = 0; $i < count($pars_parts) - 1; $i++) {
				$pars[] = $pars_parts[$i];
			}

			$pars = join('/', $pars);

			if (preg_match_all("/\{draw +name=([A-Za-z_\-0-9]+) *\}/", $data, $draws)) {
				//$this->invalidate_cache($page);
				for ($i = 0; $i < count($draws[0]); $i++) {
					$id = $draws[1][$i];

					$repl = '';
					$name = $id . '.gif';

					if (file_exists("img/wiki/$bitdomain$name")) {
						if ($gBitUser->hasPermission( 'bit_p_edit_drawings' ) || $gBitUser->hasPermission( 'bit_p_admin_drawings' )) {
						$repl = "<a href='#' onClick=\"javascript:window.open('".DRAWINGS_PKG_URL."edit.php?page=" . urlencode($page). "&amp;path=$pars&amp;drawing={$id}','','menubar=no,width=252,height=25');\"><img border='0' src='img/wiki/$bitdomain$name' alt='click to edit' /></a>";
						} else {
						$repl = "<img border='0' src='img/wiki/$bitdomain$name' alt='a drawing' />";
						}
					} else {
						if ($gBitUser->hasPermission( 'bit_p_edit_drawings' ) || $gBitUser->hasPermission( 'bit_p_admin_drawings' )) {
							$repl = "<a href='".DRAWINGS_PKG_URL."edit.php?page=" . urlencode($page). "&amp;path=$pars&amp;drawing={$id}' onkeypress='popUpWin(this.href,'fullScreen',0,0);' onclick='popUpWin(this.href,'fullScreen',0,0);return false;','','menubar=no,width=252,height=25');\">click here to create draw $id</a>";
						} else {
							$repl = tra('drawing not found');
						}
					}
					$data = str_replace($draws[0][$i], $repl, $data);
				}
			}
		}

		// Replace cookies
		if (preg_match_all("/\{cookie\}/", $data, $rsss)) {
			require_once( KERNEL_PKG_PATH.'tagline_lib.php' );
			global $taglinelib;
			for ($i = 0; $i < count($rsss[0]); $i++) {
				$cookie = $taglinelib->pick_cookie();
				$data = str_replace($rsss[0][$i], $cookie, $data);
			}
		}

		// Replace dynamic variables
		// Dynamic variables are similar to dynamic content but they are editable
		// from the page directly, intended for short data, not long text but text
		// will work too
		//     Now won't match HTML-style '%nn' letter codes.
		if (preg_match_all("/%([^% 0-9][^% 0-9][^% ]*)%/",$data,$dvars)) {
			// remove repeated elements
			$dvars = array_unique($dvars[1]);
			// Now replace each dynamic variable by a pair composed of the
			// variable value and a text field to edit the variable. Each
			foreach($dvars as $dvar) {
				$query = "select `data` from `".BIT_DB_PREFIX."tiki_dynamic_variables` where `name`=?";
				$result = $this->mDb->query($query,Array($dvar));
				if($result->numRows()) {
				$value = $result->fetchRow();
				$value = $value["data"];
				} else {
				//Default value is NULL
				$value = "NaV";
				}
				// Now build 2 divs
				$id = 'dyn_'.$dvar;

				if( $gBitUser->hasPermission( 'bit_p_edit_dynvar' ) ) {
					$span1 = "<span  style='display:inline;' id='dyn_".$dvar."_display'><a class='dynavar' onClick='javascript:toggle_dynamic_var(\"$dvar\");' title='".tra('Click to edit dynamic variable').": $dvar'>$value</a></span>";
					$span2 = "<span style='display:none;' id='dyn_".$dvar."_edit'><input type='text' name='dyn_".$dvar."' value='".$value."' /></span>";
				} else {
					$span1 = "<span class='dynavar' style='display:inline;' id='dyn_".$dvar."_display'>$value</span>";
					$span2 = '';
				}
				$html = $span1.$span2;
				//It's important to replace only once
				$dvar_preg = preg_quote( $dvar );
				$data = preg_replace("+%$dvar_preg%+",$html,$data,1);
				//Further replacements only with the value
				$data = str_replace("%$dvar%",$value,$data);

			}
			//At the end put an update button
			//<br /><div style="text-align:center"><input type="submit" name="dyn_update" value="'.tra('Update variables').'"/></div>
			$data='<form method="post" name="dyn_vars">'.$data.'<div style="display:none;"><input type="submit" name="_dyn_update" value="'.tra('Update variables').'"/></div></form>';
		}

		// Replace dynamic content occurrences
		if (preg_match_all("/\{content +id=([0-9]+)\}/", $data, $dcs)) {
			for ($i = 0; $i < count($dcs[0]); $i++) {
				$repl = $this->get_actual_content($dcs[1][$i]);

				$data = str_replace($dcs[0][$i], $repl, $data);
			}
		}

		// Replace Dynamic content with random selection
		if (preg_match_all("/\{rcontent +id=([0-9]+)\}/", $data, $dcs)) {
			for ($i = 0; $i < count($dcs[0]); $i++) {
				$repl = $this->get_random_content($dcs[1][$i]);

				$data = str_replace($dcs[0][$i], $repl, $data);
			}
		}

		// Replace boxes
		$data = preg_replace("/\^([^\^]+)\^/", "<div class=\"bitbox\">$1</div>", $data);
		// Replace colors ~~color:text~~
		$data = preg_replace("/\~\~([^\:]+):([^\~]+)\~\~/", "<span style=\"color:$1;\">$2</span>", $data);
		// Replace background colors ++color:text++
		$data = preg_replace("/\+\+([^\:]+):([^\+]+)\+\+/", "<span style=\"background:$1;\">$2</span>", $data);
		// Underlined text
		$data = preg_replace("/===([^\=]+)===/", "<span style=\"text-decoration:underline;\">$1</span>", $data);
		// Center text
		$data = preg_replace("/::(.+?)::/", "<div style=\"text-align:center;\">$1</div>", $data);
		// Line breaks
		$data = preg_replace('/%%%/', '<br />', $data);

		// New syntax for wiki pages ((name|desc)) Where desc can be anything
		preg_match_all("/\(\(($this->mWikiWordRegex)\|(.+?)\)\)/", $data, $pages);

		for ($i = 0; $i < count($pages[1]); $i++) {
			$pattern = $pages[0][$i];

			$pattern = preg_quote($pattern, "/");

			$pattern = "/" . $pattern . "/";

			// Replace links to external wikis
			$repl2 = true;

			if (strstr($pages[1][$i], ':')) {
				$wexs = explode(':', $pages[1][$i]);

				if (count($wexs) == 2) {
					$wkname = $wexs[0];

					if ($this->mDb->getOne("select count(*) from `".BIT_DB_PREFIX."tiki_extwiki` where `name`=?",array($wkname)) == 1) {
						$wkurl = $this->mDb->getOne("select `extwiki`  from `".BIT_DB_PREFIX."tiki_extwiki` where `name`=?",array($wkname));
						$wkurl = '<a href="' . str_replace('$page', urlencode($wexs[1]), $wkurl). '">' . $wexs[1] . '</a>';
						$data = preg_replace($pattern, "$wkurl", $data);
						$repl2 = false;
					}
				}
			}

			if ($repl2) {
				// 24-Jun-2003, by zaufi
				// TODO: future optimize: get page description and modification time at once.

				// text[0] = link description (previous format)
				// text[1] = timeout in seconds (new field)
				// text[2..N] = drop
				$text = explode("|", $pages[5][$i]);

				if( $exists = $this->pageExists( $pages[1][$i], $pCommonObject->mContentId, $pCommonObject ) ) {
					$desc = count( $exists ) == 1 ? (isset( $exists['description'] ) ? $exists['description'] : '') : tra( 'multiple pages with this name' );
					$modTime = count( $exists ) == 1 ? (isset( $exists['last_modified'] ) ? (int)$exists['last_modified'] : 0 ) : 0;
					$uri_ref = WIKI_PKG_URL."index.php?page=" . urlencode($pages[1][$i]);

					$repl = '<a title="'.$desc.'" href="'.$uri_ref.'">'.( (strlen(trim($text[0])) > 0 ? $text[0] : $pages[1][$i]) ).'</a>';

					// Check is timeout expired?
					if (isset($text[1]) && (time() - $modTime ) < intval($text[1])) {
						// Append small 'new' image. TODO: possible 'updated' image more suitable...
						$repl .= '&nbsp;<img src="img/icons/new.gif" border="0" alt="'.tra("new").'" />';
					}
				} else {
					$uri_ref = WIKI_PKG_URL."edit.php?page=" . urlencode($pages[1][$i]);
					$repl = ' <a class="create" href="'.$uri_ref.'">'.( (strlen(trim($text[0])) > 0 ? $text[0] : $pages[1][$i]) ).'</a>';
				}

				$data = preg_replace($pattern, "$repl", $data);
			}
		}

		// New syntax for wiki pages ((name)) Where name can be anything
		preg_match_all("/\(\(([^\)][^\)]+)\)\)/", $data, $pages);
		foreach (array_unique($pages[1])as $page_parse) {
			$repl2 = true;

			if (strstr($page_parse, ':')) {
				$wexs = explode(':', $page_parse);

				if (count($wexs) == 2) {
					$wkname = $wexs[0];

					if ($this->mDb->getOne("select count(*) from `".BIT_DB_PREFIX."tiki_extwiki` where `name`=?",array($wkname)) == 1) {
						$wkurl = $this->mDb->getOne("select `extwiki`  from `".BIT_DB_PREFIX."tiki_extwiki` where `name`=?",array($wkname));

						$wkurl = '<a href="' . str_replace('$page', urlencode($wexs[1]), $wkurl). '">' . $wexs[1] . '</a>';
						$data = preg_replace("/\(\($page_parse\)\)/", "$wkurl", $data);
						$repl2 = false;
					}
				}
			}

			if ($repl2) {
	// This is a hack for now. page_exists_desc should not be needed here sicne blogs and articles use this function

				$exists = $this->pageExists( $page_parse, $pCommonObject->mContentId, $pCommonObject );
				$repl = BitPage::getDisplayLink( $page_parse, $exists );
				$page_parse_pq = preg_quote($page_parse, "/");
				$data = preg_replace("/\(\($page_parse_pq\)\)/", "$repl", $data);
			}
		}

		if ($gBitSystem->isPackageActive( 'hotwords' ) ) {
			if( empty( $hotwordlib ) ) {
				include_once(HOTWORDS_PKG_PATH."hotword_lib.php");
				global $hotwordlib;
				$words = $hotwordlib->get_hotwords();
			}
		}

		// Links to internal pages
		// If they are parenthesized then don't treat as links
		// Prevent ))PageName(( from being expanded	\"\'
		//[A-Z][a-z0-9_\-]+[A-Z][a-z0-9_\-]+[A-Za-z0-9\-_]*
		if( $gBitSystem->isPackageActive( 'wiki' ) && $gBitSystem->isFeatureActive( 'feature_wikiwords' ) ) {
			// The first part is now mandatory to prevent [Foo|MyPage] from being converted!
			// the {2} is curious but seems to prevent things like "__Administration / Modules__" getting linked - spiderr
			$pages = $this->extractWikiWords( $data );
			foreach( $pages as $page_parse) {
				if( empty( $words ) || !array_key_exists( $page_parse, $words ) ) {
					if( $exists = $this->pageExists( $page_parse, $pCommonObject->mContentId, $pCommonObject ) ) {
						$desc = count( $exists ) == 1 ? (isset( $exists['description'] ) ? $exists['description'] : '') : tra( 'multiple pages with this name' );
						// call statically since $pCommonObject might be something like BitUser
						$repl = BitPage::getDisplayLink( $page_parse, $exists );
					} elseif( $gBitSystem->getPreference('feature_wiki_plurals') == 'y' && $this->get_locale() == 'en_US') {
						// Link plural topic names to singular topic names if the plural
						// doesn't exist, and the language is english
						$plural_tmp = $page_parse;
						// Plurals like policy / policies
						$plural_tmp = preg_replace("/ies$/", "y", $plural_tmp);
						// Plurals like address / addresses
						$plural_tmp = preg_replace("/sses$/", "ss", $plural_tmp);
						// Plurals like box / boxes
						$plural_tmp = preg_replace("/([Xx])es$/", "$1", $plural_tmp);
						// Others, excluding ending ss like address(es)
						$plural_tmp = preg_replace("/([A-Za-rt-z])s$/", "$1", $plural_tmp);
						// prevent redundant pageExists calls if plurals are on, and plural is same as original word
						$exists = ( $plural_tmp != $page_parse ) ? $this->pageExists( $plural_tmp, $pCommonObject->mContentId, $pCommonObject ) : NULL;
						$repl = BitPage::getDisplayLink( $page_parse, $exists );
					} else {
						$repl = BitPage::getDisplayLink( $page_parse, $exists );
					}
					$slashedParse = preg_replace( "/([\/\[\]\(\)])/", "\\\\$1", $page_parse );
					$data = preg_replace("/([ \n\t\r\,\;]|^)".$slashedParse."($|[ \n\t\r\,\;\.])/", "$1"."$repl"."$2", $data);
					//$data = str_replace($page_parse,$repl,$data);
				}
			}
		}
		// This protects ))word((, I think?
		$data = preg_replace("/([ \n\t\r\,\;]|^)\)\)([^\(]+)\(\(($|[ \n\t\r\,\;\.])/", "$1" . "$2" . "$3", $data);

		// reinsert hash-replaced links into page
		foreach ($noparsedlinks as $np) {
			$data = str_replace($np["key"], $np["data"], $data);
		}

		// TODO: I think this is 1. just wrong and 2. not needed here? remove it?
		// Replace ))Words((
		$data = preg_replace("/\(\(([^\)]+)\)\)/", "$1", $data);

		// Images
		preg_match_all("/(\{img [^\}]+})/i", $data, $pages);

		foreach (array_unique($pages[1])as $page_parse) {
			$parts = explode(" ", $page_parse);

			$imgdata = array();
			$imgdata["src"] = '';
			$imgdata["height"] = '';
			$imgdata["width"] = '';
			$imgdata["link"] = '';
			$imgdata["align"] = '';
			$imgdata["float"] = '';
			$imgdata["desc"] = '';

			foreach ($parts as $part) {
				$part = str_replace('}', '', $part);
				$part = str_replace('{', '', $part);
				$part = str_replace('\'', '', $part);
				$part = str_replace('"', '', $part);

				if (strstr($part, '=')) {
				$subs = explode("=", $part, 2);

				$imgdata[$subs[0]] = $subs[1];
				}
			}

			//print("todo el tag es: ".$page_parse."<br/>");
			//print_r($imgdata);
			$repl = '<img alt="' . tra('Image') . '" src="'.$imgdata["src"].'" style="border:0;'.( !empty( $imgdata["float"] ) ? ' float:'.$imgdata["float"].';' : '' ).'"';



			if ($imgdata["width"])
				$repl .= ' width="' . $imgdata["width"] . '"';

			if ($imgdata["height"])
				$repl .= ' height="' . $imgdata["height"] . '"';

			if ($imgdata["align"])
				$repl .= ' align="' . $imgdata["align"] . '"';

			$repl .= ' />';

			if ($imgdata["link"]) {
				$repl = '<a href="' . $imgdata["link"] . '">' . $repl . '</a>';
			}

			if ($imgdata["desc"]) {
				$repl = '<table cellpadding="0" cellspacing="0"><tr><td>' . $repl . '</td></tr><tr><td><small>' . $imgdata["desc"] . '</small></td></tr></table>';
			}


			$data = str_replace($page_parse, $repl, $data);
		}

		$links = $this->get_links($data);

		$notcachedlinks = $this->get_links_nocache($data);

		$cachedlinks = array_diff($links, $notcachedlinks);

		$this->cache_links($cachedlinks,$pCommonObject);

		// Note that there're links that are replaced
		foreach ($links as $link) {
			if ((strstr($link, $_SERVER["SERVER_NAME"])) || (!strstr($link, '//'))) {
				$class = '';
			} else {
				$class = 'class="external"';
			}

			// comments and anonymously created pages get nofollow
			if( get_class( $pCommonObject ) == 'comments' || ( isset( $pCommonObject->mInfo['user_id'] ) &&  $pCommonObject->mInfo['user_id'] == ANONYMOUS_USER_ID ) ) {
				$class .= ' rel="nofollow" ';
			}

			// The (?<!\[) stuff below is to give users an easy way to
			// enter square brackets in their output; things like [[foo]
			// get rendered as [foo]. -rlpowell

			if( $gBitSystem->isFeatureActive( 'cachepages') && $pCommonObject->isCached( $link ) ) {
				//use of urlencode for using cached versions of dynamic sites
				$cosa = "<a class=\"bitcache\" href=\"".KERNEL_PKG_URL."view_cache.php?url=".urlencode($link)."\">(cache)</a>";

				//$link2 = str_replace("/","\/",$link);
				//$link2 = str_replace("?","\?",$link2);
				//$link2 = str_replace("&","\&",$link2);
				$link2 = str_replace("/", "\/", preg_quote($link));
				$pattern = "/(?<!\[)\[$link2\|([^\]\|]+)\|([^\]]+)\]/";
				$data = preg_replace($pattern, "<a $class href='$link'>$1</a>", $data);
				$pattern = "/(?<!\[)\[$link2\|([^\]\|]+)\]/";
				$data = preg_replace($pattern, "<a $class href='$link'>$1</a> $cosa", $data);
				$pattern = "/(?<!\[)\[$link2\]/";
				$data = preg_replace($pattern, "<a $class href='$link'>$link</a> $cosa", $data);
			} else {
				//$link2 = str_replace("/","\/",$link);
				//$link2 = str_replace("?","\?",$link2);
				//$link2 = str_replace("&","\&",$link2);
				$link2 = str_replace("/", "\/", preg_quote($link));

				$pattern = "/(?<!\[)\[$link2\|([^\]\|]+)([^\]])*\]/";
				$data = preg_replace($pattern, "<a $class href='$link'>$1</a>", $data);
				$pattern = "/(?<!\[)\[$link2\]/";
				$data = preg_replace($pattern, "<a $class href='$link'>$link</a>", $data);
			}
		}

		// Handle double square brackets.  -rlpowell
		$data = str_replace( "[[", "[", $data );

		if ($gBitSystem->getPreference('feature_wiki_tables') != 'new') {
			// New syntax for tables
			if (preg_match_all("/\|\|(.*)\|\|/", $data, $tables)) {
				$maxcols = 1;

				$cols = array();

				for ($i = 0; $i < count($tables[0]); $i++) {
				$rows = explode('||', $tables[0][$i]);

				$col[$i] = array();

				for ($j = 0; $j < count($rows); $j++) {
					$cols[$i][$j] = explode('|', $rows[$j]);

					if (count($cols[$i][$j]) > $maxcols)
					$maxcols = count($cols[$i][$j]);
				}
				}

				for ($i = 0; $i < count($tables[0]); $i++) {
				$repl = '<table class="bittable">';

				for ($j = 0; $j < count($cols[$i]); $j++) {
					$ncols = count($cols[$i][$j]);

					if ($ncols == 1 && !$cols[$i][$j][0])
						continue;

					$repl .= '<tr class="'.( ( $j % 2 ) ? 'even' : 'odd' ).'">';

					for ($k = 0; $k < $ncols; $k++) {
						$repl .= '<td ';

						if ($k == $ncols - 1 && $ncols < $maxcols)
							$repl .= ' colspan="' . ($maxcols - $k).'"';

						$repl .= '>' . $cols[$i][$j][$k] . '</td>';
					}

					$repl .= '</tr>';
				}

				$repl .= '</table>';
				$data = str_replace($tables[0][$i], $repl, $data);
				}
			}
		} else {
			// New syntax for tables
			// REWRITE THIS CODE
			if (preg_match_all("/\|\|(.*?)\|\|/s", $data, $tables)) {
				$maxcols = 1;

				$cols = array();

				for ($i = 0; $i < count($tables[0]); $i++) {
				$rows = split("\n|\<br\/\>", $tables[0][$i]);
				$col[$i] = array();

				for ($j = 0; $j < count($rows); $j++) {
					$rows[$j] = str_replace('||', '', $rows[$j]);
					$cols[$i][$j] = explode('|', $rows[$j]);
					if (count($cols[$i][$j]) > $maxcols)
					$maxcols = count($cols[$i][$j]);
				}
				}

				for ($i = 0; $i < count($tables[0]); $i++) {
				$repl = '<table class="bittable">';

				for ($j = 0; $j < count($cols[$i]); $j++) {
					$ncols = count($cols[$i][$j]);

					if ($ncols == 1 && !$cols[$i][$j][0])
						continue;

					$repl .= '<tr class="'.( ( $j % 2 ) ? 'even' : 'odd' ).'">';

					for ($k = 0; $k < $ncols; $k++) {
						$repl .= '<td ';

						if ($k == $ncols - 1 && $ncols < $maxcols)
							$repl .= ' colspan="' . ($maxcols - $k).'"';

						$repl .= '>' . $cols[$i][$j][$k] . '</td>';
					}

					$repl .= '</tr>';
				}

				$repl .= '</table>';
				$data = str_replace($tables[0][$i], $repl, $data);
				}
			}
		}

		// change back any end of lines that were temporarily removed in parse_data_plugins
		$data = preg_replace( "/#EOL/", "\n", $data );

		// 08-Jul-2003, by zaufi
		// HotWords will be replace only in ordinal text
		// It looks __realy__ goofy in Headers or Titles

		// Get list of HotWords
		if ( isset($hotwordlib) ) {
			$words = $hotwordlib->get_hotwords();
		} else {
			$words = '';
		}
		// Now tokenize the expression and process the tokens
		// Use tab and newline as tokenizing characters as well  ////
		$lines = explode("\n", $data);
		$data = '';
		$listbeg = array();
		$divdepth = array();
		$inTable = 0;

		// loop: process all lines
		foreach ($lines as $line) {

			// bitweaver now ignores leading space because it is *VERY* disturbing to unaware users - spiderr
			$line = trim( $line );
			// Check for titlebars...
			// NOTE: that title bar should be start from begining of line and
			//	   be alone on that line to be autoaligned... else it is old styled
			//	   styled title bar...
			if (substr(ltrim($line), 0, 2) == '-=' && substr(rtrim($line), -2, 2) == '=-') {
				// This is not list item -- must close lists currently opened
				while (count($listbeg))
				$data .= array_shift($listbeg);

				//
				$align_len = strlen($line) - strlen(ltrim($line));

				// My textarea size is about 120 space chars.
				//define('TEXTAREA_SZ', 120);

				// NOTE: That strict math formula (split into 3 areas) gives
				//	   bad visual effects...
				// $align = ($align_len < (TEXTAREA_SZ / 3)) ? "left"
				//		: (($align_len > (2 * TEXTAREA_SZ / 3)) ? "right" : "center");
				//
				// Going to introduce some heuristic here :)
				// Visualy (remember that space char is thin) center starts at 25 pos
				// and 'right' from 60 (HALF of full width!) -- thats all :)
				//
				// NOTE: Guess align only if more than 10 spaces before -=title=-
				if ($align_len > 10) {
					$align = ($align_len < 25) ? "left" : (($align_len > 60) ? "right" : "center");
					$align = ' style="text-align: ' . $align . ';"';
				} else {
					$align = '';
				}
				//
				$line = trim($line);
				$line = '<div class="bitbar"' . $align . '>' . substr($line, 2, strlen($line) - 4). '</div>';
				$data .= $line;
				// TODO: Case is handled ...  no need to check other conditions
				//	   (it is apriory known all they false, moreover sometimes
				//	   check procedure need > O(0) of compexity)
				//	   -- continue to next line...
				//	   MUST replace all remaining parse blocks to the same logic...
				continue;
			}

			// Replace old styled titlebars
			if (strlen($line) != strlen($line = preg_replace("/-=(.+?)=-/", "<div class='bitbar'>$1</div>", $line))) {
				$data .= $line;
				continue;
			}

			// check if we are inside a table, if so, ignore monospaced and do
			// not insert <br/>
			$inTable += substr_count($line, "<table");
			$inTable -= substr_count($line, "</table");

			// If the first character is ' ' and we are not in pre then we are in pre
			// bitweaver now ignores leading space because it is *VERY* disturbing to unaware users - spiderr
			if (substr($line, 0, 1) == ' ' && $gBitSystem->getPreference('feature_wiki_monosp') == 'y' && $inTable == 0) {
				// This is not list item -- must close lists currently opened
				while (count($listbeg))
				$data .= array_shift($listbeg);

				// If the first character is space then
				// change spaces for &nbsp;
				$line = '<span style="font-family:monospace;">' . str_replace(' ', '&nbsp;', substr($line, 1)). '</span>';
			}

			// Replace Hotwords before begin
			if ($gBitSystem->getPreference('package_hotwords') == 'y') {
				$line = $hotwordlib->replace_hotwords($line, $words);
			}

			// Replace monospaced text
			$line = preg_replace("/-\+(.*?)\+-/", "<code>$1</code>", $line);
			// Replace bold text
			$line = preg_replace("/__(.*?)__/", "<b>$1</b>", $line);
			$line = preg_replace("/\'\'(.*?)\'\'/", "<i>$1</i>", $line);
			// Replace definition lists
			$line = preg_replace("/^;([^:]+):([^\n]+)/", "<dl><dt>$1</dt><dd>$2</dd></dl>", $line);

			if (0) {
				$line = preg_replace("/\[([^\|]+)\|([^\]]+)\]/", "<a $class href='$1'>$2</a>", $line);

				// Segundo intento reemplazar los [link] comunes
				$line = preg_replace("/\[([^\]]+)\]/", "<a $class href='$1'>$1</a>", $line);
				$line = preg_replace("/\-\=([^=]+)\=\-/", "<div class='bitbar'>$1</div>", $line);
			}

			// This line is parseable then we have to see what we have
			if (substr($line, 0, 3) == '---') {
				// This is not list item -- must close lists currently opened
				while (count($listbeg))
				$data .= array_shift($listbeg);

				$line = '<hr/>';
			} else {
				$litype = substr($line, 0, 1);

				if ($litype == '*' || $litype == '#') {
				$listlevel = $this->how_many_at_start($line, $litype);

				$liclose = '</li>';
				$addremove = 0;

				if ($listlevel < count($listbeg)) {
					while ($listlevel != count($listbeg))
					$data .= array_shift($listbeg);

					if (substr(current($listbeg), 0, 5) != '</li>')
					$liclose = '';
				} elseif ($listlevel > count($listbeg)) {
					$listyle = '';

					while ($listlevel != count($listbeg)) {
					array_unshift($listbeg, ($litype == '*' ? '</ul>' : '</ol>'));

					if ($listlevel == count($listbeg)) {
						$listate = substr($line, $listlevel, 1);

						if (($listate == '+' || $listate == '-') && !($litype == '*' && !strstr(current($listbeg), '</ul>') || $litype == '#' && !strstr(current($listbeg), '</ol>'))) {
						$thisid = 'id' . microtime() * 1000000;

						$data .= '<br/><a id="flipper' . $thisid . '" href="javascript:flipWithSign(\'' . $thisid . '\')">[' . ($listate == '-' ? '+' : '-') . ']</a>';
						$listyle = ' id="' . $thisid . '" style="display:' . ($listate == '+' ? 'block' : 'none') . ';"';
						$addremove = 1;
						}
					}

					$data .= ($litype == '*' ? "<ul$listyle>" : "<ol$listyle>");
					}

					$liclose = '';
				}

				if ($litype == '*' && !strstr(current($listbeg), '</ul>') || $litype == '#' && !strstr(current($listbeg), '</ol>')) {
					$data .= array_shift($listbeg);

					$listyle = '';
					$listate = substr($line, $listlevel, 1);

					if (($listate == '+' || $listate == '-')) {
					$thisid = 'id' . microtime() * 1000000;

					$data .= '<br/><a id="flipper' . $thisid . '" href="javascript:flipWithSign(\'' . $thisid . '\')">[' . ($listate == '-' ? '+' : '-') . ']</a>';
					$listyle = ' id="' . $thisid . '" style="display:' . ($listate == '+' ? 'block' : 'none') . ';"';
					$addremove = 1;
					}

					$data .= ($litype == '*' ? "<ul$listyle>" : "<ol$listyle>");
					$liclose = '';
					array_unshift($listbeg, ($litype == '*' ? '</li></ul>' : '</li></ol>'));
				}

				$line = $liclose . '<li>' . substr($line, $listlevel + $addremove);

				if (substr(current($listbeg), 0, 5) != '</li>')
					array_unshift($listbeg, '</li>' . array_shift($listbeg));
				} elseif ($litype == '+') {
				// Must append paragraph for list item of given depth...
				$listlevel = $this->how_many_at_start($line, $litype);

				// Close lists down to requested level
				while ($listlevel < count($listbeg))
					$data .= array_shift($listbeg);

					if (count($listbeg)) {
						if (substr(current($listbeg), 0, 5) != '</li>') {
						array_unshift($listbeg, '</li>' . array_shift($listbeg));

						$liclose = '<li>';
						} else
						$liclose = '<br/>';
					} else
						$liclose = '';

					$line = $liclose . substr($line, count($listbeg));
					} else {
					// This is not list item -- must close lists currently opened
					while (count($listbeg))
						$data .= array_shift($listbeg);

					// Get count of (possible) header signs at start
					$hdrlevel = $this->how_many_at_start($line, '!');

					// If 1st char on line is '!' and its count less than 6 (max in HTML)
					if ($litype == '!' && $hdrlevel > 0 && $hdrlevel <= 6) {
						// Remove possible hotwords replaced :)
						//   Umm, *why*?  Taking this out lets page
						//   links in headers work, which can be nice.
						//   -rlpowell
						// $line = strip_tags($line);

						// OK. Parse headers here...
						$anchor = '';
						$aclose = '';
						$addremove = 0;

						// Close lower level divs if opened
						for (;current($divdepth) >= $hdrlevel; array_shift($divdepth))
						$data .= '</div>';

						// May be spesial signs present after '!'s?
						$divstate = substr($line, $hdrlevel, 1);

						if ($divstate == '+' || $divstate == '-') {
						// OK. Must insert flipper after HEADER, and then open new div...
						$thisid = 'id' . microtime() * 1000000;

						$aclose = '<a id="flipper' . $thisid . '" href="javascript:flipWithSign(\'' . $thisid . '\')">[' . ($divstate == '-' ? '+' : '-') . ']</a>';
						$aclose .= '<div id="' . $thisid . '" style="display:' . ($divstate == '+' ? 'block' : 'none') . ';">';
						array_unshift($divdepth, $hdrlevel);
						$addremove = 1;
						}

						$line = $anchor . "<h$hdrlevel>" . substr($line, $hdrlevel + $addremove). "</h$hdrlevel>" . $aclose;
					} elseif (!strcmp($line, "...page...")) {
						// Close lists and divs currently opened
						while (count($listbeg))
						$data .= array_shift($listbeg);

						while (count($divdepth)) {
						$data .= '</div>';

						array_shift ($divdepth);
						}

						// Leave line unchanged... index.php will split wiki here
						$line = "...page...";
					} else {
						// Usual paragraph.
						if ($inTable == 0 && !preg_match("/\{maketoc.*?\}/i",$line)) {
							$line .= '<br/>';
						}
					}
				}
			}

			$data .= $line;
		}

		// Close lists may remains opened
		while (count($listbeg)) {
			$data .= array_shift($listbeg);
		}

		// Close header divs may remains opened
		for ($i = 1; $i <= count($divdepth); $i++) {
			$data .= '</div>';
		}

		// Close BiDi DIVs if any
		for ($i = 0; $i < $bidiCount; $i++) {
		$data .= "</div>";
		}

		foreach ($noparsed as $np) {
			$data = str_replace($np["key"], $np["data"], $data);
		}

		foreach ($preparsed as $pp) {
			$data = str_replace($pp["key"], "<pre>" . $pp["data"] . "</pre>", $data);
		}

		// Process pos_handlers here
		foreach ($this->pos_handlers as $handler) {
			$data = $handler($data);
		}

		global $gLibertySystem;
		// create a table of contents for this page
		// this function is called manually, since it processes the HTML code
		if( preg_match( "/\{maketoc.*?\}/i", $data ) && @$gLibertySystem->mPlugins['datamaketoc']['is_active'] == 'y' ) {
			$data = data_maketoc($data);
		}

		return $data;
	}
}

?>
