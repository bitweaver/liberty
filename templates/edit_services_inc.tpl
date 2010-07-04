{if !$translateFrom}
	{foreach from=$gLibertySystem->mServices item=service key=service_guid}
		{if $service.services.$serviceFile && (empty($gContent) || $gContent->hasService( $service_guid ))}
			<div class="service">
			{include file=$service.services.$serviceFile edit_content_status_tpl=$edit_content_status_tpl}
			</div>
		{/if}
	{/foreach}
{/if}
