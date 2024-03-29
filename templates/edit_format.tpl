{strip}
{* comment or content @TODO pass in as a var in includes *}
{if ( $post_comment_request || $post_comment_preview || $gBitSystem->isFeatureActive('comments_ajax') ) && $gComment}
	{assign var=contentObject value=$gComment}
{else}
	{assign var=contentObject value=$gContent}
{/if}

{if $translationsList}
	<div class="form-group">
		{formfeedback error=$errors.format}
		{formlabel label="Language" for="lang_code"}
		{forminput}
			{if $translateFrom}
				<input type="hidden" name="i18n[from_id]" value="{$translateFrom->mContentId}" />
			{/if}
			<select name="i18n[lang_code]" id="lang_code">
				{foreach from=$translationsList key=codeKey item=lang}
					<option value="{$codeKey}" {if $smarty.request.i18n.lang_code==$codeKey || $langCode==$codeKey || ($codeKey==$gBitSystem->getConfig('bitlanguage') && !$smarty.request.i18n.lang_code && !$langCode)}selected="selected" {/if}>{$lang.native_name}</option>
				{/foreach}
			</select>
			{formhelp note="The language of this page"}
		{/forminput}
	</div>
{/if}

{* We have to count these first because of the tikiwiki format options which may show even if it is the only format option. *}
{assign var=numformat value=0}
{foreach name=formatPlugins from=$gLibertySystem->mPlugins item=plugin key=guid}
	{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'format'}
		{assign var=numformat value=$numformat+1}
		{if $plugin.plugin_guid == "tikiwiki"}
			{assign var=format_options value=true}
		{/if}
		{* this is only used if set once *}
		{assign var=singleplugin value=$plugin}
	{/if}
{/foreach}
{if $numformat > 1 || $format_options}
	<div class="form-group">
		{formfeedback error=$errors.format}
		{formlabel label="Content Format"}
		{foreach name=formatPlugins from=$gLibertySystem->mPlugins item=plugin key=guid}
			{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'format'}
				{forminput label="radio"}
					{if $numformat > 1}
							<input type="radio" name="{$format_guid_variable|default:"format_guid"}" value="{$plugin.edit_field}"
							{if $formatGuid eq $plugin.plugin_guid} checked="checked"
							{elseif !$formatGuid and $plugin.plugin_guid eq $gBitSystem->getConfig('default_format', 'tikiwiki')} checked="checked" {assign var=formatGuid value='tikiwiki'}
							{/if} onclick="
								{if $gBitSystem->isPackageActive('ckeditor')}
									if($(this).val() == 'bithtml') { createCkEditor('{$textarea_id}'); } else { destroyCkEditor('{$textarea_id}'); }
								{/if}
								{if $gBitSystem->isPackageActive('quicktags')}
									{foreach from=$gLibertySystem->mPlugins item=tag key=guid}
										{if $tag.is_active eq 'y' and $tag.edit_field and $tag.plugin_type eq 'format'}
											{if $tag.plugin_guid eq $plugin.plugin_guid}
												BitBase.showById
											{else}
												BitBase.hideById
											{/if}
											('qt{$textarea_id}{$tag.plugin_guid}'); 
										{/if}
									{/foreach}
								{/if}
							"
						/> {$plugin.edit_label}
					{else}
						{$plugin.edit_label}
					{/if}
					{if $plugin.plugin_guid == "tikiwiki"}
						{if !$gBitSystem->isFeatureActive('content_force_allow_html')}
							{if $gBitUser->hasPermission( 'p_liberty_enter_html' ) || $gBitSystem->isFeatureActive('content_allow_html')}
								<label class="inline-block checkbox"><input type="checkbox" name="preferences[content_enter_html]" value="y" id="{$textarea_id}-html" {if $contentObject->mPrefs.content_enter_html}{if $gBitSystem->isPackageActive('ckeditor')}contenteditable="true"{/if} checked="checked" {/if} {*if $gBitSystem->isPackageActive('ckeditor')}onclick="if($(this).is(':checked')) createCkEditor('{$textarea_id}'); else destroyCkEditor('{$textarea_id}');{/if*}"/> {tr}Allow HTML{/tr}</label>
							{elseif is_object($contentObject) && $contentObject->getPreference( 'content_enter_html' )}
								[ {tr}HTML will remain as HTML{/tr} ]
							{else}
								[ {tr}HTML will be escaped{/tr} ]
							{/if}
						{/if}
					{/if}
					{formhelp note=$plugin.edit_help}
				{/forminput}
			{/if}
		{/foreach}
		{if $numformat > 1}
			{forminput}
				{formhelp note="Choose what kind of syntax you want to submit your data in."}
			{/forminput}
		{else}
			<input type="hidden" name="{$format_guid_variable|default:"format_guid"}" value="{$gBitSystem->getConfig('default_format','tikiwiki')}" />
		{/if}
	</div>
{else}
	{* if there was one format in the liberty plugins hash then use it and display a label so user knows what format is being used, otherwise use default and hide it*}
	{if $numformat eq 1}
		<div class="form-group">
			{formlabel label="Content Format"}
			{forminput}
				{$singleplugin.edit_label}
			{/forminput}
		</div>
	{/if}
	<input type="hidden" name="{$format_guid_variable|default:"format_guid"}" value="{if $numformat eq 1}{$singleplugin.edit_field}{else}{$gBitSystem->getConfig('default_format','tikiwiki')}{/if}" />
{/if}

{if $gBitSystem->isPackageActive('ckeditor') && ($formatGuid=='bithtml')}{* || (is_object($contentObject) && $formatGuid=='tikiwiki' && $contentObject->getPreference('content_enter_html')))} *}
<script>
$(document).ready( function() {
createCkEditor('{$textarea_id}');
} );
</script>
{/if}
{/strip}
