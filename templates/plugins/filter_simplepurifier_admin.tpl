<div class="admin liberty">
	<div class="header">
		<h1>{tr}Admin Liberty Plugins{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Simple HTML Purifier Features"}
			{formfeedback error=$errorMsg}

			<div class="form-group">
				{formlabel label="Approved HTML tags" for="approved_html_tags"}
				{formfeedback warning=$errors.approved}
				{forminput}
					<input type="text" id="approved_html_tags" name="approved_html_tags" size="50" maxlength="250" value="{$approved_html_tags|escape}" />
					{formhelp note="A list of approved HTML tags. All other tags will be stripped."}
				{/forminput}
			</div>

			<div class="form-group submit">
				<input type="submit" name="apply" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
