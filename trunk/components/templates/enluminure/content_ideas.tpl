{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

{* Don't edit these assign settings *}



{* Display a list of several ideas *}
{assign var=ideaList value=$ideaList|default:false}
{assign var=idea value=$idea|default:false}

{if isset($ideaList)}
{if $ideaList != false}
	<table class="contentIdeas">
	{if isset($ideaListInfo.reorder)}
		<tr>
			<td class="hint left">
				{$ideaListInfo.reorder}
			</td>
		</tr>
	{/if}
	{if isset($navigation)}
		<tr>
			<td align="right">
				{"&nbsp;&nbsp;"|implode:$navigation}
			</td>
		</tr>
	{/if}
	{if isset($ideaList.0)}
		{section loop=$ideaList name=mRows}
		<tr class="{cycle values="alternate3,alternate4"}">
			<td>
				{if array_key_exists('metadata', $ideaList[mRows])}
					{$ideaList[mRows].metadata}
				{/if}
				{if array_key_exists('user', $ideaList[mRows])}
					&nbsp;&nbsp;<span class="hint">{$ideaList[mRows].user}</span>
				{/if}
				{if array_key_exists('keywords', $ideaList[mRows])}
					<br><strong>{$ideaList[mRows].keywordTitle}:</strong>&nbsp;&nbsp;
					{"&nbsp;"|implode:$ideaList[mRows].keywords}
				{/if}
			</td>
			<td>
				{section loop=$ideaList[mRows].links name=lRows}
					{$ideaList[mRows].links[lRows]}&nbsp;
				{/section}
			</td>
		</tr>
		{/section}
	{/if}
	</table>
{/if}
{/if}




{* Display the idea editing form *}
{if isset($idea)}
{if $idea != false} 
	{$idea.hidden}
	<table class="Ideas">
	<tr>
		<td>{$idea.keyword}</td>
	</tr>
	<tr>
		<td>
			<table>
			<tr>
			{if isset($idea.locations)}
				<td>{$idea.locations}</td>
			{else}
				<td>&nbsp;</td>
			{/if}
			</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td>{$idea.ideaTitle}<br>{$idea.idea}</td>
	</tr>
	{if isset($idea.add)}
		<tr><td>{$idea.add}</td></tr>
	{/if}

	<tr>
		<td><br></td>
	</tr>
	{if isset($idea.form.private)}
		<tr>
			<td>{$idea.form.private}&nbsp;&nbsp;{$idea.form.submit}</td>
		</tr>
	{else}
		<tr>
			<td>{$idea.form.submit}</td>
		</tr>
	{/if}
	{if isset($idea.otherIdeas)}
		<tr><td><hr></td></tr>
		{section loop=$idea.otherIdeas name=cRows}
		<tr class="{cycle values="alternate1,alternate2"}">
			<td>
				{$idea.otherIdeas[cRows].text}
				{if array_key_exists('user', $idea.otherIdeas[cRows])}
					&nbsp;&nbsp;<span class="hint">{$idea.otherIdeas[cRows].user}</span>
				{/if}
			</td>
			{if array_key_exists('links', $idea.otherIdeas[cRows])}
				<td>
					{section loop=$idea.otherIdeas[cRows].links name=lRows}
						{$idea.otherIdeas[cRows].links[lRows]}
					{/section}
				</td>
			{else}
					<td>&nbsp;</td>
			{/if}
		</tr>
		{/section}
		<tr>
			<td><br></td>
		</tr>
	{/if}
	</table>
	{$idea.formfoot}
{/if}
{/if}
