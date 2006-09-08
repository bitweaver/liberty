{ldelim}
{if count($listcontent) > 0}
  "Status": {ldelim}
    "code": 200,
    "request": "datasearch"
  {rdelim},
  "Content":[{section name=n loop=$listcontent}
    {ldelim}
		  "content_id":{if $listcontent[n].content_id}{$listcontent[n].content_id}{else}null{/if},
		  "content_type_guid":'{$listcontent[n].content_type_guid}',
		  "content_description":'{$listcontent[n].content_description}',
		  "lat":{if $listcontent[n].lat}{$listcontent[n].lat}{else}null{/if},
		  "lng":{if $listcontent[n].lng}{$listcontent[n].lng}{else}null{/if},
		  "title":'{$listcontent[n].title}',
		  "created":{if $listcontent[n].created}{$listcontent[n].created}{else}null{/if},
		  "last_modified":{if $listcontent[n].last_modified}{$listcontent[n].last_modified}{else}null{/if},
		  "modifier_real_name":'{$listcontent[n].modifier_real_name}',
		  "modifier_user_id":{if $listcontent[n].modifier_user_id}{$listcontent[n].modifier_user_id}{else}null{/if},
		  "creator_real_name":'{$listcontent[n].creator_real_name}',
		  "creator_user_id":{if $listcontent[n].creator_user_id}{$listcontent[n].creator_user_id}{else}null{/if},
		  "display_url":'{$listcontent[n].display_url}',
		  "hits":{if $listcontent[n].hits}{$listcontent[n].hits}{else}null{/if},
		  "stars_rating_count":{if $listcontent[n].stars_rating_count}{$listcontent[n].stars_rating_count}{else}null{/if},
		  "stars_rating":{if $listcontent[n].stars_rating}{$listcontent[n].stars_rating}{else}null{/if},
		  "stars_pixels":{if $listcontent[n].stars_pixels}{$listcontent[n].stars_pixels}{else}null{/if},
		  "stars_user_rating":{if $listcontent[n].stars_user_rating}{$listcontent[n].stars_user_rating}{else}null{/if},
		  "stars_user_pixels":{if $listcontent[n].stars_user_pixels}{$listcontent[n].stars_user_pixels}{else}null{/if}
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
