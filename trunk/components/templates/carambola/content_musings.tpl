{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin content_musings.tpl -->
<div class="contentMusings">

		<strong>
		<span class="small">{$resourceSingle.musingsTitle}
		</span></strong>{if isset($resourceSingle.musingsEditLink)}&nbsp;&nbsp;{$resourceSingle.musingsEditLink}{/if}

{if isset($resourceSingle.musings.0)}
{section loop=$resourceSingle.musings name=mRows}
<div class="{cycle values="alternate3,alternate4"}">
	{if array_key_exists('editLink', $resourceSingle.musings[mRows])}
		{$resourceSingle.musings[mRows].editLink}&nbsp;
	{/if}
	{if array_key_exists('details', $resourceSingle.musings[mRows])}
		<strong>{$resourceSingle.musings[mRows].details}</strong>&nbsp;&nbsp;
	{/if}
	{if array_key_exists('musing', $resourceSingle.musings[mRows])}
	{$resourceSingle.musings[mRows].musing}
	{/if}
	{if array_key_exists('userAdd', $resourceSingle.musings[mRows])}
		&nbsp;&nbsp;<span class="hint">{$resourceSingle.musings[mRows].userAdd}
		&nbsp;({$resourceSingle.musings[mRows].timestamp})</span>
	{/if}
	{if array_key_exists('keywords', $resourceSingle.musings[mRows])}
		<br><strong>{$resourceSingle.musings[mRows].keywordTitle}:</strong>&nbsp;&nbsp;
		{"&nbsp;"|implode:$resourceSingle.musings[mRows].keywords}
	{/if}
</div>
{/section}
{/if}
</div>
<!-- end content_musings.tpl -->
