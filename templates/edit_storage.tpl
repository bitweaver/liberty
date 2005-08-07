{php}
include (LIBERTY_PKG_PATH."edit_storage_inc.php");
{/php}
{strip}
{if $gContent->hasUserPermission('bit_p_content_attachments')}
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

	<!-- Attach existing attachment -->
	<div class="row">
		{formlabel label="Existing Attachment ID"}
		{forminput}
			<input type="text" name="existing_attachment_id[]" id="existing_attachment_id_input" size="6"/><br />
			<a href="{$smarty.const.LIBERTY_PKG_URL}attachment_browser.php" title="{tr}Opens attachemnt browser in new window{/tr}" onkeypress="popUpWin(this.href,'standard',600,400);" onclick="popUpWin(this.href,'standard',600,400);return false;">{tr}Attachment Browser{/tr}</a>
		{/forminput}
	</div>

	{if $gContent->mStorage}
		<div class="row">
			<table class="data" summary="List of attached files">
				<tr>
					<th scope="col">Thumbnail</th>
					<th scope="col" title="File Properties"></th>
				</tr>
				{foreach from=$gContent->mStorage item=storage key=attachmentId}
				<tr class="{cycle values="odd,even"}">
					<td style="text-align:center;"><a href="{$storage.source_url}"><img src="{$storage.thumbnail_url.small}" alt="{$storage.filename}" /></a></td>
					<td>
						ID: {$attachmentId}
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
{/if}
{/strip}
