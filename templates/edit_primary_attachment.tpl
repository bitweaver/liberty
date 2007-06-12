{strip}
{assign var=primary_attachment_id value=$gContent->getField('primary_attachment_id')}
{if !empty($primary_attachment_id)}
	<div class="row">
		{assign var=storage value=$gContent->mStorage[$primary_attachment_id]}
		{jspopup href=$storage.source_url title=$storage.title|default:$storage.filename notra=1 img=$storage.thumbnail_url.avatar}
		{formhelp note="click to see large preview"}
	</div>
	<div class="row">
		<input type=checkbox name="detach_primary_attachment">{biticon ipackage=icons iname="edit-cut" iexplain="Detach"}
		{formhelp note="Remove the association with this attachment."}
	</div>
{/if}
<div class="row">
	<input type=text name="primary_attachment_id" id=primary_attachment />
	{formhelp note="Enter an existing attachment id to use."}
</div>
{if $gBitUser->hasPermission('p_liberty_attach_attachments') }
	{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
		{* $no_plugins is set by the including template *}
		{if $plugin.is_active eq 'y' and $plugin.primary_edit_field and $plugin.plugin_type eq 'storage' and !$no_plugins}
			<div class="row">
				{eval var=$plugin.primary_edit_field}
				{formhelp note=`$plugin.edit_help`}
			</div>
		{/if}
	{/foreach}
{/if}
{/strip}
