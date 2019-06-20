{include file='page_header.tpl'}

{html_global_error errors=$errors class=errorbox}

<h2>Artikelcode {$artikelcode.artikelcode|escape:HTML} bewerken</h2>

<form method="post" action="{$base}/artikelcode/opslaan/code/{$artikelcode.artikelcode|escape:HTML}">

	{include file='artikelcode/_form.tpl'}

	<br />
	<input type="submit" value="Artikelcode opslaan" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/artikelcode'" />
</form>

{include file='page_footer.tpl'}