{strip}
{if $gBitUser->hasPermission('p_liberty_attach_attachments') }
	{* thinking of moving this stuff into an upload_function in the default plugin - xing *}
	{php} include (LIBERTY_PKG_PATH."edit_storage_inc.php"); {/php}

	<div class="row">
		{formlabel label="Upload File(s)"}
		{forminput}
			{if $gBitSystem->getConfig("liberty_attachment_style") == "multiple"}
				<div id="upload_div"></div>
				<input type="file" name="upload" size="40" id="uploads" />
				<script type="text/javascript">
					var upload_files   = document.getElementById( 'upload_div' );
					var upload_element = document.getElementById( 'uploads' );
					var multi_selector = new MultiSelector( upload_files, {$gBitSystem->getConfig('liberty_max_multiple_attachments',10)});
					multi_selector.addNamedElement( upload_element , 'uploads');
				</script>
				{formhelp note='After selecting the file you want to upload, please return to the edit area and click the save button.'}
			{elseif $gBitSystem->getConfig("liberty_attachment_style") == "ajax"}
				<input type="file" name="upload" size="40" id="upload" onchange="javascript:LibertyAttachment.uploader(this, '{$smarty.const.LIBERTY_PKG_URL}attachment_uploader.php','{tr}Please wait for the current upload to finish.{/tr}', 'liberty_upload_frame');" />
				{include file="bitpackage:liberty/attachment_uploader_inc.tpl"}
				{formhelp note='After selecting the file you want to upload, please return to the edit area and click the save button.'}
			{else}
				<input type="file" name="upload" size="40" />
				{formhelp note='After selecting the file you want to upload, please return to the edit area and click the save button.'}
			{/if}
		{/forminput}
	</div>
{/if}
{/strip}
