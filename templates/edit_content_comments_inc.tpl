{if $gBitSystem->isPackageActive('moderation') &&
	( 
		( $gBitSystem->isFeatureActive( 'comments_allow_owner_moderation' ) && $gContent->hasEditPermission() ) || 
		( $gBitSystem->isFeatureActive( 'comments_allow_moderation' ) && ( $gBitUser->isAdmin() || $gContent->hasUserPermission('p_liberty_edit_comments') ) )
	)}
	{* comments_moderate_all we handle in comments_inc, no input value is required and its not an option *}
	<div class="row">
		{formlabel label="Moderate Comments" for="moderate_comments"}
		{forminput}
			<input type="checkbox" name="preferences_store[moderate_comments]" id="moderate_comments" value="y" {if $gContent->getPreference( 'moderate_comments' )}checked="checked"{/if} />
			{formhelp note="Comments will be hidden until you approve them."}
		{/forminput}
	</div>
{/if}
