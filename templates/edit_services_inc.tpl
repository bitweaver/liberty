{foreach from=$gLibertySystem->mServices item=service}
	{foreach from=$service item=serviceInfo}
		{if $serviceInfo.$serviceFile}
			{include file=$serviceInfo.$serviceFile}
		{/if}
	{/foreach}
{/foreach}
