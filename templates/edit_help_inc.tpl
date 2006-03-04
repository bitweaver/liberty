{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/edit_help_inc.tpl,v 1.10 2006/03/04 11:13:55 squareing Exp $ *}

{strip}
{if $gBitSystem->isFeatureActive( 'wiki_help' )}
	{jstabs}
		{jstab title="Help"}
			{foreach from=$formatplugins item=p}
				{if $p.is_active eq 'y'}
					{box title=`$p.name` class="help box"}
						{if $p.description eq ''}
							{tr}There's no description available for {$p.name}{/tr}
						{else}
							{$p.description}
						{/if}
						{if $p.help_page}
							<br />{tr}To view syntax help, please visit {jspopup href="http://www.bitweaver.org/wiki/index.php?page=`$p.help_page`" title=$p.help_page class=external}.{/tr}
						{/if}
					{/box}
				{/if}
			{/foreach}
			{box title="Syntax Help" class="help box"}
				{tr}For more information, please visit {jspopup href="http://www.bitweaver.org/" title="www.bitweaver.org" class=external}{/tr}
			{/box}
		{/jstab}

		{if count($dataplugins) ne 0}
			{jstab title="Plugin Help"}
				<table class="data help">
					<tr>
						<td style="vertical-align: top; width:1px;">
							{tr}Select the plugin{/tr}:<br />
							<select size="15" onchange="javascript:flipMulti(this.options[this.selectedIndex].value,'2')">
								{foreach from=$dataplugins item=p}
									{if $p.is_active eq 'y'}
										<option value="{$p.windowId}">{$p.title}</option>
									{/if}
								{/foreach}
							</select>
						</td>
						<td style="vertical-align: top;">
							{foreach from=$dataplugins item=p}
								{if $p.is_active eq 'y'}
									<div id="{$p.windowId}" style="display:none;">
										<table class="data help">
											<tr>
												<th colspan="4" style="text-align: center;">{$p.title}</th>
											</tr>
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
												<td colspan="4" title="{tr}A link to a Help Page on bitweaver.org.{/tr}">help_page => {$p.help_page}</td>
											</tr>
											{if !$p.variable_syntax}
												<tr class="even">
													<td colspan="2" style="text-align: center;" title="{tr}Click to Visit the Help Page on bitweaver.org in a new window{/tr}">
														<input type="button" value="Visit the Help Page" onclick="javascript:popUpWin('http://bitweaver/wiki/index.php?page={$p.help_page}','standard',800,800)"></input>
													</td>
													<td colspan="2" style="text-align: center;" title="{tr}Click to Insert the Syntax into the page{/tr}">
														<input type="button" value="Insert the Syntax" onclick="javascript:insertAt('{$textarea_id}','{$p.syntax}')"></input>
													</td>
												</tr>
											{/if}
										</table>
									</div>
								{/if}
							{/foreach}
						</td>
					</tr>
					<tr>
						<td></td>
						<td title="{tr}Parameter Data sent to the Plugin{/tr}">
							{foreach from=$dataplugins item=p}
								{if $p.is_active eq 'y'}
									<div id="{$p.extWinId}" style="display:none;">
										{$p.exthelp}
									</div>
								{/if}
							{/foreach}
						</td>
					</tr>
				</table>
				<script type="text/javascript"> flipMulti('{$firstPlugin}','2'); </script>
{*				<noscript>
					{foreach from=$dataplugins item=p}
						{if $p.is_active eq 'y'}
							{box title=`$p.name` class="help box"}
								{if $p.description eq ''}
									{tr}There's no description available for the plugin {$p.name}{/tr}
								{else}
									{$p.description}
								{/if}
								{if $p.syntax}
									<br/>{tr}Syntax{/tr}: {$p.syntax}<br/>
								{/if}
								{if $p.exthelp ne ''}
									<a title="{tr}Parameter Data sent to the Plugin{/tr}" href="javascript:flip('help-{$p.guid}');">{tr}Display Parameter Data{/tr}</a>
									<div id="help-{$p.guid}" style="display: none;">{$p.exthelp}</div>
								{/if}
								{if $p.help_page}
									<br />{tr}for additional information about this plugin, see {jspopup href="http://www.bitweaver.org/wiki/index.php?page=`$p.help_page`" type=fullscreen title=$p.help_page class=external}.{/tr}<br/>
								{/if}
							{/box}
 						{/if}
					{/foreach}
				</noscript> *****************}
			{/jstab}
		{/if}
	{/jstabs}
{/if}
{/strip}
