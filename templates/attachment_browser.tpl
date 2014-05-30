{strip}
<div class="form-group">
	{formlabel label="Attach Existing File(s)" for="existing_attachment_id_input"}
	{forminput}
		<input type="text" name="existing_attachment_id[]" id="existing_attachment_id_input" size="20"/>
		{formhelp note="Attaching an item to your page will insert a small icon representing the file. Please use the attachment IDs listed below.<br />You can attach multiple items at once by seperating them with a ',' (comma)."}
	{/forminput}
</div>

{include file="bitpackage:liberty/edit_storage_list.tpl"}

{pagination pgnName="pgnPage" pgnPage=$curPage numPages=$numPages offset=$smarty.request.offset ajaxId=attbrowser}
{/strip}
