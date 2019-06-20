{include file='page_header.tpl'}

<h2>Openstaande facturen</h2>

{paged_table pager=$pager sorter=$sorter width="100%"}
	{table_header}		
			<th>Kenmerk</th>
			{sortable_column name="datum" value="Datum"}
			{sortable_column name="bedrijfsnaam,achternaam" value="Klant"}
			{sortable_column name="totaal" value="Totaal (incl.&nbsp;BTW)"}

			{sortable_column name="leeftijd" value="Openstaand"}

			{if $user_type == 'Administratie' || $user_type == 'Directie'}
			<th class="noprint">Afboeken</th>
			{/if}
			{if $user_type == 'Klant'}
			<th class="noprint">Betalen</th>
			{/if}
			{if $user_type == 'Directie'}
			<th class="noprint">Herzenden</th>
			{/if}			
	{/table_header}

	<tbody>
		{foreach from=$results key=factuurvolgnummer item=factuur}

		{if $factuur.incasso}
			{assign var="colour" value="green"}
		{elseif $factuur.leeftijd > $payment_due_delta + 21}
			{assign var="colour" value="red"}
		{elseif $factuur.leeftijd > $payment_due_delta + 14}
			{assign var="colour" value="purple"}
		{elseif $factuur.leeftijd > $payment_due_delta + 7}
			{assign var="colour" value="orange"}
		{else}
			{assign var="colour" value="black"}
		{/if}

		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			<td><a href="{$base}/factuur/bekijk/naam/{factuurnummer factuur=$factuur}.pdf">{factuurnummer factuur=$factuur}</a></td>
			<td>{$factuur.datum|date_format:"%d/%m/%Y"}</td>
			<td>{klantnaam klant=$factuur}</td>
			<td>{prijs prijs=$factuur.totaal}</td>
			<td style="color: {$colour}"}>
				{$factuur.leeftijd} dag{if $factuur.leeftijd != 1}en{/if}
			</td>
			{if $user_type == 'Administratie' || $user_type == 'Directie'}
			<td class="noprint"><a href="{$base}/betaling/afboeken/id/{$factuur.volgnummer}">Afboeken</a></td>
			{/if}
			{if $user_type == 'Klant'}
			<td class="noprint"><a href="{$base}/betaling/betaal/id/{$factuur.volgnummer}">Betalen</a></td>
			{/if}
			{if $user_type == 'Directie'}
			<td class="noprint"><a href="{$base}/factuur/herzenden/id/{$factuur.volgnummer}">Herzenden</a></td>			
			{/if}			
		</tr>
		{/foreach}

		<tr>
			<td colspan="7" style="background-color: #FAFAFA"></td>
		</tr>

		<tr>
			<th colspan="3"><br /><b>Totaal openstaand factuurbedrag:</b></th>
			<th>Incl. BTW:<br />{prijs prijs=$totaal}</th>
			<th colspan="2"></th>
			{if $user_type == 'Directie'}
			<th></th>
			{/if}
		</tr>
	</tbody>

{/paged_table}

{include file='page_footer.tpl'}
