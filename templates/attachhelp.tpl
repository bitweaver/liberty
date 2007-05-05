{if $gBitUser->hasPermission( 'p_liberty_attach_attachments' )}
	{capture name=input}
		{tr}To include this file in a wiki page, blog post, article &hellip;, use the following string{/tr}:
		{if $attachment.legend}
			<br />
		{/if}
		<input id="attachhelp" class="attachhelp" size="32" value="{ldelim}attachment id={$attachment.attachment_id}{if $smarty.request.size} size={$smarty.request.size}{/if}{rdelim}" />
	{/capture}

	{if $attachment.legend}
		<div class="row">
			{formlabel label="Attachment help" for="attachhelp"}
			{forminput}
				{$smarty.capture.input}
			{/forminput}
		</div>
	{else}
		<p class="formhelp">
			{$smarty.capture.input}
		</p>
	{/if}
{/if}