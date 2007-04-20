{strip}
	{include file="bitpackage:liberty/edit_format.tpl"}

	{if $gBitSystem->isFeatureActive('package_smileys')}
		{include file="bitpackage:smileys/smileys_full.tpl"}
	{/if}

	{if $gBitSystem->isFeatureActive('package_quicktags')}
		{include file="bitpackage:quicktags/quicktags_full.tpl"}
	{/if}

	<div class="row">
		{formlabel label="Details"}
		{forminput}
			<textarea {spellchecker rows=$smarty.cookies.rows|default:20} id="{$textarea_id}" name="{$textarea_name|default:edit}" rows="{$smarty.cookies.rows|default:20}" cols="50">{$textarea_data|default:$gContent->mInfo.data|escape:html}</textarea>
		{/forminput}
	</div>
			
	{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl}
{/strip}
