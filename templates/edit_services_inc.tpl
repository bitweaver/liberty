{if !$translateFrom}
	{foreach from=$gLibertySystem->mServices item=service}
		{foreach from=$service item=serviceInfo}
			{if $serviceInfo.$serviceFile}
				{include file=$serviceInfo.$serviceFile edit_content_status_tpl=$edit_content_status_tpl}
			{/if}
		{/foreach}
	{/foreach}
{/if}
