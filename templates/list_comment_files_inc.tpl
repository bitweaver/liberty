{strip}
{if $storageHash}
	<h4 class="table">
		{if $storageHash|@count > 1}
			{tr}Files{/tr}
		{else}
			{tr}File{/tr}
		{/if}
	</h4>

 	{if $errors}
 		{formfeedback warning=$errors}
 	{/if}

	<table class="table data">
		<tr>
			<th style="width:40%;">{tr}File{/tr}</th>
			<th style="width:10%;">{tr}Type{/tr}</th>
			<th style="width:10%;">{tr}Size{/tr}</th>
			{* not really pertinent but here in case of some future need
			<th style="width:20%;">{tr}Last Modified{/tr}</th>
			*}
		</tr>
		{foreach from=$storageHash item=item key=id}
			<tr class="{cycle values="odd,even"}" >
				<td style="text-align:left;">
					{jspopup notra=1 href="`$item.display_url`&popup=y" title=$item.file_name|default:"File `$id`"|escape}
				</td>
				<td style="text-align:center;">
					{$item.mime_type}
				</td>
				<td style="text-align:center;">
					{$item.file_size|display_bytes}
				</td>
				{* not really pertinent but here in case of some future need
				<td style="text-align:right;">
					{$item.last_modified|bit_short_datetime}
				</td>
				*}
			</tr>
		{/foreach}
	</table>
{/if}
{/strip}
