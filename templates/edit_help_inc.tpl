{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/edit_help_inc.tpl,v 1.15 2006/04/25 07:04:54 squareing Exp $ *}

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
				<table class="data help">
					<tr>
						<td rowspan="2" style="vertical-align:top; width:1px;">
							<div id="hidefromnonjsbrowsers" style="display:none;">
								{tr}Select the plugin{/tr}:<br />
								<select size="15" onchange="javascript:flipMulti(this.options[this.selectedIndex].value,1,1)">
									{foreach from=$dataplugins item=p}
										<option value="{$p.guid}">{$p.title|escape}</option>
									{/foreach}
								</select>
							</div>
						</td>

						<td style="vertical-align:top;">
							{foreach from=$dataplugins item=p}
								<script type="text/javascript">/*<![CDATA[*/
									document.write( '<div id="{$p.guid}1" style="display:none;">' );
								/*]]>*/</script>
									<h2>{tr}Plugin{/tr}: {$p.title}</h2>
									<ul>
										<li class="{cycle values="odd,even"}"><strong>{tr}Syntax{/tr}:</strong> <a href="#" title="{tr}Click to insert syntax into editor{/tr}" onclick="javascript:insertAt('{$textarea_id}','{$p.syntax}')">{$p.syntax}</a></li>
										<li class="{cycle}"><strong>{tr}Description{/tr}:</strong> {$p.description}</li>
										<li class="{cycle}"><strong>{tr}Online Help{/tr}:</strong> {jspopup href="http://www.bitweaver.org/wiki/`$p.help_page`" title=`$p.help_page`}</a></li>
									</ul>
									<br /><br />
									{$p.exthelp}
								<script type="text/javascript">/*<![CDATA[*/
									document.write( '</div>' );
								/*]]>*/</script>
							{/foreach}
						</td>
					</tr>
				</table>
			<script type="text/javascript">/*<![CDATA[*/
				show('hidefromnonjsbrowsers');
				flipMulti('{$FirstPluginWinId}',1,1);
			/*]]>*/</script>
		{/if}
	{/if}
{/strip}
