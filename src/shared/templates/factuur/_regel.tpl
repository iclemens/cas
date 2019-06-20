<tr>
	<td>{$i}.</td>
	<td>
		<input type="hidden" name="ref[]" value="{$regel.ref}" />
		<input type="text" size="5" id="artikelcode{$i}"
			name="artikelcode[]" 
			onkeypress="return noEnter(event)"
			onchange="artikelcodeOnChange()"
			value="{$regel.artikelcode}" />
	</td>
	<td>
		<input type="text" size="70" name="omschrijving[]"
			value="{$regel.omschrijving|escape:html}" 
			onchange="factuurRegelOnChange()" />
	</td>
	<td>
		<input type="text" size="4" name="aantal[]" 	
			value="{$regel.aantal}" 
			onchange="aantalOnChange(this)" 
			onkeyup="aantalOnKeyUp(this)" />
	</td>
	<td>
		<input type="text" size="4" name="prijs[]" 
			value="{$regel.prijs/100}" 
			onchange="prijsOnChange(this)" 
			onkeyup="prijsOnKeyUp(this)" />
	</td>
	<td>
		<div id="unlock_{$i}" style="display: none"><input type="button" value="Bewerk" onclick="removeFromDB({$i});" /></div>
	</td>
	<td>
		<div id="totaal_{$i}"></div>			
	</td>
</tr>