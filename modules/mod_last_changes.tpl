{* $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_changes.tpl,v 1.7 2007/09/30 22:25:43 laetzer Exp $ *}
{strip}
{if $modLastContent}
	{bitmodule title="$moduleTitle" name="last_changes"}
		<ol>
			{section name=ix loop=$modLastContent}
				<li>
					{if !$contentType }
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
		<a class="more" href="{$smarty.const.LIBERTY_PKG_URL}list_content.php?user_id={$gQueryUserId}&amp;sort_mode=last_modified_desc{if $contentType}&amp;content_type_guid={$contentType}{/if}">{tr}View more{/tr}&hellip;</a>
	{/bitmodule}
{/if}
{/strip}
