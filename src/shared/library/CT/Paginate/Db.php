<?php
	/**
	 * Paginates a database-table in a supposedly scalable manner.
	 *
	 * @author	Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package	boekhouding
	 */
	 
	Zend_Loader::loadClass('CT_Paginate');

	/**
	 * Paginate the results of a database query.
	 */
	class CT_Paginate_Db extends CT_Paginate
	{
		const mdGuess = 0;
		const mdCount = 1;
		const mdFetch = 2;

		var $_mode = CT_Paginate_Db::mdFetch;

		/**
		 * Initializes the database-table paginator.
		 *
		 * @param Zend_Db $database Database provider
		 * @param integer $page The initial (current) page
		 * @param integer $range The number of results on the page
		 */
		function __construct($database, $page = 1, $range = 25)
		{
			$this->db = $database;
			
			if($page < 1)
				$this->currentPage = 1;
			else
				$this->currentPage = $page;
			
			if($range < 1)
				$this->range = 15;
			else
				$this->range = $range;					
		}
	
		/**
		 * Extends a Zend_Db_Select object with the limit clause required to
		 * focus the database onto the currently selected page.
		 * Note: This function requests one record more than required in order
		 * to estimate the number of records in the database.
		 *
		 * @param Zend_Db_Select $query Query to extend
		 */
		protected function extendQuery($query)
		{
			if($this->_mode == CT_Paginate_Db::mdFetch)
				return $query; 
			
			return $query->limit($this->range + 1, ($this->currentPage - 1) * $this->range);
		}
		
		/**
		 * Retreives the records on the current page.
		 *
		 * @throws Exception In case the page is out of range
		 *
		 * @param Zend_Db_Select $query Query to extend
		 */
		function getRecords($query)
		{
			$query = $this->extendQuery($query);

			$queryStr = (string) $query;

			if($this->_mode == CT_Paginate_Db::mdCount) {			
				$queryStr = 'SELECT SQL_CALC_FOUND_ROWS' . substr($queryStr, 6);
			}

			$stmt = $this->db->query($queryStr);
			
			$records = $stmt->fetchAll();

			if($this->_mode == CT_Paginate_Db::mdFetch) {
				$this->recordCount = count($records);

				return array_slice($records, ($this->currentPage - 1) * $this->range, $this->range);
			}

			if($this->_mode == CT_Paginate_Db::mdCount) {
				$query = $this->db->select()->from('', 'FOUND_ROWS() AS recordcount');

				$stmt = $this->db->query($query);

				$recordCount = array_pop($stmt->fetchAll());
				$this->recordCount = $recordCount['recordcount'];

				return $records;
			}

			// Estimate the amount of records in the database.
			// Note: A more precise way is to use a COUNT(*) statement.
			// Future version of this class might implement such statement.
			$this->recordCount = ($this->currentPage - 1) * $this->range + count($records);

			//if(count($records) == 0)
				//throw new Exception("Page out of bounds");

			// Remove the extra record...
			if(count($records) > $this->range)
				array_pop($records);

			return $records;
		}
	};
