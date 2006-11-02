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
						{if $plugin_type eq 'format'}
							{formfeedback warning="If you disable a format, content pages using that format can no longer be edited."}
						{elseif $plugin_type eq 'data'}
							{formfeedback warning="Disabling plugins will also disable them in content pages, even if they are already in use."}
						{/if}

						<table class="panel">
							<caption>{tr}Plugin Type: {$plugin_type_label}{/tr}</caption>
							<tr>
								<th style="width:70%;">{tr}Plugin{/tr}</th>
								<th style="width:20%;">{tr}GUID{/tr}</th>
								{if $plugin_type eq 'format'}
									<th style="width:5%;">{tr}Default{/tr}</th>
								{/if}
								<th style="width:5%;">{tr}Active{/tr}</th>
							</tr>

							{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
								{if $plugin.plugin_type eq $plugin_type}
									<tr class="{cycle values="odd,even"}">
										<td>
												{if $plugin_type eq 'data'}
													<h3>{$plugin.title|escape}</h3>
												{else}
													<h3>{$plugin.edit_label}</h3>
												{/if}
												{$plugin.description}
										</td>
										<td>{$guid}</td>
										{if $plugin_type eq 'format'}
											<td align="center">{if $plugin.is_active == 'y'}{html_radios values=$guid name="default_format" checked=$gBitSystem->getConfig('default_format')}{/if}</td>
										{/if}
										<td align="center">
											{if $plugin.is_active=='x'}
												Missing
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
							{formfeedback warning="{tr}This will change the way any wiki page that contains HTML will be displayed{/tr}"}
							<div class="row">
								{formlabel label="Allow HTML" for="allow_html"}
								{forminput}
									<input type="checkbox" name="content_allow_html" value="y" {if $gBitSystem->isFeatureActive('content_allow_html')}checked="checked"{/if} />
									This will render HTML in all content pages if it is present. This is a security risk to allow HTML entry by untrusted users, but is usually required for existing installations. For a more controlled environment, assign the <a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php">p_liberty_enter_html permission</a>.
									{formhelp note="Allow the use of HTML in tikiwiki format content."}
								{/forminput}
							</div>
							<div class="row">
								{formlabel label="Allow HTML" for="allow_html"}
								{forminput}
								{/forminput}
							</div>
						{/if}
					{/jstab}
				{/foreach}
			{/jstabs}

			<div class="row submit">
				<input type="submit" name="pluginsave" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
