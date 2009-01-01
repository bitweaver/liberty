{if $attachment.thumbnail_url.panorama}
	{include file="bitpackage:liberty/mime/image/player.tpl"}
	{include file="bitpackage:liberty/mime_meta_inc.tpl"}
{else}
	{include file=$gLibertySystem->getMimeTemplate('view', $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER)}
{/if}
