<?php
	/**
	 * CT_Clieop_Batch, Project CAS
	 *
	 * @author		Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package		CT_Clieop
	 */

	Zend_Loader::loadClass('CT_Clieop_Number');
	Zend_Loader::loadClass('CT_Clieop_String');
	Zend_Loader::loadClass('CT_Clieop_Record');
	Zend_Loader::loadClass('CT_Clieop_Item');

	/**
	 * Batch header (internal)
	 */
	class CT_Clieop_Batch_Header extends CT_Clieop_Record
	{
		function __construct() {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 10),
				'variantCode' => new CT_Clieop_String(1, 'B'),
				'transactionGroup' => new CT_Clieop_String(2),
				'accountOrderingParty' => new CT_Clieop_Number(10),
				'batchSequenceNumber' => new CT_Clieop_Number(4),
				'deliveryCurrency' => new CT_Clieop_String(3, 'EUR'),
				'batchIdentification' => new CT_Clieop_String(16),
				'filler' => new CT_Clieop_String(10)
				);
		}
	}

	/**
	 * Batch description (internal)
	 */
	class CT_Clieop_Batch_Description extends CT_Clieop_Record
	{
		function __construct($description) {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 20),
				'variantCode' => new CT_Clieop_String(1, 'A'),
				'fixedDescription' => new CT_Clieop_String(32),
				'filler' => new CT_Clieop_String(13)
				);

			$this->fixedDescription = $description;
		}
	}

	/**
	 * Information about ordering party (internal)
	 */
	class CT_Clieop_Batch_OrderingParty extends CT_Clieop_Record
	{
		function __construct() {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 30),
				'variantCode' => new CT_Clieop_String(1, 'B'),
				'nameCode' => new CT_Clieop_Number(1),
				'processingDate' => new CT_Clieop_Number(6),
				'nameOrderingParty' => new CT_Clieop_String(35),
				'testCode' => new CT_Clieop_String(1),
				'filler' => new CT_Clieop_String(2)
				);
		}
	}

	/**
	 * Batch footer (internal)
	 */
	class CT_Clieop_Batch_Footer extends CT_Clieop_Record
	{
		function __construct() {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 9990),
				'variantCode' => new CT_Clieop_String(1, 'A'),
				'totalAmount' => new CT_Clieop_Number(18),
				'totalAccounts' => new CT_Clieop_Number(10),
				'itemCount' => new CT_Clieop_Number(7),
				'filler' => new CT_Clieop_String(10)
				);
		}
	}

	/**
	 * Represents a clieop3 batch
	 */
	class CT_Clieop_Batch extends CT_Clieop_Record
	{
		var $header;
		var $orderingParty;
		var $footer;

		var $_description = array();
		var $_items = array();

		/**
		 * Creates a new Clieop3 batch
		 */
		function __construct()
		{
			$this->header = new CT_Clieop_Batch_Header();
			$this->orderingParty = new CT_Clieop_Batch_OrderingParty();
			$this->footer = new CT_Clieop_Batch_Footer();
		}

		/**
		 * Adds a description to the batch. The description is
		 * added to the descriptions of individual items.
		 *
		 * @param string $description Description
		 */
		public function addDescription($description)
		{
			if(count($this->_description) > 3)
				throw new Exception("Clieop03: Only four Description records are allowed in a Batch");

			$this->_description[] = new CT_Clieop_Batch_Description($description);
		}

		/**
		 * Adds a Clieop03 item to the batch.
		 *
		 * @param CT_Clieop_Item $item Clieop03 item
		 */
		public function addItem($item)
		{
			if(count($this->_items) > 99999)
				throw new Exception("Clieop03: Only 100.000 Item records are allowed in a single Batch");

			$this->_items[] = $item;
		}

		/**
		 * Dumps all fields to a Clieop03 stream.
		 *
		 * @return string Clieop03 stream.
		 */
		public function write()
		{
			$out = $this->header->write();

			foreach($this->_description as $description)
				$out .= $description->write();

			$out .= $this->orderingParty->write();

			foreach($this->_items as $item)
				$out .= $item->write();

			$out .= $this->footer->write();

			return $out;
		}
	}
