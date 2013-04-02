{strip}

<div class="floaticon">{bithelp}</div>

<div class="admin liberty">
	<div class="header">
		<h1>{tr}Admin Liberty Plugins{/tr}</h1>
	</div>

	<div class="body">
		{form}
			{formfeedback error=$errorMsg}
			{jstabs}
				{foreach from=$pluginTypes item=plugin_type key=plugin_type_label}
					{jstab title="$plugin_type_label"}
						{legend legend=$plugin_type|capitalize|cat:" Plugins"}
							{if $plugin_type eq 'format'}
								{formfeedback warning="If you disable a format, content pages using that format can no longer be edited."}
							{elseif $plugin_type eq 'storage'}
								{formfeedback warning="These plugins have been replaced by the mime plugins. We are working on removing these."}
							{elseif $plugin_type eq 'data'}
								{formfeedback warning="Disabling plugins will also disable them in content pages, even if they are already in use."}
							{/if}

							<table class="panel">
								<caption>{tr}Plugin Type: {$plugin_type_label}{/tr}</caption>
								<tr>
									<th class="width85p">{tr}Plugin{/tr} <small>[guid]</small></th>
									{if $plugin_type eq 'format'}
										<th class="width5p">{tr}Default{/tr}</th>
										<th class="width10p">{tr}Active{/tr}</th>
									{else}
										<th class="width15p">{tr}Active{/tr}</th>
									{/if}
								</tr>

								{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
									{if $plugin.plugin_type eq $plugin_type}
										<tr class="{cycle values="odd,even"}">
											<td>
												{if $plugin.edit_label}
													<h3>
														<label for="{$guid}">{$plugin.edit_label} <small>[{$guid}]</small></label>
													</h3>
												{else}
													<h3>
														<label for="{$guid}">{$plugin.title|escape} <small>[{$guid}]</small></label>
													</h3>
												{/if}
												<label for="{$guid}">{$plugin.description}</label>
												{if $plugin.requirements.output}
													{formfeedback hash=$plugin.requirements.output}
												{/if}
											</td>
											{if $plugin_type eq 'format'}
												<td class="aligncenter">
													{if $plugin.is_active == 'y'}{html_radios values=$guid name="default_format" checked=$gBitSystem->getConfig('default_format')}{/if}
												</td>
											{/if}
											<td class="actionicon">
												{if $plugin.help_page}
													{jspopup href="http://www.bitweaver.org/wiki/`$plugin.help_page`" ibiticon="icons/dialog-information" title=`$plugin.help_page` class="external"}
												{/if}
												{if $plugin.plugin_settings_url}
													<a href="{$plugin.plugin_settings_url}">{booticon iname="icon-edit"   iexplain="Plugin Settings"}</a>
												{/if}
												{if $plugin.is_active == 'x'}
													Missing
												{elseif $plugin.plugin_type == 'mime' && $guid == $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER}
													{biticon iname="dialog-ok" iexplain="Default"}
													<input type="hidden" name="PLUGINS[{$guid}]" value="y" />
												{else}
													{html_checkboxes name="PLUGINS[`$guid`]" values="y" checked=`$plugin.is_active` labels=false id=$guid}
												{/if}
											</td>
										</tr>
									{/if}
								{/foreach}
							</table>

							<br />

							{if $plugin_type eq 'format'}
								{formfeedback warning="{tr}This will change the way any wiki page that contains HTML will be displayed. We recommend turning on HTMLPurifier if either of these is on.{/tr}"}
								<div class="control-group">
									{formlabel label="Allow HTML" for="allow_html"}
									{forminput}
										<input type="checkbox" name="content_allow_html" id="allow_html" value="y" {if $gBitSystem->isFeatureActive('content_allow_html')}checked="checked"{/if} />
										This will render HTML in all content pages if it is present. This is a security risk to allow HTML entry by untrusted users, but is usually required for existing installations. For a more controlled environment, assign the <a href="{$smarty.const.USERS_PKG_URL}admin/edit_role.php">p_liberty_enter_html permission</a>.
										{formhelp note="Allow the use of HTML in tikiwiki format content."}
									{/forminput}
								</div>
								<div class="control-group">
									{formlabel label="Force Allow HTML" for="force_allow_html"}
									{forminput}
										<input type="checkbox" name="content_force_allow_html" id="force_allow_html" value="y" {if $gBitSystem->isFeatureActive('content_force_allow_html')}checked="checked"{/if} />
										This will force HTML to be allowed for all users in tikiwiki format content. We require this on if you are using CKEditor and recommended it with any other WYSIWYG editor.
										{formhelp note="This will force the allowance of HTML in tikiwiki format content for all users."}
									{/forminput}
								</div>
							{/if}
						{/legend}
					{/jstab}
				{/foreach}
			{/jstabs}

			<div class="control-group submit">
				<input type="submit" name="pluginsave" value="{tr}Save Plugin Settings{/tr}" />
			</div>

			<div class="control-group">
				{formlabel label="Reset all plugin settings" for=""}
				{forminput}
					<input type="submit" name="reset_all_plugins" value="{tr}Reset Plugins{/tr}" />
					{formhelp note="This will remove all plugin settings from the database and reset them to the default values. This can be useful if some plugins don't seem to work or you simply want to reset all values on this page."}
				{/forminput}
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
