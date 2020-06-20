{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

{if isset($resourceSingle.attachments) && isset($resourceSingle.attachments.title) && isset($resourceSingle.urls)}
<table class="contentAttachments">
<tr>
	{if isset($resourceSingle.attachments) && isset($resourceSingle.attachments.title)}
	<td class="small">
	<strong>{$resourceSingle.attachments.title}</strong>{if isset($resourceSingle.attachments.editLink)}&nbsp;&nbsp;{$resourceSingle.attachments.editLink}{/if}&nbsp;&nbsp;
		{if isset($resourceSingle.attachments.embargoed)}
			{$resourceSingle.attachments.embargoed}
		{/if}

		{", "|implode:$resourceSingle.attachments.attachments}
	</td>
	{/if}
	
	{if isset($resourceSingle.urls.title)}
	<td class="small">
		<strong>{$resourceSingle.urls.title}</strong>{if isset($resourceSingle.urls.editLink)}&nbsp;&nbsp;{$resourceSingle.urls.editLink}{/if}
		{if isset($resourceSingle.urls)}&nbsp;&nbsp;{", "|implode:$resourceSingle.urls.urls}{/if}
	</td>
	{/if}
</tr>
</table>
{/if}