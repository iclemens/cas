{include file="page_header.tpl"}

<h2>Te factureren klanten</h2>

<p>
	De volgende klanten hebben nog te factureren items. Met behulp van de knop "factureren" kunt u deze eenvoudig factureren.
</p>

<table class="content-container">

	<tr>		
		<td colspan="2">			

			{include file="_wachtrij.tpl"}
		</td>
	</tr>
</table>

<p class="noprint">
<a href="{$base}/factuur/nieuw">Nieuwe factuur maken</a><br />
<a href="{$base}/factureren/nieuw">Nieuwe items in de wachtrij plaatsen</a ><br />
</p>

{include file="page_footer.tpl"}