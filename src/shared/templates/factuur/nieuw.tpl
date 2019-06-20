{include file='page_header.tpl'}

<h2>Nieuwe factuur</h2>

<form name="factuur" method="post" action="{$base}/factuur/voorbeeld">
{include file='factuur/form.tpl'}
<br />
<input type="submit" value="Maak voorbeeld" />
<input type="button" value="Annuleren" onclick="document.location = '{$base}/index'" />
</form>

{if $klantnummer neq 0}
<script language="javascript">
	document.getElementsByName("klantnummer")[0].value = {$klantnummer};	
	klantnummerOnChange(0,0)
</script>
{/if}

{include file='page_footer.tpl'}
