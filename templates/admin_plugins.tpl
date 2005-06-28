{strip}

<div class="floaticon">{bithelp}</div>

<div class="admin liberty">
	<div class="header">
		<h1>{tr}Admin Liberty Plugins{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Liberty Plugins"}
			{formfeedback error=$errorMsg}

			{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
				{if $prev_type ne $plugin.plugin_type and $prev_type ne ''}
					</table><!-- close all but last table -->
					<br />
				{/if}

				{if $prev_type ne $plugin.plugin_type}
					<!-- create new table on plugin_type change -->
					<table class="panel">
						<caption>{tr}Plugin Type: {$plugin.plugin_type}{/tr}</caption>
						<tr>
							<th style="width:70%;">{tr}Plugin{/tr}</th>
							{if $plugin.plugin_type eq 'format'}
								<th style="width:5%;">{tr}Default{/tr}</th>
							{/if}
							<th style="width:20%;">{tr}GUID{/tr}</th>
							<th style="width:5%;">{tr}Active{/tr}</th>
						</tr>
				{/if}

				{assign var=prev_type value=$plugin.plugin_type}
				<tr class="{cycle values="odd,even"}">
					<td>
						{if $plugin.plugin_type eq 'data'}
							<h3>{$plugin.title}</h3>
						{/if}
						<label for="{$guid}">
							{$plugin.plugin_description}
						</label>
					</td>
					{if $plugin.plugin_type eq 'format'}
						<td align="center">{if $plugin.is_active == 'y'}{html_radios values=$guid name="default_format" checked=$default_format}{/if}</td>
					{/if}
					<td>{$guid}</td>
					<td align="center">
						{if $plugin.is_active=='x'}
							Missing
						{else}
							{html_checkboxes name="PLUGINS[`$guid`]" values="y" checked=`$plugin.is_active` labels=false id=$guid}
						{/if}
					</td>
				</tr>
			{/foreach}

			</table><!-- close last table -->

			{formfeedback warning="If you disable a format, wiki pages using that format can no longer be edited. Disabling plugins will also disable them in wiki pages, if they are already in use."}

			<div class="row submit">
				<input type="submit" name="pluginsave" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
