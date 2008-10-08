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

		{if $smarty.request.attachment_id}
			<ul class="data" id="usage">
				<li>{tr}Attachment ID {$smarty.request.attachment_id} is used in the following content{/tr}:
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
				<th class="width1p"></th>
				<th>{tr}Details{/tr}</th>
			</tr>
			{foreach from=$attachments item=attachment}
				<tr class="{cycle values="odd,even"}">
					<td style="text-align:center">
						<a href="{$attachment.display_url}">
							<img src="{$attachment.thumbnail_url.small}" alt="{$attachment.filename}" title="{tr}Attachment Thumbnail{/tr}" />
						</a>
					</td>

					<td>
						{include file="bitpackage:liberty/mime_meta_inc.tpl" display=1 nohelp=1}
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
