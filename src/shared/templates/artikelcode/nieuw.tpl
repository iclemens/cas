{include file='page_header.tpl'}

{html_global_error errors=$errors class=errorbox}

<h2>Artikelcode toevoegen</h2>

<form method="post" action="{$base}/artikelcode/maak">

	Artikelcode:<br />
	{html_textbox field=artikelcode value=$artikelcode.artikelcode errors=$errors}

	<br />
	<br />


	{include file='artikelcode/_form.tpl'}

	<br />
	<input type="submit" value="Artikelcode toevoegen" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/artikelcode'" />
</form>

{include file='page_footer.tpl'}