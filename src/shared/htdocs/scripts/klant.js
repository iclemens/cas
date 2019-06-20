/**
 * klanten.js
 * Customer specific javascript code
 *
 * Copyright (C) 2007 Citrus-IT
 *
 * Author: Ivar Clemens
 * Date:   2007-12-16
 */

var customerReq;

function copy_adres(from, to) {
  document.getElementsByName(to + 'adres')[0].value =
    document.getElementsByName(from + 'adres')[0].value;
  document.getElementsByName(to + 'adres2')[0].value =
    document.getElementsByName(from + 'adres2')[0].value;
  document.getElementsByName(to + 'postcode')[0].value =
    document.getElementsByName(from + 'postcode')[0].value; 
  document.getElementsByName(to + 'plaats')[0].value =
    document.getElementsByName(from + 'plaats')[0].value;
  document.getElementsByName(to + 'land')[0].value =
    document.getElementsByName(from + 'land')[0].value;
}

function set_visibility(el, state) {
  if(state == true)
    el.style.display = 'block';
  else
    el.style.display = 'none';
}

function processCustomerXML(root) {
	if(root.getAttribute("nummer") != klantnummer) 
		return false;

	try {
		for(i = 0; i < root.childNodes.length; i++) {
			node = root.childNodes.item(i);

			if(node.nodeName == 'factuurtekst') {
				document.forms[0].elements['factuurtekst'].value = node.childNodes.item(0).data;
			}
		}			
	} catch(e) {
		document.forms[0].elements['tekst'].value = "Error:\n" + e;
	}
}

function customerCallback() {
	req = customerReq;

	if(!req)
		return;

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
				alert(e);
			}
		} else {
			alert('Er is een fout opgetreden:\n' + req.statusText);
		}
	}
}

function factuurtekstchk_onclick() {
  set_visibility(
    document.getElementById('factuurtekst'), 
    document.getElementById('factuurtekstchk').checked);
    
    if(document.getElementById('factuurtekstchk').checked &&
    	document.forms[0].elements['factuurtekst'].value == '') {

		customerReq = executeXMLHttpRequest(baseURL + "/klant/klant_xml/id/" +
			klantnummer, customerCallback);
	}
}

function factuurtemplatechk_onclick() {
  set_visibility(
    document.getElementById('factuurtemplate'),
    document.getElementById('factuurtemplatechk').checked);
}

