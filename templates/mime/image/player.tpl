{if $attachment.pano.aspect}
	{assign var=params value="&minPA=-`$attachment.pano.pa`&maxPA=`$attachment.pano.pa`"}
{/if}
{if $wrapper.panosize}
	{assign var=panosize value=$wrapper.panosize}
	{assign var=panowidth  value=$gThumbSizes.$panosize.width}
	{assign var=panoheight value=$gThumbSizes.$panosize.height}
{/if}
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="100%" height="100%" title="{$attachment.filename}">
	<param name="allowFullScreen" value="true" />
	<param name="movie" value="{$smarty.const.UTIL_PKG_URL}javascript/fspp/pan0.swf?panoSrc={$attachment.thumbnail_url.panorama}{$params}" />
	<param name="quality" value="high" />
	<param name="BGCOLOR" value="#AAAAAA" />
	<embed src="{$smarty.const.UTIL_PKG_URL}javascript/fspp/pan0.swf?panoSrc={$attachment.thumbnail_url.panorama}{$params}" allowFullScreen="true"
		width="{$panowidth|default:"100%"}" height="{$panoheight|default:"500px"}" quality="high"
		pluginspage="http://www.macromedia.com/go/getflashplayer"
		type="application/x-shockwave-flash" bgcolor="#DDDDDD">
	</embed>
</object>
