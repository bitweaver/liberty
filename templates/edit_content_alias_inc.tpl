{if $gContent->hasUserPermission('p_liberty_edit_content_alias')}
	<div class="row">
		{formlabel label="Aliases"}
		{forminput}
			<textarea name="alias_string">{foreach from=$gContent->getAliases() item=alias}{$alias}
{/foreach}</textarea>
			{formhelp note="An alias is an alternate page title that will be used in lookup. Enter one alternate name per line."}
		{/forminput}
	</div>
{/if}

