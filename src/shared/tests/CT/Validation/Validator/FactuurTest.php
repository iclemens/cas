<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/Validation/Validator/Factuur.php";

	class CT_Validation_Validator_FactuurTest extends CT_TestCase
	{
		var $validInvoice = array(
			"klantnummer" => 60001,
			"datum" => "2006/01/01",

			"regels" => array(
				"0" => array(
					"artikelcode" => "40332",
					"aantal" => 1,
					"prijs" => 100,
					"omschrijving" => "Product X"))
		);

		protected function setUp() 
		{
			addSampleCustomers();
			addSampleArticleCodes();
		}

		protected function tearDown()
		{
			resetTable("klanten");
			resetTable("artikelcodes");
		}

		private function getErrorsForSingleFieldMutation($field, $target) {
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Factuur();

			$factuur = $this->validInvoice;
			$factuur[$field] = $target;

			$validator->validate($factuur, $errors);
			return $errors;
		}

		public function testSupportsOnlyArrays() {
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Factuur();

			$this->assertTrue($validator->supports(array()));
			$this->assertFalse($validator->supports("String"));
		}

		public function testKlantnummerBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('klantnummer', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('klantnummer'));		
		}

		public function testDateBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('datum', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('datum'));		
		}

		public function testCorrect() {
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Factuur();

			$factuur = $this->validInvoice;
			$validator->validate($factuur, $errors);
			$this->assertFalse($errors->hasErrors());
		}
	}
?>
