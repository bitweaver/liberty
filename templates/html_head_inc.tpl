{* $Header$ *}
{strip}
{if $structureInfo}
	<link rel="index" title="{tr}Contents{/tr}" href="index.php?structure_id={$structureInfo.root_structure_id}" />
	{if $structureInfo.prev.structure_id}
		<link rel="prev" title="{tr}Previous{/tr}" href="index.php?structure_id={$structureInfo.prev.structure_id}" />
	{/if}
	{if $structureInfo.next.structure_id}
		<link rel="next" title="{tr}Next{/tr}" href="index.php?structure_id={$structureInfo.next.structure_id}" />
	{/if}
{/if}

{* perhaps we can remove this as well at some point and load it using
$gBitThemes->loadJavascript(); *}
{if $gContent}
	{if $gContent->mContentTypeGuid}
		<script>
			LibertyContent = {ldelim}{rdelim};
			LibertyContent.CONTENT_TYPE_GUID = "{$gContent->mContentTypeGuid}";
		</script>
	{/if}
	{$gContent->getField('metatags')}
{/if}
{/strip}
