{if $gContent->hasUserPermission('p_liberty_edit_content_alias')}
	<div class="control-group">
		{formlabel label="Aliases"}
		{forminput}
			<textarea name="alias_string" rows="2" cols="35">{if $smarty.post.preview}{$pageInfo.alias_string}{else}{foreach from=$gContent->getAliases() item=alias}{$alias|cat:"\r"}{/foreach}{/if}</textarea>
			{formhelp note="An alias is an alternate page title that will be used in lookup. Enter one alternate name per line."}
		{/forminput}
	</div>
{/if}
