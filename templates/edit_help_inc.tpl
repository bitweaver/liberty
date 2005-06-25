{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/edit_help_inc.tpl,v 1.1.2.2 2005/06/25 11:25:39 squareing Exp $ *}

{strip}
{if $gBitSystem->isFeatureActive( 'feature_wikihelp' )}
	{jstabs}
		{jstab title="Help"}
			{foreach from=$formatplugins item=p}
				{if $p.is_active eq 'y'}
					{box title=`$p.name` class="help box"}
						{if $p.help eq ''}
							{tr}No description available{/tr}
						{else}
							{$p.help}
						{/if}

						{if $p.help_page}
							<br />{tr}To view syntax help, please visit <a onkeypress="popUpWin(this.href,'standard',600,400);" onclick="popUpWin(this.href,'standard',600,400);return false;" class="external" href="http://bitweaver.org/wiki/index.php?page={$p.help_page}">{$p.help_page}</a>.{/tr}
						{/if}
					{/box}
				{/if}
			{/foreach}

			{box title="Syntax Help" class="help box"}
				{tr}For more information, please visit <a class="external" href="http://www.bitweaver.org">bitweaver.org</a>{/tr}
			{/box}
		{/jstab}

		{if count($dataplugins) ne 0}
			{jstab title="Plugin Help"}
				{foreach from=$dataplugins item=p}
					{if $p.is_active eq 'y'}
						{box title=`$p.name` class="help box"}
							{if $p.help eq ''}
								{tr}no description available{/tr}
							{else}
								{$p.help}
							{/if}

							{if $p.syntax}
								<br/>{tr}Syntax{/tr}: {$p.syntax}<br/>
							{/if}

							{if $p.exthelp ne ''}
								<a title="{tr}Extended Help{/tr}" href="javascript:flip('help-{$p.name}');">{tr}Display Extended Help{/tr}</a>
								<div id="help-{$p.name}" style="display: none;">{$p.exthelp}</div>
							{/if}

							{if $p.help_page}
								<br />{tr}for additional information about this plugin, see <a class="external" href="http://bitweaver.org/wiki/index.php?page=DataPlugin{$p.help_page}">{$p.help_page}</a>.{/tr}<br/>
							{/if}
						{/box}
					{/if}
				{/foreach}
			{/jstab}
		{/if}
	{/jstabs}
{/if}
{/strip}
