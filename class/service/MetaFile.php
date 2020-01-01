<?php

class MetaFile
{

	public $thumbsPath;

	/**
	 * @var string this is meta.json file for the whole folder
	 */
	public $jsonFile;

	public $json;

	public function __construct($thumbsPath, $fileName)
	{
		$this->thumbsPath = $thumbsPath;	// root for this Source

		$dirName = dirname($fileName);
		$fileName = $dirName . '/meta.json';
		$this->jsonFile = $this->getDestinationFor($fileName);
//		$this->log('jsonFile', $jsonFile);
		echo 'Reading ', $this->jsonFile, PHP_EOL;
//		$start = microtime(true);
		$this->json = $this->getCachedJSONFrom($this->jsonFile);
//		echo 'Read in ', number_format(microtime(true) - $start, 3), PHP_EOL;
	}

	/**
	 * /data/thumbs/PrefixMerged/folder/path/file.jpg
	 * @param string $suffix = $this->shortened most of the time
	 * @return bool|string
	 */
	public function getDestinationFor($suffix)
	{
		$destination = cap($this->thumbsPath) . $suffix;
		@mkdir(dirname($destination), 0777, true);
		$real = realpath($destination);    // after mkdir()
		if ($real) {
			$destination = $real;
		}
		return $destination;
	}

	public function getCachedJSONFrom($jsonFile)
	{
		static $jsonPath;
		static $jsonData = [];

		if ($jsonFile == $jsonPath && $jsonData) {
			return $jsonData;
		}

		if (file_exists($jsonFile)) {
			$fileContent = file_get_contents($jsonFile);
			$jsonData = json_decode($fileContent);
		} else {
			$jsonData = [];
		}

		$jsonPath = $jsonFile;
		return (object)$jsonData;
	}

	public function has($file)
	{
		$baseName = basename($file);
		echo 'isset(', $baseName, '): ', isset($json->$baseName), PHP_EOL;
		if (isset($this->json->$baseName)) {
			return true;
		}
		return false;
	}

	public function set($fileName, $meta)
	{
		$baseName = basename($fileName);
//		echo 'baseName: ', $baseName, PHP_EOL;
		$this->json->$baseName = $meta;
//		echo 'Entries in meta.json: ', sizeof((array)$this->json), PHP_EOL;
//		debug(array_keys((array)$this->json));
//		debug($this->json);
		$jsonString = json_encode($this->json, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR);
//		echo $jsonString, PHP_EOL;
//		echo 'Saving ', $this->jsonFile, ' [', strlen($jsonString), ']', PHP_EOL;
		file_put_contents($this->jsonFile, $jsonString);
	}

}
