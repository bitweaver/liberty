{* get all tabbed services *}
{foreach from=$gLibertySystem->mServices item=service}
	{foreach from=$service item=serviceInfo}
		{if $serviceInfo.content_edit_tab_tpl}
			{include file=$serviceInfo.content_edit_tab_tpl}
		{/if}
	{/foreach}
{/foreach}
