{strip}
<div class="row aligncenter">
	{assign var=size value=$smarty.request.size|default:medium}
	{if $gBitSystem->isFeatureActive( 'site_fancy_zoom' )}
		{if $attachment.original}
			<a href="{$attachment.source_url|escape}">
		{else}
			<a href="{$attachment.thumbnail_url.large}">
		{/if}
	{/if}
	<img title="" alt="" src="{$attachment.thumbnail_url.$size}" />
	{if $gBitSystem->isFeatureActive( 'site_fancy_zoom' )}
		</a>
	{/if}
</div>

{if !$attachment.thumbnail_is_mime}
	<div class="pagination">
		{tr}View other sizes{/tr}<br />
		{foreach name=size key=size from=$attachment.thumbnail_url item=url}
			<a href="{$attachment.display_url|escape}{if strpos($attachment.display_url,'?')}&amp;{else}?{/if}size={$size}">{tr}{$size}{/tr}</a>
			{if !$smarty.foreach.size.last}&nbsp;&bull;&nbsp;{/if}
		{/foreach}
		{if $attachment.original}
			&nbsp;&bull;&nbsp;<a href="{$attachment.source_url|escape}">{tr}Original File{/tr}</a>
		{/if}
	</div>
{/if}
{include file="bitpackage:liberty/mime_meta_inc.tpl"}
{/strip}
