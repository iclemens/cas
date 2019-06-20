<?php
	/**
	 * Sorts a database-table.
	 *
	 * @author	Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package	boekhouding
	 */
	 
	Zend_Loader::loadClass('CT_Sort');

	/**
	 * Sort the results of a database query.
	 */
	class CT_Sort_Db extends CT_Sort
	{
		/**
		 * Initializes the database-table sorter.
		 *
		 * @param Zend_Db $database Database provider
		 */
		function __construct($database, $sort)
		{
			$this->db = $database;

			parent::__construct($sort);
		}
	
		/**
		 * Extends a Zend_Db_Select object with the order clause required to
		 * sort the database columns in the correct order.
		 *
		 * @param Zend_Db_Select $query Query to extend
		 */
		public function extendQuery($query)
		{
			foreach($this->_sort as $field) {
				$query = $query->order($field);
			}

			return $query;
		}

	};
