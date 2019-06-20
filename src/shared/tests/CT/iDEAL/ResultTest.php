<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/iDEAL/Result.php";

	class CT_iDEAL_ResultTest extends PHPUnit_Framework_TestCase
	{
		public function testInvalidField() {
			$result = new CT_iDEAL_Result();

			try {
				$result->status;
			} catch(Exception $expected) {
				return;
			}

			$this->fail("Expected exception");
		}

		public function testInvalidXML() {
			$result = new CT_iDEAL_Result();

			try {
				$result->parse("This is not XML");
			} catch(Exception $expected) {
				return;
			}

			$this->fail("Expected exception");
		}

		public function testInvalidXMLFinish() {
			$result = new CT_iDEAL_Result();

			try {
				$result->parse("This is not XML");
			} catch(Exception $expected) {
			}

			try {
				$result->finish();
			} catch(Exception $expected) {
				return;
			}

			$this->fail("Expected exception");
		}

		public function testValidXML() {
			$ideal_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
				"<Notification xmlns=\"http://www.idealdesk.com/Message\" version=\"1.1.0\">" .
 				"<createDateTimeStamp>20060911220500</createDateTimeStamp>" .
 				"<transactionID>0020012303934219</transactionID>" .
 				"<purchaseID>XL2006001</purchaseID>" .
 				"<status>Success</status>" .
				"</Notification>";

			$result = new CT_iDEAL_Result();
			$result->parse($ideal_xml);
			$result->finish();

			$this->assertEquals($result->status, "Success");
			$this->assertEquals($result->transactionID, "0020012303934219");
			$this->assertEquals($result->purchaseID, "XL2006001");
			$this->assertEquals($result->createDateTimeStamp, "20060911220500");
		}

		public function testValidXMLDefaultPath() {
			$ideal_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
				"<Notification xmlns=\"http://www.idealdesk.com/Message\" version=\"1.1.0\">" .
 				"<createDateTimeStamp>createDateTimeStamp</createDateTimeStamp>" .
 				"<transactionID>transactionID</transactionID>" .
 				"<purchaseID>purchaseID</purchaseID>" .
 				"<status>status</status>" .
				"<default>Default</default>" .
				"</Notification>";

			$result = new CT_iDEAL_Result();
			$result->parse($ideal_xml);
			$result->finish();

			$this->assertEquals($result->status, "status");
			$this->assertEquals($result->transactionID, "transactionID");
			$this->assertEquals($result->purchaseID, "purchaseID");
			$this->assertEquals($result->createDateTimeStamp, "createDateTimeStamp");
		}
	}
?>