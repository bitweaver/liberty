{strip}
<div class="row">
	{formlabel label="Panorama Image" for="panorama"}
	{forminput}
		<input type="checkbox" value="y" name="plugin[{$attachment.attachment_id}][mimeimage][preference][is_panorama]" id="panorama" {if $attachment.thumbnail_url.panorama}checked="checked"{/if} />
		{formhelp note="If this image is a 360&deg; panoramic image with equirectangular projection, check this box."}
	{/forminput}
</div>
{/strip}
