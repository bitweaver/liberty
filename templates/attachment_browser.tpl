{strip}
<div class="row">
	{formlabel label="Attach Existing File(s)" for="existing_attachment_id_input"}
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
		{formhelp note="Clicking on any of the attachments below, will display the correct attachment syntax in the textbox above. Insert this text into the textarea where needed."}
	{/forminput}
</div>

<table class="data">
	<caption>{tr}Available Attachements{/tr} <span class="total">[ {$userAttachments.cant} ]</span></caption>
	{counter start=-1 name="cells" print=false}
	{foreach from=$userAttachments.data item=attachment key=foo}
		{counter name="cells" assign="cells" print=false}
		{if $cells % 2 eq 0}
			<tr class="{cycle values="odd,even"}">
		{/if}

		<td>
			<a title="{tr}Attachment id: {$attachment.attachment_id}{/tr}" href="javascript:insertAt( 'copy', '{ldelim}attachment id={$attachment.attachment_id}{rdelim}' );">
				<img src="{$attachment.thumbnail_url.small}" alt="{$attachment.filename}" /><br />
				{$attachment.filename}<br />
				Attachment ID: {$attachment.attachment_id}
			</a>
		</td>

		{if $cells % 2 ne 0}
			</tr>
		{/if}
	{foreachelse}
		<tr class="norecords"><td>{tr}No Records Found{/tr}</td></tr>
	{/foreach}

	{if $cells % 2 eq 0}
		<td>&nbsp;</td></tr>
	{/if}
</table>

{libertypagination pgnName="pgnPage" pgnPage=$curPage numPages=$numPages offset=$smarty.request.offset}
{/strip}
