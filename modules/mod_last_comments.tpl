{* $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_comments.tpl,v 1.1 2007/05/21 20:44:06 squareing Exp $ *}
{strip}
{if $modLastComments}
	{bitmodule title="$moduleTitle" name="last_comments"}
		<ol>
			{section name=ix loop=$modLastComments}
				<li>
					{$modLastComments[ix].root_content_title}: {$modLastComments[ix].display_link}
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
