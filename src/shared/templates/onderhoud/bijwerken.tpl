{include file='page_header.tpl'}

<h2>Database bijwerken</h2>

{if $infomessage != ''}
<div class="infobox">{$infomessage}</div>
{/if}

<p>{if count($patches) == 0}
Er zijn op dit moment geen database updates beschikbaar.
{else}
Er zijn updates beschikbaar, deze kunt u installeren door de <i>database bijwerken</i> knop te gebruiken.
De patches die tijdens dit proces geinstalleerd zullen worden staan weergegeven in de onderstaande tabel.    
{/if}</p>

<table class="generic_table">
	<thead>
		<tr>
			<th>Datum</th>
			<th>Omschrijving</th>
		</tr>
	</thead>
	
	<tbody>
		{if count($patches) == 0}
		<tr>
			<td colspan="2">Er zijn momenteel geen updates.</td>
		</tr>
		{else}
		{foreach from=$patches item=patch}
		<tr>
			<td>{$patch.date}</td>
			<td>{$patch.description}</td>
		</tr>
		{/foreach}
		{/if}
	</tbody>
</table>

<br />

<form method="post" action="{$base}/onderhoud/bijwerken">
	<input type="submit" value="Database bijwerken" />
</form>

{include file='page_footer.tpl'}
