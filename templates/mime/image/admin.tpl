{* $Header$ *}
{strip}
<div class="admin liberty">
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

			<div class="row">
				{formlabel label="Panorama Size" for="mime_image_panorama_width"}
				{forminput}
					{html_options values=$panWidth options=$panWidth name="mime_image_panorama_width" id="mime_image_panorama_width" selected=$gBitSystem->getConfig('mime_image_panorama_width')|default:3000} {tr}pixels{/tr}
					{formhelp note="Set the maximum panorama image size. The larger the image size, the better it will be for zooming but it will also take longer to download the image for viewing."}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="settings_store" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
