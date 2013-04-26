{strip}
{if $formatplugins}
	{jstab title="Format Help"}
		<h4>{tr}Syntax and input format help{/tr}</h4>
		{foreach from=$formatplugins item=p}
			<h5>{if $p.format_help}<a href="#{$p.plugin_guid}">{/if}{$p.edit_label} Help{if $p.format_help}</a>{/if}</h5>
			{$p.description} {if $p.help_page}{tr}To view syntax help, please visit {jspopup href="http://www.bitweaver.org/wiki/index.php?page=`$p.help_page`" title=$p.help_page class=external}.{/tr}{/if}
		{/foreach}

		{foreach from=$formatplugins item=p}
			{if $p.format_help}
				{include file=$p.format_help}
			{/if}
		{/foreach}
	{/jstab}
{/if}
{/strip}
