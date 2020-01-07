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
				$path = path_plus($this->source->path, $this->file->getPath());
				if ($this->file->isImage()) {
					$ip = ImageParser::fromFile($path);
					$meta = $ip->getMeta();
					//	debug($meta);
					//	echo 'Meta has ', sizeof($meta), PHP_EOL;
//					$this->metaFile->set(basename($this->file->getPath()), $meta);
					$this->saveMetaToDB($meta, $this->file->id);
				} elseif ($this->file->isVideo()) {
					// TODO process video metadata
					$vp = VideoParser::fromFile($path);
					$meta = $vp->getMeta();
					$this->saveMetaToDB($meta, $this->file->id);
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

	public function saveMetaToDB($meta, $fileID)
	{
		$this->db->transaction();
		foreach ($meta as $key => $val) {
			$encoded = is_scalar($val) ? $val : json_encode($val, JSON_THROW_ON_ERROR);
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
