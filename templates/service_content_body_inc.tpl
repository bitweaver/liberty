{if $gBitSystem->isFeatureActive( 'liberty_display_status' ) && ($gBitUser->hasPermission('p_liberty_edit_content_status') || $gBitUser->hasPermission('p_liberty_edit_all_status')) && !is_null($serviceHash.content_status_id) && $serviceHash.content_status_id != 50}
	<p class="liberty_status">{biticon iname=dialog-warning iexplain="Warning"} {tr}The status of this content is <strong>{$gContent->getContentStatusName($serviceHash.content_status_id)}</strong>{/tr}.
		{if $serviceHash.content_status_id == -1 && $gBitSystem->isPackageActive('moderation') &&
			( 
				( $gBitSystem->isFeatureActive( 'comments_allow_owner_moderation' ) && $gContent->hasEditPermission() ) || 
				( $gBitSystem->isFeatureActive( 'comments_allow_moderation' ) && ( $gBitUser->isAdmin() || $gContent->hasUserPermission('p_liberty_edit_comments') ) )
			)}
		<a href="{$smarty.const.MODERATION_PKG_URL}index.php?moderation_id={$serviceHash.moderation_id}">Approve/Reject</a>
		{/if}
	</p>
{/if}{$gContent->mInfo.content_status_name}
