<?php
	/**
	 * Invoice_Factory, Citrus-IT Online Boekhouding
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2008 Ivar Clemens
	 * @package    CT_Invoice
	 */

	/**
	 */
	class CT_Invoice_Factory
	{
		static function getInvoiceBuilder($type)
		{
			$class_name = 'CT_Invoice_' . $type;

			Zend_Loader::loadClass($class_name);

			return new $class_name();
		}
	}

