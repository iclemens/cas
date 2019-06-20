<?php
	@include_once '../utility/setup.php';

	/**
	 * @package boekhouding
	 * @subpackage UnitTests
	 */

	require_once "utility.php";

	class CT_UtilityTest extends CT_TestCase
	{
		protected function setUp() 
		{
		}

		protected function tearDown() 
		{
		}

		public function testWrapLine()
		{
			$output =  wrapLine('Test line', 9);
			$this->assertEquals($output[0], 'Test line');

			$output =  wrapLine('Test line', 4);
			$this->assertEquals($output[0], 'Test');
			$this->assertEquals($output[1], 'line');

			$output =  wrapLine('Test  lin', 4);
			$this->assertEquals($output[0], 'Test');
			$this->assertEquals($output[1], 'lin');

			$output =  wrapLine('Test  line', 4);
			$this->assertEquals($output[0], 'Test');
			$this->assertEquals($output[1], 'line');

			$output =  wrapLine('Test lines', 4);
			$this->assertEquals($output[0], 'Test');
			$this->assertEquals($output[1], 'line');
			$this->assertEquals($output[2], 's');

			$output =  wrapLine('This_is_a_very_long_line', 4);
			$this->assertEquals($output[0], 'This');
			$this->assertEquals($output[1], '_is_');
			$this->assertEquals($output[2], 'a_ve');

			$output =  wrapLine('', 4);
			$this->assertEquals($output[0], '');
		}

		public function wrapMultiline()
		{
			$output = wrapMultiLine("Test line\nTest line", 9);
			$this->assertEquals($output, "Test line\nTest line");
		}

		public function testRelativeMonth()
		{
			$rel_to = '2008-05-07';

			$this->assertEquals(relativeMonth('2006-12-01', $rel_to), -13);
			$this->assertEquals(relativeMonth('2007-01-01', $rel_to), -12);
			$this->assertEquals(relativeMonth('2007-12-01', $rel_to), -1);
			$this->assertEquals(relativeMonth('2008-01-01', $rel_to), 0);
			$this->assertEquals(relativeMonth('2008-12-01', $rel_to), 11);
			$this->assertEquals(relativeMonth('2009-12-01', $rel_to), 23);
		}

		public function testYearFromRelMonth()
		{
			$rel_to = '2008-05-07';

			$this->assertEquals(yearFromRelativeMonth(-13, $rel_to), 2006);
			$this->assertEquals(yearFromRelativeMonth(-12, $rel_to), 2007);
			$this->assertEquals(yearFromRelativeMonth(-1, $rel_to), 2007);
			$this->assertEquals(yearFromRelativeMonth(0, $rel_to), 2008);
			$this->assertEquals(yearFromRelativeMonth(23, $rel_to), 2009);
		}		

		public function testMonthFromRelMonth()
		{
			$rel_to = '2008-05-07';

			$this->assertEquals(monthFromRelativeMonth(-13), 12);
			$this->assertEquals(monthFromRelativeMonth(-12), 1);
			$this->assertEquals(monthFromRelativeMonth(0), 1);
			$this->assertEquals(monthFromRelativeMonth(11), 12);
			$this->assertEquals(monthFromRelativeMonth(23), 12);
		}
	}

