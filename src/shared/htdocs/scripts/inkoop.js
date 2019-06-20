/**
 * inkooop.js
 * Various javascript routines associated with the inkoop controller
 *
 * Copyright (C) 2007 Citrus-IT
 *
 * Author: Ivar Clemens
 * Date: 2007-08-09
 * 
 */

/**
 * Creates a text input for use in forms
 *
 * @name - The name of the field within the form
 * @size - The size of the field in characters
 *
 * @return - An input element
 */
function createTextInput(name, size)
{
	input = document.createElement("input");
	input.setAttribute("name", name);
	input.setAttribute("size", size);
	input.setAttribute("type", "text");

	return input;
}

/**
 * Creates an "inkoopregel" for use in the table with "inkoopregels"
 *
 * @return - A (table) row containing the correct input fields
 */
function createInkoopRegel(number)
{
	tr = document.createElement("tr");

	td = document.createElement("td");
	td.appendChild(document.createTextNode(number.toString() + "."));
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("code[]", 5);
	input.setAttribute("onChange", "inkoopRegelOnChange()");
	td.appendChild(input);
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("omschrijving[]", 70);
	input.setAttribute("onChange", "inkoopRegelOnChange()");
	td.appendChild(input);
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("aantal[]", 4);
	input.setAttribute("onChange", "aantalOnChange()");
	input.setAttribute("onKeyUp", "aantalOnKeyUp()");
	td.appendChild(input);
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("prijs[]", 4);
	input.setAttribute("onChange", "prijsOnChange(this)");
	input.setAttribute("onKeyUp", "prijsOnKeyUp(this)");
	td.appendChild(input);
	tr.appendChild(td);

	td = document.createElement("td");
	/** FIXME: The following createVATSelection function is defined elsewhere
		because smarty functions were needed. Maybe we should use some
		kind of global array instead? **/
	td.appendChild(createVATSelection("btw[]"));
	tr.appendChild(td);

	/* This breaks with style, should investigate alternatives */			
	td = document.createElement("td");
	div = document.createElement("div");
	div.setAttribute("name", "totaal_" + number.toString());
	td.appendChild(div);
	tr.appendChild(td);

	return tr;
}

/**
 * Automatically expands the table when all rows are filled
 */
function autoExpandTable() {
	aantalRegels = document.forms[0].elements['code[]'].length;

	for(i = aantalRegels - 1; i >= 0; i--) {
		if(document.forms[0].elements['code[]'][i].value == '' &&
			document.forms[0].elements['omschrijving[]'][i].value == '' &&
			document.forms[0].elements['aantal[]'][i].value == '' &&
			document.forms[0].elements['prijs[]'][i].value == '' &&
			document.forms[0].elements['btw[]'][i].value == '')
			return;
	}

	aantalRegels = document.forms[0].elements['code[]'].length;

	if(isNaN(aantalRegels))
		aantalRegels = 1;

	document.getElementById('inkoop_regels').appendChild(createInkoopRegel(aantalRegels + 1));
}


/**
 * Determines the percentage of VAT paid
 */
function determineVATCategory(price_el, vat_el) {
	// FIXME: vat_el does not exist in the beginning!
	if(vat_el.value.substr(vat_el.value.length - 1, 1) == '%') {
		vat_cat = commaToPeriod(vat_el.value.substr(0, vat_el.value.length - 1));
		} else {
		vat_val = moneyEuroToCent(vat_el.value);
	
		vat_cat = moneyEuroToCent(vat_el.value) / 
			moneyEuroToCent(price_el.value) * 100;
	}

	return vat_cat;
}


/**
 * Changes all VAT specifications to percentages
 */
function changeVATToPercentage() {
	aantalRegels = document.forms[0].elements['code[]'].length;

	for(i = aantalRegels - 1; i >= 0; i--) {
		btw_cat = determineVATCategory(
			document.forms[0].elements['prijs[]'][i],
			document.forms[0].elements['btw[]'][i]);

		if(document.forms[0].elements['btw[]'][i].value != '')
			document.forms[0].elements['btw[]'][i].value = periodToComma(btw_cat.toString()) + '%';
	}
}


/**
 * Updates all totals
 *
 * @initialUpdate - Update _everything_, or just the totals?
 */
function updateAllTotals(initialUpdate) {
	aantalRegels = document.forms[0].elements['code[]'].length;

	/* FIXME: This only works because the auto-expand function (autoExpandTable())
			*makes* it an array! If for some reason auto-expand fails this all returns NaN. */

	subtotaal = Array();

	for(i = aantalRegels - 1; i >= 0; i--) {

		if(initialUpdate) {
			updateAmount(document.forms[0].elements['aantal[]'][i], 1);
			updatePrice(document.forms[0].elements['prijs[]'][i]);
		}

		totaal = updateLineTotal(
			document.forms[0].elements['aantal[]'][i],
			document.forms[0].elements['prijs[]'][i],
			document.getElementsByName('totaal_' + (i + 1))[0]);

		btw_cat = determineVATCategory(
			document.forms[0].elements['prijs[]'][i],
			document.forms[0].elements['btw[]'][i]);

		if(!isNaN(btw_cat)) {
			btw_cat = Math.floor(btw_cat * 10) / 10;
			if(isNaN(subtotaal[btw_cat.toString()]))
				subtotaal[btw_cat.toString()] = totaal;
			else
				subtotaal[btw_cat.toString()] = subtotaal[btw_cat.toString()] + totaal;
		}
	}

	tekst = "";
	totaal = 0;

	/**
   * Constructs the tekst: BTW x% over E y,yy = zz
   * for each VAT category.
	 * TODO: Should sort by VAT category (lowest first)
   */
	for(btw_cat in subtotaal) {
		tekst = tekst + "BTW ";

		if(btw_cat == 0)
			tekst = tekst + "vrij";
		else
			tekst = tekst + btw_cat + "%";

		tekst = tekst + " over " + moneyFormatDutchWithEuro(subtotaal[btw_cat]);
		tekst = tekst + " = " + moneyFormatDutchWithEuro(moneyRound(subtotaal[btw_cat] * btw_cat / 100));

		totaal = totaal + subtotaal[btw_cat] + moneyRound(subtotaal[btw_cat] * btw_cat / 100);
		tekst = tekst + "<br />";
	}
		
	tekst = tekst + "<br />Totaal = " + moneyFormatDutchWithEuro(totaal);

	document.getElementById('totalen').innerHTML = tekst;
}

/**
 * On change handler for all fields in the table
 */
function inkoopRegelOnChange() {
	autoExpandTable();
}

/**
 * On change handler for the price field
 */
function prijsOnChange(this_el) {
	updatePrice(this_el);
	updateAllTotals(false);
	inkoopRegelOnChange();
}

/**
 * On change handler for the amount field
 */
function aantalOnChange(this_el) {
	updateAmount(this_el, 1);
	updateAllTotals(false);
	inkoopRegelOnChange();
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

function btwOnKeyUp(this_el) {
	updateAllTotals();
}

function btwOnChange(this_el) {
	changeVATToPercentage();
	updateAllTotals(false);
	inkoopRegelOnChange();
}
