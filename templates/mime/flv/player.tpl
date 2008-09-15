{strip}
{if $attachment.media_url}
	{assign var=id value="video_`$display_type``$attachment.attachment_id`"}
	{if $uploadTab}{assign var=id value="`$id`_tab"}{/if}
	<div id="{$id}"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this video.</div>
	<script type="text/javascript">/* <![CDATA[ */
		{if $thumbsize eq "small"}
			{assign var=width value=160}
			{math width=$width scrollbar=20 original_width=$attachment.meta.width original_height=$attachment.meta.height equation="original_height / original_width * width + scrollbar" assign=height}
			var so = new SWFObject('{$smarty.const.UTIL_PKG_URL}javascript/flv_player/mediaplayer.swf','player','{$width}','{$height}','7');
		{else}
			var so = new SWFObject('{$smarty.const.UTIL_PKG_URL}javascript/flv_player/mediaplayer.swf','player','{$attachment.meta.width}','{$attachment.meta.height+20}','7');
		{/if}
		so.addVariable("file","{$attachment.media_url}");
		so.addVariable("image","{$attachment.thumbnail_url.medium}");
		so.addVariable("overstretch","fit");
		so.addVariable("usefullscreen","false");
		so.addVariable("frontcolor","0x{$gBitSystem->getConfig('mime_flv_frontcolor','FFFFFF')}");
		so.addVariable("backcolor","0x{$gBitSystem->getConfig('mime_flv_backcolor','000000')}");
		so.write('{$id}');
	/* ]]> */</script>
{/if}
{/strip}
