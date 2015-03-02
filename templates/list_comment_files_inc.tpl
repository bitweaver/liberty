{strip}
{if $storageHash}
 	{if $errors}
 		{formfeedback warning=$errors}
 	{/if}
	<p>
		<div class="attachments">
		{foreach from=$storageHash item=item key=id}
			<a class="btn btn-default" href="{$item.download_url}">{booticon iname="icon-cloud-download"} {$item.file_name|default:"File `$id`"|escape} <small>({$item.file_size|display_bytes})</small></a> <small>{jspopup notra=1 href="`$item.display_url`&popup=y" title="Details..."}</small>
		{/foreach}
		</div>
	</p>
{/if}
{/strip}
