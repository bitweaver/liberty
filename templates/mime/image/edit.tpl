{strip}
{if $gBitSystem->isFeatureActive( 'mime_image_panoramas' )}
	<div class="form-group">
		<label class="checkbox">
			<input type="checkbox" value="y" name="plugin[{$attachment.attachment_id}][mimeimage][preference][is_panorama]" id="panorama" {if $attachment.thumbnail_url.panorama}checked="checked"{/if} />Panorama Image
			{formhelp note="If this image is a 360&deg; panoramic image with equirectangular projection, check this box. This works best for images with a 360&deg; by 180&deg; field of view (FOV)"}
		</label>
	</div>
{/if}
{/strip}
