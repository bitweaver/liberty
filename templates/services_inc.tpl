{assign var=serviceLocTpls value=$gLibertySystem->getServiceValues("content_`$serviceLocation`_tpl")}
{foreach from=$serviceLocTpls key=serviceName item=template}
	{include file=$template serviceHash=$serviceHash}
{/foreach}
