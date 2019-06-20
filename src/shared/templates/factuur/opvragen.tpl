{include file='page_header.tpl'}

<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>

<div class="noscreen">
Lijst met facturen van

{if $parameters.klantnummer == ""}
alle klanten
{else}
klant {$parameters.klantnummer|escape:HTML}
{/if}

{if $parameters.factuurnummer == ""}
{else}
met factuurnummer {$parameters.factuurnummer|escape:HTML}
{/if}

{if $parameters.boekjaar == ""}
{else}
uit boekjaar {$parameters.boekjaar|escape:HTML}
{/if}
.
</div>

<div class="noprint">

<h2>Factuur opvragen</h2>

<form name="factuur" method="get" action="{$base}/factuur/zoek">
	<input type="hidden" name="sort" value="{$sorter->getString()}" />

	<table>
{if $user_type != 'Klant'}
		<tr>

			<td colspan="3">
			Klantnummer:<br />
			<input type="text" name="klantnummer" value="{$parameters.klantnummer}" 
				onchange="genericKlantnummerOnChange($('klantnaam'), document.getElementsByName('klantnummer')[0].value)" size="8" />
			<span id="klantnaam">{klantnaam klant=$klant}</span>
			</td>

		</tr>
{/if}
	
		<tr>
			<td>
				<br />
				Factuurnummer:<br />
				<input type="text" name="factuurnummer" value="{$parameters.factuurnummer|escape:HTML}" />
			</td><td>
				<br />
				Jaar:<br />
				<input type="text" name="boekjaar" size="13" value="{$parameters.boekjaar|escape:HTML}"/><br />
			</td><td>
				<br />
				Maand:<br />
				<input type="text" name="boekmaand" size="4" value="{$parameters.boekmaand|escape:HTML}"/><br />
			</td>
		</tr>
	
		<tr>
			<td colspan="2">
				<br />
				<input type="submit" value="Zoeken..." />
			</td>
		</tr>
	</table>
</form>

<br /><hr /><br />

</div>

<h2>Resultaat</h2>

{paged_table pager=$pager sorter=$sorter filter=$parameters}
	{table_header}		
			<th>Kenmerk</th>
			{sortable_column name="datum" value="Datum"}
			{sortable_column name="bedrijfsnaam,achternaam" value="Klant"}
			{sortable_column name="totaal" value="Totaal (incl. BTW)"}

			{if $user_type == 'Directie'}
			<th class="noprint">Herzenden</th>
			{/if}

	{/table_header}

	<tbody>
		{foreach from=$results key=factuurvolgnummer item=factuur}
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			<td><a href="{$base}/factuur/bekijk/naam/{factuurnummer factuur=$factuur}.pdf">{factuurnummer factuur=$factuur}</a></td>
			<td>{$factuur.datum|date_format:"%d/%m/%Y"}</td>
			<td>{klantnaam klant=$factuur}</td>
			<td>{prijs prijs=$factuur.totaal}</td>
			{if $user_type == 'Directie'}			
			<td class="noprint"><a href="{$base}/factuur/herzenden/id/{$factuur.volgnummer}">Herzenden</a></td>
			{/if}
		</tr>
		{/foreach}

		<tr>
			<td colspan="5" style="background-color: #FAFAFA"></td>
		</tr>

		<tr>
			<th colspan="3"><br /><b>Totaal factuurbedrag:</b></th>
			<th>Incl. BTW:<br />{prijs prijs=$totaal}</th>
			{if $user_type == 'Directie'}
			<th class="noprint"></th>
			{/if}
		</tr>
	</tbody>

{/paged_table}

<div class="noprint">
<br />
<form method="post" action="{$base}/factuur/zoek_resultaat_als_PDF">

<input type="hidden" name="boekjaar" value="{$parameters.boekjaar|escape:HTML}" />
<input type="hidden" name="boekmaand" value="{$parameters.boekmaand|escape:HTML}" />
<input type="hidden" name="factuurnummer" value="{$parameters.factuurnummer|escape:HTML}" />
<input type="hidden" name="klantnummer" value="{$parameters.klantnummer|escape:HTML}" />

<input type="submit" value="Download resultaten in een PDF" />
</form>
</div>

<div id="autocomplete" class="autocomplete"></div>

<script type="text/javascript">
	setupKlantAutoCompletion();
	genericKlantnummerOnChange($('klantnaam'), document.getElementsByName('klantnummer')[0].value); 	
</script>

{include file='page_footer.tpl'}
