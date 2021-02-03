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
	protected DBInterface $db;

	public function __construct(IMetaData $file, DBInterface $db)
	{
		$this->file = $file;
		$this->db = $db;
	}

	public function __invoke($force = false)
	{
		$meta = null;
		$thumb = null;
		try {
			if (!$force && $this->file->hasMeta()) {
				$this->log('Meta', 'exists', count($this->file->getMetaData()));
			} else {
				$meta = $this->fetchExif();
			}

			$destination = $this->file->getDestination();
			if (file_exists($destination)) {
				$this->log('Thumb', 'exists', new Bytes(filesize($this->file->getDestination())));
				$thumb = $this->file->getDestination();
			} else {
				$thumb = $this->fetchThumbnail();
			}
		} catch (Intervention\Image\Exception\NotReadableException $e) {
			$this->log('** Error:', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
		} catch (ImageException $e) {
			$this->log('** Error:', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
		}
		return [$meta, $thumb];
	}

	public function fetchExif()
	{
		try {
			$path = $this->file->getFullPath();
			$this->log('Type', $this->file->isVideo() ? 'Video' : 'Image');
			$ok = false;
			if ($this->file->isImage()) {
				$ip = ImageParser::fromFile($path);
				$meta = (array)$ip->getMeta();
				if ($meta) {
					$ok = $this->saveMetaToDB($meta, $this->file->id);
					$this->log('saveMeta', $ok ? 'OK: ' . count($this->file->getMetaData()) : '*** FAIL ***');
				}
			} elseif ($this->file->isVideo()) {
				$vp = VideoParser::fromFile($path);
				$meta = (array)$vp->getMeta();
				if ($meta) {
					$ok = $this->saveMetaToDB($meta, $this->file->id);
					$this->log('saveMeta', $ok ? 'OK: ' . count($this->file->getMetaData()) : '*** FAIL ***');
				}
			} else {
				throw new Exception('Unknown file type: ' . $this->file->getExt());
			}
		} catch (Exception $e) {
			$this->log('ERROR', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
			$this->log($this->file->getFullPath());
			$this->file->update([
				'meta_timestamp' => new SQLNow(),
				'meta_error' => $e->getMessage(),
			]);
			$meta = null;
		}
		return $meta;
	}

	public function saveMetaToDB(array $meta, int $fileID)
	{
		$this->db->transaction();
		$this->file->update([
			'meta_timestamp' => new SQLNow(),
			'meta_error' => null,
		]);
		foreach ($meta as $key => $val) {
			try {
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
					//				llog($e->getMessage());
					if (str_contains($e->getMessage(), '1062 Duplicate entry')) {
						$row = MetaEntry::findOne($this->db, [
							'id_file' => $fileID,
							'name' => $key,
						]);
						$row->update([
							'value' => $encoded,
						]);
					}
				}
			} catch (JsonException $e) {
				// just ignore
			}
//			echo $row->numColumns(), PHP_EOL;
		}
		return $this->db->commit();
	}

	public function fetchThumbnail()
	{
		$thumbPath = null;
		$thumb = new Thumb($this->file);
		try {
			$thumbPath = $thumb->getThumb();    // make it if doesn't exist
			$this->log('Thumb', 'OK', new Bytes(@filesize($this->file->getDestination())));
			$this->log('Thumb->log', $thumb->log);
		} catch (NotReadableException $e) {
			$content[] = $e;
			$this->log('Thumb', '*** FAIL ***');
			$this->log('Thumb->log', $thumb->log);
		}
		return $thumbPath;
	}

}
