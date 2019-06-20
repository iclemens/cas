<script type="text/javascript">
	var klantnummer = {$klant.klantnummer};
</script>

<script src="{$base}/scripts/shared/xml.js" type="text/javascript"></script>
<script src="{$base}/scripts/klant.js" type="text/javascript"></script>

<table>
	<tr>
		<td>
			Bedrijfsnaam:<br />
			{html_textbox field=bedrijfsnaam value=$klant.bedrijfsnaam errors=$errors}
			<br /><br />
		</td><td>
			Afdeling:<br />
			{html_textbox field=afdeling value=$klant.afdeling errors=$errors}
			<br /><br />
		</td><td>
		</td>
	</tr>
	<tr>
		<td>
			BTW nummer:<br />
			{html_textbox field=btwnummer value=$klant.btwnummer errors=$errors}
			<br /><br />
		</td><td>
			Status BTW nummer:<br />
			<select name="btwgecontroleerd">
				<option value="0">Niet gecontroleerd</option>
				<option value="1" {if $klant.btwgecontroleerd}selected="selected"{/if}>Gecontroleerd</option>
			</select>
			<br /><br />
		</td><td>
			Standaard BTW:<br />
			<select name="btwcategorie">
			{foreach from=$btw_tarieven key=btw_categorie item=btw_data}
				<option value="{$btw_categorie}"
					{if $klant.btwcategorie == $btw_categorie}selected="selected"{/if}
					>{$btw_data.description} ({$btw_data.rate}%)</option>
			{/foreach}				
			</select>
			<br /><br />
		</td>
	</tr>
	<tr>
		<td>
			Aanhef:<br />
			{html_textbox field=aanhef value=$klant.aanhef errors=$errors}
		</td><td>
			Voornaam:<br />
			{html_textbox field=voornaam value=$klant.voornaam errors=$errors}
		</td><td>
			Achternaam:<br />
			{html_textbox field=achternaam value=$klant.achternaam errors=$errors}
		</td>
	</tr>
</table>

<br />

<table>
	<tr>
		<td>Klant status:</td>
		<td>
			<select name="actief">
				<option value="0" {if $klant.actief == 0}selected="selected"{/if}>Niet actief</option>
				<option value="1" {if $klant.actief == 1}selected="selected"{/if}>Actief</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Klant type:</td>
		<td>
			<select name="klanttype">
				<option value="0" {if $klant.klanttype == 0}selected="selected"{/if}>Zakelijk</option>
				<option value="1" {if $klant.klanttype == 1}selected="selected"{/if}>Particulier</option>
			</select>
		</td>
	</tr>

	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>

	<tr>
		<td>Emailtemplate:</td>
		<td>
		
			<select name="emailtemplate">
				{foreach from=$emailtemplates key=templateid item=templateomsch}
				<option value="{$templateid}"{if $klant.emailtemplate eq $templateid} selected="selected"{/if}>{$templateomsch}</option>
				{/foreach}
			</select>
		</td>
	</tr>
</table>

<br />

<input type="checkbox" 
	{if $klant.factuurtemplate != NULL}checked="checked"{/if} 
	name="factuurtemplatechk" id="factuurtemplatechk" 
	onclick="factuurtemplatechk_onclick()" />
	aangepaste template gebruiken voor facturen<br />

<input type="text" size="50" 
	{if $klant.factuurtemplate == NULL}style="display: none"{/if} 
	id="factuurtemplate" name="factuurtemplate" 
	value="{$klant.factuurtemplate|escape:html}" />
	{html_error_for_field field=factuurtemplate errors=$errors}

<br />
<hr />
<br />

<table>

	<tr>
		<td colspan="3">
			<input name="machtigingmaand" type="checkbox" {if $klant.machtigingmaand}checked="checked"{/if} /> machtiging voor maandelijkse betalingen
		</td></tr><tr><td colspan="3">
			<input name="machtigingjaar" type="checkbox" {if $klant.machtigingjaar}checked="checked"{/if} /> machtiging voor jaarlijkse betalingen
		</td>
	</tr>
</table>

<br />
<hr />
<br />

<table>
	<tr>
		<td valign="top">
			<b><u>FACTUUR:</u></b>
			<br /><br />

			Adres:<br />
			{html_textbox field=factuuradres value=$klant.factuuradres errors=$errors}<br />
			<br />
			{html_textbox field=factuuradres2 value=$klant.factuuradres2 errors=$errors}
			<br /><br />

			Postcode:<br />
			{html_textbox field=factuurpostcode value=$klant.factuurpostcode errors=$errors}
			<br /><br />

			Plaats:<br />
			{html_textbox field=factuurplaats value=$klant.factuurplaats errors=$errors}
			<br /><br />

			Land:<br />
			{html_textbox field=factuurland value=$klant.factuurland errors=$errors}
			<br /><br />

			E-mailadres:<br />
			{html_textbox field=factuuremail value=$klant.factuuremail errors=$errors}
		</td>

		<td width="1" style="border: 0px; margin: 0px; padding: 0px;"
			bgcolor="#000000"></td>
		<td>&nbsp;</td>

		<td valign="top">
			<b><u>BEZOEK:</u></b>
			<br /><br />

			Adres:<br />
			{html_textbox field=bezoekadres value=$klant.bezoekadres errors=$errors} <br />
			<br />
			{html_textbox field=bezoekadres2 value=$klant.bezoekadres2 errors=$errors}
			<br /><br />

			Postcode:<br />
			{html_textbox field=bezoekpostcode value=$klant.bezoekpostcode errors=$errors}
			<br /><br />

			Plaats:<br />
			{html_textbox field=bezoekplaats value=$klant.bezoekplaats' errors=$errors}
			<br /><br />

			Land:<br />
			{html_textbox field=bezoekland value=$klant.bezoekland' errors=$errors}
			<br /><br />

			<br />

			<input type="button" value="Zelfde als factuuradres" 
				onclick="copy_adres('factuur', 'bezoek')" />
		</td>
	</tr>
</table>

<br />
<hr />
<br />

<table>
	<tr>
		<td valign="top">
			E-mailadres:<br />
			{html_textbox field=emailadres value=$klant.emailadres errors=$errors}
			<br />
			
		</td>
		<td valign="top">
			Website:<br />
			{html_textbox field=website value=$klant.website errors=$errors}
			<br /><br />
		</td>
	</tr>
	<tr>
		<td>
			Telefoonvast:<br />
			{html_textbox field=telefoonvast value=$klant.telefoonvast errors=$errors}
		</td><td>
			Telefoonmobiel:<br />
			{html_textbox field=telefoonmobiel value=$klant.telefoonmobiel errors=$errors}
		</td>
	</tr>
</table>

<br />
<hr />
<br />

Opmerkingen:<br />
<textarea name="opmerkingen" cols="70" rows="5">{$klant.opmerkingen|escape:HTML}</textarea>

<br />
