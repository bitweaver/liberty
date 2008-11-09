{strip}
<div class="admin liberty">
	<div class="header">
		<h1>{tr}Code Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Code specific settings"}
			{formfeedback hash=$feedback}
			<div class="row">
				{formlabel label="Default Source" for="liberty_plugin_code_default_source"}
				{forminput}
					{html_options
						options=$sources
						values=$sources
						name=liberty_plugin_code_default_source
						id=liberty_plugin_code_default_source
						selected=$gBitSystem->getConfig('liberty_plugin_code_default_source')|default:php}
						{formhelp note="The default source for the code data plugin."}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="plugin_settings" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
