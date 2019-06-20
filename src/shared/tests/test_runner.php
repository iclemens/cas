<?php
	require_once 'utility/setup.php';
	require_once 'PHPUnit/Util/Filter.php';

	define("PHPUnit_MAIN_METHOD", "True");

	require_once "AllTests.php";

	/*PHPUnit_Util_Filter::addDirectoryToFilter(getcwd());
	PHPUnit_Util_Filter::addDirectoryToFilter(getcwd() . '/../library/Zend');
	PHPUnit_Util_Filter::addDirectoryToFilter(getcwd() . '/../templates/compiled');*/

	$args    = array("reportDirectory" => "../htdocs/tests/coverage");

	$runner = new PHPUnit_TextUI_TestRunner;
	$suite  = Base_AllTests::suite();

	set_time_limit(0);
	$result = $runner->doRun($suite, $args);
?>
