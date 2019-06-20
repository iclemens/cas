/**
 * factuur.js
 * Functies voor het bijwerken van factuur informatie.
 *
 * Copyright (C) 2008 Ivar Clemens
 *
 * Author: Ivar Clemens
 * Date:   2008-12-29
 */

/**
 * Creates a "factuurregel"
 *
 * @param integer number The number of the row being inserted
 * @return object A (table) row containing the correct input fields
 */
function createFactuurRegel(number)
{
	tr = document.createElement("tr");

	td = document.createElement("td");
	td.appendChild(document.createTextNode(number.toString() + "."));
	tr.appendChild(td);

	td = document.createElement("td");
	input = createTextInput("ref[]", 6);
	input.type = "hidden";
	td.appendChild(input);
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
	div.setAttribute("id", "unlock_" + number.toString());
	div.setAttribute("style", "display: none");
	
	input = document.createElement("input");
	input.setAttribute("value", "Bewerk");
	input.setAttribute("type", "button");
	input.onclick = function() { removeFromDB(number); };
	div.appendChild(input);

	td.appendChild(div);
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
 *
 * @param integer linesRequired The amount of lines required
 */
function autoExpandTable(linesRequired) {
	if(linesRequired == null)
		linesRequired = 1;

	var aantalRegels = countNumberOfItems(0, 'artikelcode[]');
	var legeRegels = 0;

	for(var i = aantalRegels - 1; i >= 0; i--) {
		if(getFormElementByName(0, 'artikelcode[]', i).value == '' &&
				getFormElementByName(0, 'omschrijving[]', i).value == '' &&
				getFormElementByName(0, 'aantal[]', i).value == '' &&
				getFormElementByName(0, 'prijs[]', i).value == '')
			legeRegels++;

		if(legeRegels >= linesRequired)
			return;
	}

	for(var i = 0; i < linesRequired - legeRegels; i++)
		document.getElementById('factuur_regels').appendChild(createFactuurRegel(aantalRegels + 1));
}

/**
 * Recalculates all (sub)totals in the entire form (as opposed to
 * updating only those values affected by the modified fields).
 *
 * @param boolean initialUpdate Performs additional steps, such as
 *		locking lines which still have a ref(erence) and updating
 *		the amount and price fields.
 */
function updateAllTotals(initialUpdate) {
	var aantalRegels = countNumberOfItems(0, 'artikelcode[]');
	var subtotaal = 0;

	for(var i = aantalRegels - 1; i >= 0; i--) {

		if(initialUpdate) {
			updateAmount(getFormElementByName(0, 'aantal[]', i), 2);
			updatePrice(getFormElementByName(0, 'prijs[]', i));

			/* Lock lines which reference the factureren table */
			if(getFormElementByName(0, 'ref[]', i).value > 0) {
				setLineLockState(i, true);
			}
		}

		var prijs_el = getFormElementByName(0, 'prijs[]', i);
		var aantal_el = getFormElementByName(0, 'aantal[]', i);
		var totaal_el = document.getElementById('totaal_' + (i + 1));

		var totaal = updateLineTotal(aantal_el, prijs_el, totaal_el);

		subtotaal = subtotaal + totaal;
	}

	var btw_categorie = document.forms[0].elements['btw_categorie'];

	var btw_text = btw_categorie.options[btw_categorie.selectedIndex].text;
	btw_text = btw_text.substr(btw_text.indexOf('(') + 1);
	btw_text = btw_text.substr(0, btw_text.indexOf('%'));

	var btw_percentage = btw_text * 1;

	var korting = document.getElementsByName('korting')[0].value;
	var kortingtype = document.getElementsByName('kortingtype')[0].value;

	if(kortingtype == 'relative') {
		subtotaal = subtotaal - moneyRound(subtotaal * korting / 100);
	} else if(kortingtype == 'absolute') {
		subtotaal = subtotaal - moneyEuroToCent(korting);
	}
	
	updateTotal(
		document.getElementById('f_subtotaal'),
		document.getElementById('f_btw'),
		document.getElementById('f_totaal'),
		subtotaal, btw_percentage);
}

/**
 * Sets the locked status of a line
 *
 * @param integer i Index of the line to be (un)locked.
 * @param boolean state True for locked, false for unlocked.
 */
function setLineLockState(i, state) {
	if(state == false) {
		getFormElementByName(0, 'ref[]', i).value = '';
	}

	getFormElementByName(0, 'artikelcode[]', i).readOnly = state;
	getFormElementByName(0, 'artikelcode[]', i).readOnly = state;
	getFormElementByName(0, 'omschrijving[]', i).readOnly = state;
	getFormElementByName(0, 'aantal[]', i).readOnly = state;
	getFormElementByName(0, 'prijs[]', i).readOnly = state;

	if(state == true) {
		document.getElementById('unlock_' + (i + 1)).style.display = 'block';
	} else {
		document.getElementById('unlock_' + (i + 1)).style.display = 'none';
	}
}

/**
 * Processes the XML sent in response to a remove request.
 *
 * @param object root The XML response received
 */
function processRemoveXML(root) {
	var reference = root.getAttribute('ref');

	var numberOfLines = countNumberOfItems(0, 'artikelcode[]');

	for(var i = numberOfLines - 1; i >= 0; i--) {
		if(getFormElementByName(0, 'ref[]', i).value == reference) {
			setLineLockState(i, false);
		}
	}
}

/**
 * Removes the line from the database
 *
 * @param integer i Index of the line to be removed
 */
function removeFromDB(i) {
	// Button cannot be clicked if it is not visible.
	if(document.getElementById('unlock_' + (i)).style.display == 'none')
		return;
		
	// Remove unlock button such that it cannot be clicked twice
	document.getElementById('unlock_' + (i)).style.display = 'none';

	new Ajax.Request(baseURL + "/factureren/verwijder_xml/id/" +
		document.forms[0].elements['ref[]'][i - 1].value, {
			method: 'get',
			onSuccess: function(transport) {
				xmldoc = transport.responseXML;
				
				try {
					for(i = 0; i < xmldoc.childNodes.length; i++) {
						root = xmldoc.childNodes.item(i);

						if(root.nodeName == 'removed') {
							processRemoveXML(root);
							break;
						}
					}
				} catch(e) {
				}
			}
		});
}

/**
 * Clears all lines locked by the 'addFacturerenRegel' function
 */
function removeLockedLines() {
	var aantalRegels = countNumberOfItems(0, 'artikelcode[]');

	for(var i = aantalRegels - 1; i >= 0;i --) {
		if(getFormElementByName(0, 'artikelcode[]', i).readOnly) {
			setLineLockState(i, false);

			getFormElementByName(0, 'artikelcode[]', i).value = '';
			getFormElementByName(0, 'omschrijving[]', i).value = '';
			getFormElementByName(0, 'aantal[]', i).value = '';
			getFormElementByName(0, 'prijs[]', i).value = '';
		}
	}
}

/**
 * Checks whether line /i/ is empty (i.e. the values of
 * the artikelcode, omschrijving, aantal and prijs fields
 * are all blank).
 *
 * @param integer i Index of the line to check
 * @return boolean True if empty (all-blank), false otherwise
 */
function isEmptyLine(i)
{
	if(getFormElementByName(0, 'artikelcode[]', i).value == '' &&
		getFormElementByName(0, 'omschrijving[]', i).value == '' &&
		getFormElementByName(0, 'aantal[]', i).value == '' &&
		getFormElementByName(0, 'prijs[]', i).value == '')
		return true;

	return false;
}

/**
 * Copies the information contained in node to the invoice line
 * designated by vrijeRegel. The information is locked until an
 * unlock order is issued.
 *
 * @param integer vrijeRegel Index of the line to fill
 * @param object node Data to will the line with
 */
function addFacturerenRegelXML(vrijeRegel, node) {
	/* Ref(erence) is used to identify the original factureren database entry */
	getFormElementByName(0, 'ref[]', vrijeRegel).value = node.getAttribute('ref');
	
	document.getElementById('unlock_' + (vrijeRegel + 1)).style.display = 'block';

	for(var i = 0 ; i < node.childNodes.length; i++) {
		var node2 = node.childNodes.item(i);

		if(node2.nodeName == 'artikelcode') {
			getFormElementByName(0, 'artikelcode[]', vrijeRegel).readOnly = true;
			getFormElementByName(0, 'artikelcode[]', vrijeRegel).value = 
				node2.childNodes.item(0).data;
		}

		if(node2.nodeName == 'omschrijving') {
			getFormElementByName(0, 'omschrijving[]', vrijeRegel).readOnly = true;
			getFormElementByName(0, 'omschrijving[]', vrijeRegel).value = 
				node2.childNodes.item(0).data;
		}

		if(node2.nodeName == 'aantal') {
			getFormElementByName(0, 'aantal[]', vrijeRegel).readOnly = true;

			try {
				getFormElementByName(0, 'aantal[]', vrijeRegel).value = 
					node2.childNodes.item(0).data;
			} catch(e) {
				getFormElementByName(0, 'aantal[]', vrijeRegel).value = '';
			}
		}

		if(node2.nodeName == 'prijs') {
			getFormElementByName(0, 'prijs[]', vrijeRegel).readOnly = true;

			try {
				getFormElementByName(0, 'prijs[]', vrijeRegel).value = 
					node2.childNodes.item(0).data / 100;
			} catch(e) {
				getFormElementByName(0, 'prijs[]', vrijeRegel).value = '';
			}
		}
	}
}

/**
 * Adds each line contained within the factureren XML to
 * the invoice.
 *
 * @param object node The factureren XML object
 */
function addFacturerenRegelsXML(node) {

	var numberOfLines = countNumberOfItems(0, 'artikelcode[]');
	var currentLine = 0;

	for(j = 0; j < node.childNodes.length; j++) {
		node2 = node.childNodes.item(j);

		if(node2.nodeName == 'regel') {

			while(!isEmptyLine(currentLine)) {
				currentLine++;

				if(currentLine >= numberOfLines) {
					document.getElementById('factuur_regels').appendChild(createFactuurRegel(currentLine + 1));
					numberOfLines++;
				}
			}

			addFacturerenRegelXML(currentLine, node2);
		}
	}

	return true;
}

/**
 * Processes the 'factureren' XML and adds its data as lines to the table
 *
 * @param object root The XML containing the data to insert
 */
function processFacturerenXML(root) {
	/* Verify customer code in case it has changed */
	if(root.getAttribute("klantnummer") != document.forms[0].elements['klantnummer'].value) 
		return false;
	
	/* Clears out locked lines, they belong to a different customer */
	removeLockedLines()

	var notEnoughSpace = false;
	var linesRequired = 0;

	for(i = 0; i < root.childNodes.length; i++) {
		node = root.childNodes.item(i);

		if(node.nodeName == 'regels')
			addFacturerenRegelsXML(node);
	}

	autoExpandTable(1);

	updateAllTotals(true);
}

/**
 * Requests invoice lines from the database and inserts all
 * that it returns into the invoice.
 */
function updateFactureren()
{
	var klantnummer = document.getElementsByName('klantnummer')[0].value;

	// Fetch list of items to put in the invoice
	new Ajax.Request(baseURL + "/factureren/lijst_xml/id/" + klantnummer, {
			method: 'get',
			onSuccess: function(transport) {
				try {
					xmldoc = transport.responseXML;
					
					for(i = 0; i < xmldoc.childNodes.length; i++) {
						root = xmldoc.childNodes.item(i);
						if(root.nodeName == 'factureren') {
							processFacturerenXML(root);
							break;
						}
					}
				} catch(e) {
				}
			}
		}); 
}

/**
 * Update customer information by querying the database.
 */
function updateCustomerInfo()
{
	var klantnummer = document.getElementsByName('klantnummer')[0].value;

	// Update customer information
	new Ajax.Request(baseURL + "/klant/klant_xml/id/" + klantnummer, {
			method: 'get',
			onSuccess: function(transport) {
				
				var rootNode = extractNode(transport.responseXML, ['klant']);
	
				if(rootNode.getAttribute("nummer") == 0 ||
					rootNode.getAttribute("nummer") != document.forms[0].elements['klantnummer'].value) {
					
					$('klantnaam').update('Ongeldige klant...');
					$('tekst').update('');
					
					return;
				}

				$('klantnaam').update(extractNodeValue(transport.responseXML, ['klant', 'naam']));				
				$('tekst').update(extractNodeValue(transport.responseXML, ['klant', 'emailtemplate', 'inhoud']));

				// Werk BTW categorie bij
				var btwCategorie = $('btw_categorie');
				var numCategories = btwCategorie.options.length;
				var j;

				for(j = 0; j < numCategories; j++) {

					if(btwCategorie.options[j].value == extractNodeValue(transport.responseXML, ['klant', 'btwcategorie'])) {
						btwCategorie.selectedIndex = j;
					}
				}

				// Werk machtiging veld bij
				var machtigingNode = extractNode(transport.responseXML, ['klant', 'machtiging']);
				
				var month = (machtigingNode.getAttribute('maand') == 'true');
				var year = (machtigingNode.getAttribute('jaar') == 'true');

				if(month || year) {
					if(month && year) {
						typeText = 'maandelijks en jaarlijks';
					} else {
						if(month) {
							typeText = 'maandelijks';
						} else {
							typeText = 'jaarlijks';
						}
					}

					$('incasso_type').update(typeText);
					$('incasso_div').style.display = 'block';
					$('incasso').checked = true;
				} else {
					$('incasso').checked = false;
					$('incasso_div').style.display = 'none';
				}

				updateAllTotals();
			}});
}

/*****************************************************
 * Event handlers
 *****************************************************/

/**
 * Updates all information related to the currently selected customer.
 *
 * @param element The input box which contains the customer id.
 * @param selected_element The element which contains the name of the selected customer. 
 */
function klantnummerOnChange(element, selected_element) {
	if(selected_element) {
		$('klantnaam').update(selected_element.childNodes.item(1).innerHTML);
	} else {
		$('klantnaam').update('Naam wordt opgehaald...');
		$('incasso_div').style.display = 'none';
	}

	updateFactureren();
	updateCustomerInfo();
}

function factuurRegelOnChange(this_el) {
	autoExpandTable();
}

function prijsOnKeyUp(prijs_el) {
	factuurRegelOnChange();
	updateAllTotals(false);
}

function aantalOnKeyUp(aantal_el) {
	factuurRegelOnChange();
	updateAllTotals(false);
}

function prijsOnChange(prijs_el) {
	factuurRegelOnChange();
	updatePrice(prijs_el);
	updateAllTotals(false);
}

function aantalOnChange(aantal_el) {
	factuurRegelOnChange();
	updateAmount(aantal_el, 2);
	updateAllTotals(false);
}

function btwOnChange(btwcategory_el) {
	updateAllTotals(false);
}
