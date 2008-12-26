{strip}
{if $attachment.thumbnail_url.panorama}
	{if $wrapper.output == 'desc' || $wrapper.output == 'description'}
		{if $attachment.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url|default:$attachment.display_url}">{/if}
			{$wrapper.description|escape|default:$attachment.filename}
		{if $attachment.display_url}</a>{/if}
	{else}
		<{$wrapper.wrapper|default:'div'} class="{$wrapper.class|default:'att-plugin'}"{if $wrapper.style} style="{$wrapper.style}{/if}">
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
			{if $wrapper.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url}">{/if}
				{$wrapper.description|escape}<br />
			{if $wrapper.display_url}</a>{/if}
		</{$wrapper.wrapper|default:'div'}>

		<a href="javascript:void(0);" id="navigation-link-{$attachment.attachment_id}" onclick='flip("navigation-{$attachment.attachment_id}");flip("navigation-link-{$attachment.attachment_id}");'>{tr}Image Controls{/tr}</a>
		<div id="navigation-{$attachment.attachment_id}" style="display:none;">
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
		</div>
	{/if}
{else}
	{include file=$gLibertySystem->getMimeTemplate('attachment', $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER)}
{/if}
{/strip}
