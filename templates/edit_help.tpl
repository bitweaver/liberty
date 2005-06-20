{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/Attic/edit_help.tpl,v 1.2 2005/06/20 07:27:12 lsces Exp $ *}
{* Show wiki syntax and plugins help *}
{* TODO: Add links to add samples to edit form *}

{if $gBitSystem->isFeatureActive( 'feature_wikihelp' )}

{jstabs}
	{jstab title="Help"}
		{box title="Wiki Help" class="help box"}
			For more information, please see <a href="http://www.bitweaver.org/wiki/WikiSyntax">WikiSyntax</a> on <a href="http://www.bitweaver.org">bitweaver.org</a>.
		{/box}
	{/jstab}

	{if $gBitSystemPrefs.feature_drawings eq 'y'}
		{jstab title="Drawings"}
			{box title="Drawings" class="help box"}
				{literal}{{/literal}draw name=foo}  creates the editable drawing foo
			{/box}
		{/jstab}
	{/if}

	{jstab title="Format"}
		{box title="Text Color" class="help box"}
		{tr}Changing the color of text is easy. Before the text you want colored - insert 2 tildie (~) characters with the colors name followed by a colon. 2 additional tildie characters need to be added at the end of the colored text.{/tr}<br />
		{tr}Example:  ~~blue:Text to be colored~~ will produce <font color="blue">Text to be colored</font>{/tr}<br />
		{tr}HTML Colors can also be specified by placing a # character with the HTML Color where the color name should be.{/tr}<br />
		{tr}Example:  ~~#FF0060:Text to be colored~~ will produce <font color="#FF0060">Text to be colored</font>.{/tr}<br />
		{tr}For Web-Safe Color Names see <a class='wiki' target=_blank href='http://www.bitweaver.org/wiki/Browser+ColorNames'>bitweaver</a> or for HTML Color numbers see <a class='wiki' target=_blank href='http://www.pagetutor.com/pagetutor/makapage/picker/'>The Color Picker II</a>{/tr}
		{/box}

		{box title="Italics" class="help box"}
			{tr}To <i>italicize</i> a word or phrase using Wiki syntax, simply surround it with two single quotes (apostrophes), like{/tr} ''<i>{tr}this{/tr}</i>''. {tr}If you wish to italicize an entire phrase, just{/tr} ''<i>{tr}surround the entire phrase{/tr}</i>''.
			{tr}Note: insert note about italics not carrying over line breaks.{/tr}<br />
			{tr}Syntax: ''italics''{/tr}<br />
			{tr}Example: "My friend Jane was ''very'' excited to get her new car."{/tr}<br />
			{tr}Displayed: "My friend Jane was <i>very</i> excited to get her new car."{/tr}
		{/box}

		{box title="Underlining" class="help box"}
			<u>{tr}Underlining{/tr}</u> {tr}text is similar to{/tr} <i>{tr}italicizing{/tr}</i> text.
			{tr}You can underline an entire phrase by{/tr} ===<u>{tr}surrounding it with three equals signs on either side{/tr}</u>===,
			{tr}or just a single{/tr} ===<u>{tr}word{/tr}</u>===.<br />
			{tr}Syntax: ===underlined==={/tr}<br />
			{tr}Example: "My friend Scott is ===very=== nervous about his date on Friday."{/tr}<br />
			Displayed: "My friend Scott is <u>very</u> nervous about his date on Friday."
		{/box}

		{box title="Bold" class="help box"}
			{tr}<b>Bolding</b> text is very similiar to <i>italicizing</i> &amp; <u>underlining</u>. To make text bold, surround the __<b>word</b>__ or phrase with __<b>two underscores on either side</b>__.{/tr}
			{tr}Syntax: __bold__{/tr}<br />
			{tr}Example: "__When__ are we going to the movies?"{/tr}<br />
			{tr}Displayed: "<b>When</b> are we going to the movies?"{/tr}
		{/box}

		{box title="Combinations of Tags" class="help box"}
			{tr}bitweaver gives you even more choices by allowing you to simply combine tags to form different formatting combinations. For example, ''__<i><b>this text is both italicized &amp; bold</b></i>__''. __===<b><u>This text is both bold &amp; underlined</u></b>===__. ''__<i><u>This text is both italicized &amp; underlined</u></i>__''.{/tr}
		{/box}
	{/jstab}

	{jstab title="Images"}
		{box title="Images" class="help box"}
			"img src=http://example.com/foo.jpg width=200 height=100 align=center link=http://www.yahoo.com desc=foo"
			displays an image. height, width, desc, link, and alignment are optional
		{/box}

		{box title="Non-cacheable images" class="help box"}
			img src=http://example.com/foo.jpg?nocache=1 width=200 height=100 align=center link=http://www.yahoo.com desc=foo
			displays an image. height, width, desc, link, and align are optional
		{/box}
	{/jstab}

	{jstab title="Links"}
		{box title="WikiLinks" class="help box"}
			(({tr}WikiPageName{/tr})) would automatically link to the wiki page called "WikiPageName" with the text of the link displayed as, "WikiPageName"
			(({tr}WikiPageName|description{/tr})) would automatically create the link for the page called "WikiPageName"for wiki references
			)){tr}SomeName{/tr}(( prevents referencing
		{/box}

		{box title="External links" class="help box"}
			{tr}use square brackets for an external link{/tr}: [URL]
			[URL|{tr}link_description{/tr}]
			[URL|{tr}description{/tr}|nocache]
		{/box}
	{/jstab}

	{jstab title="Miscellaneous"}
		{box title="Lists" class="help box"}
			* {tr}for bullet lists{/tr},<br />
			# {tr}for numbered lists{/tr},<br />
			;{tr}term{/tr}:{tr}definition{/tr} {tr}for definiton lists{/tr}
		{/box}

		{box title="Tables" class="help box"}
			{if $gBitSystemPrefs.feature_wiki_tables eq 'new'}
				"||row1-col1|row1-col2|row1-col3<br />row2-col1|row2-col2col3||"  creates a table
			{else}
				"||row1-col1|row1-col2|row1-col3||row2-col1|row2-col2col3||" {tr}creates a table{/tr}
			{/if}
		{/box}

		{box title="Simple box" class="help box"}
			^{tr}Box content{/tr}^ Creates a box
		{/box}

		{box title="Other" class="help box"}
			"!", "!!", "!!!" makes headings<br />
			"---" makes a horizontal rule<br />
			"-={tr}title{/tr}=-" creates a title bar<br />
			use ...page... to separate pages<br />
			Non parsed sections: "~np~ {tr}data{/tr} ~/np~" Prevents parsing data
		{/box}

		{box title="Dynamic Tags" class="help box"}
			RSS feeds: rss id=n max=m  displays rss feed with id=n maximum=m items<br />
			Dynamic variables: "%{tr}name{/tr}%" Inserts an editable variable<br />
			Dynamic content: content id=n  Will be replaced by the actual value of the dynamic content block with id=n
		{/box}
	{/jstab}

	{if count($plugins) ne 0}
		{jstab title="Plugins"}
			{foreach from=$plugins item=p}
				{box title=`$p.name` class="help box"}
					{if $p.help eq ''}{tr}No description available{/tr}{else}{$p.help}{/if}
					{if $p.syntax}<br/><strong>{tr}Example Syntax{/tr}:</strong>{$p.syntax}<br/>{/if}
					{if $p.exthelp ne ''}<a title="{tr}More Details{/tr}" href="javascript:flip('help-{$p.name}');">{tr}More Details{/tr}</a>
					{if $p.tpopg}<br/>{tr}For additional information about this plugin - see bitweaver.Org or click <a class='wiki' target=_blank href='{$p.tpopg}'><strong>Here</strong></a> to visit.{/tr}<br/>{/if}
					<div id="help-{$p.name}" style="display: none;">{tr}{$p.exthelp}{/tr}</div>{/if}
				{/box}
			{/foreach}
		{/jstab}
	{/if}
{/jstabs}
{/if}
