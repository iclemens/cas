<table width="700">
	<tr>
		<td valign="top" width="100">
			Klant:<br />

			<input type="text" name="klantnummer" value="{$periodiekeregels[0].klantnummer}" onchange="klantnummerOnChange(0,0)" size="8" />

			<br />
			{html_error_for_field field=klantnummer errors=$errors}
		</td><td valign="top" width="250">
			<br />
			<span id="klantnaam"></span>
		</td><td valign="top">
			btw:<br />

			<select id="btw_categorie" name="btw_categorie" onchange="btwOnChange(this)">
			{foreach from=$btw_tarieven key=cur_btw_categorie item=btw_data}
				<option value="{$cur_btw_categorie}"
					{if $btw_categorie == $cur_btw_categorie}selected="selected"{/if}
					>{$btw_data.description} ({$btw_data.rate}%)</option>
			{/foreach}
			</select>


<!--			<select id="btw_categorie" name="btw_percentage" onchange="btwOnChange(this)">
			{foreach from=$btw_tarieven key=cur_btw_categorie item=btw_data}
				<option value="{$btw_data.rate}"
					{if $periodiekeregels[0].btw_percentage == $btw_data.rate}selected{/if}
					>{$btw_data.description} ({$btw_data.rate}%)</option>
			{/foreach}
			</select>	-->
		</td>
	</tr>
	<tr>
	  <td colspan="3">
<br />
Factureren in:
<table width="700">
<tr>
<td width="175">
<input type="checkbox" name="maand[]" value="1" onchange="maandOnChange(this)" {if $maanden[1]}checked="checked"{/if}/> {maand nr=1}<br />
<input type="checkbox" name="maand[]" value="2" onchange="maandOnChange(this)" {if $maanden[2]}checked="checked"{/if} /> {maand nr=2}<br />
<input type="checkbox" name="maand[]" value="3" onchange="maandOnChange(this)" {if $maanden[3]}checked="checked"{/if} /> {maand nr=3}<br />
</td><td width="175">
<input type="checkbox" name="maand[]" value="4" onchange="maandOnChange(this)" {if $maanden[4]}checked="checked"{/if} /> {maand nr=4}<br />
<input type="checkbox" name="maand[]" value="5" onchange="maandOnChange(this)" {if $maanden[5]}checked="checked"{/if} /> {maand nr=5}<br />
<input type="checkbox" name="maand[]" value="6" onchange="maandOnChange(this)" {if $maanden[6]}checked="checked"{/if} /> {maand nr=6}<br />
</td><td width="175">
<input type="checkbox" name="maand[]" value="7" onchange="maandOnChange(this)" {if $maanden[7]}checked="checked"{/if} /> {maand nr=7}<br />
<input type="checkbox" name="maand[]" value="8" onchange="maandOnChange(this)" {if $maanden[8]}checked="checked"{/if} /> {maand nr=8}<br />
<input type="checkbox" name="maand[]" value="9" onchange="maandOnChange(this)" {if $maanden[9]}checked="checked"{/if} /> {maand nr=9}<br />
</td><td width="175">
<input type="checkbox" name="maand[]" value="10" onchange="maandOnChange(this)" {if $maanden[10]}checked="checked"{/if} /> {maand nr=10}<br />
<input type="checkbox" name="maand[]" value="11" onchange="maandOnChange(this)" {if $maanden[11]}checked="checked"{/if} /> {maand nr=11}<br />
<input type="checkbox" name="maand[]" value="12" onchange="maandOnChange(this)" {if $maanden[12]}checked="checked"{/if} /> {maand nr=12}<br />
</td></tr>
</table>
{html_error_for_field field=perioden errors=$errors}
<br />
<input type="button" value="Niets selecteren" onclick="javascript:uncheckAll()" />
<input type="button" value="Alles selecteren" onclick="javascript:checkAll()" />
	</td>
  </tr>
  <tr>
	<td colspan="2">
	  <br />
	  Eerste maand:<br />
	  <input type="text" name="startdatum" value="{$periodiek.startdatum}" readonly="readonly" style="background-color: #EEEEEE; color: black;" />
      <input type="button" value="Kies datum" 
		onclick="displayCalendar(document.periodiek.startdatum, 'dd/mm/yyyy', this)" />
	</td>
	<td colspan>
	  <br />
	  Laatste maand:<br />
	  <input type="text" name="einddatum" value="{$periodiek.startdatum}" readonly="readonly" style="background-color: #EEEEEE; color: black;" />
      <input type="button" value="Kies datum" 
		onclick="displayCalendar(document.periodiek.einddatum, 'mm/yyyy', this)" />
	</td>
  </tr>
</table>

<br />


<br />
<hr />
<br />

<table>
	<thead>
	<tr>
		<th>Nr.</th>
		<th>Art.</th>
		<th>Omschrijving</th>
		<th>Aantal</th>
		<th>Prijs</th>
	</tr>
	</thead>

	<tbody id="periodiek_regels">
		{assign var=i value=1}
		{foreach from=$periodiekeregels item=regel}
			{include file='periodiek/_regel.tpl'}
			{assign var=i value=$i+1}
		{/foreach}

		{section name=loop start=$i loop=2}
			{include file='periodiek/_regel.tpl'}
			{assign var=i value=$i+1}
		{/section}

	</tbody>
</table>

<br />
