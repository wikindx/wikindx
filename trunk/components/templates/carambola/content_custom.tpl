{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin content_custom.tpl -->
<div class="contentCustom">
{section loop=$resourceSingle.custom name=cRows}
<div class="{cycle values="alternate1,alternate2"}">

	<strong><span class="small">{$resourceSingle.custom[cRows].title}</span></strong>
	&nbsp;&nbsp;
	{if array_key_exists('editLink', $resourceSingle.custom[cRows])}
		{$resourceSingle.custom[cRows].editLink}
	{/if}
	{if array_key_exists('text', $resourceSingle.custom[cRows])}
	{$resourceSingle.custom[cRows].text}
	{/if}
	{if isset($multiUser)}
	{if array_key_exists('userAdd', $resourceSingle.custom[cRows]) or array_key_exists('userEdit', $resourceSingle.custom[cRows])}
		<br><span class="hint">{if array_key_exists('userAdd', $resourceSingle.custom[cRows])}{$resourceSingle.custom[cRows].userAdd}{/if}{if array_key_exists('userEdit', $resourceSingle.custom[cRows])}&nbsp;&nbsp;{$resourceSingle.custom[cRows].userEdit}{/if}</span>
	{/if}
	{/if}

</div>
{/section}
</div>
<!-- end content_custom.tpl -->
