<?php
	@include_once '../utility/setup.php';

	if (!defined('PHPUnit_MAIN_METHOD')) {
		define('PHPUnit_MAIN_METHOD', 'Controllers_AllTests::main');
	}
 
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';
	require_once 'FacturerenTest.php';
	require_once 'FactuurTest.php';
	require_once 'KlantenTest.php';
 
	class Controllers_AllTests
	{
  	public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Controllers');

				$suite->addTestSuite('Controllers_FactuurTest');
				$suite->addTestSuite('Controllers_KlantenTest');
 				$suite->addTestSuite('Controllers_FacturerenTest');
 
        return $suite;
    }
}
 
	if (PHPUnit_MAIN_METHOD == 'Controllers_AllTests::main') {
    Controllers_AllTests::main();
	}
