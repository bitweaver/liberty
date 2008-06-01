{strip}
{if $attachment.audio_url}
	{if $thumbsize eq "small"}
		{assign var=width value=160}
		{assign var=height value=140}
	{else}
		{assign var=width value=400}
		{assign var=height value=320}
	{/if}
	<div id="audio_player_{$area}{$attachment.attachment_id}"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this video.</div>
	<script type="text/javascript">/* <![CDATA[ */
		{if !$attachment.thumbnail_is_mime && $attachment.thumbnail_url.medium}
			var so = new SWFObject('{$smarty.const.UTIL_PKG_URL}javascript/flv_player/mediaplayer.swf','mpl','{$width}','{$height}','7');
			so.addVariable("image","{$attachment.thumbnail_url.medium}");
		{else}
			var so = new SWFObject('{$smarty.const.UTIL_PKG_URL}javascript/flv_player/mediaplayer.swf','mpl','{$width}','20','7');
		{/if}
		so.addVariable("file","{$attachment.audio_url}");
		so.addVariable("frontcolor","0x{$gBitSystem->getConfig('mime_audio_frontcolor','FFFFFF')}");
		so.addVariable("backcolor","0x{$gBitSystem->getConfig('mime_audio_backcolor','000000')}");
		so.write('audio_player_{$area}{$attachment.attachment_id}');
	/* ]]> */</script>
{/if}

{if $area eq "storage_thumbs"}
	{if $attachment.meta.title}{$attachment.meta.title}<br />{/if}
	<a href="{$attachment.display_url}">{tr}Full Details{/tr}</a>
{/if}
{/strip}
