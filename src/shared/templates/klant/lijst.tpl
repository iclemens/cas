{include file='page_header.tpl'}

<h2>Klanten</h2>

	<p>In het {$application_name} zijn de belangrijkste
	  gegevens van al onze klanten opgenomen. Middels deze module
	  kunt u een nieuwe klant toevoegen of een bestaande bewerken.
	  Weest u er terdege van bewust dat het onmogelijk is klanten
	  te verwijden uit het gegevensbestand.</p>

<form name="factuur" method="get" action="{$base}/klant/lijst">
	<input type="hidden" name="sort" value="{$sorter->getString()}" />
<table width="40%">
  <tr><td>
	Klantstatus:<br />
	<select name="actief">
	  <option value="-1" {if $parameters.actief == -1}selected="selected"{/if}>Alle klanten</option>
	  <option value="0" {if $parameters.actief == 0}selected="selected"{/if}>Niet actief</option>
	  <option value="1" {if $parameters.actief == 1}selected="selected"{/if}>Actief</option>
	</select>
  </td><td>
	Klanttype:<br />
	<select name="klanttype">
	  <option value="-1" {if $parameters.klanttype == -1}selected="selected"{/if}>Alle klanten</option>
	  <option value="0" {if $parameters.klanttype == 0}selected="selected"{/if}>Zakelijk</option>
	  <option value="1" {if $parameters.klanttype == 1}selected="selected"{/if}>Particulier</option>
	</select>
  </td><td><br />
	<input type="submit" value="Zoeken..." />
  </td></tr></table>
</form>
<br />

{paged_table pager=$pager sorter=$sorter filter=$parameters}
	{table_header}
		{sortable_column name="klantnummer" value="Klantnummer"}
		{sortable_column name="bedrijfsnaam" value="Bedrijfsnaam"}
		{sortable_column name="achternaam" value="Contact pers."}

		<th class="noprint">Bewerken</th>
	{/table_header}

	<tbody>
	{foreach from=$klanten item=klant}
		<tr {if $klant.actief eq 0}class="inactive"{/if}>
			<td onclick="document.location='{$base}/klant/bewerk/id/{$klant.klantnummer}'">
				{$klant.klantnummer|escape:'html'}
			</td>
			<td>{$klant.bedrijfsnaam|escape:'html'}</td>
			<td>{$klant.aanhef|escape:'html'} {$klant.voornaam|escape:'html'} {$klant.achternaam|escape:'html'}</td>			
			<td class="noprint">
				<a href="{$base}/klant/bewerk/id/{$klant.klantnummer}">Bewerken</a>
			</td>
		</tr>
	{/foreach}
	</tbody>
	
{/paged_table}

<div class="noprint">
	<ul class="menu">
		<li><a href="{$base}/klant/nieuw">Nieuwe klant toevoegen</a></li>
	</ul>
</div>

{include file='page_footer.tpl'}
