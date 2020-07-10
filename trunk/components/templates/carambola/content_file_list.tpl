{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin content_file_list.tpl -->
<div class="contentFileList">
{section loop=$fileList name=rows}
<div class="{cycle values="alternate1,alternate2"}">
	{if isset($fileList[rows])}{$fileList[rows]}{/if}
	{if isset($fileListIds[rows])}{$fileListIds[rows]}{/if}
</div>
{/section}
</div>
<!-- end content_file_list.tpl -->
