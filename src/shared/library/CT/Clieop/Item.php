<?php
	/**
	 * CT_Clieop_Item, Project CAS
	 *
	 * @author		Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package		CT_Clieop
	 */

	Zend_Loader::loadClass('CT_Clieop_Number');
	Zend_Loader::loadClass('CT_Clieop_String');
	Zend_Loader::loadClass('CT_Clieop_Record');

	/**
	 * Transaction information (interal)
	 */
	class CT_Clieop_Item_Transaction extends CT_Clieop_Record
	{
		function __construct() {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 100),
				'variantCode' => new CT_Clieop_String(1, 'A'),
				'transactionType' => new CT_Clieop_String(4),
				'amount' => new CT_Clieop_Number(12),
				'accountPayer' => new CT_Clieop_Number(10),
				'accountBeneficiary' => new CT_Clieop_Number(10),
				'filler' => new CT_Clieop_String(9)
				);
		}
	}

	/**
	 * Name of the payee (interal)
	 */
	class CT_Clieop_Item_PayerName extends CT_Clieop_Record
	{
		function __construct($name = '') {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 110),
				'variantCode' => new CT_Clieop_String(1, 'B'),
				'namePayer' => new CT_Clieop_String(35),
				'filler' => new CT_Clieop_String(10)
				);

			$this->namePayer = $name;
		}
	}

	/**
	 * Payment reference (interal)
	 */
	class CT_Clieop_Item_PaymentReference extends CT_Clieop_Record
	{
		function __construct($reference = '') {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 150),
				'variantCode' => new CT_Clieop_String(1, 'A'),
				'paymentReference' => new CT_Clieop_String(16),
				'filler' => new CT_Clieop_String(29)
				);

			$this->paymentReference = $reference;
		}
	}

	/**
	 * Description (interal)
	 */
	class CT_Clieop_Item_Description extends CT_Clieop_Record
	{
		function __construct($description = '') {
			$this->_fields = array(
				'recordCode' => new CT_Clieop_Number(4, 160),
				'variantCode' => new CT_Clieop_String(1, 'A'),
				'description' => new CT_Clieop_String(32),
				'filler' => new CT_Clieop_String(13)
				);

			$this->description = $description;
		}
	}

	/**
	 * Item for use in Cliep03 batches.
	 */
	class CT_Clieop_Item
	{
		var $transaction;
		var $_namePayer = array();
		var $_paymentReference = array();
		var $_description = array();

		/**
		 * Creates a blank item
		 */
		function __construct() 
		{
			$this->transaction = new CT_Clieop_Item_Transaction();
		}

		/**
		 * Sets the (optinal) payer's name
		 *
		 * @param string $name Name of the payer
		 */
		public function addPayerName($name)
		{
			if(count($this->_namePayer) > 0)
				throw new Exception("Clieop03: Only one PayerName record is allowed within an Item");

			$this->_namePayer[] = new CT_Clieop_Item_PayerName($name);
		}

		/**
		 * Sets the (optinal) reference
		 *
		 * @param string $reference Reference
		 */
		public function addPaymentReference($reference)
		{
			if(count($this->_paymentReference) > 0)
				throw new Exception("Clieop03: Only one PaymentReference record is allowed within an Item");

			$this->_paymentReference[] = new CT_Clieop_Item_PaymentReference($reference);
		}

		/**
		 * Adds a description (max. 4)
		 *
		 * @param string $description Description
		 */
		public function addDescription($description)
		{
			if(count($this->_description) > 3)
				throw new Exception("Clieop03: Only four Description records are allowed with an Item");

			$this->_description[] = new CT_Clieop_Item_Description($description);
		}

		/**
		 * Dumps all fields in a Clieop03 stream.
		 *
		 * @return string Clieop03 stream.
		 */
		public function write()
		{
			$out = $this->transaction->write();

			foreach($this->_namePayer as $namePayer)
				$out .= $namePayer->write();

			foreach($this->_paymentReference as $paymentReference)
				$out .= $paymentReference->write();

			foreach($this->_description as $description)
				$out .= $description->write();

			return $out;
		}
	}
