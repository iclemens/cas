{include file='page_header.tpl'}

{literal}
<script type="text/javascript">
	function do_submit() {
		if(document.forms[0].wachtwoord1.value !=
			document.forms[0].wachtwoord2.value) {
				alert("De wachtwoorden zijn niet gelijk");
		} else {
			document.forms[0].submit();
		}
	}
</script>
{/literal}

<h2>Wachtwoord aanpassen</h2> 

{if $message}
<div class="errorbox">
{$message}
</div>
{/if}

<p>
Op deze pagina kunt u uw wachtwoord wijzigen in een voor u wenselijk wachtwoord.
</p>

<form method="post" action="{$base}/gebruiker/wachtwoord_wijzigen">
	<p>
	
	Huidig wachtwoord:<br />
	<input type="password" name="wachtwoord" /><br />
	<br />
	
	Nieuw wachtwoord:<br />
	<input type="password" name="wachtwoord1" /><br />
	<br />

	Nieuw wachtwoord (ter controle):<br />
	<input type="password" name="wachtwoord2" /><br />
	<br />

	<input type="button" value="Wijzig wachtwoord" onclick="do_submit()" />
	</p>
</form>

{include file='page_footer.tpl'}
