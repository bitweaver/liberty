{if $gBitSystem->isFeatureActive( 'liberty_display_status' ) && ($gBitUser->hasPermission('p_liberty_edit_content_status') || $gBitUser->hasPermission('p_liberty_edit_all_status'))}
	<div class="row">
		{formlabel label="Status" for="content_status_id"}
		{forminput}
			{html_options name="content_status_id" options=$gContent->getContentStatus() selected=$gContent->getField('content_status_id',$smarty.const.BIT_CONTENT_DEFAULT_STATUS)}
		{/forminput}
	</div>
{/if}
