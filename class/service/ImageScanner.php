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

			$tg = new ThumbGen($this->file);
			$thumb = $tg->generate();
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
			$this->log('Type', $this->file->isVideo() ? 'Video' : 'Image');
			$pf = ParserFactory::getInstance($this->file);
			$parser = $pf->getParser();
			$meta = (array)$parser->getMeta();
			if ($meta) {
				$ok = $this->saveMetaToDB($meta);
				$this->log('saveMeta', $ok ? 'OK: ' . count($this->file->getMetaData()) : '*** FAIL ***');
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

	/// TODO: extract to separate service
	public function saveMetaToDB(array $meta)
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
						'id_file' => $this->file->id,
						'name' => $key,
						'value' => $encoded,
					]);
				} catch (PDOException $e) {
					// some strings can't be saved in DB
					//				llog($e->getMessage());
					if (str_contains($e->getMessage(), '1062 Duplicate entry')) {
						$row = MetaEntry::findOne($this->db, [
							'id_file' => $this->file->id,
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

}
