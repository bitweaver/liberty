{if $gBitSystem->isFeatureActive( 'site_edit_help' ) && $display_help_tab == 1} {* $display_help_tab is set in {textarea} plugin *}
	{jstab title="Wiki Help"}
		{include file="bitpackage:liberty/edit_help_inc.tpl"}
	{/jstab}
{/if}
