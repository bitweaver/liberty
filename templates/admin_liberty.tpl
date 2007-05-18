{strip}
{form}	
	{jstabs}
		{jstab title="General Settings"}
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
						{html_radios options=$attachmentStyleOptions values=$attachmentStyleOptions id=liberty_attachment_style name=liberty_attachment_style checked=$gBitSystem->getConfig('liberty_attachment_style') separator="<br />"}
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

			{legend legend="Captcha Settings"}
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
				<p class="help">{tr}To set additional parameters and options please view and edit the freecap captcha file itself:{/tr} <code>{$smarty.const.UTIL_PKG_PATH}freecap/freecap.php</code></p>
				<p class="warning">{tr}If you can access the following file, please view the freecap.php file for details on how to secure your site against spammers{/tr}: <a href="{$smarty.const.UTIL_PKG_URL}freecap/.ht_freecap_words">{tr}Dictionary{/tr}</a></p>
			{/legend}
		{/jstab}

		{jstab title="Liberty Cache"}
			{legend legend="Liberty Cache"}
				<div class="row">
					{formlabel label="Liberty Cache" for="liberty_cache"}
					{forminput}
						{html_options name=liberty_cache id=liberty_cache values=$cacheTimes options=$cacheTimes selected=$gBitSystem->getConfig('liberty_cache')}
						{formhelp note='Cache all parsed content. This will dramatically reduce load on the server if pages are called frequently.' page=''}
					{/forminput}
				</div>

				{foreach from=$formLibertyCache key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="HTML Cleanup"}
			{legend legend="Purification System"}
				<div class="row">
					{formlabel label="Purification System"}
					{forminput}
						{html_options name=liberty_html_purifier options=$gLibertySystem->purifyHtmlMethods() selected=$gBitSystem->getConfig('liberty_html_purifier', 'simple')}
						{formhelp note="Which system should be used to purify incoming HTML. The simple algorithm is faster but <strong>far less</strong> robust and secure than <a href=http://htmlpurifier.org>HTML Purifier</a> which has a much richer feature set. HTMLPurifier is recommended to protect against the most XSS attacks. The Simple system is known to <strong>fail XSS smoke tests</strong> and is therefore not recommended."}
					{/forminput}
				</div>
			{/legend}

			{legend legend="Simple Purifier Features"}
				<div class="row">
					{formlabel label="Acceptable HTML tags" for="approved_html_tags"}
					{formfeedback warning=$errors.warning}
					{forminput}
						<input type="text" id="approved_html_tags" name="approved_html_tags" size="50" maxlength="250" value="{$approved_html_tags|escape}" />
						{formhelp note="List of allowed HTML tags. All other tags will be stripped when users save content. This will affect all format plugins and all purification systems."}
					{/forminput}
				</div>
			{/legend}

			{legend legend="HTMLPurifier Features"}
				<div class="row">
					{formlabel label="Blacklisted HTML tags" for="blacklisted_html_tags"}
					{formfeedback warning=$errors.blacklist}
					{forminput}
						<input type="text" id="blacklisted_html_tags" name="blacklisted_html_tags" size="50" maxlength="250" value="{$gBitSystem->getConfig('blacklisted_html_tags')|escape}" />
						{formhelp note="A comma seperated list of tags that should NOT be allowed in any content."}
					{/forminput}
				</div>

				{foreach from=$formLibertyHtmlPurifierFeatures key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item, $output.default) labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Image Processing System"}
			{legend legend="Image Processing System"}
				<input type="hidden" name="page" value="{$page}" />
				{php}if( extension_loaded( 'gd' ) ) {{/php}{assign var=gdInstalled value=TRUE}{php}}{/php}
				{if !$gdInstalled}
					{formfeedback warning='The GD library is not installed. For newer Linux systems (Fedora, etc.), you need to install the php-gd RPM with a command such as "yum install php-gd".'}
				{/if}
				<div class="row">
					{formlabel label="GD library" for="gd"}
					{forminput}
						<label>
							<input type="radio" id="gd" name="image_processor" value="gd" {if !$gdInstalled}disabled="disabled"{/if} {if !$gBitSystem->getConfig('image_processor') || $gBitSystem->getConfig('image_processor')=='gd'}checked="checked"{/if} />
							<br />
							{if !$gdInstalled}
								{biticon ipackage=icons iname="large/image-missing" iexplain="Not Installed"} {tr}Library is <strong>not</strong> installed{/tr}
							{else}
								{biticon ipackage=icons iname="large/image-x-generic" iexplain="Installed"} {tr}Library is installed{/tr}
							{/if}
						</label>
					{/forminput}
				</div>

				{php}if( extension_loaded( 'magickwand' ) ) {{/php}{assign var=magickwandInstalled value=TRUE}{php}}{/php}
				{if !$magickwandInstalled}
					{formfeedback warning='To use MagickWand, you need to install the magickwand php extension. Unix and Windows users can find source code at <a href="http://www.magickwand.org/download/php/">the ImageMagick downloads website.</a>.'}
				{/if}
				<div class="row">
					{formlabel label="ImageMagick MagickWand" for="wand"}
					{forminput}
						<label>
							<input type="radio" id="wand" name="image_processor" value="magickwand" {if !$magickwandInstalled}disabled="disabled"{/if} {if $gBitSystem->getConfig('image_processor')=='magickwand'}checked="checked"{/if}/>
							<br />
							{if !$magickwandInstalled}
								{biticon ipackage=icons iname="large/image-missing" iexplain="Not Installed"} {tr}Library is <strong>not</strong> installed{/tr}
							{else}
								{biticon ipackage=icons iname="large/image-x-generic" iexplain="Installed"} {tr}Library is installed{/tr}
							{/if}
						</label>
					{/forminput}
				</div>

				{php}if( extension_loaded( 'imagick' ) ) {{/php}{assign var=imagickInstalled value=TRUE}{php}}{/php}
				{if !$imagickInstalled}
					{formfeedback warning='php-imagick is a no longer supported PECL extension for PHP + ImageMagick. We recommend using the much better supported magickwand option above. If you need to install the php-imagick extension, linux users can find RPM files at <a href="http://phprpms.sourceforge.net/imagick">PHPRPMs</a> (or compile a <a href="http://sourceforge.net/project/showfiles.php?group_id=112092&amp;package_id=139307&amp;release_id=292417">source rpm</a>). Windows users can try <a href="http://www.bitweaver.org/builds/php_imagick.dll">this dll</a> however it has not been tested well.'}
				{/if}
				<div class="row">
					{formlabel label="ImageMagick PHP-iMagick" for="magick"}
					{forminput}
						<label>
							<input type="radio" id="magick" name="image_processor" value="imagick" {if !$imagickInstalled}disabled="disabled"{/if} {if $gBitSystem->getConfig('image_processor')=='imagick'}checked="checked"{/if}/>
							<br />
							{if !$imagickInstalled}
								{biticon ipackage=icons iname="large/image-missing" iexplain="Not Installed"} {tr}Library is <strong>not</strong> installed{/tr}
							{else}
								{biticon ipackage=icons iname="large/image-x-generic" iexplain="Installed"} {tr}Library is installed{/tr}
							{/if}
						</label>
					{/forminput}
				</div>

				{foreach from=$formImageFeatures key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}
	{/jstabs}

	<div class="row submit">
		<input type="submit" name="change_prefs" value="{tr}Change preferences{/tr}" />
	</div>
{/form}
{/strip}
