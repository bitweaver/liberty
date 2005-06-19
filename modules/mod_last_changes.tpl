{* $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_changes.tpl,v 1.1 2005/06/19 04:55:51 bitweaver Exp $ *}
{strip}
{if $modLastContent}
	{bitmodule title="$moduleTitle" name="last_changes"}
		<ol>
			{section name=ix loop=$modLastContent}
				<li>
					{if $showContentType }
						<strong>{tr}{$modLastContent[ix].content_description}{/tr}: </strong>
					{/if}
					{$modLastContent[ix].display_link}
					{if $showDate}
						<br/><span class="date">{$modLastContent[ix].last_modified|bit_long_date}</span>
					{/if}
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ol>
		<a href="{$gBitLoc.LIBERTY_PKG_URL}list_content.php?user_id={$gQueryUserId}&amp;sort_mode=last_modified_desc">{tr}View more{/tr}&hellip;</a>
	{/bitmodule}
{/if}
{/strip}
