{include file='page_header.tpl'}

{html_global_error errors=$errors}

<h2>Gebruiker aanmaken</h2>

<form method="POST" action="{$base}/gebruiker/maak">

	{include file='gebruiker/form.tpl' actie=nieuw}

	<br />
	<input type="submit" value="Gebruiker aanmaken" />
	<input type="button" value="Annuleren" onClick="document.location = '{$base}/gebruiker/lijst'" />
</form>

{include file='page_footer.tpl'}
