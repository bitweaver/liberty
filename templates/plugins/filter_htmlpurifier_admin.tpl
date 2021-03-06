<div class="admin liberty">
	<div class="header">
		<h1>{tr}Admin Liberty Plugins{/tr}</h1>
	</div>

	<div class="body">
		{form legend="HTMLPurifier Features"}
			{formfeedback error=$errorMsg}

			<div class="form-group">
				{formlabel label="Blacklisted HTML tags" for="blacklisted_html_tags"}
				{formfeedback warning=$errors.blacklist}
				{forminput}
					<input type="text" id="blacklisted_html_tags" name="blacklisted_html_tags" class="form-control" maxlength="250" value="{$gBitSystem->getConfig('blacklisted_html_tags')|escape}" />
					{formhelp note="A comma seperated list of tags that should NOT be allowed in any content."}
				{/forminput}
			</div>

			{foreach from=$htmlPurifier key=item item=output}
				<div class="form-group">
					{forminput label="checkbox"}
						{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item, $output.default) labels=false id=$item} {tr}{$output.label}{/tr}
						{formhelp note=$output.note page=$output.page}
					{/forminput}
				</div>
			{/foreach}

			<div class="form-group submit">
				<input type="submit" class="btn btn-default" name="apply" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
