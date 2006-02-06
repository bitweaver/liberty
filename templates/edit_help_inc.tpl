{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/edit_help_inc.tpl,v 1.7 2006/02/06 22:56:47 squareing Exp $ *}

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
								<a title="{tr}Extended Help{/tr}" href="javascript:flip('help-{$p.guid}');">{tr}Display Extended Help{/tr}</a>
								<div id="help-{$p.guid}" style="display: none;">{$p.exthelp}</div>
							{/if}

							{if $p.help_page}
								<br />{tr}for additional information about this plugin, see {jspopup href="http://www.bitweaver.org/wiki/index.php?page=`$p.help_page`" type=fullscreen title=$p.help_page class=external}.{/tr}<br/> 
							{/if}
						{/box}
					{/if}
				{/foreach}
			{/jstab}
		{/if}
	{/jstabs}
{/if}
{/strip}
