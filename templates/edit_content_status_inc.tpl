<div class="row">
	{formlabel label="Status"}
	{forminput}
		{html_options name="content_status_id" options=$gLibertySystem->getContentStatus() selected=$gContent->getField('content_status_id',$smarty.const.BIT_CONTENT_DEFAULT_STATUS)}
	{/forminput}
</div>
