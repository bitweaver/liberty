{strip}
<noscript>
	{* the tr blocks are intentionally split so the second part only has to be translated once since it is duplicated in the iFrame. *}
	<div class="warning">
		{tr}JavaScript is required for AJAX uploads.{/tr}&nbsp;
		{tr}You must save the content to upload an attachment.{/tr}
	</div>
</noscript>

{* Ensure content_id and content_type_guid are sent with the form if possible. *}
<input type="hidden" name="liberty_attachments[content_id]" value="{$gContent->mContentId}" />
<input type="hidden" name="liberty_attachments[content_type_guid]" value="{$gContent->mContentTypeGuid}" />
<input type="hidden" name="liberty_attachments[title]" value="{* this is a place holder populated by our upload scrpt - see bitewaver.js *}" />
<input type="hidden" name="liberty_attachments[form_id]" value="{$form_id}" />

{* Note! iFrame MUST not be display: none or Safari pops a window instead. *}
{* I am not dynamically creating the iFrame to give a warning for browsers with no iframe support. *}
<iframe src="about:blank" id="liberty_upload_frame_{$form_id}" name="liberty_upload_frame_{$form_id}" onload="javascript:LibertyAttachment.uploaderComplete('liberty_upload_frame_{$form_id}', 'edit_storage_list_{$form_id}', 'upload_{$form_id}', '{$formid|default:editpageform}');" style="position: absolute; left: -10000px;">
	<div class="warning">
		{tr}iFrame support is required for AJAX uploads.{/tr}&nbsp;
		{tr}You must save the content to upload an attachment.{/tr}
	</div>
</iframe>
{/strip}
