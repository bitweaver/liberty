{* $Header$ *}
{strip}
<div class="admin liberty">
	<div class="header">
		<h1>{tr}PDF Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="PDF Plugin settings"}
			<input type="hidden" name="settings" value="{$pluginSettingsGuid|escape}" />
			<input type="hidden" name="tk" value="{$gBitUser->mTicket}" />
			{if !$gLibertySystem->isPluginActive( 'mimepdfx' )}
				{formfeedback error="This plugins has not been enabled. You need to enable it for these settings to take effect."}
			{/if}

			{formfeedback hash=$feedback}

			{foreach from=$pdfSettings key=feature item=output}
				<div class="form-group">
					{formlabel label=$output.label for=$feature}
					{forminput}
						{if $output.type == 'checkbox'}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature,$output.default|default:"") labels=false id=$feature}
						{else}
							<input type='text' name="{$feature}" id="{$feature}" size="{if $output.type == 'text'}40{else}5{/if}" value="{$gBitSystem->getConfig($feature)|escape|default:$output.default}" />
						{/if}
						{formhelp note=$output.note page=$output.page|default:""}
					{/forminput}
				</div>
			{/foreach}

			<div class="form-group submit">
				<input type="submit" name="plugin_settings" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
