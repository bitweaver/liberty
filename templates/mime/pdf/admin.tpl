{* $Header$ *}
{strip}
<div class="admin liberty">
	<div class="header">
		<h1>{tr}PDF Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="PDF Plugin settings"}
			<p class="warning">
				{booticon iname="icon-warning-sign"   iexplain="Warning"} {tr}To make use of this plugin, you need to install <a class="external" href="http://www.swftools.org/">SWF Tools</a>. This will provide all necessary tools to convert uploaded PDF files to shockwave flash files that can be viewed in your browser.{/tr}
			</p>

			{if !$gLibertySystem->isPluginActive( 'mimepdf' )}
				{formfeedback error="This plugins has not been enabled. You need to enable it for these settings to take effect."}
			{/if}

			{formfeedback hash=$feedback}

			{foreach from=$pdfSettings key=feature item=output}
				<div class="control-group">
					{formlabel label=`$output.label` for=$feature}
					{forminput}
						{if $output.type == 'checkbox'}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
						{else}
							<input type='text' name="{$feature}" id="{$feature}" size="{if $output.type == 'text'}40{else}5{/if}" value="{$gBitSystem->getConfig($feature)|escape|default:$output.default}" />
						{/if}
						{formhelp note=`$output.note` page=`$output.page`}
					{/forminput}
				</div>
			{/foreach}

			<div class="control-group submit">
				<input type="submit" name="settings_store" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
