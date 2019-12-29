<?php

class ScanDir extends AppController
{

    /**
     * @var DBLayerSQLite
     */
    protected $db;

    public function __construct(DBLayerSQLite $db)
    {
    	parent::__construct();
        $this->db = $db;
    }

    public function __invoke()
    {
        $request = Request::getInstance();
//        $dir = $request->importCLIparams()->get('dir');
//        var_dump($request->getAll());
        $dir = $_SERVER['argv'][2];
        echo 'dir: ', $dir, PHP_EOL;

        $scanner = new \App\Service\ScanDir($this->db, $dir);
        $scanner();
    }

}