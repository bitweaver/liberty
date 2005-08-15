{assign var=serviceTpls value=$gLibertySystem->getServiceValues('content_edit_tpl')}
{foreach from=$serviceTpls key=serviceName item=template}
	{include file=$template}
{/foreach}