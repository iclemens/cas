{include file='page_header.tpl'}

<h2>Klant aanmaken</h2>

<form method="post" action="{$base}/klant/maak">

	<input type="hidden" name="form_id" value="{$form_id}" />

	{include file='klant/form.tpl'}

	<br />
	<input type="submit" value="Klant aanmaken" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/klant'" />
</form>

{include file='page_footer.tpl'}
