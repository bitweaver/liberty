{strip}
{* $Header$ *}

{if $dataplugins}
	{jstab title="Plugins"}
		{if $dataplugins}
			<h2>{tr}Data Plugin Help{/tr}</h2>
			<div {if !$gBitThemes->isJavascriptEnabled()}style="display:none;"{/if}>
				{tr}Click on the plugin you need help for{/tr}:<br />
				<select size="10" onchange="javascript:BitBase.flipMulti(this.options[this.selectedIndex].value,1,1);">
					{foreach from=$dataplugins item=p}
						<option value="{$p.plugin_guid}">{$p.title|escape|default:"{tr}No Title{/tr}"} &bull; {ldelim}{$p.tag|lower}{rdelim}</option>
					{/foreach}
				</select>
			</div>

			{foreach from=$dataplugins item=p}
				{if $gBitThemes->isJavascriptEnabled()}<div id="{$p.plugin_guid}1" style="display:none;">{/if}
				<h2>{tr}Plugin{/tr}: {$p.title|escape|default:"{tr}No Title{/tr}"}</h2>
					<ul>
						<li class="{cycle values="odd,even"}"><strong>{tr}Description{/tr}:</strong> {$p.description}</li>
						<li class="{cycle}"><strong>{tr}Syntax{/tr}:</strong>
							&nbsp;<a href="#" title="{tr}Click to insert syntax into editor{/tr}" onclick="javascript:BitBase.insertAt('{$textarea_id|default:$smarty.const.LIBERTY_TEXT_AREA}','{$p.syntax|@addslashes}');">{$p.syntax}</a>
						</li>
						<li class="{cycle}"><strong>{tr}Online Help{/tr}:</strong> {jspopup href="http://www.bitweaver.org/wiki/`$p.help_page`" title=`$p.help_page` class="external"}</li>
					</ul>
					<hr />
					{$p.exthelp}
				{if $gBitThemes->isJavascriptEnabled()}</div>{/if}
			{/foreach}
			<hr />
		{/if}

		{if $mimeplugins}
			<h2>{tr}Mime Plugin Help{/tr}</h2>
			<div {if !$gBitThemes->isJavascriptEnabled()}style="display:none;"{/if}>
				{tr}Click on the plugin you need help for{/tr}:<br />
				<select size="10" onchange="javascript:BitBase.flipMulti(this.options[this.selectedIndex].value,1,1);">
					{foreach from=$mimeplugins item=p}
						<option value="{$p.plugin_guid}">{$p.title|escape|default:"{tr}No Title{/tr}"}</option>
					{/foreach}
				</select>
			</div>

			{foreach from=$mimeplugins item=p}
				{if $gBitThemes->isJavascriptEnabled()}<div id="{$p.plugin_guid}1" style="display:none;">{/if}
				<h2>{tr}Plugin{/tr}: {$p.title|escape|default:"{tr}No Title{/tr}"}</h2>
					<ul>
						<li class="{cycle values="odd,even"}"><strong>{tr}Description{/tr}:</strong> {$p.description}</li>
					</ul>
					<hr />
					{$p.exthelp}
				{if $gBitThemes->isJavascriptEnabled()}</div>{/if}
			{/foreach}
			<hr />
		{/if}
	{/jstab}
{/if}

{if $formatplugins}
	{jstab title="Format Help"}
		<h2>{tr}Syntax and input format help{/tr}</h2>
		{foreach from=$formatplugins item=p}
			<h3>{if $p.format_help}<a href="#{$p.plugin_guid}">{/if}{$p.edit_label} Help{if $p.format_help}</a>{/if}</h3>
			{$p.description} {if $p.help_page}{tr}To view syntax help, please visit {jspopup href="http://www.bitweaver.org/wiki/index.php?page=`$p.help_page`" title=$p.help_page class=external}.{/tr}{/if}
		{/foreach}

		{foreach from=$formatplugins item=p}
			{if $p.format_help}
				<a name="{$p.plugin_guid}"></a>
				<h1>{$p.edit_label} Help</h1>
				{include file=$p.format_help}
			{/if}
		{/foreach}
	{/jstab}
{/if}
{/strip}
