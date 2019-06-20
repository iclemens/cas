{include file='page_header.tpl'}

<script type="text/javascript" src="{$base}/scripts/shared/xml.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/utility.js"></script>
<script type="text/javascript" src="{$base}/scripts/shared/autocomplete.js"></script>

{literal}
<script type="text/javascript">
	function incasso_invoeren(volgnummer) {
		if(confirm("Wilt u deze incasso invoeren?") == true) {
 			new Ajax.Request(baseURL + "/factuur/invoeren", {
				method: 'post',
				parameters: {id: volgnummer},
				onSuccess: function(transport) {
					window.location.reload(true);					
				}
			});
		}
	}
</script>
{/literal}

<h2>Openstaande incassos</h2>

{paged_table pager=$pager sorter=$sorter width="100%" filter=$parameters}
	{table_header}		
			<th>Kenmerk</th>
			{sortable_column name="datum" value="Datum"}
			{sortable_column name="achternaam" value="Klant"}
			{sortable_column name="factuurplaats" value="Plaats"}
			{sortable_column name="totaal" value="Bedrag"}
			{if $user_type == 'Directie'}
			<th class="noprint">Ingevoerd</th>
			{/if}
	{/table_header}

	<tbody>
		{foreach from=$results key=factuurvolgnummer item=factuur}

		{assign var="colour" value="black"}

		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">		
			<td><a href="{$base}/factuur/bekijk/naam/{factuurnummer factuur=$factuur}.pdf">{factuurnummer factuur=$factuur}</a></td>
			<td>{$factuur.datum|date_format:"%d/%m/%Y"}</td>
			<td>{klantnaam klant=$factuur|escape:html}</td>
			<td>{$factuur.factuurplaats|escape:html}</td>
			<td>{prijs prijs=$factuur.totaal}</td>
			{if $user_type == 'Directie'}
			<td class="noprint"><a href="#" onclick="javascript:incasso_invoeren({$factuur.volgnummer});">Ingevoerd</a></td>
			{/if}
		</tr>
		{/foreach}

		<tr>
			<td colspan="7" style="background-color: #FAFAFA"></td>
		</tr>
	</tbody>

{/paged_table}

{include file='page_footer.tpl'}
