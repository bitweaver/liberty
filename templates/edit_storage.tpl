{strip}
{* in some ajax cases we need to uniquely identify portions of a form so we get an id if we dont already have one *}
{if !$form_id}
	{capture name=form_id}
		{form_id}
	{/capture}
	{assign var=form_id value=$smarty.capture.form_id}
{/if}
{* we will use the LibertyMime method if available *}
{if $gLibertySystem->isPluginActive( $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER )}
	{foreach from=$gLibertySystem->getAllMimeTemplates('upload') item=tpl}
		{include file=$tpl}
	{/foreach}

	{include file="bitpackage:liberty/edit_storage_list.tpl" uploadTab=TRUE}

{else}

	{* this condition is a temporary hack to disable ajax uploads on new content to avoid bogus entires in liberty_attachments.
	 * we all want to see this working asap and are thinking of the best way to fix this - xing - Wednesday Nov 14, 2007   18:38:18 CET *}
	{if $gBitSystem->getConfig('liberty_attachment_style') != 'ajax' || $gContent->isValid()}

		{if $gBitUser->hasPermission('p_liberty_attach_attachments') }
			{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
				{* $no_plugins is set by the including template *}
				{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'storage' and !$no_plugins}
					<div class="control-group">
						{formlabel label=`$plugin.edit_label`}
						{forminput}
							{eval var=$plugin.edit_field}
							{formhelp note=`$plugin.edit_help`}
						{/forminput}
					</div>
				{/if}
			{/foreach}
		{/if}

		{include file="bitpackage:liberty/edit_storage_list.tpl" uploadTab=TRUE}
	{else}

		{if $gBitUser->hasPermission('p_liberty_attach_attachments') }
			{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
				{* $no_plugins is set by the including template *}
				{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'storage' and !$no_plugins}
					<div class="control-group">
						{formlabel label=`$plugin.edit_label`}
						{forminput}
							{eval var=$plugin.edit_field_new}
							{formhelp note=`$plugin.edit_help_new`}
						{/forminput}
					</div>
				{/if}
			{/foreach}
		{/if}

	{/if}

	{* end of annoying ajax upload prevention hack *}

{/if}
{/strip}
