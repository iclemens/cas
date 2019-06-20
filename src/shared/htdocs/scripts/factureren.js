/**
 * factureren.js
 * Dynamic totals calculation and auto-expand functionality for
 * the to-be-invoiced module.
 *
 * Copyright (C) 2007 Citrus-IT
 *
 * Author: Ivar Clemens
 * Date:   2007-12-15
 */

/**
 * Creates a "factuurregel" for use in the table with "factuurregels"
 *
 * @number - The number of the row being inserted
 * @return - A (table) row containing the correct input fields
 */
function createFactuurRegel(number)
{
	tr = document.createElement("tr");

	td = document.createElement("td");
	td.appendChild(document.createTextNode(number.toString() + "."));
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("artikelcode[]", 5);
	setArtikelcodeAutoCompletion(input);
	td.appendChild(input);
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("omschrijving[]", 70);
	input.onchange = function() { factuurRegelOnChange(); };
	td.appendChild(input);
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("aantal[]", 4);
	input.onchange = function() { aantalOnChange(this); };
	input.onkeyup = function() { aantalOnKeyUp(this); };
	td.appendChild(input);
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("prijs[]", 4);
	input.onchange = function() { prijsOnChange(this); };
	input.onkeyup = function() { prijsOnKeyUp(this); };
	td.appendChild(input);
	tr.appendChild(td);

	/* This breaks with style, should investigate alternatives */			
	td = document.createElement("td");
	div = document.createElement("div");
	div.setAttribute("id", "totaal_" + number.toString());
	td.appendChild(div);
	tr.appendChild(td);

	return tr;
}

/**
 * Automatically expands the table when all rows are filled
 */
function autoExpandTable() {
	var aantalRegels = countNumberOfItems(0, 'artikelcode[]');

	for(var i = 0; i < aantalRegels; i++) {
		if(getFormElementByName(0, 'artikelcode[]', i).value == '' &&
				getFormElementByName(0, 'omschrijving[]', i).value == '' &&
				getFormElementByName(0, 'aantal[]', i).value == '' &&
				getFormElementByName(0, 'prijs[]', i).value == '')
			return;
	}

	document.getElementById('factuur_regels').appendChild(createFactuurRegel(aantalRegels + 1));
}

/**
 * Updates all totals
 *
 * @initialUpdate - Update _everything_, or just the totals?
 */
function updateAllTotals(initialUpdate) {
	var aantalRegels = countNumberOfItems(0, 'artikelcode[]');

	/* FIXME: This only works because the auto-expand function (autoExpandTable())
			*makes* it an array! If for some reason auto-expand fails this all returns NaN. */

	for(var i = aantalRegels - 1; i >= 0; i--) {

		if(initialUpdate) {
			updateAmount(getFormElementByName(0, 'aantal[]', i), 2);
			updatePrice(getFormElementByName(0, 'prijs[]', i));
		}
				
		totaal = updateLineTotal(
			getFormElementByName(0, 'aantal[]', i),
			getFormElementByName(0, 'prijs[]', i),
			
			//getFormElementByName(0, 'omschrijving[]', i));
			document.getElementById('totaal_' + (i + 1)));

	}
}

/**
 * On change handler for _all_ fields in the table
 */
function factuurRegelOnChange(this_el) {
	
	autoExpandTable();
}

/**
 * On change handler for the price field
 */
function prijsOnChange(this_el) {
	updatePrice(this_el);
	updateAllTotals(false);
	factuurRegelOnChange();
}

/**
 * On change handler for the amount field
 */
function aantalOnChange(this_el) {
	updateAmount(this_el, 2);
	updateAllTotals(false);
	factuurRegelOnChange();
}

/**
 * Keypress handler for the price field
 * Dynamically updates totals
 */
function prijsOnKeyUp(this_el) {
	updateAllTotals(false);
}

/**
 * Keypress handler for the amount field
 * Dynamically updates totals
 */
function aantalOnKeyUp(this_el) {
	updateAllTotals(false);
}
