{foreach from=$gLibertySystem->mServices item=service}
	{foreach from=$service item=serviceInfo}
		{if $serviceInfo.content_edit_mini_tpl}
			{include file=$serviceInfo.content_edit_mini_tpl}
		{/if}
	{/foreach}
{/foreach}
