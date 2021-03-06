{strip}
<div class="form-group aligncenter">
	{* this should really get a max h or w and base size on the bounding box *}
	{assign var=height value=$attachment.preferences.height}
	{assign var=width value=$attachment.preferences.width}
	{assign var=size value=$smarty.request.size|default:medium}
	{assign var=swfwidth value=$gThumbSizes.$size.width}
	{math assign=multiplier equation="$swfwidth/$width"}
	{math assign=swfheight equation="round($height*$multiplier)"}
	<object width="{$swfwidth}" height="{$swfheight}">
		<param name="movie" value="{$attachment.source_url}" />
		<embed src="{$attachment.source_url}" width="{$swfwidth}" height="{$swfheight}">
		</embed>
		<img src="{$smarty.const.LIBERTY_PKG_URL}templates/noflash.container.gif" width="200" height="100" alt="" />
	</object>
</div>
<div class="pagination">
	{tr}View other sizes{/tr}<br />
	{foreach name=size key=size from=$attachment.thumbnail_url item=url}
		<a href="{$attachment.display_url|escape}{if strpos($attachment.display_url,'?')}&amp;{else}?{/if}size={$size}">{tr}{$size}{/tr}</a>
		{if !$smarty.foreach.size.last}&nbsp;&bull;&nbsp;{/if}
	{/foreach}
</div>
{/strip}
{include file="bitpackage:liberty/mime_meta_inc.tpl"}
