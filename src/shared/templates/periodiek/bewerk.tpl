{include file='page_header.tpl'}
<script type="text/javascript" src="{$base}/scripts/shared/money.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/periodiek.js"></script>

<h2>Periodiekeregel {$periodiekeregels[0].volgnummer} bewerken</h2>

<form method="post" action="{$base}/periodiek/opslaan/id/{$periodiekeregels[0].volgnummer}">

	<input type="hidden" name="form_id" value="{$form_id}" />

	{include file='periodiek/_form.tpl'}

	<div id="autocomplete" class="autocomplete"></div>

	<script type="text/javascript">
		allowTableExpansion = false;
		updateAllTotals(true);	
		setupArtikelcodeAutoCompletion();
		setupKlantAutoCompletion();
	</script>

	<br />
	<input type="submit" value="Periodiekeregel opslaan" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/periodiek'" />
</form>

{include file='page_footer.tpl'}