<?php
	@include_once '../utility/setup.php';

	if (!defined('PHPUnit_MAIN_METHOD')) {
		define('PHPUnit_MAIN_METHOD', 'CT_AllTests::main');
	}
 
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';
 
	require_once 'CT/UserTest.php';

	require_once 'CT/Validation/AllTests.php';
	require_once 'CT/iDEAL/AllTests.php';
	require_once 'CT/Db/AllTests.php';

	require_once 'CT/Formatting/AllTests.php';

	require_once 'CT/UtilityTest.php';
 
	class CT_AllTests
	{
		public static function main()
		{
			PHPUnit_TextUI_TestRunner::run(self::suite());
		}

		public static function suite()
		{
			$suite = new PHPUnit_Framework_TestSuite('CT');

			$suite->addTestSuite('CT_UserTest');
			$suite->addTestSuite('CT_UtilityTest');
			$suite->addTestSuite(CT_Validation_AllTests::suite());
			$suite->addTestSuite(CT_iDEAL_AllTests::suite());
			$suite->addTestSuite(CT_Db_AllTests::suite());
			$suite->addTestSuite(CT_Formatting_AllTests::suite());

			return $suite;
		}
	}
 
	if (PHPUnit_MAIN_METHOD == 'CT_AllTests::main') {
		CT_AllTests::main();
	}
