{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin menu template -->
<table class="menuTable" id="menuTable">
<tr>
	<td>
	{if isset($menu)}
		{menu data=$menu}
	{/if}
	</td>
	
	{if isset($inline1)} {* $inline1 is one of 4 containers for inline plugin output *}
	<td>{$inline1}</td>
	{/if}
	
	{if isset($inline2)} {* $inline2 is one of 4 containers for inline plugin output *}
	<td>{$inline2}</td>
	{/if}
	
	{* $help is the help icon that is displayed if a help topic is available for the displayed page *}
	<td class="helplink">{$help}</td>
	{* Displayed if RSS turned on in config.php. Values are set in header.tpl *}
	{if $displayRss == true}
	<td><a href="{$rssFeed}" title="Subscribe to RSS feed"><img src="{$tplPath}/images/rss.png" style="border:0;text-align:right;" alt="Subscribe to RSS feed"></a></td>
	{/if}
</tr>
</table>
<!-- end menu template -->
