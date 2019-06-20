<?xml version="1.0" encoding="UTF-8"?>
<klant nummer="{$klant.klantnummer}" actief="{if $klant.actief == 0}false{else}true{/if}" type="{if $klant.klanttype == 0}zakelijk{else}particulier{/if}">

	<naam>{klantnaam klant=$klant|escapexml}</naam>

	<bedrijfsnaam>{$klant.bedrijfsnaam|escapexml}</bedrijfsnaam>
	<afdeling>{$klant.afdeling|escapexml}</afdeling>

	<contactpersoon>
		<aanhef>{$klant.aanhef|escapexml}</aanhef>
		<voornaam>{$klant.voornaam|escapexml}</voornaam>
		<achternaam>{$klant.achternaam|escapexml}</achternaam>		
	</contactpersoon>

	<factuuradres>
		<adres>{$klant.factuuradres|escapexml}</adres>
		{if $klant.factuuradres2 != ''}<adres>{$klant.factuuradres2|escapexml}</adres>{/if}
		<postcode>{$klant.factuurpostcode|escapexml}</postcode>
		<plaats>{$klant.factuurplaats|escapexml}</plaats>
		<land>{$klant.factuurland|escapexml}</land>
		<email>{$klant.factuuremail|escapexml}</email>
	</factuuradres>

	<bezoekadres>
		<adres>{$klant.bezoekadres|escapexml}</adres>
		{if $klant.bezoekadres2 != ''}<adres>{$klant.bezoekadres2|escapexml}</adres>{/if}
		<postcode>{$klant.bezoekpostcode|escapexml}</postcode>
		<plaats>{$klant.bezoekplaats|escapexml}</plaats>
		<land>{$klant.bezoekland|escapexml}</land>
	</bezoekadres>

	<emailtemplate>
		<onderwerp>{$emailtemplate.onderwerp|escapexml}</onderwerp>
		<inhoud>{$emailtemplate.inhoud|escapexml}</inhoud>
	</emailtemplate>

	<btwnummer gecontroleerd="{if $klant.btwgecontroleerd == 0}false{else}true{/if}">{$klant.btwnummer|escapexml}</btwnummer>
	<btwcategorie>{$klant.btwcategorie}</btwcategorie>

	<machtiging maand="{if $klant.machtigingmaand == 0}false{else}true{/if}" jaar="{if $klant.machtigingjaar == 0}false{else}true{/if}" />

	<email>{$klant.emailadres|escapexml}</email>
	<website>{$klant.website|escapexml}</website>
	<telefoonvast>{$klant.telefoonvast|escapexml}</telefoonvast>
	<telefoonmobiel>{$klant.telefoonmobiel|escapexml}</telefoonmobiel>

</klant>
