<?php

class About	extends AppController
{

	/** @var DBInterface */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		/** @var Source[] $sources */
		$sources = Source::findAll($this->db, []);
		foreach ($sources as $source) {
			$content[] = '<p>'.$source->name . ' [' . $source->getFilesCount() . ']</p>';
		}

		$Parsedown = new Parsedown();
		$md = file_get_contents(__DIR__ . '/../../README.md');
		$content[] = '<div class="content">';
		$content[] = $Parsedown->text($md);
		$content[] = '</div>';
		return $this->template($content);
	}

}
