{* $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_comments.tpl,v 1.2 2007/09/22 15:07:31 nickpalmer Exp $ *}
{strip}
{if $modLastComments}
	{bitmodule title="$moduleTitle" name="last_comments"}
		<ol>
			{section name=ix loop=$modLastComments}
				<li>
					{$modLastComments[ix].root_content_title}:&nbsp;{$modLastComments[ix].display_link}
					{if $moduleParams.module_params.full}
						<div class="comment row">{$modLastComments[ix].parsed_data}</div>
					{/if}
					{if $moduleParams.module_params.show_date}
						<br /><span class="date">{$modLastComments[ix].last_modified|bit_short_datetime}</span>
					{/if}
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ol>
	{/bitmodule}
{/if}
{/strip}
