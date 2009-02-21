{strip}
<div class="admin liberty">
	<div class="header">
		<h1>{tr}DBReport Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="DBReport specific settings"}
			{formfeedback hash=$feedback}
			<div class="row">
				{formlabel label="Direct DSN Access" for="dbreports_direct"}
				{forminput}
					<input type="text" id="dbreports_direct" name="dbreports_direct" size="2" maxlength="2" value="{$gBitSystem->getConfig('dbreports_direct')|escape}" />
					{formhelp note="Enable direct DSN entries in reports."}
				{/forminput}
			</div>


			<div class="row submit">
				<input type="submit" name="plugin_settings" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
