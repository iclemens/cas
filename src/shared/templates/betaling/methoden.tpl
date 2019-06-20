{include file='page_header.tpl'}

<h2>Factuur betalen</h2>

<table width="300">
	<tr>
		<td>Bedrag (Incl. BTW):</td>
		<td>{prijs prijs=$factuur.totaal}</td>
	</tr>
	<tr>
		<td>Factuurnummer:</td>
		<td>{factuurnummer factuur=$factuur}</td>
	</tr>
</table>

{foreach from=$methoden item=methode}
{$methode}
{/foreach}

{include file='page_footer.tpl'}
