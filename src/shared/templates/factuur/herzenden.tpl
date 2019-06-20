{include file='page_header.tpl'}

<h2>Herzenden factuur</h2>

<form name="herzenden" method="post" action="{$base}/factuur/herzenden/id/{$factuur.volgnummer}">

	<table>
	<tr><td>Klant:</td><td>{$klant.klantnummer} ({klantnaam klant=$klant})</td></tr>
	<tr><td>Email adres:</td><td>{$klant.factuuremail}</td></tr>	
	<tr><td>Factuurnummer:</td><td>{factuurnummer factuur=$factuur}</td></tr>	
	</table>
	<br />
	<a href="{$base}/factuur/bekijk/naam/{factuurnummer factuur=$factuur}.pdf">Bekijk factuur</a>
	<br />
	<br />
	Onderwerp email:<br />
	<input type="text" size="40" name="onderwerp" value="{$herzenden.onderwerp|escape:html}" />
	<br />
	<br />
	Begeleidende email:<br />
	<textarea cols="80" rows="12" 
		id="tekst" name="tekst">{$herzenden.tekst|escape:html}</textarea>

	<br />
	<br />

	Stuur kopie naar (b.v. Jan Janssen &lt;jan@janssen.nl&gt;):<br />
	<input type="text" size="40" name="email" value="{$herzenden.email|escape:html}" /><br />

	<br />

	<input type="submit" value="Herzend factuur" />
	<input type="button" value="Annuleren" onClick="document.location = '{$base}/factuur/index'" />	
</form>

{include file='page_footer.tpl'}