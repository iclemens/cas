<?php
	/**
	 * Get contents of configuration in string.
	 *
	 * @param String $globalPath Location of the global project-cas files
	 * @param String $localPath Location of the local project-cas files
	 * @param String $type Type of CAS installation (production or development)
	 * @return String Configuration file in string form
	 */
	function getConfigString($globalPath, $localPath, $type)
	{
		$cfg = '<?php' . "\n";
		$cfg .= '$dataPath = "' . addSlashes($localPath) . '";' . "\n";
		$cfg .= '$globalDataPath = "' . addSlashes($globalPath) . '";' . "\n";
		$cfg .= '$configType = "' . addSlashes($type) . '";' . "\n";

		return $cfg;
	}


	/**
	 * Writes location of local and global CAS directories to the config file.
	 *
	 * @throw Exception In case the configuration file already exists.
	 *
	 * @param String $globalPath Location of the global project-cas files
	 * @param String $localPath Location of the local project-cas files
	 * @param String $type Type of CAS installation (production or development)
	 */
	function saveConfig($globalPath, $localPath, $type)
	{
		$filename = getcwd() . '/config.php';

		if(file_exists($filename))
			throw new Exception('Configuration file already exists.');

		$file = @fopen($filename, 'w');

		$cfg = getConfigString($globalPath, $localPath, $type);

		if(!$file)
			throw new Exception('Kan het configuratie bestand niet opslaan, zijn de rechten correct ingesteld? ' . 
				'Indien u de rechten niet kunt aanpassen, maak dan handmatig het config.php bestand aan. ' .
				'Het bestand: <pre>' . htmlspecialchars($filename) . '</pre> moet de volgende inhoud hebben: <pre>' .
				htmlspecialchars($cfg) . '</pre> Als u het bestand gemaakt heeft, ga dan verder met de <a href="setup.php">volgende stap</a>.');

		fputs($file, $cfg);

		fclose($file);
	}	

	
	/**
	 * Loads the configuration from an array (e.g. $_POST) and
	 * saves it using saveConfig().
	 * 
	 * @throws Exception on failure
	 * 
	 * @param array $source The source array (e.g. $_POST).
	 * @return boolean True on success, exception is thrown otherwise.
	 */
	function saveConfigFromArray($source)
	{
		$globalPath = getPathFromArray($source, 'global');
		$localPath  = getPathFromArray($source, 'local');
		saveConfig($globalPath, $localPath, 'production');
		
		return true;
	}

	
	/**
	 * Retreives the path of the global/local CAS installation from $_GET or $_POST arrays.
	 *
	 * @throws Exception If no path is given
	 *
	 * @param array $fieldValues An array containing ${type}Opt and ${type}Path (e.g. $_POST)
	 * @param string $type Either 'global' or 'local', reflecting the nature of the location
	 * @return string Path to either global or local data directory
	 */
	function getPathFromArray($fieldValues, $type)
	{
		if(!array_key_exists($type . 'Opt', $fieldValues) || !array_key_exists($type . 'Path', $fieldValues))
			throw new Exception('Een van de lokaties (' . $type . ') is nog onbekend.');

		if($fieldValues[$type . 'Opt'] == 'custom' || $fieldValues[$type . 'Opt'] == '')
			$path = $fieldValues[$type . 'Path'];
		else
			$path = $fieldValues[$type . 'Opt'];

		if(!file_exists($path))
			throw new Exception('De bij "' . $type . '" opgegeven lokatie is ongeldig');

		return $path;
	}	
