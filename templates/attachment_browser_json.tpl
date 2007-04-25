{ldelim}
{if count($gContent->mStorage) > 0}
  "Status": {ldelim}
    "code": 200,
    "request": "datasearch"
  {rdelim},
  "Attachments":[{foreach from=$gContent->mStorage item=storage key=attachmentId name=loop}
    {ldelim}
		  "id":{$attachmentId},
		  "url":'{$storage.source_url}',
		  "avatar":'{$storage.thumbnail_url.avatar}',
		  "small":'{$storage.thumbnail_url.small}',
		  "medium":'{$storage.thumbnail_url.medium}',
		  "large":'{$storage.thumbnail_url.large}',
		  "file_name":'{$storage.filename}'
		{rdelim}{if !$smarty.foreach.loop.last},{/if}
   {/foreach}]{if $curPage},
   "Pagination": {ldelim}
   	 "cant":{$cant},
	 "curPage":{$curPage},
	 "numPages":{$numPages}
   {rdelim}{/if}
{elseif count($gContent->mStorage) == 0}
  /*put error code here*/
  "Status": {ldelim}
    "code": 204,
    "request": "datasearch"
  {rdelim}
{/if}
{rdelim}
