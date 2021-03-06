{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin menu template -->
<table>
<tbody>
<tr>
	{if isset($inline2)} {* $inline2 is one of 4 containers for inline plugin output *}
	<td><div class="pluginInline2">{$inline2}</div></td>
	{/if}
	{* $help is the help icon that is displayed if a help topic is available for the displayed page *}
	<td><div class="helplink">{$help}</div></td>
	{* Displayed if the syndication is turned on in config. *}
	{if $displayRss == true}
	<td><div class="helplink"><a href="{$rssFeed}" title="Subscribe to RSS feed"><img src="{$tplPath}/images/rss.png" style="border:0;text-align:right;" alt="Subscribe to RSS feed"></a></div></td>
	<td><div class="helplink"><a href="{$atomFeed}" title="Subscribe to Atom feed"><img src="{$tplPath}/images/atom.png" style="border:0;text-align:right;" alt="Subscribe to Atom feed"></a></div></td>
	{/if}
</tr>
</tbody>
</table>

{if isset($menu)}
	{menu data=$menu}
{/if}

{if isset($inline1)} {* $inline1 is one of 4 containers for inline plugin output *}
<div class="pluginInline1">{$inline1}</div>
{/if}

<!-- end menu template -->
