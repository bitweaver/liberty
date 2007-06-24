{strip}
<div class="row">
	{formlabel label=$label|default:"Primary Attachment" for="primary_attachment_id"}
	{forminput}
		<input type="text" name="primary_attachment_id" id="primary_attachment_id" />
		{formhelp note="Enter an existing attachment id to use. Please use the attachment browser to find attachment ids."}
	{/forminput}
</div>

{assign var=primary_attachment_id value=$gContent->getField('primary_attachment_id')}
{if !empty($primary_attachment_id)}
	<div class="row">
		{formlabel label="Current Attachment" for=""}
		{forminput}
			{assign var=storage value=$gContent->mStorage[$primary_attachment_id]}
			{jspopup href=$storage.source_url title=$storage.title|default:$storage.filename notra=1 img=$storage.thumbnail_url.avatar}
			{formhelp note="Click to see large preview"}
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Detach Attachment" for=""}
		{forminput}
			<input type="checkbox" name="detach_primary_attachment">{biticon ipackage=icons iname="edit-cut" iexplain="Detach"}
			{formhelp note="Remove the association with this attachment."}
		{/forminput}
	</div>
{/if}

{* $no_plugins is set by the including template *}
{if $gBitUser->hasPermission('p_liberty_attach_attachments') and !$no_plugins}
	{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
		{if $plugin.is_active eq 'y' and $plugin.primary_edit_field and $plugin.plugin_type eq 'storage'}
			<div class="row">
				{formlabel label="Upload new Attachment" for=""}
				{forminput}
					{$plugin.primary_edit_field}
					{formhelp note=`$plugin.edit_help`}
				{/forminput}
			</div>
		{/if}
	{/foreach}
{/if}
{/strip}
