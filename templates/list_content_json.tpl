{ldelim}
{if count($listcontent) > 0}
  "Status": {ldelim}
    "code": 200,
    "request": "datasearch"
  {rdelim},
  "Content":[{section name=n loop=$listcontent}
    {ldelim}
		  "content_id":{$listcontent[n].content_id},
		  "content_type_guid":'{$listcontent[n].content_type_guid}',
		  "lat":{$listcontent[n].lat},
		  "lng":{$listcontent[n].lng},
		  "title":'{$listcontent[n].title}',
		  "created":{$listcontent[n].created},
		  "last_modified":{$listcontent[n].last_modified},
		  "modifier_real_name":'{$listcontent[n].modifier_real_name}',
		  "modifier_user_id":{$listcontent[n].modifier_user_id},
		  "creator_real_name":'{$listcontent[n].creator_real_name}',
		  "creator_user_id":{$listcontent[n].creator_user_id},
		  "display_url":'{$listcontent[n].display_url}',
		  "hits":{$listcontent[n].hits},
		  "stars_rating_count":{$listcontent[n].stars_rating_count},
		  "stars_rating":{$listcontent[n].stars_rating},
		  "stars_pixels":{$listcontent[n].stars_pixels},
		  "stars_user_rating":{$listcontent[n].stars_user_rating},
		  "stars_user_pixels":{$listcontent[n].stars_user_pixels}
		{rdelim},
   {/section}],
{elseif count($listcontent) == 0}
  /*put error code here*/
  "Status": {ldelim}
    "code": 204,
    "request": "datasearch"
  {rdelim}
{/if}
{rdelim}
