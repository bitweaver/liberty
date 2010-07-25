{if !$translateFrom}
	{foreach from=$gLibertySystem->mServices item=service key=service_guid}
		{if $service.services.$serviceFile && (empty($gContent) || $gContent->hasService( $service_guid ))}
			{if strpos($serviceFile,'mini')}<div class="service">{/if}
			{include file=$service.services.$serviceFile edit_content_status_tpl=$edit_content_status_tpl}
			{if strpos($serviceFile,'mini')}</div>{/if}
		{/if}
	{/foreach}
{/if}
