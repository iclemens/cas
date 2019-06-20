<?php
	/**
	 * Filters a list of possible CAS locations
	 *
	 * @param array $locations List of possible locations
	 * @param string $type Type type to keep (either shared or local)
	 * @return array List of CAS locations
	 */
	function filterCASLocations($locations, $type)
	{
		$filtered = array();		

		foreach($locations as $location) {
			$versionInfo = getCASVersion($location);
			
			if($versionInfo != false && ($versionInfo['module'] == '' || $versionInfo['module'] == $type))
				$filtered[] = $location;
		}
		
		return $filtered;
	}

	
	/**
	 * Gets a list of possible locations for the global
	 * CAS installation.
	 *
	 * @return array List of global CAS installations
	 */
	function suggestSharedLocation()
	{
		$global_locations = array(
			'/usr/local/share/cas',
			realpath(getcwd() . '/../../shared')
			);
		
		return filterCASLocations($global_locations, 'shared');
	}
	
	
	/**
	 * Gets a list of possible locations for the local
	 * CAS installation.
	 *
	 * @return array List of local CAS installations
	 */
	function suggestLocalLocation()
	{
		$local_locations = array(
			realpath(getcwd() . '/..'),
			realpath(getcwd() . '/../../default'),
			realpath(getcwd() . '/../../generic'),
			'~/cas'
			);
		
		return filterCASLocations($local_locations, 'local');
	}

	
	/**
	 * Determines whether CAS is present in a given directory and, if possible,
	 * reports its version. If no version is reported the existance of an actual
	 * CAS installation cannot be guaranteed.
	 *
	 * @param string $directory Name of the directory to check
	 * @return array Array containing 'version', 'branch' and 'module' (local/shared)
	 */
	function getCASVersion($directory) {
		if(!file_exists($directory))
			return false;

		$xmlFile = $directory . '/version.xml';

		try {
			$xml = @new SimpleXMLElement(@file_get_contents($xmlFile));

			$versionInfo = array('version' => (string) $xml['version'], 'branch' => (string) $xml['branch'], 'module' => (string) $xml['module']);
		} catch(Exception $e) {
			return array('version' => null, 'branch' => '');
		}

		return $versionInfo;
	}


	/**
	 * Takes version information as reported by getCASVersion and creates a string
	 * which reflects that information in a more human readable fashion.
	 *
	 * @param array $version Version information as reported by getCASVersion()
	 * @return string Human readable form of version information
	 */
	function versionString($version) {
		if($version['version'] == null)
			return 'CAS (Onbekende versie)';

		if($version['branch'] == '')
			return 'CAS (versie ' . strval($version['version']) . ')';

		return 'CAS ' . $version['branch'] . ' (versie ' . strval($version['version']) . ')';
	}
