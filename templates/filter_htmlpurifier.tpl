<div class="admin liberty">
	<div class="header">
		<h1>{tr}Admin Liberty Plugins{/tr}</h1>
	</div>

	<div class="body">
		{form legend="HTMLPurifier Features"}
			{formfeedback error=$errorMsg}

			<div class="row">
				{formlabel label="Blacklisted HTML tags" for="blacklisted_html_tags"}
				{formfeedback warning=$errors.blacklist}
				{forminput}
					<input type="text" id="blacklisted_html_tags" name="blacklisted_html_tags" size="50" maxlength="250" value="{$gBitSystem->getConfig('blacklisted_html_tags')|escape}" />
					{formhelp note="A comma seperated list of tags that should NOT be allowed in any content."}
				{/forminput}
			</div>

			{foreach from=$htmlPurifier key=item item=output}
				<div class="row">
					{formlabel label=`$output.label` for=$item}
					{forminput}
						{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item, $output.default) labels=false id=$item}
						{formhelp note=`$output.note` page=`$output.page`}
					{/forminput}
				</div>
			{/foreach}

			<div class="row submit">
				<input type="submit" name="apply" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
