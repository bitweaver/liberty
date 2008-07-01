{if $attachment.thumbnail_url.panorama}
	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="100%" height="100%" title="{$attachment.filename}">
		<param name="allowFullScreen" value="true" />
		<param name="movie" value="{$smarty.const.UTIL_PKG_URL}javascript/fspp/fspp.swf?panoSrc={$attachment.thumbnail_url.panorama}" />
		<param name="quality" value="high" />
		<param name="BGCOLOR" value="#AAAAAA" />
		<embed src="{$smarty.const.UTIL_PKG_URL}javascript/fspp/fspp.swf?panoSrc={$attachment.thumbnail_url.panorama}" allowFullScreen="true"
			width="100%" height="500px" quality="high"
			pluginspage="http://www.macromedia.com/go/getflashplayer"
			type="application/x-shockwave-flash" bgcolor="#DDDDDD">
		</embed>
	</object>
	{include file="bitpackage:liberty/mime_meta_inc.tpl"}
	<div class="row">
		{formlabel label="Keyboard Controls"}
		{forminput}
			<ul class="formhelp">
				<li>[Home] - go fullscreen</li>
				<li>{biticon iname="go-previous" iexplain="Left"} {biticon iname="go-next" iexplain="Right"} {biticon iname="go-up" iexplain="Up"} {biticon iname="go-down" iexplain="down"} - navigate</li>
				<li>[PageUp] / [PageDn] - zoom in / zoom out</li>
			</ul>
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Mouse Controls"}
		{forminput}
			<ul class="formhelp">
				<li>[press left button] + movement - navigate</li>
				<li>[wheel up] / [wheel down] - zoom in / zoom ou</li>
			</ul>
		{/forminput}
	</div>
{else}
	{include file="bitpackage:liberty/mime_default_view_inc.tpl"}
{/if}
