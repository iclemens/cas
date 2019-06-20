{include file='page_header.tpl'}

{literal}
<script type="text/javascript">

	function verwijder_emailtemplate(omschrijving, volgnummer) {
		if(confirm("Wilt u de template " + volgnummer + " (" + omschrijving + ") echt verwijderen?") == true) {
			document.location = 'http://localhost/emailtemplate/verwijder/id/' + volgnummer;
		}
	}

</script>
{/literal}

{html_global_error errors=$errors class=errorbox}

<h2>Email templates</h2>

{paged_table pager=$pager sorter=$sorter}
	{table_header}
		{sortable_column name="omschrijving" value="Omschrijving"}
		{sortable_column name="onderwerp" value="Onderwerp"}
		<th>Bewerken</th>
		<th>Verwijderen</th>
	{/table_header}

	<tbody>
		{foreach from=$emailtemplates item=emailtemplate}
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			<td onclick="document.location='{$base}/emailtemplate/bewerk/id/{$emailtemplate.volgnummer}'">{$emailtemplate.omschrijving|escape:HTML}</td>
			<td onclick="document.location='{$base}/emailtemplate/bewerk/id/{$emailtemplate.volgnummer}'">{$emailtemplate.onderwerp|escape:HTML}</td>
			<td><a href="{$base}/emailtemplate/bewerk/id/{$emailtemplate.volgnummer}">Bewerken</a></td>
			<td><a href="javascript:verwijder_emailtemplate('{$emailtemplate.omschrijving|escape:'html'}',{$emailtemplate.volgnummer});">Verwijder</a></td>		
		</tr>
		{/foreach}
	</tbody>
{/paged_table}

<p>
	<a href="{$base}/emailtemplate/nieuw">Template toevoegen</a>
</p>

{include file='page_footer.tpl'}