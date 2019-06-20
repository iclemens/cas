<?xml version="1.0" encoding="UTF-8"?>
<factureren klantnummer="{$factureren.klantnummer}">
	<regels>
{foreach from=$factureren.regels item=regel}
		<regel ref="{$regel.volgnummer}">
			<artikelcode>{$regel.artikelcode}</artikelcode>
			<omschrijving>{$regel.omschrijving}</omschrijving>
			<aantal>{$regel.aantal}</aantal>
			<prijs>{$regel.prijs}</prijs>
		</regel>
{/foreach}
	</regels>
</factureren>