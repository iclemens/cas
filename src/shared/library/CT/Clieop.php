<?php
	/**
	 * CT_Clieop, Project CAS
	 *
	 * @author		Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package		CT_Clieop
	 */

	Zend_Loader::loadClass('CT_Clieop_Number');
	Zend_Loader::loadClass('CT_Clieop_String');
	Zend_Loader::loadClass('CT_Clieop_Record');
	Zend_Loader::loadClass('CT_Clieop_Item');
	Zend_Loader::loadClass('CT_Clieop_Batch');

	/**
	 * Clieop03 header (internal)
	 */
	class CT_Clieop_Header extends CT_Clieop_Record
	{
		function __construct() {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 1),
				'variantCode' => new CT_Clieop_String(1, 'A'),
				'fileCreationDate' => new CT_Clieop_Number(6),
				'fileName' => new CT_Clieop_String(8),
				'senderIdentification' => new CT_Clieop_String(5),
				'fileIdentification' => new CT_Clieop_String(4),
				'duplicateCode' => new CT_Clieop_Number(1),
				'filler' => new CT_Clieop_String(21)
				);
		}
	}

	/**
	 * Clieop03 footer (internal)
	 */
	class CT_Clieop_Footer extends CT_Clieop_Record
	{
		function __construct() {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 9999),
				'variantCode' => new CT_Clieop_String(1, 'A'),
				'filler' => new CT_Clieop_String(45)
				);
		}
	}

	/**
	 * Represents a complete Clieop03 file (containing direct 
	 * debit or payment transactions)
	 */	
	class CT_Clieop
	{
		var $header;
		var $footer;

		var $_batches = array();	

		/**
		 * Creates a new Clieop03 file.
		 */
		function CT_Clieop()
		{
			$this->header = new CT_Clieop_Header();
			$this->footer = new CT_Clieop_Footer();
		}

		/**
		 * Adds a new batch to the Clieop03 file.
		 *
		 * @param CT_Clieop_Batch $batch Batch
		 */
		public function addBatch($batch)
		{
			$this->_batches[] = $batch;
		}

		/**
		 * Dumps all fields to a Cliep03 stream.
		 *
		 * @return string Clieop03 stream.
		 */
		public function write()
		{
			$clieop = $this->header->write();

			foreach($this->_batches as $batch)
				$clieop .= $batch->write();

			$clieop .= $this->footer->write();

			return $clieop;
		}

		/**
		 * Returns the completed Clieop03 file.
		 */
		public function getFile()
		{
			return $this->write();
		}
	}
