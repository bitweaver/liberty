{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin liberty">
	<div class="header">
		<h1>{tr}Comment Settings{/tr}</h1>
	</div>

	<div class="body">
	{form}
		{jstabs}
			{jstab title="Comment Display"}
			{legend legend="Display Settings"}
			{foreach from=$commentSettings key=item item=output}
				<div class="row">
					{formlabel label=`$output.label` for=$item}
					{forminput}
						{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
						{formhelp note=`$output.note` page=`$output.page`}
					{/forminput}
				</div>
			{/foreach}
			
			<div class="row">
				{formlabel label="Editable Time Period" for="comments_edit_minutes"}
				{forminput}
					<input type="text" name="comments_edit_minutes" value="{$gBitSystem->getConfig('comments_edit_minutes', 60)}" />
					{formhelp note="The number of minutes after creation a user can edit their comment."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Comments per Page" for="comments_per_page"}
				{forminput}
					<select name="comments_per_page" id="comments_per_page">
						<option value="5" {if $gBitSystem->getConfig('comments_per_page') eq 5}selected="selected"{/if}>5</option>
						<option value="10" {if $gBitSystem->getConfig('comments_per_page') eq 10}selected="selected"{/if}>10</option>
						<option value="20" {if $gBitSystem->getConfig('comments_per_page') eq 20}selected="selected"{/if}>20</option>
						<option value="50" {if $gBitSystem->getConfig('comments_per_page') eq 50}selected="selected"{/if}>50</option>
						<option value="100" {if $gBitSystem->getConfig('comments_per_page') eq 100}selected="selected"{/if}>100</option>
						<option value="999999" {if $gBitSystem->getConfig('comments_per_page') eq 999999}selected="selected"{/if}>All</option>
					</select>
					{formhelp note="Default number of comments per page."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Default Sort Mode" for="comments_default_ordering"}
				{forminput}
					<select name="comments_default_ordering" id="comments_default_ordering">
						<option value="commentDate_desc" {if $gBitSystem->getConfig('comments_default_ordering') eq 'commentDate_desc'}selected="selected"{/if}>{tr}Newest first{/tr}</option>
						<option value="commentDate_asc" {if $gBitSystem->getConfig('comments_default_ordering') eq 'commentDate_asc'}selected="selected"{/if}>{tr}Oldest first{/tr}</option>
						{*<option value="points_desc" {if $gBitSystem->getConfig('comments_default_ordering') eq 'points_desc'}selected="selected"{/if}>{tr}Points{/tr}</option>*}
					</select>
					{formhelp note="Select the default sort mode for comments."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Comments default display mode" for="comments_default_display_mode"}
				{forminput}
					<select name="comments_default_display_mode" id="comments_default_display_mode">
						<option value="threaded" {if $gBitSystem->getConfig('comments_default_display_mode') eq 'threaded'}selected="selected"{/if}>{tr}Threaded{/tr}</option>
						<option value="flat" {if $gBitSystem->getConfig('comments_default_display_mode') eq 'flat'}selected="selected"{/if}>{tr}Flat{/tr}</option>
					</select>
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Default post textarea lines number" for="comments_default_post_lines"}
				{forminput}
					<select name="comments_default_post_lines" id="comments_default_post_lines">
						<option value="6" {if $gBitSystem->getConfig('comments_default_post_lines') eq 6}selected="selected"{/if}>6</option>
						<option value="10" {if $gBitSystem->getConfig('comments_default_post_lines') eq 10}selected="selected"{/if}>10</option>
						<option value="20" {if $gBitSystem->getConfig('comments_default_post_lines') eq 20}selected="selected"{/if}>20</option>
						<option value="30" {if $gBitSystem->getConfig('comments_default_post_lines') eq 30}selected="selected"{/if}>30</option>
					</select>
					{formhelp note="Default number of lines in the comment post textarea."}
				{/forminput}
			</div>
			{/legend}
			{/jstab}
		{/jstabs}

		<div class="row submit">
			<input type="submit" name="change_prefs" value="{tr}Change preferences{/tr}" />
		</div>
	{/form}

		{capture name=commentUrls}
			<ul>
				{foreach from=$gBitSystem->mAppMenu item=menu}
					{if $menu.admin_comments_url}
						<li><a href="{$menu.admin_comments_url}">{$menu.menu_title}</a></li>
					{/if}
				{/foreach}
			</ul>
		{/capture}

		{if $smarty.capture.commentUrls}
			<h2>{tr}Comment Admin Links{/tr}</h2>
			<p class="help">{tr}Here are links to pages where you can fine-tune your comment settings on a package-specific manner.{/tr}</p>
			{$smarty.capture.commentUrls}
		{/if}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
