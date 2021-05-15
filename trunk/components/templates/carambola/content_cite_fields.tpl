{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

{if isset($citeFields.pageStart) or isset($citeFields.preText) or isset($citeFields.postText) or isset($citeFields.cite)}
<!-- begin content_cite_fields.tpl -->
<div class="contentCiteFields">

{if isset($citeFields.pageStart)}{$citeFields.pageStart}&nbsp;-&nbsp;{$citeFields.pageEnd}{/if}
{if isset($citeFields.preText)}{$citeFields.preText}{/if}
{if isset($citeFields.postText)}{$citeFields.postText}{/if}
{if isset($citeFields.cite)}{$citeFields.cite}{/if}

</div>
<!-- end content_cite_fields.tpl -->
{/if}
