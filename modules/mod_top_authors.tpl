{* $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_top_authors.tpl,v 1.3 2008/08/05 01:10:30 laetzer Exp $ *}
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