{strip}
	{include file="bitpackage:liberty/edit_format.tpl"}

	{if $gBitSystem->isFeatureActive('package_smileys')}
		{include file="bitpackage:smileys/smileys_full.tpl"}
	{/if}

	{if $gBitSystem->isFeatureActive('package_quicktags')}
		{include file="bitpackage:quicktags/quicktags_full.tpl"}
	{/if}

	<div class="row">
		{forminput}
			<textarea {$textarea_attributes} {spellchecker width=$cols height=$rows} id="{$textarea_id|default:$smarty.const.LIBERTY_TEXT_AREA}" name="{$textarea_name|default:edit}" {$textarea_style}>{$textarea_data|default:$gContent->mInfo.data|escape:html}</textarea>
		{/forminput}
	</div>
{/strip}
