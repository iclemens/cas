<?php
	/**
	 * Betaling_Methode_Overboeking, Citrus-IT Online Boekhouding
	 *
	 * Provides support for the bank transfer payment method.
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2008 Ivar Clemens
	 * @package    CT_Betaling
	 */

	Zend_Loader::loadClass('CT_Betaling_Methode');

	/**
	 * Provides support for the direct bank transfer payment method.
	 *
	 * @package boekhouding
	 */
	class CT_Betaling_Methode_Overboeking implements CT_Betaling_Methode
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

			$smarty->assign('factuur', $invoice);
			$smarty->assign('klant', $customer);

			return $smarty->fetch("betaling/methode/overboeking.tpl");
		}
	};
