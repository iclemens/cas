<?php
	/**
	 * CT_Clieop_String, Project CAS
	 *
	 * @author		Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package		CT_Clieop
	 */

	/**
	 * Space padded strings for use in Clieop03 files.
	 */
	class CT_Clieop_String
	{
		var $_pad = ' ';
		var $_size;
		var $_value;

		/**
		 * Creates a new Clieop string with specified size
		 *
		 * @param integer $size Number of characters in the string
		 * @param string $default The default value
		 */
		function __construct($size, $default = '')
		{
			$this->_size = $size;
			$this->_value = $default;
		}

		/**
		 * Checks whether a given character is a valid clieop character.
		 */
		private function isValidCharacter($char)
		{
			if(($char >= 'A' && $char <= 'Z') ||
				($char >= 'a' && $char <= 'z') ||
				($char >= '0' && $char <= '9'))
				return true;
				
			$chars = '.()+&$*;:-/,%?@=`"';

			for($i = 0; $i < strlen($chars); $i++)
				if(substr($chars, $i, 1) == $char)
					return true;

			return false;
		}

		/**
		 * Changes the current string value.
		 *
		 * @param string $value New value
		 */
		public function setValue($value)
		{
			$this->_value = $value;
		}

		/**
		 * Retreives the current string value
		 *
		 * @return string Current value
		 */
		public function getValue()
		{
			return $this->_value;
		}

		/**
		 * Pads the string for use in a Clieop03 stream
		 *
		 * @return string Space padded string
		 */
		public function write()
		{
			return substr(str_pad(strval($this->_value), $this->_size, $this->_pad, STR_PAD_RIGHT), -$this->_size);	
		}

	}
