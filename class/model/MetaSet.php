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
	 * @var MetaArray[]
	 */
	protected $data;

	public function __construct(Filesystem $filesystem)
	{
		error_log(__METHOD__);
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

			try {
				$images = $this->readMeta($file);
				$dirName = dirname($file['path']);
				foreach ($images as &$meta) {
					$meta['_path_'] = $dirName;
					$meta = new Meta($meta);
				}
				$models[$dirName] = new MetaArray($images);
			} catch (Exception $e) {};
		}

		return $models;
	}

	public function readMeta(array $file)
	{
		$json = [];
		$fullPath = $this->prefix . '/' . $file['path'];
		$fileContent = file_get_contents($fullPath);
		if ($fileContent) {
			$json = json_decode($fileContent, true);
		}
		if (!$json) {
			throw new Exception('File ['.$fullPath.'] cannot be parsed as JSON');
		}
		return $json;
	}

	public function size()
	{
		$sum = 0;
		foreach ($this->data as $set) {
			$sum += $set->getSize();
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
		$onlyMeta = array_map(function (MetaArray $ma) {
			return $ma->getAll();
		}, $this->data);
		return call_user_func_array('array_merge', $onlyMeta);
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

	/**
	 * @param callable $predicate
	 * @return Meta[]
	 */
	public function filterMA(callable $predicate)
	{
		return array_filter($this->data, $predicate);
	}

}
