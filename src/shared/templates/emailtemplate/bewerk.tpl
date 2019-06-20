{include file='page_header.tpl'}

{html_global_error errors=$errors class=errorbox}

<h2>Template {$emailtemplate.volgnummer|escape:HTML} bewerken</h2>

<form method="post" action="{$base}/emailtemplate/opslaan/id/{$emailtemplate.volgnummer|escape:HTML}">

	{include file='emailtemplate/_form.tpl'}

	<br />
	<input type="submit" value="Template opslaan" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/emailtemplate'" />
</form>

{include file='page_footer.tpl'}