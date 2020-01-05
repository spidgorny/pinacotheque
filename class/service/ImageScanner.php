<?php

use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;

class ImageScanner
{

	protected $source;

	/**
	 * @var MetaForSQL
	 */
	public $file;

	/**
	 * @var DBInterface
	 */
	protected $db;

	public function __construct(MetaForSQL $file, DBInterface $db)
	{
		$this->file = $file;
		$this->source = $file->getSource();
		$this->db = $db;
	}

	public function __invoke()
	{
		try {
//			debug($metaFile);
			if (!$this->file->hasMeta()) {
				if ($this->file->isImage()) {
					$image = $this->readImage();
					$ip = new ImageParser($image);
					$meta = $ip->getMeta();
					//				debug($meta);
					//				echo 'Meta has ', sizeof($meta), PHP_EOL;
//					$this->metaFile->set(basename($this->file->getPath()), $meta);
					$this->saveMetaToDB($meta, $this->file->id);
				} elseif ($this->file->isVideo()) {
					// TODO process video metadata
				}
			}
			// thumbnail
			$destination = $this->file->getDestination();
			if (!file_exists($destination)) {
				$thumb = new Thumb($this->file);
				try {
					$thumb->getThumb();    // make it if doesn't exist
				} catch (NotReadableException $e) {
					$content[] = $e;
				}
			}
		} catch (Intervention\Image\Exception\NotReadableException $e) {
			echo '** Error: ' . $e->getMessage(), PHP_EOL;
		}
	}

	public function readImage()
	{
		$manager = new ImageManager();
		$path = path_plus($this->source->path, $this->file->getPath());
		$image = $manager->make($path);
		return $image;
	}

	public function saveMetaToDB($meta, $fileID)
	{
		$this->db->transaction();
		foreach ($meta as $key => $val) {
			$encoded = is_scalar($val) ? $val : json_encode($val);
			try {
				/** @var SQLite3Result $row */
				$row = MetaEntry::insert($this->db, [
					'id_file' => $fileID,
					'name' => $key,
					'value' => $encoded,
				]);
			} catch (PDOException $e) {
				// some strings can't be saved in DB
			}
//			echo $row->numColumns(), PHP_EOL;
		}
		$this->db->commit();
	}

}
