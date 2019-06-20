<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/money.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>
<script type="text/javascript" src="{$base}/scripts/factuur.js"></script>

<table width="700">
	<tr>
		<td valign="top">
			Klant:<br />

			<input type="text" name="klantnummer" value="{$factuur.klantnummer}" onchange="klantnummerOnChange(0,0)" size="8" />

			<br />
			{html_error_for_field field=klantnummer errors=$errors}
		</td><td valign="top" width="250">
			<br />
			<span id="klantnaam"></span>
		</td><td valign="top">
			btw:<br />
			<select id="btw_categorie" name="btw_categorie" onchange="btwOnChange(this)">
			{foreach from=$btw_tarieven key=cur_btw_categorie item=btw_data}
				<option value="{$cur_btw_categorie}"
					{if $btw_categorie == $cur_btw_categorie}selected="selected"{/if}
					>{$btw_data.description} ({$btw_data.rate}%)</option>
			{/foreach}
			</select>

		</td><td valign="top">
			Datum:<br />
			<input type="text" name="datum" value="{$factuur.datum}" readonly="readonly" style="background-color: #EEEEEE; color: black;" />
			<input type="button" value="Kies datum" 
				onclick="displayCalendar(document.factuur.datum, 'dd/mm/yyyy', this)" />
			<br />
			{html_error_for_field field=datum errors=$errors}
		</td>
	</tr>
</table>

<div id="incasso_div" {if not ($klant.machtigingmaand) and not ($klant.machtigingjaar)}style="display: none"{/if}>
<input type="checkbox" {if $factuur.incasso == '1'}checked="checked"{/if} id="incasso" name="incasso" /> Automatische incasso (<span id="incasso_type">{if $klant.machtigingmaand or $klant.machtigingjaar}{if $klant.machtigingmaand and $klant.machtigingjaar}maandelijks en jaarlijks{else}{if $klant.machtigingmaand}maandelijks{else}jaarlijks{/if}{/if}{else}type onbekend{/if}</span>)
</div>

<br />

<!--Onderwerp van de email:<br />
<span id="onderwerp">{$factuur.onderwerp|escape:html}</span>
<input size="80" name="onderwerp" id="onderwerp">{$factuur.onderwerp|escape:html}</input>

<br />
<br />-->

Inhoud van de email:<br />
<textarea cols="80" rows="9" 
	id="tekst" name="tekst">{$factuur.tekst|escape:html}</textarea>
<br />

<hr />

{html_global_error errors=$errors class=errorbox}
<br/>

<table>
	<thead>
	<tr>
		<th>Nr.</th>
		<th>Art.</th>
		<th>Omschrijving</th>
		<th>Aantal</th>
		<th>Prijs</th>
		<th></th>
		<th>Totaal</th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<td></td>
		<td></td>
		<td>Korting:</td>
		<td>
			<input type="text" size="4" 
				name="korting"
				value="{if $factuur.kortingtype == "absolute"}{$factuur.korting/100}{else}{$factuur.korting}{/if}"
				onchange="updateAllTotals()" />
		</td>
		<td>
			<select name="kortingtype"
				onchange="updateAllTotals()">
				<option value="absolute" {if $factuur.kortingtype == "absolute"}selected="selected"{/if}>EUR</option>
				<option value="relative" {if $factuur.kortingtype == "relative"}selected="selected"{/if}>%</option>
			</select>
		</td>
		<td></td>
		<td></td>
	</tr>
	</tfoot>

	<tbody id="factuur_regels">
{assign var=i value=1}
{foreach from=$factuur.regels item=regel}
	{include file='factuur/_regel.tpl'}
	{assign var=i value=$i+1}
{/foreach}

{section name=loop start=$i loop=$aantalregels+1}	
	{include file='factuur/_regel.tpl'}
	{assign var=i value=$i+1}
{/section}
	</tbody>

</table>
<br />
<table width="300">
	<tr>
		<td>Subtotaal: <div id="f_subtotaal">&euro; 0,00</div></td>

		<td>btw: <div id="f_btw">&euro; 0,00</div></td>
		<td>Totaal: <div id="f_totaal">&euro; 0,00</div></td>
	</tr>
</table>

<div id="autocomplete" class="autocomplete"></div>

<script type="text/javascript">
	updateAllTotals(true);
	setupArtikelcodeAutoCompletion();
	setupKlantAutoCompletion();
</script>

