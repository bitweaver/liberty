<!DOCTYPE html 	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>{tr}Attachment Browser{/tr}</title>

	{if $gBitLoc.styleSheet}
		<link rel="stylesheet" title="{$style}" type="text/css" href="{$gBitLoc.styleSheet}" media="all" />
	{/if}
	{if $gBitLoc.browserStyleSheet}
		<link rel="stylesheet" title="{$style}" type="text/css" href="{$gBitLoc.browserStyleSheet}" media="all" />
	{/if}
	{if $gBitLoc.customStyleSheet}
		<link rel="stylesheet" title="{$style}" type="text/css" href="{$gBitLoc.custumStyleSheet}" media="all" />
	{/if}
	{foreach from=$gBitLoc.altStyleSheets item=alt_path key=alt_name}
		<link rel="alternate stylesheet" title="{$alt_name}" type="text/css" href="{$alt_path}" media="screen" />
	{/foreach}

	<script type="text/javascript"><!--
		var tikiCookiePath = "{$gBitLoc.cookie_path}";
		var tikiCookieDomain = "{$gBitLoc.cookie_domain}";
		var tikiIconDir = "{$gBitLoc.LIBERTY_PKG_URL}icons";
		var tikiRootUrl = "{$gBitLoc.BIT_ROOT_URL}";
	--></script>
	<script type="text/javascript" src="{$gBitLoc.KERNEL_PKG_URL}bitweaver.js"></script>

	{literal}
		<script type="text/javascript"><!--
		function returnAttachmentId(attachmentId) {
			self.opener.document.getElementById("existing_attachment_id_input").value = attachmentId;
			self.close();
		}
		--></script>
	{/literal}

	<!--[if gte IE 5.5000]>
		<script type="text/javascript" src="{$gBitLoc.THEMES_PKG_URL}js/pngfix.js"></script>
	<![endif]-->

{strip}
</head>
<body>
	<div id="attbrowser">
		<div class="display attbrowser">
			<div class="header">
				<h1>Attachment Browser</h1>
				<h2>at some point this page should only show attachments that aren't already attached.</h2>
			</div>

			<div class="body">
				{legend legend="Avalable attachments"}
					<noscript>
						<p>Since you don't seem to have javascript enabled, please hover your mouse over an attachment and use the number that appears to attach a given item to your page.
						</p>
					</noscript>

					<table class="data">
						{counter start=-1 name="cells" print=false}
						{foreach from=$userAttachments item=attachment key=foo}
							{counter name="cells" assign="cells" print=false}
							{if $cells % 2 eq 0}
								<tr class="{cycle values="odd,even"}">
							{/if}
							<td>
								<a title="{tr}Attachment id: {$attachment.attachment_id}{/tr}" href="javascript:void();" style="cursor:hand;" onclick="returnAttachmentId({$attachment.attachment_id})">
									<img src="{$attachment.thumbnail_url.small}" alt="{$attachment.filename}" />
									<br />
									{$attachment.filename}
								</a>
							</td>
							{if $cells % 2 ne 0}
								</tr>
							{/if}
						{/foreach}
						{if $cells % 2 eq 0}
							<td>&nbsp;</td></tr>
						{/if}
					</table>
					{formhelp note="Clicking on an item will attach the item to your wiki page."}
				{/legend}
			</div><!-- end .body -->
		</div><!-- end .attbrowser -->
	</div><!-- end #attbrowser -->
</body>
</html>
{/strip}
