<?php
	if (!defined('PHPUnit_MAIN_METHOD')) {
  	define('PHPUnit_MAIN_METHOD', 'CT_Validation_Validator_AllTests::main');
	}
 
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';

	require_once 'CT/Validation/Validator/KlantTest.php';
	require_once 'CT/Validation/Validator/FactuurTest.php';

	class CT_Validation_Validator_AllTests
	{
  	public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('CT Validation Validator');
 
        $suite->addTestSuite('CT_Validation_Validator_KlantTest');
				$suite->addTestSuite('CT_Validation_Validator_FactuurTest');
 
        return $suite;
    }
	}
 
	if (PHPUnit_MAIN_METHOD == 'Framework_AllTests::main') {
  	CT_Validation_AllTests::main();
	}
?>