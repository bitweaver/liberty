{strip}
{form}
	{jstabs}
		{jstab title="General Settings"}
			{legend legend="General Settings"}
				<div class="row">
					{foreach from=$formLibertyFeatures key=item item=output}
						<div class="row">
							{formlabel label=`$output.label` for=$item}
							{forminput}
								{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
								{formhelp note=`$output.note` page=`$output.page`}
							{/forminput}
						</div>
					{/foreach}

					<div class="row">
						{formlabel label="Liberty Cache" for="liberty_cache"}
						{forminput}
							<select id="liberty_cache" name="liberty_cache">
								<option value="0"     {if $gBitSystem->getConfig('liberty_cache') eq 0}    selected="selected"{/if}>{tr}0 (no cache){/tr}</option>
								<option value="60"    {if $gBitSystem->getConfig('liberty_cache') eq 60}   selected="selected"{/if}>{tr}1 minute{/tr}</option>
								<option value="300"   {if $gBitSystem->getConfig('liberty_cache') eq 300}  selected="selected"{/if}>{tr}5 minutes{/tr}</option>
								<option value="600"   {if $gBitSystem->getConfig('liberty_cache') eq 600}  selected="selected"{/if}>{tr}10 minutes{/tr}</option>
								<option value="900"   {if $gBitSystem->getConfig('liberty_cache') eq 900}  selected="selected"{/if}>{tr}15 minutes{/tr}</option>
								<option value="1800"  {if $gBitSystem->getConfig('liberty_cache') eq 1800} selected="selected"{/if}>{tr}30 minutes{/tr}</option>
								<option value="3600"  {if $gBitSystem->getConfig('liberty_cache') eq 3600} selected="selected"{/if}>{tr}1 hour{/tr}</option>
								<option value="7200"  {if $gBitSystem->getConfig('liberty_cache') eq 7200} selected="selected"{/if}>{tr}2 hours{/tr}</option>
								<option value="14400" {if $gBitSystem->getConfig('liberty_cache') eq 14400}selected="selected"{/if}>{tr}4 hours{/tr}</option>
							</select>
							{formhelp note='Cache all parsed content. This will dramatically reduce load on the server if pages are called frequently.' page=''}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Auto-Display Attachment Thumbnails" for="liberty_auto_display_attachment_thumbs"}
						{forminput}
							{html_options options=$thumbSizes name="liberty_auto_display_attachment_thumbs" selected=$gBitSystem->getConfig('liberty_auto_display_attachment_thumbs')}
							{formhelp note='This will automatically display thumbnails of all attachments of a given page (usually in the top right corner of the page). You can still display the items inline as well.' page=''}
						{/forminput}
					</div>
				</div>

				<div class="row">
					{formlabel label="Acceptable HTML tags" for="approved_html_tags"}
					{formfeedback warning=$errors.warning}
					{forminput}
						<input type="text" id="approved_html_tags" name="approved_html_tags" size="50" maxlength="250" value="{$approved_html_tags|escape}" />
						{formhelp note="List of allowed HTML tags. All other tags will be stripped when users save content. This will affect all format plugins."}
					{/forminput}
				</div>
			{/legend}
		{/jstab}

		{jstab title="Image Processing"}
			{legend legend="Image Processing System"}
				<input type="hidden" name="page" value="{$page}" />
				{php}if( extension_loaded( 'gd' ) ) {{/php}{assign var=gdInstalled value=TRUE}{php}}{/php}
				{if !$gdInstalled}
					{formfeedback warning='The GD library is not installed. For newer Linux systems (Fedora, etc.), you need to install the php-gd RPM with a command such as "yum install php-gd".'}
				{/if}
				<div class="row">
					{formlabel label="
						<a href='http://www.boutell.com/gd/'>
							<img class='icon' src=\"`$smarty.const.LIBERTY_PKG_URL`icons/gd_logo.jpg\" alt='GD' />
						</a>
					"}
					{forminput}
						<label>
							<input type="radio" name="image_processor" value="gd" {if !$gdInstalled}disabled="disabled"{/if} {if !$gBitSystem->getConfig('image_processor') || $gBitSystem->getConfig('image_processor')=='gd'}checked="checked"{/if} /> gdLibrary
						</label>
					{/forminput}
				</div>

				{php}if( extension_loaded( 'magickwand' ) ) {{/php}{assign var=magickwandInstalled value=TRUE}{php}}{/php}
				{if !$magickwandInstalled}
					{formfeedback warning='To use MagickWand, you need to install the magickwand php extension. Unix and Windows users can find source code at <a href="http://www.magickwand.org/download/php/">the ImageMagick downloads website.</a>.'}
				{/if}
				<div class="row">
					{formlabel label="
						<a href='http://www.imagemagick.org/'>
							<img class='icon' src=\"`$smarty.const.LIBERTY_PKG_URL`icons/imagick_logo.jpg\" alt='ImageMagick' />
						</a>
					"}
					{forminput}
						<label>
							<input type="radio" name="image_processor" value="magickwand" {if !$magickwandInstalled}disabled="disabled"{/if} {if $gBitSystem->getConfig('image_processor')=='magickwand'}checked="checked"{/if}/> ImageMagick's MagickWand
						</label>
					{/forminput}
				</div>

				{php}if( extension_loaded( 'imagick' ) ) {{/php}{assign var=imagickInstalled value=TRUE}{php}}{/php}
				{if !$imagickInstalled}
					{formfeedback warning='php-imagick is a no longer supported PECL extension for PHP + ImageMagick. We recommend using the much better supported magickwand option above. If you need to install the php-imagick extension, linux users can find RPM files at <a href="http://phprpms.sourceforge.net/imagick">PHPRPMs</a> (or compile a <a href="http://sourceforge.net/project/showfiles.php?group_id=112092&package_id=139307&release_id=292417">source rpm</a>). Windows users can try <a href="http://www.bitweaver.org/builds/php_imagick.dll">this dll</a> however it has not been tested well.'}
				{/if}
				<div class="row">
					{formlabel label="
						<a href='http://www.imagemagick.org/'>
							<img class='icon' src=\"`$smarty.const.LIBERTY_PKG_URL`icons/imagick_logo.jpg\" alt='ImageMagick' />
						</a>
					"}
					{forminput}
						<label>
							<input type="radio" name="image_processor" value="imagick" {if !$imagickInstalled}disabled="disabled"{/if} {if $gBitSystem->getConfig('image_processor')=='imagick'}checked="checked"{/if}/> php-imagick
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
