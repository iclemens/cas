<?php
	/**
	 * Default formatting functions
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2008 Ivar Clemens
	 * @package    boekhouding
	 */

	Zend_Loader::loadClass("CT_Formatting_Abstract");

	class CT_Formatting_Default implements CT_Formatting_Abstract {

		public function getCustomerRef($customer)
		{
			// 1-9999 (do not have to prepend 0s)
			$customernr = $customer['klantnr'];

			return strval($customernr);
		}

		public function getInvoiceRef($invoice)
		{
			// yyyyVOLG

			$invoiceref = date('Y', strtotime($invoice['datum']));

			if(!array_key_exists('factuurnummer', $invoice))
				return $invoiceref . '####';

			if(gettype($invoice['factuurnummer']) == 'string' && $invoice['factuurnummer'] == '')
				return $invoiceref . '####';

			$invoicenr = $invoice['factuurnummer'];

			if($invoicenr >= 10000)
				return $invoiceref . "####";

			if($invoicenr < 10)
				$invoiceref .= "0";
			if($invoicenr < 100)
				$invoiceref .= "0";
			if($invoicenr < 1000)
				$invoiceref .= "0";

			$invoiceref .= $invoicenr;

			return $invoiceref;
		}

		public function getInvoiceDetailsFromFilename($filename)
		{
			$num_matches = preg_match("/^([0-9][0-9][0-9][0-9])([0-9][0-9][0-9][0-9]).pdf$/",
				$filename, $matches);

			if($num_matches != 1)
			  throw new Exception("Ongeldig factuurnummer");

			return array(
			  "boekjaar" => $matches[1],
			  "factuurnummer" => $matches[2]);
		}

		public function getUsernameFromCustomer($customer)
		{
			return strtolower($this->getCustomerRef($customer));
		}

		public function getCustomerRefFromUsername($username)
		{
			return intval($username);
		}
	}
?>
