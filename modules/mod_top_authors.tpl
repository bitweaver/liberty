{* $Header$ *}
{strip}
{if $modAuthors}
	{bitmodule title="$moduleTitle" name="top_authors"}
		<ol>
			{section name=ix loop=$modAuthors}
				<li>
					{displayname hash=$modAuthors[ix]}
				</li>
			{/section}
			<li class="more"><a href="{$smarty.const.USERS_PKG_URL}index.php?sort_mode=registration_date_desc">{tr}Show more{/tr} &hellip;</a></li>
		</ol>
	{/bitmodule}
{/if}
{/strip}