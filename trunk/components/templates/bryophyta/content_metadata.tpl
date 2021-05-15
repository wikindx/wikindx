{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

{$metadata.hidden}
{if isset($metadata.original)} {* i.e. adding a comment to a quote or paraphrase (neither of which will be editable) *}
<table class="contentMetadata">
<tr class="alternate1">
	<td>{$metadata.original}</td>
</tr>

<tr>
	<td><hr></td>
</tr>

<tr>
	<td>{$metadata.commentTitle}<br>{$metadata.comment}</td>
</tr>

<tr>
	<td><br></td>
</tr>

{if isset($metadata.otherComments)}
{section loop=$metadata.otherComments name=cRows}
<tr class="{cycle values="alternate1,alternate2"}">
	<td>
		{$metadata.otherComments[cRows].comment}
		{if array_key_exists('userAdd', $metadata.otherComments[cRows])}
			&nbsp;&nbsp;<span class="hint">{$metadata.otherComments[cRows].userAdd}&nbsp;&nbsp;({$metadata.otherComments[cRows].timestamp})</span>
		{/if}
	</td>
</tr>
{/section}
{/if}

<tr>
	<td><br></td>
</tr>

<tr>
	<td>{$metadata.form.private}&nbsp;&nbsp;{$metadata.form.submit}</td>
</tr>
</table>
{/if}

{if !isset($metadata.original)} {* i.e. adding or editing a quote, paraphrase or musing *}
<table>
<tr>
	<td>{$metadata.keyword}</td>
</tr>
<tr>
	<td>
		<table>
		<tr>
			<td>{$metadata.locations}</td>
		</tr>
		</table>
	</td>
</tr>

<tr>
	<td><br></td>
</tr>

<tr>
	<td>{$metadata.metadataTitle}<br>{$metadata.metadata}</td>
</tr>

<tr>
	<td><br></td>
</tr>

{if isset($metadata.comment)}
<tr>
	<td>{$metadata.commentTitle}<br>{$metadata.comment}</td>
</tr>

<tr>
	<td><br></td>
</tr>
{/if}

{if isset($metadata.otherComments)}
{section loop=$metadata.otherComments name=cRows}
<tr class="{cycle values="alternate1,alternate2"}">
	<td>
		{$metadata.otherComments[cRows].comment}
		{if array_key_exists('userAdd', $metadata.otherComments[cRows])}
			&nbsp;&nbsp;<span class="hint">{$metadata.otherComments[cRows].userAdd}&nbsp;&nbsp;({$metadata.otherComments[cRows].timestamp})</span>
		{/if}
	</td>
</tr>

{/section}
<tr>
	<td><br></td>
</tr>
{/if}

<tr>
	<td>{$metadata.form.private}&nbsp;&nbsp;{$metadata.form.submit}</td>
</tr>
</table>
{/if}
{$metadata.formfoot}
