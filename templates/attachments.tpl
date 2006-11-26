{strip}
<div class="listing liberty">
	<div class="header">
		<h1>{tr}Liberty Attachments{/tr}</h1>
	</div>

	<div class="body">
		{if $gBitUser->isAdmin()}
			{form legend="Attachment Filter"}
				{formfeedback hash=$feedback}

				<div class="row">
					{formlabel label="User" for=""}
					{forminput}
					<input type="text" name="login" value="{$smarty.request.login}" />
						{formhelp note="Enter the login name of a given user."}
					{/forminput}
				</div>

				<div class="submit">
					<input type="submit" name="apply" value="{tr}Filter{/tr}" />
				</div>
			{/form}
		{/if}

		<table class="data">
			<caption>{tr}Liberty Attachments{/tr}</caption>
			<tr>
				<th style="width:1px"></th>
				<th>{tr}Details{/tr}</th>
				<th>{tr}Actions{/tr}</th>
			</tr>
			{foreach from=$attachments item=attachment}
				<tr class="{cycle values="odd,even"}">
					<td style="text-align:center">
						<a href="{$attachment.source_url}">
							<img src="{$attachment.thumbnail_url.small}" alt="{$attachment.filename}" title="{tr}Attachment Thumbnail{/tr}" />
						</a>
					</td>
					<td>
						{tr}Owner{/tr}: {displayname hash=$attachment}
						<br />
						{tr}Size{/tr}: {$attachment.file_size|kbsize}
						<br />
						{tr}Wiki plugin link{/tr}: {$attachment.wiki_plugin_link}
					</td>
					<td>
					</td>
				</tr>
			{foreachelse}
				<tr class="norecords"><td colspan="3">
					{tr}No records found{/tr}
				</td></tr>
			{/foreach}
		</table>

		{pagination login=$smarty.request.login}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
