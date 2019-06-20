<?php
	/**
	 * Betaling_Methode, Citrus-IT Online Boekhouding
	 *
	 * Abstract class which represents a payment method.
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2008 Ivar Clemens
	 * @package    CT_Betaling
	 */

	/**
	 * Abstract class which represents a payment method.
	 *
	 * When adding a new payment option, this should be the
	 * parent class.
	 *
	 * @package boekhouding
	 */
	interface CT_Betaling_Methode
	{
		/**
		 * Should return HTML code describing how to initiate the payment.
		 *
		 * @param array $customer
		 * @param array $invoice
		 * @return object
		 */		
		public function fetchOption($customer, $invoice);
	}

