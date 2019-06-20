/**
 * periodiek.js
 * Dynamic totals calculation and auto-expand functionality for
 * the periodic invoices module.
 *
 * Copyright (C) 2007 Citrus-IT
 *
 * Author: Ivar Clemens
 * Date:   2008-02-10
 */

/**
 * 
 */
var allowTableExpansion = false;
var customerReq;

/**
 * Creates a "periodiekeregel" for use in the table with "periodiekeregels"
 *
 * @number - The number of the row being inserted
 * @return - A (table) row containing the correct input fields
 */
function createPeriodiekeRegel(number)
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
	input.onchange = function() { periodiekeRegelOnChange(); };
	input.onkeyup = function() { omschrijvingOnKeyUp(this); };
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
	if(!allowTableExpansion)
		return;

	var aantalRegels = countNumberOfItems(0, 'artikelcode[]');

	for(var i = 0; i < aantalRegels; i++) {
		if(getFormElementByName(0, 'artikelcode[]', i).value == '' &&
				getFormElementByName(0, 'omschrijving[]', i).value == '' &&
				getFormElementByName(0, 'aantal[]', i).value == '' &&
				getFormElementByName(0, 'prijs[]', i).value == '')
			return;
	}

	document.getElementById('periodiek_regels').appendChild(createPeriodiekeRegel(aantalRegels + 1));
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

function uncheckAll()
{
	for(i = 0; i < 12; i++) {
		element = getFormElementByName(0, 'maand[]', i);
		element.checked = false;
		maandOnChange(element);
	}
}

function checkAll()
{
	for(i = 0; i < 12; i++) {
		element = getFormElementByName(0, 'maand[]', i);
		element.checked = true;
		maandOnChange(element);
	}
}

/**
 * On change handler for month checkboxes
 */
function maandOnChange(this_el) {
	billThisMonth = document.getElementById('billThisMonth');

	if(!billThisMonth)
		return;

	var date = new Date();
	var month = date.getMonth();

	if(((this_el.value) - 1) == month) {
		billThisMonth.style.display = this_el.checked?'block':'none';
	}
}

function processCustomerXML(root) {
	if(root.getAttribute("nummer") != document.forms[0].elements['klantnummer'].value) 
		return false;

	try {
		for(i = 0; i < root.childNodes.length; i++) {
			node = root.childNodes.item(i);

			if(node.nodeName == 'naam') {
				document.getElementById('klantnaam').innerHTML = node.childNodes.item(0).data;
			}

			if(node.nodeName == 'btwcategorie') {
				var btwCategorie = document.getElementById('btw_categorie');
				var numCategories = btwCategorie.options.length;
				var j;

				for(j = 0; j < numCategories; j++) {

					if(btwCategorie.options[j].value == node.childNodes.item(0).data) {
						btwCategorie.selectedIndex = j;
					}
				}
			}
		}
	} catch(e) {
		document.forms[0].elements['tekst'].value = "Error:\n" + e;
	}
}

function customerCallback() {
	req = customerReq;

	if(req.readyState == 4) {
		if(req.status == 200) {
			xmldoc = req.responseXML;	

			try {
				for(i = 0; i < xmldoc.childNodes.length; i++) {
					root = xmldoc.childNodes.item(i);

					if(root.nodeName == 'klant') {

						processCustomerXML(root);
						break;
					}
				}
			} catch(e) {
				alert('Kan JavaScript niet uitvoeren: ' + e);
			}
		} else {
			alert('Er is een fout opgetreden:\n' + req.statusText);
		}
	}
}

function klantnummerOnChange(element, selected_element) {
	genericKlantnummerOnChange($('klantnaam'), document.getElementsByName('klantnummer')[0].value);

	customerReq = executeXMLHttpRequest(baseURL + "/klant/klant_xml/id/" +
		document.getElementsByName('klantnummer')[0].value, customerCallback);
}

/**
 * On change handler for _all_ fields in the table
 */
function periodiekeRegelOnChange(this_el) {	
	autoExpandTable();
}

/**
 * On change handler for the price field
 */
function prijsOnChange(this_el) {
	updatePrice(this_el);
	updateAllTotals(false);
	periodiekeRegelOnChange();
}

/**
 * On change handler for the amount field
 */
function aantalOnChange(this_el) {
	updateAmount(this_el, 2);
	updateAllTotals(false);
	periodiekeRegelOnChange();
}

/**
 * Executes when the description changes
 */
function omschrijvingOnKeyUp(this_el) {
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
