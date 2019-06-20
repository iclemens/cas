<?php
	/**
	 * CT_Validator_Validator_Artikelcode, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	Zend_Loader::loadClass('CT_Validation_Validator');
	Zend_Loader::loadClass('CT_Validation_Errors');

	/**
	 * Valideert de gegevens van een artikelcode
	 *
	 * @package boekhouding
	 */
	class CT_Validation_Validator_Artikelcode extends CT_Validation_Validator
	{
		public function supports($class)
		{
			if(is_array($class))
				return true;
			return false;
		}

		public function validate(&$object, &$error)
		{
			$this->rejectOnBlank($error, 'omschrijving', $object['omschrijving']);

			if(strlen($object['omschrijving']) > 60)
				$error->rejectValue('omschrijving', 'Waarde is te lang (max. 60)');
		}
	}
