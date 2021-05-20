<!DOCTYPE html>
<html lang="{$lang}">
<head>
	{* Smarty logic to display or not various elements depending upon the type of page (see below). *}
	{**
	* WIKINDX : Bibliographic Management system.
	* @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
	* @author The WIKINDX Team
	* @license https://www.isc.org/licenses/ ISC License
	*}
	<!-- begin header template -->
	{* Display the WIKINDX title in the browser title bar *}
	<title>{$title}</title>
	
	{* WIKINDX expects the charset to be UTF-8 -- leave this *}
	<meta charset="UTF-8">

	{* Smart phone/tablet friendly *}
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- With IE 8 and 9, use only edge engine rendering (more compliant with web standards) -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	{* Google scholar meta tags can be added here *}
	{assign var=gsMetaTags value=$gsMetaTags|default:false}
	{$gsMetaTags}
	
	{* Change this path and file name *}
	<link rel="stylesheet" href="{$tplPath}/template.css?ver={$smarty.const.WIKINDX_PUBLIC_VERSION}" type="text/css">
	<link rel="stylesheet" href="{$tplPath}/../override.css?ver={$smarty.const.WIKINDX_PUBLIC_VERSION}" type="text/css">
	<link rel="shortcut icon" type="image/x-icon" href="{$tplPath}/images/favicon.ico?ver={$smarty.const.WIKINDX_PUBLIC_VERSION}">

    {* Displayed if the syndication is turned on in config. *}
	{if $displayRss == true}
	<link rel="alternate" type="{$wkx_mimetype_rss}" title="{$rssTitle}" href="{$rssFeed}">
	<link rel="alternate" type="{$wkx_mimetype_atom}" title="{$rssTitle}" href="{$atomFeed}">
	{/if}
	
	<!-- Required Javascript -->
	{* Placeholder for any javascript scripts or includes added in the PHP scripts.  DO NOT REMOVE. *}
	{$scripts}
</head>
<body>
<noscript><!-- Check we have javascript enabled in the browser. -->
<div id="nojs" class="error">Javascript is disabled or not supported in your browser. JavaScript must be enabled in order for you to use WIKINDX fully. Enable JavaScript through your browser options then <a href="/">try again</a>, otherwise, try using a different browser.</div>
</noscript>

{include file='header.tpl'}

{if $displayMenu == true}
	{include file='menu.tpl'}
{/if}

{include file='content.tpl'}

{if $displayFooter == true}
	{include file='footer.tpl'}
{/if}

</body>
</html>
