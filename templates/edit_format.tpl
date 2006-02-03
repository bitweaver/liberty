{strip}

<div class="row">
	{formfeedback error=$errors.format}
	{formlabel label="Content Format"}
	{foreach name=formatPlugins from=$gLibertySystem->mPlugins item=plugin key=guid}
		{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'format'}
			{forminput}
				<label>
					{$plugin.edit_field}
					{if $pageInfo.format_guid eq $plugin.plugin_guid}
						checked="checked"
					{elseif !$pageInfo.format_guid and $plugin.plugin_guid eq $gBitSystemPrefs.default_format}
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

{/strip}
