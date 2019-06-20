<?php
	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "CT/Validation/Errors.php";

	class CT_Validation_ErrorsTest extends PHPUnit_Framework_TestCase
	{
		public function testFieldError() {
			$errors = new CT_Validation_Errors();

			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors("Field"));
			$this->assertFalse($errors->hasFieldErrors("Other"));
			$this->assertFalse($errors->hasGlobalErrors());

			$errors->rejectValue("Field", "FieldError");
			
			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasFieldErrors("Field"));
			$this->assertFalse($errors->hasFieldErrors("Other"));
			$this->assertFalse($errors->hasGlobalErrors());

			$this->assertContains("FieldError", $errors->getErrors());
			$this->assertContains("FieldError", $errors->getFieldErrors("Field"));
			$this->assertNotContains("FieldError", $errors->getFieldErrors("Other"));
			$this->assertNotContains("FieldError", $errors->getGlobalErrors());
		}

		public function testGlobalError() {
			$errors = new CT_Validation_Errors();

			$this->assertFalse($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors("Field"));
			$this->assertFalse($errors->hasFieldErrors("Other"));
			$this->assertFalse($errors->hasGlobalErrors());

			$errors->reject("GlobalError");
			
			$this->assertTrue($errors->hasErrors());
			$this->assertFalse($errors->hasFieldErrors("Field"));
			$this->assertFalse($errors->hasFieldErrors("Other"));
			$this->assertTrue($errors->hasGlobalErrors());

			$this->assertContains("GlobalError", $errors->getErrors());
			$this->assertContains("GlobalError", $errors->getGlobalErrors());
			$this->assertNotContains("GlobalError", $errors->getFieldErrors("Field"));
		}

		public function testMultipleErrors() {
			$errors = new CT_Validation_Errors();
			$errors->reject("GlobalError1");
			$errors->reject("GlobalError2");
			$errors->reject("GlobalError3");

			$errors->rejectValue("Field1", "Field1Error1");
			$errors->rejectValue("Field1", "Field1Error2");
			$errors->rejectValue("Field2", "Field2Error1");

			$this->assertTrue($errors->hasErrors());
			$this->assertTrue($errors->hasGlobalErrors());
			$this->assertTrue($errors->hasFieldErrors("Field1"));
			$this->assertTrue($errors->hasFieldErrors("Field2"));
			$this->assertFalse($errors->hasFieldErrors("Field3"));

			$this->assertContains("GlobalError1", $errors->getErrors());
			$this->assertContains("GlobalError2", $errors->getErrors());
			$this->assertContains("GlobalError3", $errors->getErrors());
			$this->assertNotContains("GlobalError4", $errors->getErrors());

			$this->assertContains("Field1Error1", $errors->getFieldErrors("Field1"));
			$this->assertContains("Field1Error2", $errors->getFieldErrors("Field1"));
			$this->assertContains("Field2Error1", $errors->getFieldErrors("Field2"));

			$this->assertNotContains("Field2Error1", $errors->getFieldErrors("Field1"));
			$this->assertNotContains("Field1Error1", $errors->getFieldErrors("Field2"));
		}
	}
?>