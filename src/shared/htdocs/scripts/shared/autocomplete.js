/**
 * autocomplete.js
 * Autocompletion
 *
 * Copyright (C) 2007 Citrus-IT
 *
 * Author: Ivar Clemens
 * Date:   2008-02-15
 */

function genericKlantnummerOnChange(klantnaam, klantnummer) {
	if(klantnummer != 0) {
		klantnaam.update('Naam wordt opgehaald...');

		new Ajax.Request(baseURL + "/klant/klant_xml/id/" + klantnummer, {
			method: 'get',
			onSuccess: function(transport) {
				var rootNode = extractNode(transport.responseXML, ['klant']);
	
				if(rootNode.getAttribute("nummer") == 0 ||
					rootNode.getAttribute("nummer") != document.forms[0].elements['klantnummer'].value) {
					
					$('klantnaam').update('Ongeldige klant...');
					
					return;
				}
	
				klantnaam.update(
					extractNode(transport.responseXML, ['klant', 'naam']));			
			}});
	} else {
		klantnaam.update('Geen klant geselecteerd');
	}
}

function setKlantAutoCompletion(element)
{
	if(typeof klantnummerOnChange == 'undefined') {
		new Ajax.Autocompleter(element, 'autocomplete',
			baseURL + '/klant/autocomplete',
			{paramName: 'partial', 'afterUpdateElement': 
				function(el,sel) {
					genericKlantnummerOnChange($('klantnaam'), document.getElementsByName('klantnummer')[0].value);
				}
			});
	} else {
		new Ajax.Autocompleter(element, 'autocomplete',
			baseURL + '/klant/autocomplete',
			{paramName: 'partial', 'afterUpdateElement': klantnummerOnChange});
	}
}

function setupKlantAutoCompletion()
{
	setKlantAutoCompletion(document.getElementsByName('klantnummer')[0]);
}

function setArtikelcodeAutoCompletion(element)
{
	new Ajax.Autocompleter(element, 'autocomplete', 
		baseURL + '/artikelcode/autocomplete',
		{paramName: 'partial'});
}

function setupArtikelcodeAutoCompletion()
{
	var aantalRegels = countNumberOfItems(0, 'artikelcode[]');	

	for(var i = aantalRegels - 1; i >= 0; i--)
		setArtikelcodeAutoCompletion(getFormElementByName(0, 'artikelcode[]', i));
}
