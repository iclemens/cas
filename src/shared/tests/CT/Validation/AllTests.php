<?php
	if (!defined('PHPUnit_MAIN_METHOD')) {
  	define('PHPUnit_MAIN_METHOD', 'CT_Validation_AllTests::main');
	}
 
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';

	require_once 'CT/Validation/ValidatorTest.php';
	require_once 'CT/Validation/ErrorsTest.php';

	require_once 'CT/Validation/Validator/AllTests.php';

	class CT_Validation_AllTests
	{
  	public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('CT Validation');
 
        $suite->addTestSuite('CT_Validation_ErrorsTest');
				$suite->addTestSuite('CT_Validation_ValidatorTest');

				$suite->addTestSuite(CT_Validation_Validator_AllTests::suite());
 
        return $suite;
    }
	}
 
	if (PHPUnit_MAIN_METHOD == 'Framework_AllTests::main') {
  	CT_Validation_AllTests::main();
	}
?>