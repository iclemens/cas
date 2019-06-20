<?php
	@include_once '../utility/setup.php';

	if (!defined('PHPUnit_MAIN_METHOD')) {
		define('PHPUnit_MAIN_METHOD', 'CT_AllTests::main');
	}
 
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';

	require_once 'CT/Formatting/KuminaTest.php';
 
	class CT_Formatting_AllTests
	{
  		public static function main()
		{
			PHPUnit_TextUI_TestRunner::run(self::suite());
		}
 
		public static function suite()
		{
			$suite = new PHPUnit_Framework_TestSuite('CT_Formatting');
 				
			$suite->addTestSuite('CT_Formatting_KuminaTest');
			return $suite;
		}
}
 
	if (PHPUnit_MAIN_METHOD == 'CT_AllTests::main') {
		CT_Formatting_AllTests::main();
	}

