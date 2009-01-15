{strip}
{if $attachment.media_url}
	<div class="row aligncenter">
		<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="800" height="600" id="viewer" align="middle">
			<param name="allowScriptAccess" value="sameDomain" />
			<param name="movie" value="{$attachment.media_url}" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#ffffff" />
			<embed src="{$attachment.media_url}" quality="high" bgcolor="#ffffff" width="800" height="600" name="myviewer" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
		</object>
	</div>

	{include file=bitpackage:liberty/mime_meta_inc.tpl}
{else}
	{include file=$gLibertySystem->getMimeTemplate('view', $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER)}
{/if}
{/strip}
