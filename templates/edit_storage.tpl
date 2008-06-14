{strip}
{* we will use the LibertyMime method if available *}
{if $gLibertySystem->isPluginActive( $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER )}
	{foreach from=$gLibertySystem->getAllMimeTemplates('upload') item=tpl}
		{include file=$tpl}
	{/foreach}

{else}

{* this condition is a temporary hack to disable ajax uploads on new content to avoid bogus entires in liberty_attachments.
 * we all want to see this working asap and are thinking of the best way to fix this - xing - Wednesday Nov 14, 2007   18:38:18 CET *}
{if $gBitSystem->getConfig('liberty_attachment_style') != 'ajax' || $gContent->isValid()}

{if $gBitUser->hasPermission('p_liberty_attach_attachments') }
	{php} include (LIBERTY_PKG_PATH."edit_storage_inc.php"); {/php}
	{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
		{* $no_plugins is set by the including template *}
		{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'storage' and !$no_plugins}
			<div class="row">
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
	{php} include (LIBERTY_PKG_PATH."edit_storage_inc.php"); {/php}
	{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
		{* $no_plugins is set by the including template *}
		{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'storage' and !$no_plugins}
			<div class="row">
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

{* The new attachment browser is supposd to only provide an easy means of
viewing existing content that can be inserted into the contnet using
{attachment} or {content} or similar. there is no means to attach and detach
content anymore *}
{*if $gBitUser->hasPermission('p_liberty_attach_attachments') }
	<h2 class="clear"><a href="javascript:void(0);" onclick="BitAjax.updater( 'attbrowser', '{$smarty.const.LIBERTY_PKG_URL}ajax_attachment_browser.php', 'ajax=true&amp;content_id={$gContent->mContentId}' );">{tr}Attachment Browser{/tr}</a></h2>
	<noscript><div class="warning">{tr}The attachment browser only works with javascript enabled.{/tr}</div></noscript>
	<div id="attbrowser" class="attbrowser"><p>{tr}Please click on the Attachement Browser link above to view available attachments.{/tr}</p></div>
{else}
	<p>{tr}Sorry - you do not have permission to attach files to this content.{/tr}</p>
{/if*}

{/if}
{/strip}
