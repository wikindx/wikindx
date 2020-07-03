{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

{if isset($citeFields.pageStart) or isset($citeFields.preText) or isset($citeFields.postText) or isset($citeFields.cite)}
<table class="contentCiteFields">
<tr>
	{if isset($citeFields.pageStart)}<td>{$citeFields.pageStart}&nbsp;-&nbsp;{$citeFields.pageEnd}</td>{/if}
	{if isset($citeFields.preText)}<td>{$citeFields.preText}</td>{/if}
	{if isset($citeFields.postText)}<td>{$citeFields.postText}</td>{/if}
	{if isset($citeFields.cite)}<td>{$citeFields.cite}</td>{/if}
</tr>
</table>
{/if}