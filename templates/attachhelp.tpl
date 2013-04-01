{if $gBitUser->hasPermission( 'p_liberty_attach_attachments' )}
	{capture name=input}
		<input id="attachhelp" class="attachhelp" size="32" value="{$attachhelp}" />
		{if !$nohelp}
			{formhelp note="String to include this file as an attachment in a wiki page, blog post, article etc."}
		{/if}
	{/capture}

	{if $legend}
		<div class="control-group">
			{formlabel label="Attachment" for="attachhelp"}
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
