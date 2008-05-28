{strip}
<div class="display attachment">
	<div class="header">
		<h1>{tr}View Attachment{/tr}</h1>
	</div>

	<div class="body">
		{legend legend="File Details"}
			{include file=$view_template preferences=$attachment.preferences}
		{/legend}

		{if ( $gBitUser->isAdmin() || $gBitUser->mUserId == $attachment.user_id ) && $edit_template}
			{form legend="Edit File Details"}
				{formfeedback hash=$feedback}
				{include file=$edit_template preferences=$attachment.preferences}

				<div class="row submit">
					<input type="hidden" name="attachment_id" value="{$smarty.request.attachment_id}" />
					<input type="submit" name="plugin_submit" value="{tr}Update Plugin Data{/tr}" />
				</div>
			{/form}
		{/if}

		{legend legend="Content this attachment belongs to"}
			<div class="row">
				{formlabel label="Title"}
				{forminput}
					<a href="{$gContent->getDisplayUrl()}">{$gContent->getTitle()}</a>
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Content Type"}
				{forminput}
					{$gContent->mType.content_description}
				{/forminput}
			</div>
		{/legend}

	</div><!-- end .body -->
</div><!-- end .attachment -->
{/strip}
