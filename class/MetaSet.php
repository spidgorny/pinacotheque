<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class MetaSet
{

	/**
	 * @var Filesystem
	 */
	protected $fileSystem;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var array
	 */
	protected $data;

	public function __construct(Filesystem $filesystem)
	{
		$this->fileSystem = $filesystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = __DIR__ . '/../data/thumbs';
		$adapter->setPathPrefix($this->prefix);

		$this->data = $this->init();
	}

	public function init()
	{
		$files = $this->fileSystem->listContents('', true);
		$models = [];

		foreach ($files as $file) {
			$baseName = basename($file['path']);
			if ($baseName != 'meta.json') {
				continue;
			}

			$images = $this->readMeta($file);
			$dirName = dirname($file['path']);
			$models[$dirName] = $images;
		}

		return $models;
	}

	public function readMeta(array $file)
	{
		$fileContent = file_get_contents($this->prefix.'/'.$file['path']);
		$json = json_decode($fileContent, true);
		return $json;
	}

	public function size()
	{
		$sum = 0;
		foreach ($this->data as $set) {
			$sum += sizeof($set);
		}
		return $sum;
	}

	/**
	 * FileDateTime
	 * @param $field
	 * @return array
	 */
	public function groupBy($field)
	{
		$result = [];
		foreach ($this->data as $set) {
			foreach ($set as $image) {
				$key = ifsetor($image[$field]);
				$result[$key] = ifsetor($result[$key], []);
				$result[$key][] = $image;
			}
		}
		return $result;
	}

}
