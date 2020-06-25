{if $gBitSystem->isFeatureActive( 'liberty_display_status' ) && ($gBitUser->hasPermission('p_liberty_edit_content_status') || $gBitUser->hasPermission('p_liberty_edit_all_status')) && !is_null($serviceHash.content_status_id) && $serviceHash.content_status_id != 50}
	{assign var=contentStatusId value=$serviceHash.content_status_id}
	<p class="liberty_status">{booticon iname="icon-warning-sign"   iexplain="Warning"} {tr}The status of this content is{/tr} <strong>{$gContent->getContentStatusName($contentStatusId)}</strong>.</p>
{/if}{$gContent->mInfo.content_status_name}
