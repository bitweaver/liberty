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
								{html_checkboxes name="$item" values="y" checked=`$gBitSystemPrefs.$item` labels=false id=$item}
								{formhelp note=`$output.note` page=`$output.page`}
							{/forminput}
						</div>
					{/foreach}
				</div>

				<div class="row">
					{formlabel label="Acceptable HTML tags" for="approved_html_tags"}
					{formfeedback warning=$errors.warning}
					{forminput}
						<input type="text" id="approved_html_tags" name="approved_html_tags" size="60" maxlength="250" value="{$approved_html_tags|escape}" />
						{formhelp note="List of allowed HTML tags. All other tags will be stripped when users save content. This will affect all format plugins."}
					{/forminput}
				</div>
			{/legend}
		{/jstab}

		{jstab title="Comment Settings"}
			{legend legend="Comment Settings"}
				{foreach from=$formCommentFeatures key=item item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$item}
						{forminput}
							{html_checkboxes name="$item" values="y" checked=`$gBitSystemPrefs.$item` labels=false id=$item}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}

				<div class="row">
					{formlabel label="Comments per Page" for="comments_per_page"}
					{forminput}
						<select name="comments_per_page" id="comments_per_page">
							<option value="10" {if $gBitSystemPrefs.comments_per_page eq 10}selected="selected"{/if}>10</option>
							<option value="20" {if $gBitSystemPrefs.comments_per_page eq 20}selected="selected"{/if}>20</option>
							<option value="50" {if $gBitSystemPrefs.comments_per_page eq 50}selected="selected"{/if}>50</option>
							<option value="100" {if $gBitSystemPrefs.comments_per_page eq 100}selected="selected"{/if}>100</option>
							<option value="999999" {if $gBitSystemPrefs.comments_per_page eq 999999}selected="selected"{/if}>All</option>
						</select>
						{formhelp note="Default number of comments per page."}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Default Sort Mode" for="comments_default_ordering"}
					{forminput}
						<select name="comments_default_ordering" id="comments_default_ordering">
							<option value="commentDate_desc" {if $gBitSystemPrefs.comments_default_ordering eq 'commentDate_desc'}selected="selected"{/if}>{tr}Newest first{/tr}</option>
							<option value="commentDate_asc" {if $gBitSystemPrefs.comments_default_ordering eq 'commentDate_asc'}selected="selected"{/if}>{tr}Oldest first{/tr}</option>
							{*<option value="points_desc" {if $gBitSystemPrefs.comments_default_ordering eq 'points_desc'}selected="selected"{/if}>{tr}Points{/tr}</option>*}
						</select>
						{formhelp note="Select the default sort mode for comments."}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Comments default display mode" for="comments_default_display_mode"}
					{forminput}
						<select name="comments_default_display_mode" id="comments_default_display_mode">
							<option value="threaded" {if $gBitSystemPrefs.comments_default_display_mode eq 'threaded'}selected="selected"{/if}>{tr}Threaded{/tr}</option>
							<option value="flat" {if $gBitSystemPrefs.comments_default_display_mode eq 'flat'}selected="selected"{/if}>{tr}Flat{/tr}</option>
						</select>
					{/forminput}
				</div>
			{/legend}
		{/jstab}

		{jstab title="Image Processing"}
			{legend legend="Image Processing System"}
				<input type="hidden" name="page" value="{$page}" />
				{php}if( extension_loaded( 'gd' ) ) {{/php}{assign var=gdInstalled value=TRUE}{php}}{/php}
				<div class="row">
					{formlabel label="
						<a href='http://www.boutell.com/gd/'>
							<img class='icon' src=\"`$gBitLoc.LIBERTY_PKG_URL`icons/gd_logo.jpg\" alt='GD' />
						</a>
					"}
					{forminput}
						<label>
							<input type="radio" name="image_processor" value="gd" {if !$gdInstalled}disabled="disabled"{/if} {if !$gBitSystemPrefs.image_processor || $gBitSystemPrefs.image_processor=='gd'}checked="checked"{/if} /> gdLibrary
						</label>
						{if !$gdInstalled}
							{formfeedback warning='The GD library is not installed. For newer Linux systems (Fedora, etc.), you need to install the php-gd RPM with a command such as "yum install php-gd".'}
						{/if}
					{/forminput}
				</div>

				{php}if( extension_loaded( 'imagick' ) ) {{/php}{assign var=imagickInstalled value=TRUE}{php}}{/php}
				<div class="row">
					{formlabel label="
						<a href='http://www.imagemagick.org/'>
							<img class='icon' src=\"`$gBitLoc.LIBERTY_PKG_URL`icons/imagick_logo.jpg\" alt='ImageMagick' />
						</a>
					"}
					{forminput}
						<label>
							<input type="radio" name="image_processor" value="imagick" {if !$imagickInstalled}disabled="disabled"{/if} {if $gBitSystemPrefs.image_processor=='imagick'}checked="checked"{/if}/> ImageMagick
						</label>
						{if !$imagickInstalled}
							{formfeedback warning='To use <a href="http://www.imagemagick.org/www/download.html">ImageMagick</a>, you need to install the php-imagick extension. For Linux users, RPM files can be found at <a href="http://phprpms.sourceforge.net/imagick">PHPRPMs</a> (or compile a <a href="http://sourceforge.net/project/showfiles.php?group_id=112092&package_id=139307&release_id=292417">source rpm</a>). Windows users can try <a href="http://www.bitweaver.org/builds/php_imagick.dll">this dll</a> however it has not been tested well.'}
						{/if}
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
