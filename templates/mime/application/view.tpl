{strip}
<div class="form-group aligncenter">
	{assign var=size value=$smarty.request.size|default:icon}
	<a href="{$attachment.source_url|escape}">
		<img title="" alt="" src="{$attachment.thumbnail_url.$size}" />
	</a>
</div>
{/strip}
{include file="bitpackage:liberty/mime_meta_inc.tpl"}
