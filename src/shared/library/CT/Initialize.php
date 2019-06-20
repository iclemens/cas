<?php
	/**
	 * Database update functionality for CAS
 	 *
 	 * PHP version 5
 	 *
 	 * @author     Ivar Clemens <post@ivarclemens.nl>
 	 * @copyright  2008 Ivar Clemens
	 * @package    boekhouding
	 */

	Zend_Loader::loadClass('CT_Db_Versie');

	abstract class CT_Initialize
	{
		/**
		 * Location of the installation scripts.
		 *
		 * @var string installLocation
		 */
		protected $installLocation;


		/**
		 * Location of the update scripts.
		 *
		 * @var string updateLocation
		 */
		protected $updateLocation;


		/**
		 * Initializes the class
		 */
		public function __construct($location)
		{
			$this->installLocation = getResourceLocation($location);
			$this->updateLocation = getResourceLocation($location);			
		}


		/**
		 * Retreives all available patch files.
		 *
		 * @return array An array containing the filesnames of the found patches
		 */
		protected function listPatchFiles()
		{
			$dir = opendir($this->updateLocation);
			$patches = array();

			if($dir == 0)
				throw new Exception('Kan de patch locatie (' . $this->updateLocation . ') niet openen.');

			while($dentry = readdir($dir)) {
				$match = preg_match('/^([0-9]*)-([0-9]*)-([a-z]*).sql$/', $dentry, $matches);

				if($match)
					$patches[] = $dentry;
			}

			closedir($dir);
			sort($patches);

			return $patches;
		}


		/**
		 * Returns the details of a given patch.
		 *
		 * @param $patch string The (file)name of the patch
		 *
		 * @return array Details of the patch (description, revision, etc.)
		 */
		protected function getPatchDetails($patch)
		{
			if($patch == '')
				throw new Exception('No patch specified.');
			
			$match = preg_match('/^([0-9]*)-([0-9]*)-([a-z]*).sql$/', $patch, $matches);

			if(!$match)
				throw new Exception('Invalid patch (' . $patch . ')');

			$datum = substr($matches[1], 0, 4) . '-' . substr($matches[1], 4, 2) . '-' . substr($matches[1], 6, 2);
			$details = array('date' => $datum, 'name' => $matches[1] . '-' . $matches[2], 'table' => $matches[3], 'filename' => $matches[0]);

			$file = fopen(joinDirStrings($this->updateLocation, $patch), 'r');
			$line = fgets($file);
			fclose($file);			

			if(substr($line, 0, 2) == '--')
				$details['description'] = trim(substr($line, 2));
			else
				$details['description'] = 'Update voor tabel ' . $details['table'];

			return $details;
		}
 

		/**
		 * Retreives a list of all patches.
		 * 
		 * @param string $after Limit results to patches after the one specified.
		 * 
		 * @return array List of patches found.
		 */
		public function listAvailableUpdates($after = null)
		{
			$allPatches = $this->listPatchFiles();
			$filteredList = array();

			foreach($allPatches as $key => $value) {
				$details = $this->getPatchDetails($value);
				
				if($after == null || strcmp($after, $details['name']) < 0)
					$filteredList[$key] = $details;
			}

			return $filteredList;

		}


		public abstract function createDatabase($creds);
		public abstract function createTables($creds);
		public abstract function applySinglePatch($patchName);
		
		
		/**
		 * Applies all waiting patches.
		 */
		public function applyAllPatches()
		{
			$currentVersion = CT_Db_Versie::getVersion();
			$patchList = $this->listAvailableUpdates($currentVersion);

			foreach($patchList as $patch)
				$this->applySinglePatch($patch['filename']);			
		}

		
		/**
		 * Creates a database specific instance of the CT_Initialize class.
		 */
		public static function factory($dbtype, $location = null)
		{
			switch($dbtype) {
				case 'pdo_mysql':
					$className = 'CT_Initialize_MySQL';
					break;
				default:
					throw new Exception('Uw database wordt niet ondersteund.');
			}
			
			if($location == null)
				$location = 'sql';
			
			Zend_Loader::loadClass($className);
			return new $className($location);
		}
	}
