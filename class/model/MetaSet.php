<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Represents all meta.json files in a project
 */
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
		$this->prefix = $adapter->getPathPrefix();

		$this->data = $this->init();
	}

	public function init()
	{
		$files = $this->fileSystem->listContents('', true);
		$models = [];

		foreach ($files as &$file) {
//			$file = new File($file);
			$baseName = basename($file['path']);
			if ($baseName != 'meta.json') {
				continue;
			}

			$images = $this->readMeta($file);
			$dirName = dirname($file['path']);
			foreach ($images as &$meta) {
				$meta['_path_'] = $dirName;
				$meta = new Meta($meta);
			}
			$models[$dirName] = $images;
		}

		return $models;
	}

	public function readMeta(array $file)
	{
		$json = [];
		$fileContent = file_get_contents($this->prefix.'/'.$file['path']);
		if ($fileContent) {
			$json = json_decode($fileContent, true);
		}
		if (!$json) {
			//debug($file);
		}
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

	public function get()
	{
		return $this->data;
	}

	/**
	 * @return Meta[]
	 */
	public function getLinear()
	{
		return call_user_func_array('array_merge', $this->data);
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
				$key = ifsetor($image->$field);
				$result[$key] = ifsetor($result[$key], []);
				$result[$key][] = $image;
			}
		}
		return $result;
	}

	/**
	 * @param callable $predicate
	 * @return Meta[]
	 */
	public function filter(callable $predicate)
	{
		return array_filter($this->getLinear(), $predicate);
	}

}
