{include file='page_header.tpl'}

<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>

<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>

{literal}
<script type="text/javascript">
function klantnummerOnChange(element, selected_element) {
	if(selected_element) {
		document.getElementById('klantnaam').innerHTML = selected_element.childNodes.item(1).innerHTML;
	} else {
		document.getElementById('klantnaam').innerHTML = '';
	}
}
</script>
{/literal}

<h2>Totalen per artikelcode</h2>

<div id="searchbox" class="noprint">
	<form name="factuur" method="get" action="{$base}/overzicht/totaal_per_artikelcode">
		<input type="hidden" name="sort" value="{$sorter->getString()}" />

		<table>
			{if $user_type != 'Klant'}
			<tr><td colspan="3">
				Klantnummer:<br />
				<input type="text" name="klantnummer" value="{$parameters.klantnummer}" onchange="klantnummerOnChange(0,0)" size="8" />
				<span id="klantnaam">{klantnaam klant=$klant}</span>
			</td></tr>
			{/if}

			<tr><td>
				<br />
				Jaar:<br />
				<input type="text" name="boekjaar" size="13" value="{$parameters.boekjaar|escape:HTML}"/><br />
			</td><td>
				<br />
				Maand:<br />
				<input type="text" name="boekmaand" size="4" value="{$parameters.boekmaand|escape:HTML}"/><br />
			</td></tr>
	
			<tr><td colspan="2">
				<br />
				<input type="submit" value="Zoeken..." />
			</td></tr>
		</table>
	</form>
</div>

<br />
<hr />
<br />

{paged_table pager=$pager sorter=$sorter}
	{table_header}
		{sortable_column name="artikelcode" value="Artikelcode"}
		{sortable_column name="omschrijving" value="Omschrijving"}
		{sortable_column name="aantal" value="Aantal regels"}
		{sortable_column name="totaal" value="Totaal bedrag"}
	{/table_header}

	<tbody>
	{foreach from=$results item=result}
		<tr>
			<td>{$result.artikelcode}</td>
			<td>{$result.omschrijving}</td>
			<td>{$result.aantal}</td>
			<td>{prijs prijs=$result.totaal}</td>
		</tr>
	{/foreach}
	</tbody>
	
{/paged_table}

<div class="noprint">
	<ul class="menu">
		<li><a href="{$base}/artikelcode/nieuw">Nieuwe artikelcode toevoegen</a></li>
	</ul>
</div>

<div id="autocomplete" class="autocomplete"></div>

<script type="text/javascript">
	setupKlantAutoCompletion();
</script>

{include file='page_footer.tpl'}