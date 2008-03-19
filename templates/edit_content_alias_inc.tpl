{if $gContent->hasUserPermission('p_liberty_edit_content_alias')}
	<div class="row">
		{formlabel label="Alias"}
		{forminput}
			<textarea name="alias_string">{foreach from=$gContent->getAliases() item=alias}{$alias}
{/foreach}</textarea>
			{formhelp note="Enter one alternate name per line. It will be searched in page lookup."}
		{/forminput}
	</div>
{/if}

