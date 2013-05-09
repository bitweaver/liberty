{strip}
{if $gBitUser->hasPermission('p_liberty_attach_attachments') }
	<div class="control-group">
		{formlabel label="PBase image ID"}
		{forminput}
			<input type="input" name="mimeplugin[{$smarty.const.PLUGIN_MIME_GUID_PBASE}][pbase_id]" size="10" />
			{formhelp note='Use the ID number in the URL of the image on <a href="http://www.pbase.com/">PBase</a>.'}
		{/forminput}
	</div>
{/if}
{/strip}
