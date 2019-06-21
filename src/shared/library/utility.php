<?php
	/**
	 * ctype_digit
	 * De functie ctype_digit is niet op elke php installatie beschikbaar.
	 * Daarom volgt hier een alternatieve implementatie.
	 *
	 * @digit Het karakter dat getest moet worden
	 * @return True als @digit een cijfer is, anders false
	 */
	if(!function_exists('ctype_digit'))
	{
		function ctype_digit($digit) 
		{
			return is_numeric($digit);
		}
	}

	/**
	 * Generates a random string.
	 *
	 * @param int $length Length (in characters) of the random string to generate
	 */
	function generateRandomString($length)
	{
		/**
		 * The list of characters used in construction of the string 
		 */
		$characterList = 'pyfgcrlaoeuidhtnsqjkxbmwvzPYFGCRLAOEUIDHTNSQJKXBMWVZ0123456789';
		$randomString = '';

		for($i = 0; $i < $length; $i++) 
			$randomString .= substr($characterList, mt_rand(0, strlen($characterList)), 1);			
	
		return $randomString;
	}


	/**
	 *
	 */
	function getValueFromArray(&$source, $field, $default = '')
	{
		if(!is_array($source))
			return $default;

		if(array_key_exists($field, $source))
			return $source[$field];
		return $default;
	}


	/**
	 * Returns the absolute location of a file or directory.
	 *
	 * If the resource location is not already absolute, we'll try
	 * and find it in the private $dataPath, otherwise the global
	 * $globalDataPath is probed.
	 *
	 * @param string $resource Name of the resource to be located
	 * @param bool $search_global Search for the resource in the global path.
	 *
	 * @return string Absolute location of the resource
	 */
	function getResourceLocation($resource, $search_global = true)
	{
		global $dataPath, $globalDataPath;

		// The location is already absolute
		if($resource{0} == '/') 
			return $resource;

		// Check private location
		$privateFile = joinDirStrings($dataPath, $resource);

		if(file_exists($privateFile))
			return $privateFile;

		if(!$search_global) 
			throw new Exception("Resource '" . $resource . "' not found in local setup");

		// Check global location
		$publicFile = joinDirStrings($globalDataPath, $resource);

		if(file_exists($publicFile))
			return $publicFile;

		throw new Exception("Resource '" . $resource . "' not found");
	}

	/**
	 * Returns the temporary directory defined in the config file
	 *
	 * @return string Temporary directory
	 */
	function getTemporaryDirectory()
	{
		$config = Zend_Registry::get('config');

		return getResourceLocation($config->temp_dir, false);
	}

	/**
	 * Creates a temporary directory.
	 *
	 * @param string $prefix A prefix for the temporary directory.
	 * @param string $tmpdir The name of the temporary directory to use.
	 * 			 If not specified, the system default will be used.
	 *
	 * @return string The name of the newly created directory, or false on failure
	 */
	function createTemporaryDirectory($prefix, $tmpdir)
	{
		if(strlen($tmpdir) <= 0)
			$tmpdir = getTemporaryDirectory();

		$tries = 0;

		/**
		 *  The random directory name should be unique the
		 *	first time, but we'll repeat the procedure a
		 *  couple of times just in case. A finite amount
		 *  of tries is specified to avoid infinite loops 
		 */
		while($tries < 10) {
			$randomDirname = $tmpdir . '/' . $prefix . generateRandomString(5);

			if(!file_exists($randomDirname)) {
				if(mkdir($randomDirname, 0700) == true)
					return $randomDirname;
			}
		}

		/* This should not happen, but you'll never know! */
		return false;
	}

	/**
	 * Stitches two directory names together
	 */
	function joinDirStrings($dir1, $dir2)
	{
		$separator = '/';

		if(substr($dir1, strlen($dir1) - 1, 1) == $separator)
			$dir1 = substr($dir1, 0, strlen($dir1) - 1);

		if(substr($dir2, 0, 1) == $separator)
			$dir2 = substr($dir2, 1);

		return $dir1 . $separator . $dir2;
	}

	/**
	 * Stitches multiple directory names together
	 */
	function joinDirStringArray($strings) 
	{
		$path = '';		

		foreach($strings as $string) {
			if(strlen($path) > 0) {
				$path = joinDirStrings($path, $string);
			} else {
				$path = $string;
			}
		}

		return $path;
	}


	function isBlankLine($line)
	{
		for($i = 0; $i < mb_strlen($line); $i++) {
			$char = mb_substr($line, $i, 1);

			if($char != ' ' && $char != '\n' && $char != '\t')
				return false;				
		}

		return true;
	}

	/**
	 * Wordwraps a single line
	 *
	 * @param string $line Line to wrap
	 * @param int $length Amount of characters per line
	 *
	 * @return array Array containing lines of max. $length length
	 */
	function wrapLine($line, $length)
	{
		if($length == null)
			$length = 75;

		$output = array();

		$lastSpace = -1;
		$lastChop = -1;

		for($i = 0; $i < mb_strlen($line); $i++) {
			$char = mb_substr($line, $i, 1);

			if($i - $lastChop > $length) {
				if($lastSpace == -1 || $lastSpace == $lastChop) {
					$currentLine = mb_substr($line, $lastChop + 1, $i - $lastChop - 1);
					$lastChop = $i - 1;

					if(!isBlankLine($currentLine))
						$output[] = $currentLine;
				} else {
					$currentLine = mb_substr($line, $lastChop + 1, $lastSpace - $lastChop - 1);
					$lastChop = $lastSpace;

					if(!isBlankLine($currentLine))
						$output[] = $currentLine;

					$lastSpace = -1;
				}
			}

			if($char == ' ' || $char == '\t' || $char == '\n')
				$lastSpace = $i;
		}

		$output[] = mb_substr($line, $lastChop + 1);

		return $output;
	}

	/**
	 * Wordwraps a piece of text on a line-by-line basis
	 *
	 */
	function wrapMultiline($text, $length)
	{
		if($length == null)
			$length = 75;

		$wrapped_lines = array();
		$lines = explode("\n", $text);

		foreach($lines as $line) {
			$wrapped = wrapLine($line, $length);
			
			$wrapped_lines = array_merge($wrapped_lines, $wrapped);
		}

		return implode("\n", $wrapped_lines);
	}

	/**
	 * Returns the number of the month, given a relative number
	 * of months calculated by the relativeMonth function.
	 */
	function monthFromRelativeMonth($rel_month)
	{
		return ($rel_month % 12 + (($rel_month <= 0) ? 12 : 0)) % 12 + 1;
	}

	/**
	 * Returns the number of the years, given a relative number
	 * of months calculated by the relativeMonth function.
	 */
	function yearFromRelativeMonth($rel_month, $rel_to = NULL)
	{
		if($rel_to == NULL)
			$year = date('Y');
		else
			$year = yearFromString($rel_to);

		return $year + floor($rel_month / 12);
	}

	/**
	 * Returns the month part of the date string as an integer.
	 *
	 * @param string $date Datestring
	 * @return int Month		 
	 */
	function monthFromString($date)
	{
		return intval(date('n', strtotime($date)));
	}

	/**
	 * Returns the year part of the date string as an integer.
	 *
	 * @param string $date Datestring
	 * @return int Year		 
	 */
	function yearFromString($date)
	{
		return intval(date('Y', strtotime($date)));
	}

	/**
	 * Returns the number of months relative to January of the
	 * current year.
	 *
	 * Examples:
	 * Jan PREVYEAR    -12
	 * Dec PREVYEAR	   -1
	 * Jan CURYEAR 		0
	 * Dec CURYEAR		11
	 * Jan NEXTYEAR		12
	 */
	function relativeMonth($date, $rel_to = NULL)
	{
		if($rel_to == NULL)
			$rel_to = date('Y/m/01');

		return (monthFromString($date) - 1) - 
			(yearFromString($rel_to) - yearFromString($date)) * 12;
	}

	/**
	 * Searches for smarty in some of the most probable locations
	 *
	 * @throws Exception In case smarty could not be located
	 *
	 * @return String Location of smarty 
	 */
	function getSmartyLocation()
	{
		global $dataPath, $globalDataPath;

		$config = Zend_Registry::get('config');

		$probableLocations = array();

		$libaries = $config->libraries;

		if(isset($libraries)) {
			$probableLocations[] = $libraries->smarty;
		}

		$probableLocations[] = '/usr/share/php/smarty';	// Default on gentoo, maybe others
		$probableLocations[] = '/usr/share/php/smarty/libs';	// Default on debian
		$probableLocations[] = $globalDataPath . '/library/Smarty';		// Local backup copy

		foreach($probableLocations as $location) {
			if(file_exists(joinDirStrings($location, 'Smarty.class.php')))
				return $location;
		}

		$suggestedPath = realpath($globalDataPath) . '/library/Smarty';

		throw new Exception('Kan "smarty" niet vinden, plaats smarty in de volgende directory op de webserver: ' . $suggestedPath);
	}

	/**
	 * Determines which resources (e.g. a class, a file) are 
	 * available for the user to choose from. 
	 *
	 * @param string $dirName Name of the directory containing the resource
	 * @param array $ignoreList List of items to ignore (e.g. Abstract classes)
	 * @param string $suffix What kind of resource to locate (.php for classes)
	 * @return List of resources
	 */
	function getResourceList($dirName, $ignoreList, $suffix = '.php')
	{
		$options = array();	

		$dir = opendir($dirName);

		while($filename = readdir($dir)) {
			if(substr($filename, -strlen($suffix)) != $suffix)
				continue;

			$name = substr($filename, 0, -strlen($suffix));
			
			if(in_array($name, $ignoreList))
				continue;
			
			$options[$name] = $name;
		}
	
		closedir($dir);
		
		return $options;
	}	
	
	/**
	 * Converteert een prijs (in centen) naar een string met komma.
	 *
	 * @prijs De prijs in centen
	 * @return De prijs met euroteken en komma
	 */
	function fmt_prijs($params) {
		$s_prijs = '';
		$sign = '';
		$prijs = round($params['prijs']);
		
		if($prijs < 0) {
			$prijs = abs($prijs);
			$sign = '-';
		}

		$i = 0;
		while($prijs > 0) {
			$digit = $prijs % 10;
			$prijs = floor($prijs / 10);

			if($i == 2)
				$s_prijs = ',' . $s_prijs;
			if($i > 2 && ($i - 2) % 3 == 0)
				$s_prijs = '.' . $s_prijs;

			$s_prijs = $digit . $s_prijs;
			$i++;
		}

		if($i == 0)
			$s_prijs = '0' . $s_prijs;
		if($i <= 1)
			$s_prijs = '0' . $s_prijs;
		if($i <= 2)
			$s_prijs = "0," . $s_prijs;

		if(array_key_exists('eurosign', $params))
			return $params['eurosign'] . ' ' . $sign . $s_prijs;
		else
			return "\xe2\x82\xac " . $sign . $s_prijs;
	}

	function fmt_klantnaam($params) {
		$klant = $params['klant'];

		if(!is_array($params['klant'])) {
			return 'Ongeldige klantnaam';
		}

		if($klant['klanttype'] == 0)
			$out .= $klant['bedrijfsnaam'] . ', ';
		$out .= $klant['voornaam'] . ' ' . $klant['achternaam'];

		return $out;
	}

	/**
	 * Parses the any date, even those in the common d/m/Y format.
	 * This function was copied from the php documentation at www.php.net
	 */
	function parse_date($date, $format = '%d/%m/%Y') {
		if(!preg_match_all("/%([YmdHMp])([^%])*/", $format, $formatTokens, PREG_SET_ORDER))
			return false;

		$datePattern = '';
  
		foreach($formatTokens as $formatToken) {
			if(count($formatToken) > 2) {
				$delimiter = preg_quote($formatToken[2], "/");
			} else {
				$delimiter = '';
			}

			$datePattern .= "(.*)" . $delimiter;
		}
  
		if(!preg_match("/" . $datePattern . "/", $date, $dateTokens))
			return false;

		$dateSegments = array();
		
		for($i = 0; $i < count($formatTokens); $i++) {
			$dateSegments[$formatTokens[$i][1]] = $dateTokens[$i + 1];
		}

		$dateReformated = '';

		if(array_key_exists('Y', $dateSegments) && array_key_exists('m', $dateSegments) &&
				array_key_exists('d', $dateSegments)) {
			$dateReformated .= $dateSegments["Y"] . "-" . $dateSegments["m"] . "-" . $dateSegments["d"];
		} else {
			return false;
		}
   	
		if(array_key_exists('H', $dateSegments) && array_key_exists('M', $dateSegments))
			$dateReformated .= " " . $dateSegments["H"] . ":" . $dateSegments["M"];
  
		return strtotime($dateReformated);
	}
	
	/**
	 * Generated HTML code for lists
	 *
	 * @param string $name Name of the HTML list element
	 * @param array $values Values contained in the list
	 * @param string $selected Selected element (key)
	 * @param string $error An error message to display alongside the list
	 * @return string HTML for the listbox
	 */
	function html_dropdown($name, array $values, $selected = NULL, $error = NULL) {
		$out = "<select name=\"$name\">";

		foreach($values as $key => $value) {
			if($selected == $key)
				$select = "SELECTED";
			else
				$select = "";

			$out .= "<option value=\"$key\" $select>";
			$out .= htmlentities($value, ENT_COMPAT, "UTF-8");
			$out .= "</option>";
		}

		$out .= "</select>";

		if(($error) != '') {
			$out .= "<div class=\"error\">";
			$out .= htmlentities($error, ENT_COMPAT, "UTF-8");
			$out .= "</div>";
		}

		return $out;
	}

	/**
	 * Returns HTML code for a list containing all customers
	 * 
	 * @deprecated Use javascript method instead
	 *
	 * @param string $name Name of the HTML object
	 * @param array $klanten List of customers
	 * @return string HTML code
	 */
	function html_select_klant(string $name, array $klanten) {
		$_klanten = array();
		$_klanten[0] = "Maak een keuze uit het klantenbestand:";

		foreach($klanten as $klant) 
			$_klanten[$klant['klantnummer']] = fmt_klantnaam($klant);
		
		return html_dropdown($name, $_klanten, 0);
	}

	/**
	 * DEPRECATED: This function has been replaced by joinDirStrings.
	 */
	function join_dirs($dir1, $dir2)
	{
		return joinDirStrings($dir1, $dir2);
	}
	
