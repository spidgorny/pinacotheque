<?php

use Intervention\Image\Exception\ImageException;
use Intervention\Image\Exception\NotReadableException;

class ImageScanner
{

	use LogTrait;

	/**
	 * @var MetaForSQL
	 */
	public $file;

	/**
	 * @var DBInterface
	 */
	protected $db;

	public function __construct(IMetaData $file, DBInterface $db)
	{
		$this->file = $file;
		$this->db = $db;
	}

	public function __invoke()
	{
		$meta = null;
		$thumb = null;
		try {
			$meta = $this->fetchExif();
			$thumb = $this->fetchThumbnail();
		} catch (Intervention\Image\Exception\NotReadableException $e) {
			echo '** Error: ' . $e->getMessage(), PHP_EOL;
		} catch (ImageException $e) {
			echo '** Error: ' . $e->getMessage(), PHP_EOL;
		}
		return [$meta, $thumb];
	}

	public function fetchExif()
	{
		if ($this->file->hasMeta()) {
			$this->log('Meta', 'exists', count($this->file->getMetaData()));
		}

		try {
			$path = $this->file->getFullPath();
			$this->log('Type', $this->file->isVideo() ? 'Video' : 'Image');
			$ok = false;
			if ($this->file->isImage()) {
				$ip = ImageParser::fromFile($path);
				$meta = (array)$ip->getMeta();
				if ($meta) {
					$ok = $this->saveMetaToDB($meta, $this->file->id);
				}
			} elseif ($this->file->isVideo()) {
				$vp = VideoParser::fromFile($path);
				$meta = (array)$vp->getMeta();
				$ok = $this->saveMetaToDB($meta, $this->file->id);
			}
			$this->log('Meta', $ok ? 'OK: ' . count($this->file->getMetaData()) : '*** FAIL ***');
		} catch (Exception $e) {
			$this->file->update([
				'meta_timestamp' => new SQLNow(),
				'meta_error' => $e->getMessage(),
			]);
		}
		return $meta;
	}

	public function saveMetaToDB(array $meta, $fileID)
	{
		$this->db->transaction();
		$this->file->update([
			'meta_timestamp' => new SQLNow(),
			'meta_error' => null,
		]);
		foreach ($meta as $key => $val) {
			try {
				$encoded = is_scalar($val) ? $val : json_encode($val, JSON_THROW_ON_ERROR);
				/** @var SQLite3Result $row */
				$row = MetaEntry::insert($this->db, [
					'id_file' => $fileID,
					'name' => $key,
					'value' => $encoded,
				]);
			} catch (PDOException $e) {
				// some strings can't be saved in DB
			} catch (JsonException $e) {
				// just ignore
			}
//			echo $row->numColumns(), PHP_EOL;
		}
		return $this->db->commit();
	}

	public function fetchThumbnail()
	{
		$destination = $this->file->getDestination();
		if (file_exists($destination)) {
			$this->log('Thumb', 'exists', new Bytes(filesize($this->file->getDestination())));
			return $this->file->getDestination();
		}

		$thumbPath = null;
		$thumb = new Thumb($this->file);
		try {
			$thumpPath = $thumb->getThumb();    // make it if doesn't exist
			$this->log('Thumb', 'OK', new Bytes(filesize($this->file->getDestination())));
			$this->log('Thumb->log', $thumb->log);
		} catch (NotReadableException $e) {
			$content[] = $e;
			$this->log('Thumb', '*** FAIL ***');
			$this->log('Thumb->log', $thumb->log);
		}
		return $thumpPath;
	}

}
