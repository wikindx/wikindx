{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin content_quotes.tpl -->
<div class="contentQuotes">

		<strong><span class="small">{$resourceSingle.quotesTitle}</span></strong>{if isset($resourceSingle.quotesEditLink)}&nbsp;&nbsp;{$resourceSingle.quotesEditLink}{/if}


{if isset($resourceSingle.quotes.0)}
{section loop=$resourceSingle.quotes name=qRows}
<div class="{cycle values="alternate3,alternate4"}">
		{if array_key_exists('editLink', $resourceSingle.quotes[qRows])}
		{$resourceSingle.quotes[qRows].editLink}&nbsp;
		{/if}
		
		{if array_key_exists('details', $resourceSingle.quotes[qRows])}
			<strong>{$resourceSingle.quotes[qRows].details}</strong>&nbsp;&nbsp;
		{/if}
		
		{if array_key_exists('quote', $resourceSingle.quotes[qRows])}
		{$resourceSingle.quotes[qRows].quote}
		{/if}
		
		{if array_key_exists('userAdd', $resourceSingle.quotes[qRows])}
			&nbsp;&nbsp;<span class="hint">{$resourceSingle.quotes[qRows].userAdd}</span>
		{/if}
		{if array_key_exists('keywords', $resourceSingle.quotes[qRows])}
			<br><strong>{$resourceSingle.quotes[qRows].keywordTitle}:</strong>&nbsp;&nbsp;
			{"&nbsp;"|implode:$resourceSingle.quotes[qRows].keywords}
		{/if}
		{if array_key_exists('comments', $resourceSingle.quotes[qRows])}
			<br><strong>{$resourceSingle.quotes[qRows].commentTitle}:</strong>
			{section loop=$resourceSingle.quotes[qRows].comments name=cRows}
				<br>{$resourceSingle.quotes[qRows].comments[cRows].comment}
				&nbsp;&nbsp;<span class="hint">{$resourceSingle.quotes[qRows].comments[cRows].userAdd}
				&nbsp;({$resourceSingle.quotes[qRows].comments[cRows].timestamp})</span>
			{/section}
		{/if}
</div>
{/section}
{/if}
</div>
<!-- end content_quotes.tpl -->
