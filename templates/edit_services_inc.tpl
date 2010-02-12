{if !$translateFrom}
	{foreach from=$gLibertySystem->mServices item=service}
		{if $service.services.$serviceFile}
			{include file=$service.services.$serviceFile edit_content_status_tpl=$edit_content_status_tpl}
		{/if}
	{/foreach}
{/if}
