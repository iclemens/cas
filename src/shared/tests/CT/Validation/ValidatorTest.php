<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/Validation/Validator.php";

	class CT_Validation_ValidatorTest extends PHPUnit_Framework_TestCase
	{
		public function testBlank() {
			$this->assertTrue(CT_Validation_Validator::isBlank(""));
			$this->assertTrue(CT_Validation_Validator::isBlank(" "));
			$this->assertTrue(CT_Validation_Validator::isBlank("  "));
			$this->assertTrue(CT_Validation_Validator::isBlank(" \t "));
			$this->assertTrue(CT_Validation_Validator::isBlank("\r"));
			$this->assertTrue(CT_Validation_Validator::isBlank("\n"));

			$this->assertFalse(CT_Validation_Validator::isBlank(" _ "));
		}

		public function testPostcode() {
			$this->assertTrue(CT_Validation_Validator::isPostcode("5283 NK"));
			
			$this->assertFalse(CT_Validation_Validator::isPostcode(""));
			$this->assertFalse(CT_Validation_Validator::isPostcode("0000"));
			$this->assertFalse(CT_Validation_Validator::isPostcode("000  XX"));
			$this->assertFalse(CT_Validation_Validator::isPostcode("AAAA 10"));
		}

		public function testPhonenumber() {
			$this->assertTrue(CT_Validation_Validator::isPhoneNumber("0411-675999"));
			$this->assertTrue(CT_Validation_Validator::isPhoneNumber("06-41471018"));
			$this->assertFalse(CT_Validation_Validator::isPhoneNumber("Geen nummer"));
		}

		public function testEmail() {
			$this->assertTrue(CT_Validation_Validator::isEmail("post@ivarclemens.nl"));
			$this->assertTrue(CT_Validation_Validator::isEmail("kees@citrus-it.nl"));
			$this->assertFalse(CT_Validation_Validator::isEmail("Geen Email"));
		}

		public function testiBAN() {
			$this->assertTrue(CT_Validation_Validator::isIBAN("NL25ABNA0404283888"));
		}
	}

//	class CT_Validation_ValidatorTest extends PHPUnit_Framework_TestCase
//	{
		/*public function testBlank() {

		public function testEmail() {
			$this->assertTrue(CT_Validation_Validator::isEmail("post@ivarclemens.nl"));
			$this->assertTrue(CT_Validation_Validator::isEmail("kees@citrus-it.nl"));
			$this->assertFalse(CT_Validation_Validator::isEmail("Geen Email"));
		}

		public function testURI() {
			$this->assertTrue(CT_Validation_Validator::isURI("http://www.citrus-it.nl"));
			$this->assertFalse(CT_Validation_Validator::isURI("Geen URI"));
		}
*/


		/*public function testStrongPassword() {
			return;
			$this->assertTrue(CT_Validation_Validator::isStrongPassword("Eiz9aith"));
			$this->assertTrue(CT_Validation_Validator::isStrongPassword("phi2aiV7"));
			$this->assertTrue(CT_Validation_Validator::isStrongPassword("Eegiech4"));

			$this->assertFalse(CT_Validation_Validator::isStrongPassword(""));
			$this->assertFalse(CT_Validation_Validator::isStrongPassword("0000"));
		}*/
//	}
?>
