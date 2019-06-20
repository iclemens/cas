{include file='page_header.tpl'}

{html_global_error errors=$errors class=errorbox}

<h2>Klant {klantnummer klantnr=$klant.klantnummer} bewerken</h2>

<form method="post" action="{$base}/klant/opslaan/id/{$klant.klantnummer}">
	
	{include file='klant/form.tpl'}

	<br />
	<input type="submit" value="Klant opslaan" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/index'" />
</form>

{include file='page_footer.tpl'}
