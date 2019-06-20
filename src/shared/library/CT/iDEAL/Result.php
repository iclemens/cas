<?php
	/**
	 * iDEAL Result Parser, Citrus-IT Online Boekhouding
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @version 1.0
	 * @package boekhouding
	 */

	/**
	 * Parses the XML message sent by iDEAL concerning
	 * the state of a transaction.
	 *
	 * This class raises the Exception exception.
	 */
	class CT_iDEAL_Result {

		const iD_Error = 0;
		const iD_Waiting = 1;
		const iD_Complete = 2;

		private $state = CT_iDEAL_Result::iD_Error;

		private $xml_parser;
		private $element_stack = array();
		private $data = array();

		private function getCurrentElement() 
		{
			foreach($this->element_stack as $element)
				$current = $element;

			return $current;
		}

		private function startElement($parser, $name, $attrs) 
		{
			array_push($this->element_stack, $name);
		}

		private function endElement($parser, $name)
		{
			array_pop($this->element_stack);
		}

		private function characterData($parser, $data)
		{
			switch($this->getCurrentElement()) {
				case 'PURCHASEID':
					$this->data['purchaseID'] = trim($data);
					break;
				case 'STATUS':
					$this->data['status'] = trim($data);
					break;
				case 'TRANSACTIONID':
					$this->data['transactionID'] = trim($data);
					break;
				case 'CREATEDATETIMESTAMP':
					$this->data['createDateTimeStamp'] = trim($data);
					break;
				default:
			}
		}

		/**
		 * Initializes the class and sets the state to iD_Waiting.
		 */
		function __construct() 
		{
			$this->xml_parser = xml_parser_create();
			xml_set_element_handler($this->xml_parser, array(&$this, "startElement"), array(&$this, "endElement"));
			xml_set_character_data_handler($this->xml_parser, array(&$this, "characterData"));
			$this->state = CT_iDEAL_Result::iD_Waiting;
		}

		/**
		 * Accepts the XML received from iDEAL.
		 * @var string $data
		 */
		public function parse($data)
		{
			if($this->state != CT_iDEAL_Result::iD_Waiting)
				throw new Exception("Error: currently not accepting input");

			if(!xml_parse($this->xml_parser, $data)) {
				$this->state = CT_iDEAL_Result::iD_Error;
				throw new Exception("Error: " . xml_error_string($this->xml_parser));
			}
		}

		/**
		 * Wraps up parsing of the XML, sets the state to iD_Complete.
		 * iDEAL data is available for use only after calling this function.
		 */
		public function finish()
		{
			if($this->state == CT_iDEAL_Result::iD_Error)
				throw new Exception("Error: Invalid operation");

			xml_parser_free($this->xml_parser);
			$this->state = CT_iDEAL_Result::iD_Complete;
		}

		/**
		 * This function fetches parsed iDEAL data. It should only be
		 * called after all data is present and finish() is invoked.
		 *
		 * @var string $name Name of the variable to be fetched
		 * @return string Value of the field.
		 */
		public function __get($name) 
		{
			if($this->state == CT_iDEAL_Result::iD_Error)
				throw new Exception("Error: Invalid operation");

			if(array_key_exists($name, $this->data))
				return $this->data[$name];

			throw new Exception('Error: Invalid field: ' . $name);
		}
	};
?>
