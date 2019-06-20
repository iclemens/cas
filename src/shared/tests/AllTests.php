<?php
	require_once 'utility/setup.php';

	if (!defined('PHPUnit_MAIN_METHOD')) {
		define('PHPUnit_MAIN_METHOD', 'Base_AllTests::main');
	}
 
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';

	require_once 'Controllers/AllTests.php';
	require_once 'CT/AllTests.php';
	//require_once 'Zend/Zend/AllTests.php';

	class Base_AllTests
	{
		public static function main()
		{
			PHPUnit_TextUI_TestRunner::run(self::suite());
		}

		public static function suite()
		{
			$suite = new PHPUnit_Framework_TestSuite('Base');

			$suite->addTestSuite(Controllers_AllTests::suite());
			$suite->addTestSuite(CT_AllTests::suite());
			//$suite->addTestSuite(Zend_AllTests::suite());
 
			return $suite;
		}
	}
 
	if (PHPUnit_MAIN_METHOD == 'Base_AllTests::main') {
		Base_AllTests::main();
	}
?>
