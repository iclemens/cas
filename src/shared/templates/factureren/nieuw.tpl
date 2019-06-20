{include file='page_header.tpl'}
<script type="text/javascript" src="{$base}/scripts/shared/money.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/factureren.js"></script>

{html_global_error errors=$errors class=errorbox}

<h2>Nieuwe te factureren items</h2>

<form method="post" action="{$base}/factureren/toevoegen">

<table>
	<tr>
		<td valign="top">
			Klant:<br />

			<input type="text" name="klantnummer" value="{$factureren.klantnummer}" 
				onchange="genericKlantnummerOnChange($('klantnaam'), document.getElementsByName('klantnummer')[0].value);" size="8" />

			<br />
			{html_error_for_field field=klantnummer errors=$errors}
		</td><td>
			<br />
			<span id="klantnaam"></span>
		</td>
	</tr>
</table>

<br />
<hr />

<br />

<table>
	<thead>
	<tr>
		<th>Nr.</th>
		<th>Art.</th>
		<th>Omschrijving</th>
		<th>Aantal</th>
		<th>Prijs</th>
		<th>Totaal</th>
	</tr>
	</thead>

	<tbody id="factuur_regels">
{assign var=i value=1}
{foreach from=$factureren.regels item=regel}
	{include file='factureren/_regel.tpl'}
	{assign var=i value=$i+1}
{/foreach}

{assign var=regel value=0}
{include file='factureren/_regel.tpl'}
	</tbody>
</table>

<div id="autocomplete" class="autocomplete"></div>

<script type="text/javascript">
	updateAllTotals(true);		
	setupArtikelcodeAutoCompletion();
	setupKlantAutoCompletion();
	genericKlantnummerOnChange($('klantnaam'), document.getElementsByName('klantnummer')[0].value);
</script>

<br />
<input type="submit" value="Items toevoegen" />
<input type="button" value="Annuleren" onclick="document.location = '{$base}/index'" />
</form>

{include file='page_footer.tpl'}
