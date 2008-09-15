{strip}
{if $attachment.media_url}
	{assign var=id value="audio_`$display_type``$attachment.attachment_id`"}
	{if $uploadTab}{assign var=id value="`$id`_tab"}{/if}

	{* album covers are usually square - scrollbar is 20px high *}
	{if $thumbsize eq "small"}
		{assign var=width value=160}
		{assign var=height value=180}
	{else}
		{assign var=width value=400}
		{assign var=height value=420}
	{/if}
	<div id="{$id}"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this video.</div>
	<script type="text/javascript">/* <![CDATA[ */
		{if !$attachment.thumbnail_is_mime && $attachment.thumbnail_url.medium}
			var so = new SWFObject('{$smarty.const.UTIL_PKG_URL}javascript/flv_player/mediaplayer.swf','mpl','{$width}','{$height}','7');
			so.addVariable("image","{$attachment.thumbnail_url.medium}");
		{else}
			var so = new SWFObject('{$smarty.const.UTIL_PKG_URL}javascript/flv_player/mediaplayer.swf','mpl','{$width}','20','7');
		{/if}
		so.addVariable("file","{$attachment.media_url}");
		so.addVariable("frontcolor","0x{$gBitSystem->getConfig('mime_audio_frontcolor','FFFFFF')}");
		so.addVariable("backcolor","0x{$gBitSystem->getConfig('mime_audio_backcolor','000000')}");
		so.write('{$id}');
	/* ]]> */</script>
{/if}
{/strip}
