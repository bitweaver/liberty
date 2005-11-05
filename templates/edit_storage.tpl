{php} include (LIBERTY_PKG_PATH."edit_storage_inc.php"); {/php}
{if $gContent->hasUserPermission('bit_p_content_attachments')}
{strip}
	{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
		{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'storage'}
			<div class="row">
				{formlabel label=`$plugin.edit_label`}
				{forminput}
					{eval var=$plugin.edit_field}
					{formhelp note=`$plugin.edit_help`}
				{/forminput}
			</div>
		{/if}
	{/foreach}
{/strip}

	<h2><a href="javascript:flip( 'attbrowser' );">{tr}Attachment Browser{/tr}</a> <small>click to show / hide</small></h2>

	<script type="text/javascript">//<![CDATA[
		this.document.write( '<div class="row" style="display:{if $smarty.request.open_browser}block{else}none{/if};" id="attbrowser">' );
	//]]></script>

		<div class="row">
			{formlabel label="Attach File(s)"}
			{forminput}
				<input type="text" name="existing_attachment_id[]" id="existing_attachment_id_input" size="20"/>
				{formhelp note="Attaching an item to your page will insert a small icon representing the file. Please use the attachment IDs listed below.<br />You can attach multiple items at once by seperating them with a ',' (comma)."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Insert Attachment"}
			{forminput}
				<input type="text" name="dummy" id="copy" size="30" class="success" />
				<input type="button" value="{tr}Clear{/tr}" onclick="document.getElementById( 'copy' ).value = '';" />
				{formhelp note="Using this method will display the attachment within your text. See the help at the bottom of the page for more details.<br />Please copy the above to your textarea and insert where needed."}
			{/forminput}
		</div>

		<div class="attbrowser" style="overflow:auto; width:auto; height:400px;">
			{include file="bitpackage:liberty/attachment_browser.tpl"}
		</div>

	<script type="text/javascript">//<![CDATA[
		this.document.write( '</div>' );
	//]]></script>

{strip}
	{if $gContent->mStorage}
		<div class="row">
			<table class="data" summary="List of attached files">
				<caption>{tr}Attached Items{/tr}</caption>
				<tr>
					<th scope="col" title="{tr}Thumbnail{/tr}">{tr}Thumbnail{/tr}</th>
					<th scope="col" title="{tr}File Properties{/tr}">{tr}File Properties{/tr}</th>
				</tr>

				{foreach from=$gContent->mStorage item=storage key=attachmentId}
					<tr class="{cycle values="odd,even"}">
						<td style="text-align:center;"><a href="{$storage.source_url}"><img src="{$storage.thumbnail_url.small}" alt="{$storage.filename}" /></a></td>
						<td>
							Attachment ID: {$attachmentId}
							<br />
							Filename: {$storage.filename}
							<br />
							Actions: 
							{if $gBitUser->isAdmin() || $bit_p_detach_attachment || $storage.user_id == $gBitUser->mUserId}
								<a href="{$attachmentActionBaseURL}&amp;detachAttachment={$storage.attachment_id}">{biticon ipackage=liberty iname="detach" iexplain="detach"}</a>
							{/if}
							{if $gBitUser->isAdmin() ||  $storage.user_id == $gBitUser->mUserId}
								<a href="{$attachmentActionBaseURL}&amp;deleteAttachment={$storage.attachment_id}">{biticon ipackage=liberty iname="delete" iexplain="delete"}</a>
							{/if}
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
	{/if}
{/strip}
{/if}
