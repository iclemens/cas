{include file='page_header.tpl'}

<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>

{literal}
<script type="text/javascript">
	function verwijder_factureren(omschrijving, volgnummer) {
		if(confirm("Wilt u regel '" + omschrijving + "' echt verwijderen?") == true) {

			new Ajax.Request(baseURL + "/factureren/verwijder_xml/id/" + volgnummer, {
				method: 'post',
				parameters: {id: volgnummer},
				onSuccess: function(transport) {
					xmldoc = transport.responseXML;
					node = extractNode(xmldoc, ['removed']);
					
					if(!node.hasAttribute('ref')) {
						alert('De regel is NIET verwijderd, controlleer de log bestanden');
						return;
					}
					
					if(node.getAttribute('ref') != volgnummer) {
						alert('Er is een regel verwijderd, maar het volgnummer is niet bekend.');
						return;
					}

					window.location.reload(true);					
				}
			});
		}
	}
</script>
{/literal}

<h2>Factureren</h2>

<p>Dit is een overzicht van de te factureren items.</p>

<table width="100%" class="generic_table">
	<tr>
		<th>Klant</th>
		<th>Artikelcode</th>
		<th>Omschrijving</th>
		<th>Aantal</th>
		<th>Prijs</th>
		<th>Totaal</th>
		<th class="noprint">Verwijderen</th>
	</tr>

	{foreach from=$factureren item=facturerenregel}
	
	<tr>
		<td>{klantnaam klant=$facturerenregel}</td>
		<td>{$facturerenregel.artikelcode}</td>
		<td>{$facturerenregel.omschrijving|escape:html}</td>
		<td>{$facturerenregel.aantal}</td>
		<td>{prijs prijs=$facturerenregel.prijs}</td>
		<td>{prijs prijs=$facturerenregel.totaal}</td>
		<td class="noprint">
			<a href="javascript:verwijder_factureren('{$facturerenregel.omschrijving|escape:'html'}',{$facturerenregel.volgnummer});">Verwijderen</a>			
		</td>		
	</tr>
	{/foreach}

</table>

<p class="noprint">
<a href="{$base}/factuur/nieuw">Nieuwe factuur maken</a><br />
<a href="{$base}/factureren/nieuw">Nieuwe items in de wachtrij plaatsen</a ><br />
</p>

{include file='page_footer.tpl'}
