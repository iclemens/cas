<?php
	/**
   * Collectie bedrijfs-specifieke opmaak functies
   *
   * @author     Ivar Clemens <post@ivarclemens.nl>
   * @copyright  2007 Ivar Clemens
   * @package    boekhouding
   */

	/**
   * Collectie bedrijfs-specifieke opmaak functies
   */
	interface CT_Formatting_Abstract {

		/**
		 * Retourneert het klantnummer
		 */
		public function getCustomerRef($customer);

		/**
		 * Retourneert het factuurnummer
		 */
		public function getInvoiceRef($invoice);

		/**
		 * Retourneert de gebruikersnaam van een klant
		 */
		public function getUsernameFromCustomer($customer);

		/**
		 * Retourneert het klantnummer gegeven een gebruikersnaam
		 */
		public function getCustomerRefFromUsername($username);

		/**
		 * Haalt factuur gegevens uit een bestandsnaam
		 */
		public function getInvoiceDetailsFromFilename($filename);
	}

