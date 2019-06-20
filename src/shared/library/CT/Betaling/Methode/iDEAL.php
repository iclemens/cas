<?php
	/**
	 * Betaling_Methode_iDEAL, Citrus-IT Online Boekhouding.
	 *
	 * Provides support for the iDEAL payment method.
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2008 Ivar Clemens
	 * @package    CT_Betaling
	 */

	Zend_Loader::loadClass('CT_Betaling_Methode');

	/**
	 * Provides support for the iDEAL payment method.
	 *
	 * @package boekhouding
	 */
	class CT_Betaling_Methode_iDEAL implements CT_Betaling_Methode
	{
		/**
		 * Returns a correct iDEAL transaction form. This code is
		 * based on the Rabobank iDEAL Lite sample code.
		 *
		 * @param array $customer
		 * @param array $invoice
		 * @return string
		 */		
		public function fetchOption($customer, $invoice)
		{
			$config = Zend_Registry::get('config');
			$formatter = Zend_Registry::get('formatter');

			$smarty = new CT_Smarty($config, $formatter);

			/* Construct all nessecary data */
			$key 					= $config->ideal->key;
			$merchantID 	= $config->ideal->merchant_id;
			$subID 				= '0';
			$amount 			= $invoice['totaal'];
			$orderNumber 	= $formatter->getInvoiceRef($invoice);
			$paymentType 	= 'ideal';
			$validUntil 	= date("Y-m-d\TG:i:s\Z", strtotime("+1 week"));
		
			$itemNumber 			= '1';
			$itemDescription 	= $config->branding->company_name . ' ' . $orderNumber;
			$itemQuantity 		= '1';
			$itemPrice 				= $invoice['totaal'];

			/* Generate sha1 hash for iDEAL */
			$item = $itemNumber . $itemDescription . $itemQuantity . $itemPrice;
		
			$shastring = "$key" . "$merchantID" . "$subID" . "$amount" . "$orderNumber" .
				"$paymentType" . "$validUntil" . $item;
		
			$clean_shastring = html_entity_decode($shastring);
		
			$not_allowed = array("\t", "\n", "\r", " ");
			$clean_shastring = str_replace($not_allowed, "", $clean_shastring);
		
			$shasign = sha1($clean_shastring);

			/* Pass information to smarty */
			$smarty->assign("idealPost", $config->ideal->post_address);
			$smarty->assign("key", $key);
			$smarty->assign("merchantID", $config->ideal->merchant_id);
			$smarty->assign("subID", $subID);
			$smarty->assign("amount", $amount);
			$smarty->assign("purchaseID", $orderNumber);
		
			$smarty->assign("description", "Factuur: " . $orderNumber);
			$smarty->assign("hash", $shasign);
			$smarty->assign("paymentType", $paymentType);
			$smarty->assign("validUntil", $validUntil);
		
			$smarty->assign("itemNumber", $itemNumber);
			$smarty->assign("itemDescription", $itemDescription);
			$smarty->assign("itemQuantity", $itemQuantity);
			$smarty->assign("itemPrice", $itemPrice);

			return $smarty->fetch("betaling/methode/iDEAL.tpl");
		}
	};
