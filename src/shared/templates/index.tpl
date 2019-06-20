{include file="page_header.tpl"}

<h2>Overzicht</h2>

<p>
	Welkom bij {$application_abbr}! U kunt door het systeem navigeren 
	met de menubalk bovenaan of de opties onderaan de pagina.
</p>

<table class="content-container">

	<tr>
		<td>

		<h2>Facturatie</h2>

		<ul class="menu">
			{if $user_type == 'Directie'}
			<li><a href="{$base}/factuur/nieuw">Nieuwe factuur maken</a></li>
			<li><a href="{$base}/factureren/nieuw">Nieuwe items in de wachtrij plaatsen</a></li>
			<li><a href="{$base}/periodiek/nieuw">Nieuwe periodieke factuur maken</a></li>
			<li>&nbsp;</li>
			{/if}
				
			<li><a href="{$base}/factuur/zoek">Bestaande factuur opvragen</a></li>
			<li><a href="{$base}/factuur/openstaand">Openstaande facturen opvragen</a></li>
			<li><a href="{$base}/factuur/incasso">Openstaande incassos opvragen</a></li>
			<li>&nbsp;</li>

			{if $user_type == 'Directie'}
			<li><a href="{$base}/index/wachtrij">Klanten met te factureren items</a></li>
			<li><a href="{$base}/factureren/lijst">Lijst met te factureren items ophalen</a></li>
			<li><a href="{$base}/periodiek/lijst">Lijst met periodieke facturen ophalen</a></li>
			<li>&nbsp;</li>			
			{/if}
		</ul>
	</td>
	
	<td valign="top">
	
		<h2>Instellingen</h2>

		<ul class="menu">
			{if $user_type == 'Directie'} 
			<li><a href="{$base}/artikelcode">Artikelcodes beheren</a></li>
			<li><a href="{$base}/emailtemplate">Emailtemplates beheren</a></li>
			<li><a href="{$base}/gebruiker/lijst">Gebruikers beheren</a></li>
			{/if}
			<li><a href="{$base}/gebruiker/wachtwoord_wijzigen">Wachtwoord wijzigen</a></li>
		</ul>

		<h2>Overigen</h2>
		
		<ul class="menu">
			<li><a href="{$base}/overzicht/totaal_per_artikelcode">Totaalbedragen per artikelcode</a></li>
		</ul>
	</td>
	</tr>
</table>

{include file="page_footer.tpl"}