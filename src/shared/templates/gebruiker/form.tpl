Gebruikersnaam:<br />
<input type="text" name="gebruikersnaam" {if $actie == 'bewerk'}disabled="disabled" style="background-color: #EEEEEE; color: black;"{/if} value="{$gebruiker.gebruikersnaam|escape:html}" />
{html_error_for_field field=gebruikersnaam errors=$errors}

{if $fouten.gebruikersnaam}
<div class="errortext">{$fouten.gebruikersnaam}</div>
{/if}
<br />
<br />
Wachtwoord:<br />
<input type="password" name="wachtwoord" /><br />
{html_error_for_field field=wachtwoord errors=$errors}
<br />
Wachtwoord (opnieuw):<br />
<input type="password" name="wachtwoord2" /><br />
<br />
Actief:<br />
<select name="actief">
	<option value="0"{if $gebruiker.actief == 0} selected="selected"{/if}>Niet actief</option>
	<option value="1"{if $gebruiker.actief == 1} selected="selected"{/if}>Actief</option>
</select><br />
{html_error_for_field field=actief errors=$errors}
<br />
Type:<br />
<select name="type">
	{if $gebruiker.type != 3}
	<option value="1"{if $gebruiker.type == 1} selected="selected"{/if}>Directie</option>
	<option value="2"{if $gebruiker.type == 2} selected="selected"{/if}>Boekhouding</option>
	{else}
	<option value="3" selected="selected">Klant</option>
	{/if}
</select><br />
{html_error_for_field field=type errors=$errors}