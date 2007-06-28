{strip}
	{if !$textarea_noformat}
		{include file="bitpackage:liberty/edit_format.tpl"}
	{/if}

	{if $gBitSystem->isFeatureActive('package_smileys')}
		{include file="bitpackage:smileys/smileys_full.tpl"}
	{/if}

	{if $gBitSystem->isFeatureActive('package_quicktags')}
		{include file="bitpackage:quicktags/quicktags_full.tpl"}
	{/if}

	<div class="row">
		{formlabel label=$textarea_label for=$textarea_id}
		{forminput}
			<textarea {$textarea_class} {$textarea_attributes} {spellchecker width=$cols height=$rows} id="{$textarea_id|default:$smarty.const.LIBERTY_TEXT_AREA}" name="{$textarea_name|default:edit}" {$textarea_style}>{$textarea_data|escape:html}</textarea>
			{formhelp note=$textarea_help}
		{/forminput}
	</div>
{/strip}
