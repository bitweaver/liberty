{if $attachment}{strip}
<div class="form-group aligncenter">
	{assign var=size value=$smarty.request.size|default:medium}
	<img title="" alt="" src="{$attachment.thumbnail_url.$size}" class="img-responsive"/>
</div>

{if !$attachment.thumbnail_is_mime}
	<div class="pagination clear">
		{tr}View other sizes{/tr} {foreach name=size key=size from=$attachment.thumbnail_url item=url}
			<a rel="nofollow" href="{$attachment.display_url|escape}{if strpos($attachment.display_url,'?')}&amp;{else}?{/if}size={$size}">{tr}{$size}{/tr}</a>
			{if !$smarty.foreach.size.last} &bull; {/if}
		{/foreach}
		{if $attachment.original} &bull; <a rel="nofollow" href="{$attachment.source_url|escape}">{tr}Original File{/tr}</a> {/if}
	</div>
{/if}
{include file="bitpackage:liberty/mime_meta_inc.tpl"}
{/strip}{/if}
