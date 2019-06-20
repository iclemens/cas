/**
 * money.js
 * Support unit which handles formatting and rounding of monetary values
 *
 * Copyright (C) 2007 Citrus-IT
  *
 * Author: Ivar Clemens
 * Date: 2007-08-09
 */

/**
 * moneyDutchToFloat
 * Converts an amount in Dutch notation to a floating point number
 * If this cannot be done, 0 is returned
 *
 * @str - Een getal met een punt of komma als decimaal teken
 * @return - Een integer van het getal in @str
 */
function moneyDutchToFloat(str) {
	if(typeof str != 'string')
		return 0;

	tmp = parseFloat(str.replace(',', '.'));

	if(isNaN(tmp))
		return 0;

	return tmp;
}

/**
 * moneyEuroToCent
 * Converts an amount in euros (float) to an amount in cents (integer)
 *
 * @amount - Het bedrag (als commagetal)
 * @return - Het afgeronde bedrag in centen
 */
function moneyEuroToCent(amount)
{
	return Math.floor(moneyDutchToFloat(amount) * 100 + 0.5);
}

/**
 * moneyFormat
 * Applies formatting to an amount in cents
 *
 * @prijs - Amount in cents
 * @dsep - Decimal separator
 * @tsep - Thousands separator
 * @return - Formatted amount
 */
function moneyFormatDutch(prijs, dsep, tsep) {
	i_prijs = Math.floor(prijs + 0.5);
	s_prijs = "";

	if(i_prijs < 0) {
		sign = '-';
		i_prijs = Math.abs(i_prijs);
	} else {
		sign = '';
	}

	cnt = 0;
	while(i_prijs > 0) {
		digit = i_prijs % 10;
		i_prijs = Math.floor(i_prijs / 10);

		if(cnt == 2)
			s_prijs = dsep + s_prijs;
		if(cnt > 2 && (cnt - 2) % 3 == 0) 
			s_prijs = tsep + s_prijs;

		s_prijs = digit + s_prijs;

		cnt = cnt + 1;
	}

	if(cnt == 0)
		s_prijs = '0' + s_prijs;
	if(cnt <= 1)
		s_prijs = '0' + s_prijs;
	if(cnt <= 2)
		s_prijs = "0" + dsep + s_prijs;

	return sign + s_prijs;
}

/**
 * moneyFormatDutchWithEuro
 * Applies dutch formatting to an amount in cents, also adds euro sign.
 *
 * @prijs - Amount to round in cents
 * @return - Rounded amount
 */
function moneyFormatDutchWithEuro(prijs) {
	return "&euro; " + moneyFormatDutch(prijs, ",", ".");
}

/**
 * moneyRound
 * Rounds an amount in cents according to standards
 *
 * @amount - Amount to round in cents
 * @return - Rounded amount
 */
function moneyRound(amount)
{
	return Math.floor(amount + 0.5);
}
