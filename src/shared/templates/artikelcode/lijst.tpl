{include file='page_header.tpl'}

{literal}
<script type="text/javascript">

	function verwijder_artikelcode(omschrijving, volgnummer) {
		if(confirm("Wilt u artikelcode " + volgnummer + " (" + omschrijving + ") echt verwijderen?") == true) {
			document.location = 'http://localhost/artikelcode/verwijder/code/' + volgnummer;
		}
	}

</script>
{/literal}

{html_global_error errors=$errors class=errorbox}

<h2>Artikelcodes</h2>

{paged_table pager=$pager sorter=$sorter}
	{table_header}
		{sortable_column name="artikelcode" value="Artikelcode"}
		{sortable_column name="omschrijving" value="Omschrijving"}
		<th>Bewerken</th>
		<th>Verwijderen</th>
	{/table_header}

	<tbody>
		{foreach from=$artikelcodes item=artikelcode}
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			<td>{$artikelcode.artikelcode|escape:HTML}</td>
			<td>{$artikelcode.omschrijving|escape:HTML}</td>
			<td><a href="{$base}/artikelcode/bewerk/code/{$artikelcode.artikelcode}">Bewerken</a></td>
			<td><a href="javascript:verwijder_artikelcode('{$artikelcode.omschrijving|escape:'html'}',{$artikelcode.artikelcode});">Verwijder</a></td>		
		</tr>
		{/foreach}
	</tbody>
{/paged_table}

<p>
	<a href="{$base}/artikelcode/nieuw">Artikelcode toevoegen</a>
</p>

{include file='page_footer.tpl'}