{strip}
<div class="row" style="text-align:center;">
	{assign var=size value=$smarty.request.size|default:medium}
	<img title="" alt="" src="{$attachment.thumbnail_url.$size}" />
</div>

<div class="pagination">
	{tr}View other sizes{/tr}<br />
	{foreach name=size key=size from=$attachment.thumbnail_url item=url}
		<a href="{$attachment.display_url|escape}&amp;size={$size}">{tr}{$size}{/tr}</a>
		{if !$smarty.foreach.size.last}&nbsp;&bull;&nbsp;{/if}
	{/foreach}
</div>
{/strip}
{include file="bitpackage:liberty/mime_meta_inc.tpl"}
