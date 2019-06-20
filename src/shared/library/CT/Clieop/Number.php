<?php
	/**
	 * CT_Clieop_Number, Project CAS
	 *
	 * @author		Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package		CT_Clieop
	 */

	/**
	 * Zero padded integer for use in Clieop03 files.
	 */
	class CT_Clieop_Number
	{
		var $_size;
		var $_value;

		/**
		 * Creates a new Clieop integer with specified size
		 *
		 * @param integer $size Number of digits in the string
		 * @param integer $default The default value
		 */
		function __construct($size, $default = 0)
		{
			$this->_size = $size;
			$this->_value = $default;
		}

		/**
		 * Changes the current integer value.
		 *
		 * @param integer $value New value
		 */
		function setValue($value)
		{
			$this->_value = $value;
		}

		/**
		 * Retreives the current integer value
		 *
		 * @return integer Current value
		 */
		function getValue()
		{
			return $this->_value;
		}

		/**
		 * Pads the integer for use in a Clieop03 stream
		 *
		 * @return string Zero-padded integer
		 */
		function write()
		{
			return substr(str_pad(strval($this->_value), $this->_size, '0', STR_PAD_LEFT), -$this->_size);
		}
	}
