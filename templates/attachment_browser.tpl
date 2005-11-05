{strip}
	<noscript>
		<p>Please insert <strong>{ldelim}attachment id=#{rdelim}</strong> where # is the appropriate attachment ID.</p>
	</noscript>

	<table class="data">
		<caption>{tr}Available Attachements{/tr}</caption>
		{counter start=-1 name="cells" print=false}
		{foreach from=$userAttachments item=attachment key=foo}
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

	{libertypagination pgnName="pgnPage" pgnPage=$curPage numPages=$numPages offset=$smarty.request.offset open_browser=1}
{/strip}
