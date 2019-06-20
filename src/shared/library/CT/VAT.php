<?php
	/**
	 * Uitlezen van belasting tarieven, Project CAS
	 *
	 * @author Ivar Clemens <post@ivarclemens.nl>
	 * @package CT_VAT
	 */

	Zend_Loader::loadClass('Zend_Registry');

	/**
	 * Uitlezen van belasting tarieven, Project CAS
	 *
	 * @package CT_VAT
	 */
	class CT_VAT
	{
		/**
		 * SimpleXML object containing information about VAT
		 *
		 * @var SimpleXMLElement
		 */
		static private $_xml = null;

		/**
		 * Returns the SimpleXML VAT object
		 *
		 * @return SimpleXMLElement
		 */
		static private function getXML()
		{
			if(CT_VAT::$_xml == null) {

				$config = Zend_Registry::get('config');			

				$xmlFile = getResourceLocation('config/vat.xml');

				CT_VAT::$_xml = new SimpleXMLElement(file_get_contents($xmlFile));
			}

			return CT_VAT::$_xml;
		}

		/**
		 * Returns the default VAT category
		 *
		 * @return int The default VAT category
		 */
		static public function getDefaultVATCategory()
		{
			$xml = CT_VAT::getXML();

			return strtolower(strval($xml['default']));
		}

		/**
		 * Returns a list of available VAT categories
		 *
		 * @return array A list of VAT categories
		 */
		static public function getVATCategories()
		{
			$xml = CT_VAT::getXML();
			$categories = array();

			foreach($xml->category as $category) {
				$id = strtolower(strval($category['id']));
				$rate = strval($category['rate']);

				$categories[$id] = array("rate" => $rate, "description" => strval($category));
			}

			return $categories;
		}

		/**
		 * Returns information about a VAT category given its ID
		 *
		 * @param string $id The VAT id
		 *
		 * @return array An array containing VAT data
		 */
		static public function getVATById($id)
		{
			$xml = CT_VAT::getXML();
			$categories = array();

			foreach($xml->category as $category) {
				$local_id = strtolower(strval($category['id']));
                $rate = strval($category['rate']);

				if($local_id == $id) {
					return array("rate" => $rate, "description" => strval($category));
				}
			}

			return CT_VAT::getVATById(CT_VAT::getDefaultVATCategory());
		}
	}

