{strip}

{if $translationsList}
	<div class="row">
		{formfeedback error=$errors.format}
		{formlabel label="Language" for="lang_code"}
		{forminput}
			<select name="i18n[lang_code]" id="lang_code">
				{foreach from=$translationsList key=langCode item=lang}
					<option value="{$langCode}" {if $smarty.request.i18n.lang_code==$langCode || $pageInfo.lang_code==$langCode || ( $langCode==$gBitSystem->getConfig('bitlanguage') && !$smarty.request.i18n.lang_code && !$gContent->getField('lang_code') )}selected="selected" {/if}>{$lang.native_name}</option>
				{/foreach}
			</select>
			{formhelp note="The language of this page"}
		{/forminput}
	</div>
{/if}

{counter name=nb print=false assign=nb}
{capture name=capture_format assign=capture_format}
	<div class="row">
		{formfeedback error=$errors.format}
		{formlabel label="Content Format"}
		{foreach name=formatPlugins from=$gLibertySystem->mPlugins item=plugin key=guid}
			{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'format'}
				{forminput}
					{counter name=nb print=false assign=nb}
					<label>
						{$plugin.edit_field}
						{if $pageInfo.format_guid eq $plugin.plugin_guid}
							checked="checked"
						{elseif !$pageInfo.format_guid and $plugin.plugin_guid eq $gBitSystem->getConfig('default_format')}
							checked="checked"
						{/if}
						onclick="
							{if $gBitSystem->isPackageActive('quicktags')}
								{foreach from=$gLibertySystem->mPlugins item=tag key=guid}
									{if $tag.is_active eq 'y' and $tag.edit_field and $tag.plugin_type eq 'format'}
										{if $tag.plugin_guid eq $plugin.plugin_guid}
											show
										{else}
											hide
										{/if}
										('qt{$tag.plugin_guid}');
									{/if}
								{/foreach}
							{/if}
						"
					/> {$plugin.edit_label}</label>
					{formhelp note=`$plugin.edit_help`}
				{/forminput}
			{/if}
		{/foreach}
		{forminput}
			{formhelp note="Choose what kind of syntax you want to submit your data in."}
		{/forminput}
	</div>
{/capture}
{if $nb > 2}
	{$capture_format}
{/if}

{/strip}
