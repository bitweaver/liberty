{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/edit_help_inc.tpl,v 1.23 2007/06/13 19:27:58 wjames5 Exp $ *}

{strip}
	{if $gBitSystem->isFeatureActive( 'site_edit_help' )}
		<h2>{tr}Syntax Help{/tr}</h2>
		{foreach from=$formatplugins item=p}
			<h3>{$p.title|escape|default:"{tr}No Title{/tr}"}</h3>
			{$p.description} {if $p.help_page}{tr}To view syntax help, please visit {jspopup href="http://www.bitweaver.org/wiki/index.php?page=`$p.help_page`" title=$p.help_page class=external}.{/tr}{/if}
		{/foreach}
		<p>{tr}For more information, please visit {jspopup href="http://www.bitweaver.org/" title="www.bitweaver.org" class=external}{/tr}</p>

		{if $dataplugins|count ne 0}
			<h2>{tr}Data Plugin Help{/tr}</h2>
			<div id="hidefromnonjsbrowsers" style="display:none;">
				{tr}Click on the plugin you need help for{/tr}:<br />
				<select size="5" onchange="javascript:flipMulti(this.options[this.selectedIndex].value,1,1);">
					{foreach from=$dataplugins item=p}
						<option value="{$p.plugin_guid}">{$p.title|escape|default:"{tr}No Title{/tr}"} &bull; {ldelim}{$p.tag|lower}{rdelim}</option>
					{/foreach}
				</select>
			</div>

			{foreach from=$dataplugins item=p}
				<script type="text/javascript">/*<![CDATA[*/ document.write( '<div id="{$p.plugin_guid}1" style="display:none;">' ); /*]]>*/</script>
				<h2>{tr}Plugin{/tr}: {$p.title|escape|default:"{tr}No Title{/tr}"}</h2>
					<ul>
						<li class="{cycle values="odd,even"}"><strong>{tr}Syntax{/tr}:</strong>
							<a href="#" title="{tr}Click to insert syntax into editor{/tr}" onclick="javascript:insertAt('{$textarea_id}','{$p.syntax|@addslashes}');">{$p.syntax}</a>
							</li>
						<li class="{cycle}"><strong>{tr}Description{/tr}:</strong> {$p.description}</li>
						<li class="{cycle}"><strong>{tr}Online Help{/tr}:</strong> {jspopup href="http://www.bitweaver.org/wiki/`$p.help_page`" title=`$p.help_page` class="external"}</li>
					</ul>
					<br /><br />
					{$p.exthelp}
				<script type="text/javascript">/*<![CDATA[*/ document.write( '</div>' ); /*]]>*/</script>
			{/foreach}

			<script type="text/javascript">/*<![CDATA[*/
				showById('hidefromnonjsbrowsers');
			/*]]>*/</script>
		{/if}
	{/if}
{/strip}
