<?php

use Intervention\Image\ImageManager;

class ImageScanner
{

	protected $source;

	public $file;

	public $thumbsPath;    // root for thumbs

	/** @var MetaFile */
	protected $metaFile;

	protected $db;

	public function __construct(Source $source, $file, $thumbsPath, MetaFile $metaFile, DBInterface $db)
	{
		$this->source = $source;
		$this->file = $file;
		$this->thumbsPath = $thumbsPath;
		$this->metaFile = $metaFile;
		$this->db = $db;
	}

	public function __invoke($fileID)
	{
		try {
//			debug($metaFile);
			if (!$this->metaFile->has($this->file)) {
				echo 'Reading image ', $this->file, PHP_EOL;
				$start = microtime(true);
				$image = $this->readImage();
				echo 'Read in ', number_format(microtime(true) - $start, 3), PHP_EOL;
				$ip = new ImageParser($image);
				$meta = $ip->getMeta();
//				debug($meta);
//				echo 'Meta has ', sizeof($meta), PHP_EOL;
				$this->metaFile->set(basename($this->file), $meta);
				$this->saveMetaToDB($meta, $fileID);

				$destination = $this->metaFile->getDestinationFor($this->file);
				if (!file_exists($destination)) {
					$ip->saveThumbnailTo($destination);
				}
			}

		} catch (Intervention\Image\Exception\NotReadableException $e) {
			echo '** Error: ' . $e->getMessage(), PHP_EOL;
		}
	}

	public function readImage()
	{
		$manager = new ImageManager();
		$imagePromise = function () use ($manager) {
			static $path;
			static $image;
			if ($path == $this->file && $image) {
				return $image;
			}

			$path = path_plus($this->source->path, $this->file);
			$image = $manager->make($path);
			return $image;
		};
		$image = $imagePromise();
		return $image;
	}

	public function saveMetaToDB($meta, $fileID)
	{
		foreach ($meta as $key => $val) {
			$encoded = is_scalar($val) ? $val : json_encode($val);
			/** @var SQLite3Result $row */
			$row = MetaEntry::insert($this->db, [
				'id_file' => $fileID,
				'name' => $key,
				'value' => $encoded,
			]);
//			echo $row->numColumns(), PHP_EOL;
		}
	}

}
