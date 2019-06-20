<?php
	/**
	 * CT_Clieop_Record, Project CAS
	 *
	 * @author		Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package		CT_Clieop
	 */

	/**
	 * Base class for Clieop03 records
	 */
	class CT_Clieop_Record
	{
		var $_fields = array();

		/**
		 * Sets the value of a field
		 *
		 * @param string $field Name of the field
		 * @param mixed $value New value
		 */
		function __set($field, $value)
		{
			$this->_fields[$field]->setValue($value);
		}

		/**
		 * Retreives the value of a field
		 *
		 * @param string $field Name of the field
		 * @return mixed Value of the field
		 */
		public function __get($field)
		{
			return $this->_fields[$field]->getValue();
		}

		/**
		 * Dumps all fields in Clieop3 format
		 *
		 * @return string Clieop3 stream
		 */
		public function write()
		{
			$out = '';

			foreach($this->_fields as $field)
				$out .= $field->write();

			return $out;
		}
	}