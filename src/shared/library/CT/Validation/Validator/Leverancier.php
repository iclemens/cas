<?php
	/**
	 * CT_Validator_Validator_Leverancier, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator');
	Zend_Loader::loadClass('CT_Validation_Errors');

	/**
	 * Valideert de gegevens van een leverancier
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Leverancier extends CT_Validation_Validator
	{
		public function supports($class)
		{
			if(is_array($class))
				return true;
			return false;
		}

		public function validate(&$object, &$error)
		{
			/* Bedrijfsnaam */
			$this->rejectOnBlank(&$error, 'bedrijfsnaam', $object['bedrijfsnaam']);

			if(strlen($object['bedrijfsnaam']) > 60)
				$error->rejectValue('bedrijfsnaam', 'Waarde is te lang (max. 60)');	

			if(self::isIBAN($object['iban']) == false)
				$error->rejectValue('iban', 'IBAN code is ongeldig');

			if(self::isBIC($object['bic']) == false)
				$error->rejectValue('bic', 'BIC code is ongeldig');
		}
	}
?>
