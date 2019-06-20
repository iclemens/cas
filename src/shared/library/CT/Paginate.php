<?php
	/**
	 * Pagination, splits large tables and provides information about the amount of
	 * pages left.
	 *
	 * @author	Ivar Clemens <post@ivarclemens.nl>
	 * @copyright	2008 Ivar Clemens
	 * @package	boekhouding
	 */
	
	/**
	 * Data-source independent pagination support.
	 *
	 * @package		boekhouding
	 */
	abstract class CT_Paginate
	{

		/**
		 * The number of the current page.
		 * @var integer
		 */
		var $currentPage = 1;
	
		/**
		 * The amount of records to be displayed on a single page.
		 * @var integer
		 */
		var $range = 25;
	
		/**
		 * Estimate of the total number of records.
		 * @var integer
		 */
		var $recordCount = -1;
	
		/**
		 * Constructor
		 */
		function __construct()
		{
		}

		/**
		 * Calculates the estimated number of pages. If more pages are present,
		 * this function returns at least $currentPage + 1, otherwise $currentPage
		 * is returned. There must be at least one page, thus if no data is present,
		 * this function will return 1. 
		 *
		 * @return integer The estimated number of records, or -1 if unknown.
		 */
		function numberOfPages()
		{
			if($this->recordCount == -1)
				return -1;

			if($this->recordCount == 0)
				return 1;

			return ceil($this->recordCount / $this->range);
		}

		/**
		 * Checks whether there are any pages before the current one.
		 *
		 * @return boolean
		 */
		function hasPrevious()
		{
			if($this->currentPage > 1)
				return true;
			return false;
		}

		/**
		 * Checks whether there are more pages after the current one.
		 *
		 * @return boolean
		 */
		function hasNext()
		{
			$numPages = $this->numberOfPages();
			
			if($numPages == -1 || $this->currentPage < $numPages)
				return true;
				
			return false;
		}
	
		/**
		 * Checks whether a given page is present in the set.
		 *
		 * @param $page Number of the page to check for
		 * @return boolean
		 */
		function hasPage($page)
		{
			if($page < 1)
				return false;
				
			//if($page == 1)
			//	return true;
				
			if($page > $this->numberOfPages()) {
				// FIXME: Check whether the page is out of bounds, number of records
				// might be inaccurate
				//throw new Exception("Page out of bounds");
			}
			
			// ASSUME! the page is present...
			return true;
		}

		/**
		 * Moves the page pointer to the first page in the set
		 */
		function firstPage()
		{
			$this->currentPage = 1;
		}

		/**
		 * Moves the page pointer to the previous page, if available.
		 *
		 * @throws Exception In case there is no previous page
		 */
		function previousPage()
		{
			if(!$this->hasPrevious())
				throw new Exception("Page out of bounds");
				
			$this->currentPage--;
		}

		/**
		 * Moves the page pointer to the next page, if available.
		 *
		 * @throws Exception In case there is no next page
		 */
		function nextPage()
		{
			if(!$this->hasNext())
				throw new Exception("Page out of bounds");
			
			$this->currentPage++;			
		}

		/**
		 * Moves the page pointer to the last page
		 */
		function lastPage()
		{
			$this->currentPage = $this->numberOfPages();
		}

		/**
		 * Changes the current page
		 *
		 * @throws Exception When the page specfied is out of bounds
		 *
		 * @param int $page The number of the requested page
		 */
		function goto($page)
		{
			if(!$this->hasPage($page))
				throw new Exception("Page out of bounds");
							
			$this->currentPage = $page;
		}
		
		/**
		 * Retreives the current page-number
		 *
		 * @return integer Number of the current page
		 */
		function pageNumber()
		{
			return $this->currentPage;
		}

	};
