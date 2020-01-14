<?php

class GradientParser extends AppController
{

	/** @var DBInterface */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function __invoke()
	{
		$source = Source::findByID($this->db, 4);
		$files = $source->getFiles([
			'colors' => null,
		]);

		foreach ($files as $i => $file) {
			$sourceAndFile = $file->getDestination();
			$this->log(count($files) - $i, $sourceAndFile);
			$ip = ImageParser::fromFile($sourceAndFile);
			$quadrants = $ip->getQuadrantColorsAsHex();
			$this->log($quadrants);

			$file->update([
				'colors' => json_encode($quadrants, JSON_THROW_ON_ERROR),
			]);
		}
	}

}
