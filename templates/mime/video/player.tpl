{strip}
{if $attachment.media_url}
	{assign var=id value="video_`$caller``$attachment.attachment_id`"}
	{if $uploadTab}{assign var=id value="`$id`_tab"}{/if}
	{if $thumbsize eq "small"}
		{assign var=width value=160}
		{math width=$width original_width=$attachment.meta.width original_height=$attachment.meta.height equation="original_height / original_width * width" assign=height}
	{else}
		{assign var=width value=$attachment.meta.width}
		{assign var=height value=$attachment.meta.height}
	{/if}
	{if $attachment.media_url|regex_replace:'/\?.*$/':''|substr:-4|lower eq '.flv'}
		{* Flash video: no browser can play it any more *}
		<a href="{$attachment.media_url|escape}">{$attachment.filename|default:"{tr}Download video{/tr}"|escape}</a>
	{else}
		<video id="{$id}" class="mime-video" controls preload="metadata" playsinline style="max-width:100%;height:auto"
			{if $width} width="{$width|escape}"{/if}{if $height} height="{$height|escape}"{/if}
			{if $attachment.thumbnail_url.$thumbsize|default:$attachment.thumbnail_url.large} poster="{$attachment.thumbnail_url.$thumbsize|default:$attachment.thumbnail_url.large|escape}"{/if}>
			<source src="{$attachment.media_url|escape}" type="video/mp4" />
			<a href="{$attachment.media_url|escape}">{tr}Download video{/tr}</a>
		</video>
	{/if}
{/if}
{/strip}
