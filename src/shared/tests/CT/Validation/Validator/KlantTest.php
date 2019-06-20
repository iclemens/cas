<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/Validation/Validator/Klant.php";

	class CT_Validation_Validator_KlantTest extends PHPUnit_Framework_TestCase
	{
		var $validCustomer = array(
			"bedrijfsnaam" => "Citrus-IT",
			"aanhef" => "Dhr.",
			"voornaam" => "Kees",
			"achternaam" => "Meijs",
			"factuuradres" => "Redoutestraat 13",
			"factuurpostcode" => "5283 NK",
			"factuurplaats" => "Boxtel",
			"bezoekadres" => "Redoutestraat 13",
			"bezoekpostcode" => "5283 NK",
			"bezoekplaats" => "Boxtel",
			"actief" => 1,
			"klanttype" => 0,
			"emailadres" => "kees@citrus-it.nl",
			"website" => "http://www.citrus-it.nl",
			"telefoonvast" => "0411-675999",
			"telefoonmobiel" => "06-41471018");

		private function getErrorsForSingleFieldMutation($field, $target) {
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Klant();

			$klant = $this->validCustomer;
			$klant[$field] = $target;

			$validator->validate($klant, $errors);
			return $errors;
		}

		public function testSupportsOnlyArrays() {
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Klant();

			$this->assertTrue($validator->supports(array()));
			$this->assertFalse($validator->supports("String"));
		}

		public function testCompanyMustHaveCompanyName() {
			$errors = $this->getErrorsForSingleFieldMutation('bedrijfsnaam', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('bedrijfsnaam'));
		}

		public function testNonCompanyCannotHaveCompanyName() {
			$errors = $this->getErrorsForSingleFieldMutation('klanttype', 1);
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('bedrijfsnaam'));
		}

		public function testCompanyNameTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('bedrijfsnaam', '1234567890123456789012345678901234567890123456789012345678901');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('bedrijfsnaam'));
		}

		public function testAanhefBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('aanhef', '');
			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors('aanhef'));
		}

		public function testAanhefTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('aanhef', '123456789012345678901');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('aanhef'));
		}

		public function testVoornaamBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('voornaam', '');
			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors('voornaam'));			
		}

		public function testVoornaamTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('voornaam', '1234567890123456789012345678901');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('voornaam'));			
		}

		public function testAchternaamBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('achternaam', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('achternaam'));			
		}

		public function testAchternaamTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('achternaam', '1234567890123456789012345678901');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('achternaam'));			
		}

		public function testFactuuradresBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('factuuradres', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('factuuradres'));			
		}

		public function testFactuurpostcodeBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('factuurpostcode', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('factuurpostcode'));			
		}

		public function testFactuurplaatsBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('factuurplaats', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('factuurplaats'));			
		}

		public function testBezoekadresTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('bezoekadres', '0123456789012345678901234567890123456789012345678901234567891');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('bezoekadres'));			
		}

		public function testBezoekpostcodeTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('bezoekpostcode', '12345678901');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('bezoekpostcode'));			
		}

		public function testBezoekadresBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('bezoekadres', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('bezoekadres'));			
		}

		public function testBezoekpostcodeBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('bezoekpostcode', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('bezoekpostcode'));			
		}

		public function testBezoekplaatsBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('bezoekplaats', '');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('bezoekplaats'));			
		}

		public function testFactuuradresTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('factuuradres', '0123456789012345678901234567890123456789012345678901234567891');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('factuuradres'));			
		}

		public function testFactuurpostcodeTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('factuurpostcode', '12345678901');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('factuurpostcode'));			
		}

		public function testFactuurplaatsTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('factuurplaats', '0123456789012345678901234567890123456789012345678901234567891');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('factuurplaats'));			
		}

		public function testActiefOutOfBounds() {
			$errors = $this->getErrorsForSingleFieldMutation('actief', -1);
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('actief'));			

			$errors = $this->getErrorsForSingleFieldMutation('actief', 3);
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('actief'));
		}

		public function testKlanttypeOutOfBounds() {
			$errors = $this->getErrorsForSingleFieldMutation('klanttype', -1);
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('klanttype'));			

			$errors = $this->getErrorsForSingleFieldMutation('klanttype', 3);
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('klanttype'));
		}

		public function testEmailadresBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('emailadres', '');
			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors('emailadres'));			
		}

		public function testEmailadresInvalid() {
			$errors = $this->getErrorsForSingleFieldMutation('emailadres', 'Geen emailadres');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('emailadres'));			
		}
		
		public function testEmailadresTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('emailadres', '0123456789012345678901234567890123456789012345678901234567891');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('emailadres'));			
		}

		public function testWebsiteBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('website', '');
			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors('website'));
		}

		public function testWebsiteTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('website', '0123456789012345678901234567890123456789012345678901234567891');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('website'));			
		}

		public function testTelefoonvastTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('telefoonvast', '0411-6759991');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('telefoonvast'));			
		}

		public function testTelefoonmobielTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('telefoonmobiel', '0411-6759991');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('telefoonmobiel'));			
		}

		public function testTelefoonvastBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('telefoonvast', '');
			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors('telefoonvast'));			
		}

		public function testTelefoonmobielBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('telefoonmobiel', '');
			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors('telefoonmobiel'));			
		}

		public function testFactuurtemplateBlank() {
			$errors = $this->getErrorsForSingleFieldMutation('factuurtemplate', '');
			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors('factuurtemplate'));	
		}

		public function testFactuurtemplateInvalid() {
			$errors = $this->getErrorsForSingleFieldMutation('factuurtemplate', 'generic_invalid.tex');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('factuurtemplate'));	
		}

		public function testFactuurtemplateValid() {
			$errors = $this->getErrorsForSingleFieldMutation('factuurtemplate', 'generic_nl.tex');			
			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors('factuurtemplate'));	
		}

		public function testFactuurtemplateTooLong() {
			$errors = $this->getErrorsForSingleFieldMutation('factuurtemplate',
				 '01234567890123456789012345678901234567890123456789012345678901234567890123456789');
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors('factuurtemplate'));	
		}

		public function testCorrect() {
			$errors = new CT_Validation_Errors();
			$validator = new CT_Validation_Validator_Klant();

			$klant1 = $this->validCustomer;

			$validator->validate($klant1, $errors);
			$this->assertFalse($errors->hasErrors());
		}
	}
?>
