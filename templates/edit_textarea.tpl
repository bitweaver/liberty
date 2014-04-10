{strip}
	{if !$textarea_noformat}
		{include file="bitpackage:liberty/edit_format.tpl"}
	{/if}

	{if $gBitSystem->isPackageActive('package_smileys')}
		{include file="bitpackage:smileys/smileys_full.tpl"}
	{/if}

	{if $gBitSystem->isPackageActive('package_quicktags')}
		{include file="bitpackage:quicktags/quicktags_full.tpl"}
	{/if}
{/strip}
	<div class="control-group rt-edit">
		{formlabel label=$textarea_label for=$textarea_id}
		{forminput}
			{formfeedback error=$textarea_error}
			{if !$textarea_id}{assign var=textarea_id value=$smarty.const.LIBERTY_TEXT_AREA}{/if}
			<textarea {$textarea_class} {$textarea_attributes} {if $textarea_maxchars}onkeydown="BitBase.charCounter('{$textarea_id}','{$textarea_id}Counter','{$textarea_maxchars}');" onkeyup="BitBase.charCounter('{$textarea_id}','{$textarea_id}Counter','{$textarea_maxchars}');"{/if} {spellchecker width=$cols height=$rows} id="{$textarea_id}" name="{$textarea_name|default:edit}" {$textarea_style}>{$textarea_edit|escape}</textarea>
			{if $textarea_required}{required}{/if}

			{if $textarea_maxchars}
				<script type="text/javascript">//<![CDATA[
					if( typeof( BitBase ) == 'undefined' ){ldelim}
						BitBase = {ldelim}{rdelim};
					{rdelim};
					BitBase.charCounter = function( textareaId, counterId, maxChars ) {ldelim}
						document.getElementById( counterId ).value = maxChars - document.getElementById( textareaId ).value.length;
					{rdelim}
				//]]></script>
				{assign var=charCount value=$textarea_edit|count_characters:true}
				<div class="formhelp">{tr}Maximum character count:{/tr}{$textarea_maxchars}<br />
					{tr}Characters remaining:{/tr} <input readonly="readonly" type="text" id="{$textarea_id}Counter" size="5" value="{$textarea_maxchars-$charCount}" /></div>
			{/if}

			{formhelp note=$textarea_help}
		{/forminput}
		{if $gBitSystem->isPackageActive('ckeditor')}
			{assign var=wysiwygEdit value=true}
			{/if}
	</div>
