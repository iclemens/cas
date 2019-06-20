<?php
	if (!defined('PHPUnit_MAIN_METHOD')) {
  	define('PHPUnit_MAIN_METHOD', 'CT_iDEAL_AllTests::main');
	}
 
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';

	require_once 'CT/iDEAL/ResultTest.php';

	class CT_iDEAL_AllTests
	{
  	public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('CT iDEAL');
 
        $suite->addTestSuite('CT_iDEAL_ResultTest');
 
        return $suite;
    }
	}
 
	if (PHPUnit_MAIN_METHOD == 'Framework_AllTests::main') {
  	CT_iDEAL_AllTests::main();
	}
?>