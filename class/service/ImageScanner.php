<?php

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
		try {
//			debug($metaFile);
			if (!$this->file->hasMeta()) {
				$path = $this->file->getFullPath();
				$this->log('Type', $this->file->isVideo() ? 'Video' : 'Image?');
				$ok = false;
				if ($this->file->isImage()) {
					$ip = ImageParser::fromFile($path);
					$meta = $ip->getMeta();
					$ok = $this->saveMetaToDB($meta, $this->file->id);
				} elseif ($this->file->isVideo()) {
					$vp = VideoParser::fromFile($path);
					$meta = $vp->getMeta();
					$ok = $this->saveMetaToDB($meta, $this->file->id);
				}
				$this->log(TAB . 'Meta', $ok ? 'OK' : '*** FAIL ***');
			} else {
				$this->log(TAB . 'Meta', 'exists');
			}

			// thumbnail
			$destination = $this->file->getDestination();
			if (!file_exists($destination)) {
				$thumb = new Thumb($this->file);
				try {
					$thumb->getThumb();    // make it if doesn't exist
					$this->log(TAB . 'Thumb', 'OK');
					$this->log('Thumb->log', $thumb->log);
				} catch (NotReadableException $e) {
					$content[] = $e;
					$this->log(TAB . 'Thumb', '*** FAIL ***');
					$this->log('Thumb->log', $thumb->log);
				}
			} else {
				$this->log(TAB . 'Thumb', 'exists');
			}
		} catch (Intervention\Image\Exception\NotReadableException $e) {
			echo '** Error: ' . $e->getMessage(), PHP_EOL;
		}
	}

	public function saveMetaToDB($meta, $fileID)
	{
		$this->db->transaction();
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

}
