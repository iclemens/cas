<?php
	/**
	 * Takes various invoice parameters and generates a PDF.
	 *
   * @author     Ivar Clemens <post@ivarclemens.nl>
   * @copyright  2008 Ivar Clemens
   * @package    CT_Invoice
	 */

	/**
	 * Takes various invoice parameters and generates a PDF.
	 *
	 * Custom invoice (PDF) generators should implement this interface.
	 *
	 * Other formats are currently not supported; but the rest of the system
	 * does not make assumptions about the nature of the file returned (other
	 * than it being the invoice).
   */
	interface CT_Invoice_Abstract {
		/**
		 * Adds an invoice line to the invoice.
		 *
		 * The array passed should contain the full invoice line specification.
		 * See CT_Db_Facturen for more details.
		 *
		 * @param int $nr Number of the line, each number should only be set ONCE!
		 * @param array $regel Details of the invoice line.
		 */
		public function addInvoiceLine($nr, $regel);

		/**
		 * Sets the customer details.
		 *
		 * The array passed should contain the full customer specification.
		 * See CT_Db_Klanten for more details.
		 *
		 * @param array $klant An array containing customer details.
		 */
		public function setCustomerDetails($klant);

		/**
		 * Sets the invoice details (like date and number).
		 *
		 * The array passed should contain the full invoice specification
		 * excluding the 'regels' and all totals (see setTotals).
		 * See CT_Db_Facturen for more details.
		 *
		 * @param array $factuur An array containing the invoice details.
		 */
		public function setInvoiceDetails($factuur);

		/**
		 * Sets the totals.
		 *
		 * The array passed to this function should contain the following keys:
		 *  - 'subtotaal' - The total excluding VAT
		 *  - 'btw'       - The total amount of VAT
		 *  - 'totaal'		- The total including VAT
		 *
		 * @param array $factuur An array containing the invoice totals.
		 */
		public function setTotals($factuur);

		/**
		 * Returns the final PDF.
		 *
		 * @return string The generated PDF
		 */
		public function getPDF();
	}
