{if $gBitSystem->isFeatureActive('liberty_allow_change_owner') && $gBitUser->hasPermission('p_liberty_edit_content_owner')}
	<div class="control-group">
		{formlabel label="Owner"}
		{forminput}
			{html_options name="owner_id" options=$gBitUser->getSelectionList() selected=$gContent->getField('owner_id')|default:$gBitUser->getUserId()}
			<input type="hidden" name="current_owner_id" value="{$gContent->getField('user_id')|default:$gBitUser->getUserId()}" />
		{/forminput}
	</div>
{/if}
