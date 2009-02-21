{strip}
<div class="admin liberty">
	<div class="header">
		<h1>{tr}DBReport Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="DBReport specific settings"}
			{formfeedback hash=$feedback}
			{legend legend="DBReport Access Settings"}
				{foreach from=$formEnable key=feature item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$feature}
						{forminput}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}


			<div class="row submit">
				<input type="submit" name="change_prefs" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
