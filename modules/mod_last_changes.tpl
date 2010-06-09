{* $Header$ *}
{strip}
{if $modLastContent}
	{bitmodule title="$moduleTitle" name="last_changes"}
		<ol>
			{section name=ix loop=$modLastContent}
				<li>
					{if !$contentType }
						<strong>{tr}{$modLastContent[ix].content_name}{/tr}: </strong>
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
