{include file='page_header.tpl'}

<h2>Factuur afboeken</h2>

Factuur met kenmerk {factuurnummer factuur=$factuur} 
({prijs prijs=$factuur.totaal})
voor {klantnaam klant=$klant} is voldaan op datum:

<br /><br />
<form method="POST" action="{$base}/betaling/afboeken/id/{$factuur.volgnummer}">
<input type="text" name="datum" value="{$smarty.now|date_format:"%d/%m/%Y"}" readonly />
<input type="button" value="Kies datum" onClick="displayCalendar(document.forms[0].datum, 'dd/mm/yyyy', this)" />
<br /><br />
<input type="submit" value="Afboeken" />
<input type="button" value="Annuleren" onClick="document.location='{$base}/factuur/openstaand'" />
</form>

{include file='page_footer.tpl'}
