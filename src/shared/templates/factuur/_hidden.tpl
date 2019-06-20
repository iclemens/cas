<input type="hidden" name="klantnummer" value="{$factuur.klantnummer}" />
<input type="hidden" name="btw_categorie" value="{$btw_categorie}" />
<input type="hidden" name="btw_percentage" value="{$factuur.btw_percentage}" />
<input type="hidden" name="datum" value="{$factuur.datum}" />

<input type="hidden" name="incasso" value="{$factuur.incasso}" />

<input type="hidden" name="tekst" value="{$factuur.tekst|escape:html}" />

{assign var=i value=1}
{foreach from=$factuur.regels item=regel}
	<input type="hidden" name="ref[]" value="{$regel.ref}" />
	<input type="hidden" name="artikelcode[]" value="{$regel.artikelcode}" />
	<input type="hidden" name="omschrijving[]" value="{$regel.omschrijving|escape:html}" />
	<input type="hidden" name="aantal[]" value="{$regel.aantal}" />
	<input type="hidden" name="prijs[]" value="{$regel.prijs/100}" />

{assign var=i value=$i+1}
{/foreach}

<input type="hidden" name="korting" value="{if $factuur.kortingtype == "absolute"}{$factuur.korting/100}{else}{$factuur.korting}{/if}" />
<input type="hidden" name="kortingtype" value="{$factuur.kortingtype}" />