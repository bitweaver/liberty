{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/Attic/admin_mime_image.tpl,v 1.2 2008/07/02 10:47:30 squareing Exp $ *}
{strip}
<div class="display liberty">
	<div class="header">
		<h1>{tr}Image Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Image specific settings"}
			{foreach from=$settings key=feature item=output}
				{if $feature == 'mime_image_panoramas' && $image_processor_warning}
					{formfeedback warning="This feature is only available when using the magickwand image processor."}
				{/if}
				<div class="row">
					{formlabel label=`$output.label` for=$feature}
					{forminput}
						{if $output.type == 'checkbox'}
							{if $feature == 'mime_image_panoramas' && $image_processor_warning}
								{html_checkboxes name="$feature" values="y" labels=false id=$feature disabled="disabled"}
							{else}
								{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{/if}
						{else}
							<input type='text' name="{$feature}" id="{$feature}" size="{if $output.type == 'text'}40{else}5{/if}" value="{$gBitSystem->getConfig($feature)|escape}" /> {$output.unit}
						{/if}
						{formhelp note=`$output.note` page=`$output.page`}
					{/forminput}
				</div>
			{/foreach}

			<div class="row submit">
				<input type="submit" name="settings_store" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
