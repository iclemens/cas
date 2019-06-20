{include file='page_header.tpl'}

<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>

{literal}
<script type="text/javascript">
	function verwijder_regel(omschrijving, volgnummer) {
		if(confirm("Wilt u regel '" + omschrijving + "' echt verwijderen?") == true) {

			new Ajax.Request(baseURL + "/periodiek/verwijder_xml/id/" + volgnummer, {
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

<h2>Periodieke facturen</h2>

<div id="searchbox" class="noprint">

<form name="factuur" method="get" action="{$base}/periodiek/lijst">
	<input type="hidden" name="sort" value="{$sorter->getString()}" />

{if $user_type != 'Klant'}
	<div style="padding: 5px;">
	Klantnummer:<br />
	<input type="text" name="klantnummer" value="{$parameters.klantnummer}" onchange="genericKlantnummerOnChange($('klantnaam'), document.getElementsByName('klantnummer')[0].value);" size="8" />
	<span id="klantnaam">{klantnaam klant=$klant}</span>
	</div>
{/if}

<table width="700">
<tr>
<td width="175">
	<input type="checkbox" name="maand[]" value="1" onchange="maandOnChange(this)" {if in_array(1, $parameters.maand)}checked="checked"{/if}/> {maand nr=1}<br />
	<input type="checkbox" name="maand[]" value="2" onchange="maandOnChange(this)" {if in_array(2, $parameters.maand)}checked="checked"{/if} /> {maand nr=2}<br />
	<input type="checkbox" name="maand[]" value="3" onchange="maandOnChange(this)" {if in_array(3, $parameters.maand)}checked="checked"{/if} /> {maand nr=3}<br />
</td><td width="175">
	<input type="checkbox" name="maand[]" value="4" onchange="maandOnChange(this)" {if in_array(4, $parameters.maand)}checked="checked"{/if} /> {maand nr=4}<br />
	<input type="checkbox" name="maand[]" value="5" onchange="maandOnChange(this)" {if in_array(5, $parameters.maand)}checked="checked"{/if} /> {maand nr=5}<br />
	<input type="checkbox" name="maand[]" value="6" onchange="maandOnChange(this)" {if in_array(6, $parameters.maand)}checked="checked"{/if} /> {maand nr=6}<br />
</td><td width="175">
	<input type="checkbox" name="maand[]" value="7" onchange="maandOnChange(this)" {if in_array(7, $parameters.maand)}checked="checked"{/if} /> {maand nr=7}<br />
	<input type="checkbox" name="maand[]" value="8" onchange="maandOnChange(this)" {if in_array(8, $parameters.maand)}checked="checked"{/if} /> {maand nr=8}<br />
	<input type="checkbox" name="maand[]" value="9" onchange="maandOnChange(this)" {if in_array(9, $parameters.maand)}checked="checked"{/if} /> {maand nr=9}<br />
</td><td width="175">
	<input type="checkbox" name="maand[]" value="10" onchange="maandOnChange(this)" {if in_array(10, $parameters.maand)}checked="checked"{/if} /> {maand nr=10}<br />
	<input type="checkbox" name="maand[]" value="11" onchange="maandOnChange(this)" {if in_array(11, $parameters.maand)}checked="checked"{/if} /> {maand nr=11}<br />
	<input type="checkbox" name="maand[]" value="12" onchange="maandOnChange(this)" {if in_array(12, $parameters.maand)}checked="checked"{/if} /> {maand nr=12}<br />
</td></tr>
</table>
	
<div style="padding: 5px;">
<input type="submit" value="Zoeken..." />
</div>

</form>

</div>

<br />
<hr/ >

<h2>Resultaat</h2>

{paged_table pager=$pager sorter=$sorter filter=$parameters}
	{table_header}
		{sortable_column name="bedrijfsnaam,achternaam" value="Klant"}
		{sortable_column name="omschrijving" value="Omschrijving"}
		{sortable_column name="totaal" value="Bedrag"}
		<th>Periode</th>
		{sortable_column name="laatstgefactureerd" value="Laatst gefactureerd"}
		<th class="noprint">Bewerken</th>
		<th class="noprint">Verwijderen</th>
	{/table_header}

	{foreach from=$periodiekeregels item=regel}
	<tr>
		<td>
			{if $regel.klanttype == 0}
				{$regel.bedrijfsnaam|escape:'html'}
			{else}
				{$regel.aanhef} {$regel.voornaam} {$regel.achternaam}
			{/if}
		</td>

		<td>{$regel.omschrijving}</td>
		<td>{prijs prijs=$regel.totaal}</td>
		<td>{section name=maand loop=12}{if $regel.maanden[$smarty.section.maand.iteration]}{maand nr=$smarty.section.maand.iteration type=letter}{else}-{/if}&nbsp;{/section}</td>
		<td>{if $regel.laatstgefactureerd == NULL}Nooit{else}{$regel.laatstgefactureerd}{/if}</td>

		<td class="noprint">
			<a href="{$base}/periodiek/bewerk/id/{$regel.volgnummer}">Bewerken</a>
		</td>

		<td class="noprint">
			<a href="#" onclick="javascript:verwijder_regel('{$regel.omschrijving|escape:'html'}',{$regel.volgnummer});">
				Verwijderen</a>
		</td>
	</tr>
	{/foreach}

{/paged_table}

<div class="noprint">
	<ul class="menu">
		<li><a href="{$base}/periodiek/nieuw">Nieuwe periodieke factuur maken</a></li>
	</ul>
</div>

<div id="autocomplete" class="autocomplete"></div>

<script type="text/javascript">
	setupKlantAutoCompletion();
	genericKlantnummerOnChange($('klantnaam'), document.getElementsByName('klantnummer')[0].value);
</script>

{include file='page_footer.tpl'}
