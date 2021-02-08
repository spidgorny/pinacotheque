<?php

class ScanDir extends AppController
{

    /**
     * @var DBInterface
     */
    protected DBInterface $db;

    public function __construct(DBInterface $db)
    {
    	parent::__construct();
        $this->db = $db;
    }

    public function __invoke()
    {
        $request = Request::getInstance();
//        $dir = $request->importCLIparams()->get('dir');
//        var_dump($request->getAll());
        $sourceID = $_SERVER['argv'][2];
        if (!$sourceID) {
        	throw new Exception('Usage: php index.php ScanDir <source-id>');
		}
		$source = \Source::findByID($this->db, $sourceID);
        if (!$source) {
        	throw new Exception('Source '.$sourceID.' not found');
		}

		$dir = $source->path;
		echo 'dir: ', $dir, PHP_EOL;

        $scanner = new \App\Service\ScanDir($this->db, $source);
        $scanner();
    }

}
