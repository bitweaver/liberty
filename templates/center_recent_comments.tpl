{strip}
{if $modLastComments}
<div class="">
	<div class="header">
		<h1>{tr}{$moduleTitle}{/tr}</h1>
	</div>
	<div class="body">
		<ul class="data">
		{section name=ix loop=$modLastComments}
			<li class="item">
				<a href="{$modLastComments[ix].object->getDisplayUrl()}"><img style="width:50px;float:left;" src="{if $modLastComments[ix].object->getThumbnailUrl($moduleParams.module_params.thumb_size)}{$modLastComments[ix].object->getThumbnailUrl($moduleParams.module_params.thumb_size)}{else}{$smarty.const.USERS_PKG_URL}icons/silhouette.png{/if}" alt="{$modLastComments[ix].object->getTitle()|escape}" title="{$modLastComments[ix].object->getTitle()|escape}" /></a>
				<div style="margin-left:60px;vertical-align:top;min-height:40px;margin-bottom:10px;background-color:#eee;padding:5px;">
					{displayname hash=$modLastComments[ix]} {$modLastComments[ix].parsed_data}
					<div class="date">{$modLastComments[ix].last_modified|bit_short_datetime}</div>
				</div>
			</li>
		{/section}
		</ul>
	</div>	<!-- end .body -->
</div>
{/if}
{/strip}
