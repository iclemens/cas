/**
 * utility.js
 * Miscellaneous functions, which obviously didn't fit anywhere else
 *
 * Copyright (C) 2008 Ivar Clemens
 *
 * Author: Ivar Clemens
 * Date: 2008-12-29
 */

/**
 * Creates a text input for use in forms
 *
 * @param string name The name of the field within the form
 * @param integer size The size of the field in characters
 *
 * @return object An input element
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
 * Replaces all periods with commas
 *
 * @param string str Source string
 * @return string String containing commas instead of periods
 */
function periodToComma(str) {
	return str.replace('.', ',');	
}

/**
 * Replaces all commas with periods
 *
 * @param string str Source string
 * @return string Floating point representation of str, or 0 if none is found
 */
function commaToPeriod(str) {
	tmp = parseFloat(str.replace(',', '.'));

	if(isNaN(tmp))
		return 0;

	return tmp;
}

/**
 * Reformats the price in a textfield
 *
 * @param object price_el The input element containing the price to reformat
 */
function updatePrice(price_el) {
	price_val = moneyEuroToCent(price_el.value);

	if(price_val == 0)
		price_el.value = '';
	else
		price_el.value = moneyFormatDutch(price_val, ',', '');
}

/**
 * Reformats the value in a textfield
 *
 * @param object amount_el The input element containing the value to reformat
 * @param integer decimals The number of decimals to retain
 */
function updateAmount(amount_el, decimals) {
	mult = Math.pow(10, decimals);
	amount_val = Math.floor(commaToPeriod(amount_el.value) * mult + 0.5) / mult;

	if(amount_val == 0) {
		if(amount_el.value == '0')
			amount_el.value = '0';
		else
			amount_el.value = '';
	} else {
		amount_el.value = periodToComma(amount_val.toString());
	}
}

/**
 * Updates and returns the subtotal of a line
 *
 * @param object amount_el The element containing the number of objects
 * @param object price_el The element containing the cost of one object
 * @param object total_el The element where the total should be
 *
 * @return integer The total, for use in calculation of subtotal
 */
function updateLineTotal(amount_el, price_el, total_el)
{
	var amount_val = NaN;
	var total_val = NaN;
	var price_val = NaN;

	if(amount_el.value != '') {
		amount_val = parseFloat(amount_el.value.replace(',', '.'));

		if(isNaN(amount_val))
			amount_val = 0;
	} else {
		amount_val = 1;
	}

	if(price_el.value != '') {
		price_val = moneyEuroToCent(price_el.value);
	}

	total_val = moneyRound(amount_val * price_val);

	if(isNaN(total_val)) {
		total_el.innerHTML = "";
		return 0;
	} else {
		total_el.innerHTML = moneyFormatDutchWithEuro(total_val);
		return total_val;
	}
}

/**
 * Recalculates vat and total from a given subtotal
 * The subtotal itself is _NOT_ calculated here
 *
 * @param object subtotal_el The element to contain the subtotal
 * @param object vat_el The element to contain the amount of vat
 * @param object total_el The element to contain the grand total
 * @param integer subtotal_val The actual subtotal
 * @param integer btw_val The percentage of VAT
 */
function updateTotal(subtotal_el, vat_el, total_el, subtotal_val, btw_val)
{
	subtotal_el.innerHTML = moneyFormatDutchWithEuro(subtotal_val);
	vat_el.innerHTML = moneyFormatDutchWithEuro(moneyRound(subtotal_val * btw_val / 100));
	total_el.innerHTML = moneyFormatDutchWithEuro(subtotal_val + moneyRound(subtotal_val * btw_val / 100));
}

/**
 * Counts the number of elements with name 'key' in a form
 *
 * @param string form The number or name of the form
 * @param string key The name of the elements to count
 *
 * @return integer The number of items in the form with name 'key'.
 */
function countNumberOfItems(form, key) {
	aantalElements = document.forms[form].elements.length;
	itemCount = 0;

	for(i = 0; i < aantalElements; i++) {
		if(document.forms[form].elements[i].name == key)
			itemCount++;
	}

	return itemCount;
}

elementLookup = new Array();

/**
 * Returns the 'number'th item in the form with name 'key'
 * This fixes some issues with certain non-compliant browsers.
 *
 * Two techniques are used to speed this up:
 *  1. all previous results are stored in the elementLookup table.
 *  2. searches start at the index of the last found key
 *
 * @param string form The number or name of the form
 * @param string key The name of the element to access
 * @param integer number The number of the element to access
 *
 * @return object Reference to the form element or null if not found.
 */
function getFormElementByName(form, key, number) {
	if(typeof elementLookup[form] == 'undefined')
		elementLookup[form] = new Array();

	if(typeof elementLookup[form][key] == 'undefined')
		elementLookup[form][key] = new Array();

	if(typeof elementLookup[form][key][number] != 'undefined') 
		return document.forms[form].elements[elementLookup[form][key][number]];

	var itemCount = elementLookup[form][key].length - 1;
	var startIndex = 0;

	if(itemCount >= 0)
		startIndex = elementLookup[form][key][itemCount] + 1;

	var aantalElements = document.forms[form].elements.length;

	for(var i = startIndex; i < aantalElements; i++) {
		if(document.forms[form].elements[i].name == key) {
			itemCount++;

			elementLookup[form][key][itemCount] = i;

			if(itemCount == number)
				return document.forms[form].elements[i];
		}
	}

	return false;
}

/**
 * This is the keypress handler for when the
 *  RETURN key needs to be disabled.
 *
 * @param object e Event passed by onkeypress
 */
function noEnter(e) {
	if(window.event)
		key = window.event.keyCode;
	else
		key = e.which;

	return !(key == 13);
}

/**
 * Finds the absolute position of an object
 *
 * @author http://www.quirksmode.org/js/findpos.html
 * @license Public Domain
 */
function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}
