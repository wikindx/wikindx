{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}


<div class="contentParaphrases">

		<strong>
		<span class="small">{$resourceSingle.paraphrasesTitle}
		</span></strong>{if isset($resourceSingle.paraphrasesEditLink)}&nbsp;&nbsp;{$resourceSingle.paraphrasesEditLink}{/if}


{if isset($resourceSingle.paraphrases.0)}
{section loop=$resourceSingle.paraphrases name=pRows}
<div class="{cycle values="alternate3,alternate4"}">
		{if array_key_exists('editLink', $resourceSingle.paraphrases[pRows])}
		{$resourceSingle.paraphrases[pRows].editLink}&nbsp;
		{/if}
		
		{if array_key_exists('details', $resourceSingle.paraphrases[pRows])}
			<strong>{$resourceSingle.paraphrases[pRows].details}</strong>&nbsp;&nbsp;
		{/if}
		{if array_key_exists('paraphrase', $resourceSingle.paraphrases[pRows])}
		{$resourceSingle.paraphrases[pRows].paraphrase}
		{/if}
		{if array_key_exists('userAdd', $resourceSingle.paraphrases[pRows])}
			&nbsp;&nbsp;<span class="hint">{$resourceSingle.paraphrases[pRows].userAdd}</span>
		{/if}
		{if array_key_exists('keywords', $resourceSingle.paraphrases[pRows])}
			<br><strong>{$resourceSingle.paraphrases[pRows].keywordTitle}:</strong>&nbsp;&nbsp;
			{"&nbsp;"|implode:$resourceSingle.paraphrases[pRows].keywords}
		{/if}
		{if array_key_exists('comments', $resourceSingle.paraphrases[pRows])}
			<br><strong>{$resourceSingle.paraphrases[pRows].commentTitle}:</strong>
			{section loop=$resourceSingle.paraphrases[pRows].comments name=cRows}
				<br>{$resourceSingle.paraphrases[pRows].comments[cRows].comment}
				&nbsp;&nbsp;<span class="hint">{$resourceSingle.paraphrases[pRows].comments[cRows].userAdd}
				&nbsp;({$resourceSingle.paraphrases[pRows].comments[cRows].timestamp})</span>
			{/section}
		{/if}

</div>
{/section}
{/if}
</div>
<!-- end content_paraphrases.tpl -->
