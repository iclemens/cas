{include file='page_header.tpl'}

{html_global_error errors=$errors class=errorbox}

<h2>Template toevoegen</h2>

<form method="post" action="{$base}/emailtemplate/maak">

	{include file='emailtemplate/_form.tpl'}

	<br />
	<input type="submit" value="Template toevoegen" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/emailtemplate'" />
</form>

{include file='page_footer.tpl'}