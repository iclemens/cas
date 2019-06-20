<?php
	if (!defined('PHPUnit_MAIN_METHOD')) {
  	define('PHPUnit_MAIN_METHOD', 'CT_Db_AllTests::main');
	}
 
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';

	require_once 'CT/Db/FacturenTest.php';
	require_once 'CT/Db/PeriodiekeregelsTest.php';

 	class CT_Db_AllTests
	{
  	public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('CT Db');
 
        $suite->addTestSuite('CT_Db_PeriodiekeregelsTest');
        $suite->addTestSuite('CT_Db_FacturenTest');
 
        return $suite;
    }
	}
 
	if (PHPUnit_MAIN_METHOD == 'CT_Db_AllTests::main') {
  	CT_Db_AllTests::main();
	}
?>