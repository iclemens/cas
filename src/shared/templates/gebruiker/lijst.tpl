{include file='page_header.tpl'}

{literal}
<script type="text/javascript">

	function verwijder_gebruiker(naam, volgnummer) {
		if(confirm("Wilt u gebruiker " + naam + " echt verwijderen?") == true) {
			document.location = '{/literal}{$base}{literal}/gebruiker/verwijder/id/' + volgnummer;
		}
	}

</script>
{/literal}

<h2>Gebruikers</h2>

<p>Dit is een overzicht van de in het systeem aanwezige gebruikers.
Gebruikers in rood hebben momenteel geen toegang. </p>

<p>Let op: het is niet
mogelijk klant gebruikers te verwijderen. Het is wel mogelijk deze
gebruikers te deactiveren.
</p>

{if $mededeling != ''}
<div>{$mededeling}</div>
{/if}

<table class="generic_table" style="width: 100%">
	<thead>
		<tr>
			<th>Gebruikersnaam</th>
			<th>Type</th>
			<th>Actief</th>
			<th class="noprint">Bewerken</th>
			<th class="noprint">Verwijderen</th>
		</tr>
	</thead>

	<tbody>
		{foreach from=$gebruikers item=gebruiker}
		<tr {if $gebruiker.actief == 0}class="inactive"{/if}>
			<td onclick="document.location='{$base}/gebruiker/bewerk/id/{$gebruiker.volgnummer}'">{$gebruiker.gebruikersnaam|escape:'html'}</td>
			<td>
				{if $gebruiker.type == 1}Directie{/if}
				{if $gebruiker.type == 2}Boekhouding{/if}
				{if $gebruiker.type == 3}Klant{/if}
			</td>
			<td>
				{if $gebruiker.actief == 0}Niet actief{else}Actief{/if}
			</td>
			<td class="noprint">
				<a href="{$base}/gebruiker/bewerk/id/{$gebruiker.volgnummer}">Bewerken</a>
			</td>
			<td class="noprint">
				{if $gebruiker.type != 3 and $gebruiker.volgnummer != $gebruiker_id}
				<a href="javascript:verwijder_gebruiker('{$gebruiker.gebruikersnaam|escape:'html'}',{$gebruiker.volgnummer});">Verwijderen</a>
				{/if}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<div class="noprint">
	<ul class="menu">
		<li><a href="{$base}/gebruiker/nieuw">Nieuwe gebruiker toevoegen</a> (voor directie en administratie)</li>
		<li><a href="{$base}/klant/nieuw">Nieuwe klant toevoegen</a> (voor klanten)</li>
	</ul>
</div>

{include file='page_footer.tpl'}
