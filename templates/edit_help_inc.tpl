{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/edit_help_inc.tpl,v 1.14 2006/04/24 21:38:21 squareing Exp $ *}

{strip}
	{if $gBitSystem->isFeatureActive( 'site_edit_help' )}
		<h2>{tr}Syntax Help{/tr}</h2>
		{foreach from=$formatplugins item=p}
			<h3>{$p.title}</h3>
			{$p.description} {tr}To view syntax help, please visit {jspopup href="http://www.bitweaver.org/wiki/index.php?page=`$p.help_page`" title=$p.help_page class=external}.{/tr}
		{/foreach}
		<p>{tr}For more information, please visit {jspopup href="http://www.bitweaver.org/" title="www.bitweaver.org" class=external}{/tr}</p>

		{if $dataplugins|count ne 0}
			<h2>{tr}Data Plugin Help{/tr}</h2>
			<div id="{$helpWinId}" style="display:none;">
				<table class="data help">
					<tr>
						<td rowspan="2" style="vertical-align:top; width:1px;">
							{tr}Select the plugin{/tr}:<br />
							<select size="15" onchange="javascript:flipMulti(this.options[this.selectedIndex].value,1,2)">
								{foreach from=$dataplugins item=p}
									{if $p.guid eq $FirstPluginWinId}
										<option value="{$p.guid}" selected="selected">{$p.title|escape}</option>
									{else}
										<option value="{$p.guid}">{$p.title|escape}</option>
									{/if}
								{/foreach}
							</select>
						</td>

						<td style="vertical-align:top;">
							{foreach from=$dataplugins item=p}
								<div id="{$p.guid}1" style="display:none;">
									<ul>
										<li class="{cycle values="odd,even"}">{tr}Plugin Syntax{/tr}: {ldelim}{$p.tag|lower}{rdelim}{if $p.requires_pair} text {ldelim}/{$p.tag|lower}{rdelim}{/if}</li>
										<li class="{cycle}">{tr}Syntax{/tr}: <a href="#" title="{tr}Click to insert syntax into editor{/tr}" onclick="javascript:insertAt('{$textarea_id}','{$p.syntax}')">{$p.syntax}</a></li>
										<li class="{cycle}">{tr}Description{/tr}: {$p.description}</li>
										<li class="{cycle}">{tr}Online Help{/tr}: {jspopup href="http://www.bitweaver.org/wiki/`$p.help_page`" title=`$p.help_page`}</a></li>
									</ul>
								</div>
							{/foreach}
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top;">
							{foreach from=$dataplugins item=p}
								<div id="{$p.guid}2" style="display:none;">
									<br /><br />{$p.exthelp}
								</div>
							{/foreach}
						</td>
					</tr>
				</table>
			</div>
			<script type="text/javascript">
				show('{$helpWinId}');
				flipMulti('{$FirstPluginWinId}',1,2);
			</script>
			<noscript>
				{foreach from=$dataplugins item=p}
					{box title=`$p.title` class="help box"}
						<table class="data help">
							<tr class="odd">
								<td title="{tr}The GUID is a string used to locate the Plugins Data.{/tr}">GUID => {$p.guid}</td>
								<td title="{tr}The Tag is the string added to the text that calls the Plugin.{/tr}">tag => {$p.tag}</td>
								<td title="{tr}Provides a Default Activation Value for the Administrator.{/tr}">auto_activate => {$p.auto_activate}</td>
								<td title="{tr}The Number of Code Blocks required by the Plugin. Will be 1 or 2.{/tr}">requires_pair => {$p.requires_pair}</td>
							</tr>
							<tr class="even">
								<td colspan="4" title="{tr}States what the Plugin does.{/tr}">description => {$p.description}</td>
							</tr>
							<tr class="odd">
								<td colspan="2" title="{tr}This function is called when the Tag is found and does the actual work.{/tr}">load_function => {$p.load_function}</td>
								<td colspan="2" title="{tr}This function provides the Help Information displayed below.{/tr}">help_function => {$p.help_function}</td>
							</tr>
							<tr class="even">
								<td colspan="4" title="{tr}The Syntax needed to make the Plugin function (can be inserted into the editor).{/tr}">syntax => {$p.syntax}</td>
							</tr>
							<tr class="odd">
								<td colspan="4">help_page => <a href="http://bitweaver.org/wiki/index.php?page={$p.help_page}">{$p.help_page}</a>
								</td>
							</tr>
						</table>
						{$p.exthelp}
					{/box}
				{/foreach}
			</noscript>
		{/if}
	{/if}
{/strip}
