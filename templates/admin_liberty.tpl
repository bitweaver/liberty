{strip}
{form}
	{jstabs}
		{jstab title="General Settings"}
			{legend legend="Liberty Cache"}
				<div class="row">
					{formlabel label="Liberty Cache" for="liberty_cache"}
					{forminput}
						{html_options name=liberty_cache id=liberty_cache values=$cacheTimes options=$cacheTimes selected=$gBitSystem->getConfig('liberty_cache')}
						{formhelp note='Cache all parsed content. This will dramatically reduce load on the server if pages are called frequently.' page=''}
					{/forminput}
				</div>
			{/legend}

			{legend legend="Attachments"}
				<div class="row">
					{formlabel label="Auto-Display Attachment Thumbnails" for="liberty_auto_display_attachment_thumbs"}
					{forminput}
						{html_options options=$thumbSizes name="liberty_auto_display_attachment_thumbs" id="liberty_auto_display_attachment_thumbs" selected=$gBitSystem->getConfig('liberty_auto_display_attachment_thumbs')}
						{formhelp note='This will automatically display thumbnails of all attachments of a given page (usually in the top right corner of the page). You can still display the items inline as well.' page=''}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Liberty Attachment Style" for="liberty_attachment_style"}
					{forminput}
						{html_radios options=$attachmentStyleOptions values=$attachmentStyleOptions id=liberty_attachment_style name=liberty_attachment_style checked=$gBitSystem->getConfig('liberty_attachment_style', 'standard') separator="<br />"}
						{formhelp note=""}
					{/forminput}
				</div>
			{/legend}

			{legend legend="Miscellaneous"}
				{foreach from=$formLibertyFeatures key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}

				{foreach from=$formLibertyTextareaFeatures key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							<input type="text" name="{$item}" value="{$gBitSystem->getConfig($item, $output.default)}" />
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}

			{legend legend="Spam and Captcha Settings"}
				{foreach from=$formCaptcha key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
							{if $item == "liberty_use_captcha_freecap"}
								<p>{tr}If you can see the image below, you can use freecap{/tr}</p>
								<img src="{$smarty.const.UTIL_PKG_URL}freecap/freecap.php" alt="{tr}Random Image{/tr}" title="{tr}Random Image{/tr}" />
							{/if}
						{/forminput}
					</div>
				{/foreach}
				{foreach from=$formCaptchaTextareaFeatures key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							<input type="text" name="{$item}" value="{$gBitSystem->getConfig($item, $output.default)}" />
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
				<p class="help">{tr}To set additional parameters and options please view and edit the freecap captcha file itself:{/tr} <code>{$smarty.const.UTIL_PKG_PATH}freecap/freecap.php</code></p>
				<p class="warning">{tr}If you can access the following file, please view the freecap.php file for details on how to secure your site against spammers{/tr}: <a href="{$smarty.const.UTIL_PKG_URL}freecap/.ht_freecap_words">{tr}Dictionary{/tr}</a></p>
			{/legend}
		{/jstab}

		{jstab title="Image Processing System"}
			{legend legend="Image Processing System"}
				<input type="hidden" name="page" value="{$page}" />
				{foreach from=$imageProcessors key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							<label>
								<input type="radio" id="{$item}" name="image_processor" value="{$item}" {if !$output.installed}disabled="disabled"{/if} {if $gBitSystem->getConfig('image_processor','gd') == $item}checked="checked"{/if} />
								{if !$output.installed}
									{biticon ipackage=icons iname="large/image-missing" iexplain="Not Installed"} {tr}Library is <strong>not</strong> installed{/tr}
								{else}
									{biticon ipackage=icons iname="large/image-x-generic" iexplain="Installed"} {tr}Library is installed{/tr}
								{/if}
							</label>

							{if !$output.installed}
								{formhelp note=`$output.install_note` page=`$output.page`}
							{/if}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}

				{foreach from=$formImageFeatures key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}

				<div class="row">
					{formlabel label="Image Compression" for="liberty_thumbnail_quality"}
					{forminput}
						{html_options values=$imageCompression options=$imageCompression id=liberty_thumbnail_quality name=liberty_thumbnail_quality selected=$gBitSystem->getConfig('liberty_thumbnail_quality')|default:85}
						{formhelp note="Set the quality you want to have your thumbnails generated in. The higher the value, the better the quality but also the larger the filesize. We recommend a value between 75 and 85."}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Thumbnail Format" for="thumbformat"}
					{forminput}
						{html_options values=$thumbFormats options=$thumbFormats id=thumbformat name=liberty_thumbnail_format selected=$gBitSystem->getConfig('liberty_thumbnail_format')}
						{formhelp note="Every image-type has its pros and cons: jpgs are usually small in size but don't support transparency, gif transparency is limited and pngs can be large. If you let bitweaver select what format to use, we will do our best to pick a sensible filetype based on the format uploaded."}
					{/forminput}
				</div>
			{/legend}
		{/jstab}
	{/jstabs}

	<div class="row submit">
		<input type="submit" name="change_prefs" value="{tr}Change preferences{/tr}" />
	</div>
{/form}
{/strip}
