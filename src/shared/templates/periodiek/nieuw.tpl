{include file='page_header.tpl'}

<script type="text/javascript" src="{$base}/scripts/shared/money.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/periodiek.js"></script>

<h2>Periodiekeregel(s) aanmaken</h2>

<form method="post" name="periodiek" action="{$base}/periodiek/maak">

	<input type="hidden" name="form_id" value="{$form_id}" />

	{include file='periodiek/_form.tpl'}

	<div id="autocomplete" class="autocomplete"></div>
	
	<script type="text/javascript">
		allowTableExpansion = true;
		updateAllTotals(true);		
		setupArtikelcodeAutoCompletion();
		setupKlantAutoCompletion();
	</script>

	<div id="billThisMonth" style="display: block"><input name="deze_maand" type="checkbox" checked="checked" /> Deze maand al factureren.<br /><br /></div>
	
	<input type="submit" value="Periodiekeregel aanmaken" />
	<input type="button" value="Annuleren" onclick="document.location = '{$base}/periodiek'" />
</form>

{include file='page_footer.tpl'}