{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin content_metadata.tpl -->
{$metadata.hidden}
{if isset($metadata.original)} {* i.e. adding a comment to a quote or paraphrase (neither of which will be editable) *}
<div class="contentMetadata">
<div class="alternate1">
	{$metadata.original}
</div>

<hr>

{$metadata.commentTitle}<br>{$metadata.comment}


<br>

{if isset($metadata.otherComments)}
{section loop=$metadata.otherComments name=cRows}
<div class="{cycle values="alternate1,alternate2"}">

		{$metadata.otherComments[cRows].comment}
		{if array_key_exists('userAdd', $metadata.otherComments[cRows])}
			&nbsp;&nbsp;<span class="hint">{$metadata.otherComments[cRows].userAdd}&nbsp;&nbsp;({$metadata.otherComments[cRows].timestamp})</span>
		{/if}

</div>
{/section}
{/if}

<tr>
	<td><br></td>
</tr>

<tr>
	<td>{$metadata.form.private}&nbsp;&nbsp;{$metadata.form.submit}</td>
</tr>
</div>
{/if}

{if !isset($metadata.original)} {* i.e. adding or editing a quote, paraphrase or musing *}
<div>
{$metadata.keyword}

		<div>{$metadata.locations}</div>


<br>

{$metadata.metadataTitle}<br>{$metadata.metadata}

<br>

{if isset($metadata.comment)}
{$metadata.commentTitle}<br>{$metadata.comment}

<br>
{/if}

{if isset($metadata.otherComments)}
{section loop=$metadata.otherComments name=cRows}
<div class="{cycle values="alternate1,alternate2"}">

		{$metadata.otherComments[cRows].comment}
		{if array_key_exists('userAdd', $metadata.otherComments[cRows])}
			&nbsp;&nbsp;<span class="hint">{$metadata.otherComments[cRows].userAdd}&nbsp;&nbsp;({$metadata.otherComments[cRows].timestamp})</span>
		{/if}

</div>

{/section}
<br>
{/if}

{$metadata.form.private}&nbsp;&nbsp;{$metadata.form.submit}
</div>
{/if}
{$metadata.formfoot}
<!-- end content_metadata.tpl -->
