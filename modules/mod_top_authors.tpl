{* $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_top_authors.tpl,v 1.1 2005/06/19 04:55:51 bitweaver Exp $ *}
{strip}
{if $modAuthors}
	{bitmodule title="$moduleTitle" name="top_authors"}
		<ol>
			{section name=ix loop=$modAuthors}
				<li>
					{displayname hash=$modAuthors[ix]}
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ol>
{*		<div style="text-align:center;"><a href="{$gBitLoc.LIBERTY_PKG_URL}list_content.php?user_id={$gQueryUserId}&sort_mode=last_modified_desc">{tr}View more{/tr}...</a></div> *}
	{/bitmodule}
{/if}
{/strip}
