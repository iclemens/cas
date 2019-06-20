<?php
	/**
	 * Sorts the columns of a table.
	 *
	 * @author	Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package	boekhouding
	 */
	
	/**
	 * Data-source independent sorting support.
	 *
	 * @package		boekhouding
	 */
	abstract class CT_Sort
	{
		var $_sort;
	
		function __construct($sort)
		{
			$this->_sort = array();
			$this->orderFromString($sort);
		}

		private function getStringFromArray($sortArray)
		{
			$str = '';

			foreach($sortArray as $key) {
				if($key == '')
					continue;
					
				if($str == '')
					$str .= $key;
				else
					$str = $str . ',' . $key;
			}		

			return $str;
		}
		
		function clearOrder()
		{
			$_sort = array();
		}

		function getString()
		{
			return $this->getStringFromArray($this->_sort);
		}

		function getStringWith($string)
		{
			$tmp = array_diff($this->_sort, explode(',', $string));
			$tmp = array_merge(array($string), $tmp);

			return $this->getStringFromArray($tmp);
		}
		
		function orderFromString($string)		
		{
			$order = explode(',', $string);
			$this->mergeOrder($order);
		}
		
		function mergeOrder($order)
		{
			$this->_sort = array_merge($this->_sort, $order);
		}
	};
