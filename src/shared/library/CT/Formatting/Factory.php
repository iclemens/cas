<?php
	/**
	 * Isolates formatter instantiation.
	 *
	 * @author     Ivar Clemens <post@ivarclemens.nl>
	 * @copyright  2007 Ivar Clemens
	 * @package    boekhouding
	 */

	/**
	 */
	class CT_Formatting_Factory
	{
		/**
		 * Constructs a formatter.
		 *
		 * @param string $type The type of formatter required.
		 *
		 * @return CT_Formatting_Abstract A formatter instance.
		 */
		static function getFormatter($type)
		{
			$class_name = 'CT_Formatting_' . $type;

			Zend_Loader::loadClass($class_name);

			return new $class_name();
		}
	}
?>
