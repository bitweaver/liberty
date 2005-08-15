{assign var=serviceTpls value=$gLibertySystem->getServiceValues('content_view_tpl')}
{foreach from=$serviceTpls key=serviceName item=template}
	{include file=$template}
{/foreach}