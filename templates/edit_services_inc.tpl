{if !$translateFrom}
	{foreach from=$gLibertySystem->mServices item=service key=service_guid}
		{if $service.services.$serviceFile && (empty($gContent) || $gContent->hasService( $service_guid ))}
			{include file=$service.services.$serviceFile edit_content_status_tpl=$edit_content_status_tpl}
		{/if}
	{/foreach}
{/if}
