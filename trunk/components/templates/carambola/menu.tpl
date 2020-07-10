{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin menu.tpl -->
<div class="menuTable" id="menuTable">
	{if isset($menu)}
		{menu data=$menu}
	{/if}

	{if isset($inline1)} {* $inline1 is one of 4 containers for inline plugin output *}
	{$inline1}
	{/if}
	
	{if isset($inline2)} {* $inline2 is one of 4 containers for inline plugin output *}
	{$inline2}
	{/if}
	
	{* $help is the help icon that is displayed if a help topic is available for the displayed page *}
	{$help}
	
	{* Displayed if RSS turned on in config.php. Values are set in header.tpl *}
	{if $displayRss == true}
	<a href="{$rssFeed}" title="Subscribe to RSS feed"><img src="{$tplPath}/images/rss.png" style="border:0;text-align:right;" alt="Subscribe to RSS feed"></a>
	{/if}
</div>
<hr class="clear">
<!-- end menu.tpl -->
