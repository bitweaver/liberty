{php} include (LIBERTY_PKG_PATH."edit_storage_inc.php"); {/php}
{strip}
<script type="text/javascript">/*<![CDATA[*/ show_spinner('spinner'); /*]]>*/</script>
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

{include file="bitpackage:liberty/edit_storage_list.tpl"}

<h2 class="clear"><a href="javascript:ajax_updater( 'attbrowser', '{$smarty.const.LIBERTY_PKG_URL}ajax_attachment_browser.php', 'ajax=true' );">{tr}Attachment Browser{/tr}</a></h2>
<noscript><div class="warning">{tr}The attachment browser only works with javascript enabled.{/tr}</div></noscript>
<div id="attbrowser" class="attbrowser"><p>{tr}Please click on the Attachement Browser link above to view available attachments.{/tr}</p></div>
{/strip}
