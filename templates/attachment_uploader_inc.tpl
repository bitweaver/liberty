{strip}
<noscript>
	{* the tr blocks are intentionally split so the second part only has to be translated once since it is duplicated in the iFrame. *}
	<div class="warning">
		{tr}JavaScript is required for AJAX uploads.{/tr}&nbsp;
		{tr}You must save the content to upload an attachment.{/tr}
	</div>
</noscript>

{* Ensure content_id is sent with the form if possible. *}
<input type="hidden" name="liberty_attachments[content_id]" value="{$gContent->mContentId}" />

{* Note! iFrame MUST not be display: none or Safari pops a window instead. *}
{* I am not dynamically creating the iFrame to give a warning for browsers with no iframe support. *}
<iframe src="about:blank" id="liberty_upload_frame" name="liberty_upload_frame" onload="javascript:liberty_uploader_complete('liberty_upload_frame', 'edit_storage_list_div', 'upload');" style="position: absolute; left: -10000px;">
	<div class="warning">
		{tr}iFrame support is required for AJAX uploads.{/tr}&nbsp;
		{tr}You must save the content to upload an attachment.{/tr}
	</div>
</iframe>
{/strip}
