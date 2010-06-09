{strip}
{if $modLastComments}
<div class="">
	<div class="header">
		<h1>{tr}{$moduleTitle}{/tr}</h1>
	</div>

	<div class="body comments">
		<ul class="data">
		{section name=ix loop=$modLastComments}
			<li class="item">
				<a href="{$modLastComments[ix].display_url}"><img style="width:50px;float:left;" src="{$modLastComments[ix].object->getThumbnailUrl($moduleParams.module_params.thumb_size)|default:"`$smarty.const.USERS_PKG_URL`icons/silhouette.png"}" alt="{$modLastComments[ix].object->getTitle()|escape}" title="{$modLastComments[ix].object->getTitle()|escape}" /></a>
				<div style="margin-left:60px;vertical-align:top;min-height:40px;margin-bottom:10px;background-color:#eee;padding:5px;">
					{if !empty($modLastComments[ix].title)}<div><strong>{$modLastComments[ix].title|escape}</strong></div>{/if}
					{$modLastComments[ix].parsed_data}
<div>- {displayname hash=$modLastComments[ix]}</div>
					<div class="actiontext"><a href="{$modLastComments[ix].display_url}">{tr}View{/tr}</a>{* <a href="{$modLastComments[ix].display_url}{if strpos($modLastComments[ix].display_url,'?')}&amp;{else}?{/if}post_comment_reply_id={$modLastComments[ix].parent_id}&amp;post_comment_request=1">{tr}Reply{/tr}<a/>*}</div>
					<div class="date">{$modLastComments[ix].last_modified|bit_short_datetime}</div>
				</div>
			</li>
		{/section}
		</ul>
	</div>	<!-- end .body -->
</div>
{/if}
{/strip}
