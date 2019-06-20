<?php
	@include_once '../utility/setup.php';

	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/Formatting/Generic.php";

	class CT_Formatting_KuminaTest extends CT_TestCase
	{
		protected function setUp() 
		{
		}

		protected function tearDown() 
		{
		}

		public function testInvoiceRef()
		{
			$formatter = new CT_Formatting_Kumina();

			$invoiceref = $formatter->getInvoiceRef(array('factuurnummer' => '1', 'datum' => '2007-02-04'));
			$this->assertEquals($invoiceref, '20070001');
			$this->assertNotEquals($invoiceref, '2007####');

			$invoiceref = $formatter->getInvoiceRef(array('factuurnummer' => 2, 'datum' => '1970-02-04'));
			$this->assertEquals($invoiceref, '19700002');
			$this->assertNotEquals($invoiceref, '1970####');

			$invoiceref = $formatter->getInvoiceRef(array('factuurnummer' => 42, 'datum' => '2007-02-04'));
			$this->assertEquals($invoiceref, '20070042');

			$invoiceref = $formatter->getInvoiceRef(array('factuurnummer' => 421, 'datum' => '2007-02-04'));
			$this->assertEquals($invoiceref, '20070421');

			$invoiceref = $formatter->getInvoiceRef(array('factuurnummer' => 4215, 'datum' => '2007-02-04'));
			$this->assertEquals($invoiceref, '20074215');

			$invoiceref = $formatter->getInvoiceRef(array('factuurnummer' => 42153, 'datum' => '2007-02-04'));
			$this->assertEquals($invoiceref, '2007####');

			$invoiceref = $formatter->getInvoiceRef(array('factuurnummer' => 0, 'datum' => '2007-02-04'));
			$this->assertEquals($invoiceref, '20070000');
			$this->assertNotEquals($invoiceref, '2007000');
			
			$invoiceref = $formatter->getInvoiceRef(array('factuurnummer' => '', 'datum' => '2007-02-04'));
			$this->assertEquals($invoiceref, '2007####');
			$this->assertNotEquals($invoiceref, '20070000');

			$invoiceref = $formatter->getInvoiceRef(array('datum' => '2007-02-04'));
			$this->assertEquals($invoiceref, '2007####');
			$this->assertNotEquals($invoiceref, '20070000');
		}

		public function testCustomerRefFromUsername()
		{
			$formatter = new CT_Formatting_Kumina();
			$customer = $formatter->getCustomerRefFromUsername('60001');

			$this->assertEquals($customer, 60001);
		}

		public function testInvoiceRefFromFile()
		{
			$formatter = new CT_Formatting_Kumina();
			$invoice = $formatter->getInvoiceDetailsFromFilename('20070001.pdf');

			$this->assertEquals($invoice['boekjaar'], 2007);
			$this->assertEquals($invoice['factuurnummer'], 1);

			$invoice = $formatter->getInvoiceDetailsFromFilename('12429999.pdf');

			$this->assertEquals($invoice['boekjaar'], 1242);
			$this->assertEquals($invoice['factuurnummer'], 9999);
		}

	}

