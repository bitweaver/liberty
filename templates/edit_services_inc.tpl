{if !$translateFrom && $gContent}
	{foreach from=$gLibertySystem->mServices item=service key=service_guid}
		{if $gContent->hasService( $service_guid ) && $service.services.$serviceFile}
			{include file=$service.services.$serviceFile edit_content_status_tpl=$edit_content_status_tpl}
		{/if}
	{/foreach}
{/if}
