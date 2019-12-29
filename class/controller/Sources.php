<?php

class Sources extends AppController
{

	/**
	 * @var DBLayerSQLite
	 */
	protected $db;

	protected $prefixURL;

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
		$this->prefixURL = ShowThumb::class.'?file=';
	}

	public function __invoke()
	{
		list('min' => $min, 'max' => $max) = $this->db->fetchOneSelectQuery('files', [], '', 'min(timestamp) as min, max(timestamp) as max');
//        $content[] = 'query: ' . $this->db->getLastQuery() . BR;
//        $content[] = 'min: ' . $min . BR;
//        $content[] = 'max: ' . $max . BR;

		$timelineService = new TimelineServiceForSQL($this->prefixURL);
		$timelineService->min = new Date();
		$timelineService->min->setTimestamp($min);
		$timelineService->max = new Date();
		$timelineService->max->setTimestamp($max);

//		$content[] = 'min: ' . $timelineService->min->format('Y-m-d') . BR;
//		$content[] = 'max: ' . $timelineService->max->format('Y-m-d') . BR;

		$YM = "strftime('%Y-%m', datetime(timestamp, 'unixepoch'))";
		$imageFiles = $this->db->fetchAllSelectQuery('files', [
			'type' => 'file',
		], "GROUP BY ".$YM.
			' ORDER BY '.$YM,
			'*, '.$YM.' as YM, count(*) as count'
		);
//		$content[] = new slTable($imageFiles);
		$imageFiles = ArrayPlus::create($imageFiles);

		$byMonth = $imageFiles->reindex(static function ($key, array $row) {
			return $row['YM'];
		});

		$byMonth = $byMonth->map(static function ($el) {
			$row0 = $el[0];
			$meta = new MetaForSQL($row0 + [
					'_path_' => dirname($row0['path']),
					'FileName' => basename($row0['path']),
				]);
//			debug($meta);
			$firstMetaRestCount = [$meta];
			$firstMetaRestCount += array_fill(1, $row0['count'], null);
			return $firstMetaRestCount;
		});

		$table = $timelineService->renderTable($byMonth);

		$content[] = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);

		return $this->template($content, [
			'head' => '<link rel="stylesheet" href="www/css/pina.css" />',
		]);
	}

}
