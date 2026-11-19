{strip}
{if $gBitUser->hasPermission('p_liberty_attach_attachments') }
	<div class="form-group">
		{formlabel label="Upload File(s)"}
		{forminput}
			{if $gBitSystem->getConfig("liberty_attachment_style") == "multiple"}
				{formfeedback warning=$errors.upload}
				<input type="file" name="uploads[]" multiple />
				{formhelp note='You can select several files at once. After selecting them, please return to the edit area and click the save button.'}
			{else}
				<input type="file" name="upload" size="40" />
				{formhelp note='After selecting the file you want to upload, please return to the edit area and click the save button.'}
			{/if}
		{/forminput}
	</div>
{/if}
{/strip}
