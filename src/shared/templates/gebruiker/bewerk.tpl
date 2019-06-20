{include file='page_header.tpl'}

{html_global_error errors=$errors}

<h2>Gebruiker bewerken</h2>

<form method="post" action="{$base}/gebruiker/opslaan/id/{$gebruiker.volgnummer}">

	{include file='gebruiker/form.tpl' actie=bewerk}

	<br />
	<input type="submit" value="Gebruiker opslaan" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/gebruiker/lijst'" />
</form>

{include file='page_footer.tpl'}
