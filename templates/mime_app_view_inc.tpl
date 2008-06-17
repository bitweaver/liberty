{strip}
<div class="row" style="text-align:center;">
	{assign var=size value=$smarty.request.size|default:icon}
	<img title="" alt="" src="{$attachment.thumbnail_url.$size}" />
</div>
{/strip}
{include file="bitpackage:liberty/mime_meta_inc.tpl"}
