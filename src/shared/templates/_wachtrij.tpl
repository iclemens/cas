{if empty($klanten)}
Er staan geen items in de wachtrij.
{else}
<table width="100%" class="generic_table">
	<thead>
		<tr>
			<th>Klantnr.</th>
			<th>Klantnaam</th>
			<th>Aantal regels</th>
			<th>Factureren</th>
		</tr>
	</thead>

	<tbody>
		{foreach from=$klanten item=klant}
		<tr>
			<td>{$klant.klantnummer}</td>
			<td>{klantnaam klant=$klant}</td>
			<td>{$klant.aantalregels}</td>
			<td><a href="{$base}/factuur/nieuw/klant/{$klant.klantnummer}">Factureren</a></td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/if}