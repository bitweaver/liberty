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
						{formhelp note="Enter the login name of a given user. Enter 'all' to view all attachments."}
					{/forminput}
				</div>

				<div class="submit">
					<input type="submit" name="apply" value="{tr}Filter{/tr}" />
				</div>
			{/form}
		{/if}

		{formfeedback warning="Checking for attachment usage in content can take a while depending on the amount of content on this site - if there is a lot of content, it might not be possible at all."}

		{if $smarty.request.attachment_id}
			<ul class="data" id="usage">
				<li>{tr}This attachment is used in the following content{/tr}:
					<ul>
						{foreach from=$attachmentUsage item=content}
							<li class="{cycle values="odd,even"} item">
								{$content.display_link} &bull; {tr}Created by{/tr}: {displayname hash=$content} [{$content.created|bit_short_datetime}]
							</li>
						{foreachelse}
							<li class="norecords">
								{tr}No records found{/tr}
							</li>
						{/foreach}
					</ul>
				</li>
			</ul>
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

					<td class="actionicon">
						{smartlink ititle="Check Attachment Usage" ibiticon="icons/format-justify-fill" attachment_id=$attachment.attachment_id}
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
